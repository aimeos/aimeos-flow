<?php

/**
 * @license LGPLv3, http://www.gnu.org/copyleft/lgpl.html
 * @copyright Aimeos (aimeos.org), 2015-2016
 * @package flow
 * @subpackage Controller
 */


namespace Aimeos\Shop\Controller;

use TYPO3\Flow\Annotations as Flow;


/**
 * Controller for ExtJS adminisration interface.
 * @package flow
 * @subpackage Controller
 */
class ExtadmController extends \TYPO3\Flow\Mvc\Controller\ActionController
{
	/**
	 * @var \Aimeos\Shop\Base\Aimeos
	 * @Flow\Inject
	 */
	protected $aimeos;

	/**
	 * @var \Aimeos\Shop\Base\Context
	 * @Flow\Inject
	 */
	protected $context;


	/**
	 * Creates the initial HTML view for the admin interface.
	 *
	 * @param string $site Shop site code
	 * @param string $lang ISO language code
	 * @param integer $tab Number of the current active tab
	 */
	public function indexAction( $site = 'default', $lang = 'en', $tab = 0 )
	{
		$context = $this->context->get( $this->request );
		$context = $this->setLocale( $context, $site, $lang );

		$aimeos = $this->aimeos->get();
		$cntlPaths = $aimeos->getCustomPaths( 'controller/extjs' );
		$controller = new \Aimeos\Controller\ExtJS\JsonRpc( $context, $cntlPaths );
		$cssFiles = array();

		foreach( $aimeos->getCustomPaths( 'admin/extjs' ) as $base => $paths )
		{
			foreach( $paths as $path )
			{
				$jsbAbsPath = $base . '/' . $path;

				if( !is_file( $jsbAbsPath ) ) {
					throw new Exception( sprintf( 'JSB2 file "%1$s" not found', $jsbAbsPath ) );
				}

				$jsb2 = new \Aimeos\MW\Jsb2\Standard( $jsbAbsPath, dirname( $path ) );
				$cssFiles = array_merge( $cssFiles, $jsb2->getUrls( 'css', '' ) );
			}
		}

		$params = array( 'site' => '{site}', 'lang' => '{lang}', 'tab' => '{tab}' );
		$adminUrl = $this->uriBuilder->uriFor( 'index', $params, 'admin' );
		$jsonUrl = $this->uriBuilder->uriFor( 'do' );

		$vars = array(
			'lang' => $lang,
			'cssFiles' => $cssFiles,
			'languages' => $this->getJsonLanguages(),
			'config' => $this->getJsonClientConfig( $context ),
			'site' => $this->getJsonSiteItem( $context, $site ),
			'i18nContent' => $this->getJsonClientI18n( $aimeos->getI18nPaths(), $lang ),
			'searchSchemas' => $controller->getJsonSearchSchemas(),
			'itemSchemas' => $controller->getJsonItemSchemas(),
			'smd' => $controller->getJsonSmd( $jsonUrl ),
			'urlTemplate' => urldecode( $adminUrl ),
			'uploaddir' => $context->getConfig()->get( 'flow/uploaddir', '/.' ),
			'version' => $this->aimeos->getVersion(),
			'activeTab' => $tab,
		);

		$this->view->assignMultiple( $vars );
	}


	/**
	 * Single entry point for all JSON admin requests.
	 */
	public function doAction()
	{
		$context = $this->context->get( $this->request );
		$context = $this->setLocale( $context );
		$cntlPaths = $this->aimeos->get()->getCustomPaths( 'controller/extjs' );

		$controller = new \Aimeos\Controller\ExtJS\JsonRpc( $context, $cntlPaths );

		return $controller->process( $this->request->getArguments(), 'php://input' );
	}


	/**
	 * Returns the JS file content
	 *
	 * @return \TYPO3\Flow\Http\Response Response object
	 */
	public function fileAction()
	{
		$jsFiles = array();
		$aimeos = $this->aimeos->get();

		foreach( $aimeos->getCustomPaths( 'admin/extjs' ) as $base => $paths )
		{
			foreach( $paths as $path )
			{
				$jsbAbsPath = $base . '/' . $path;
				$jsb2 = new \Aimeos\MW\Jsb2\Standard( $jsbAbsPath, dirname( $jsbAbsPath ) );
				$jsFiles = array_merge( $jsFiles, $jsb2->getFiles( 'js' ) );
			}
		}

		foreach( $jsFiles as $file )
		{
			if( ( $content = file_get_contents( $file ) ) !== false ) {
				$this->response->appendContent( $content );
			}
		}

		$this->response->setHeader( 'Content-Type', 'application/javascript' );

		return $this->response;
	}


	/**
	 * Creates a list of all available translations.
	 *
	 * @return string JSON encoded list of language IDs with labels
	 */
	protected function getJsonLanguages()
	{
		$result = array();

		foreach( $this->aimeos->get()->getI18nList( 'admin' ) as $id ) {
			$result[] = array( 'id' => $id, 'label' => $id );
		}

		return json_encode( $result );
	}


	/**
	 * Returns the JSON encoded configuration for the ExtJS client.
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context item object
	 * @return string JSON encoded configuration object
	 */
	protected function getJsonClientConfig( \Aimeos\MShop\Context\Item\Iface $context )
	{
		$config = $context->getConfig()->get( 'admin/extjs', array() );
		return json_encode( array( 'admin' => array( 'extjs' => $config ) ), JSON_FORCE_OBJECT );
	}


	/**
	 * Returns the JSON encoded translations for the ExtJS client.
	 *
	 * @param array $i18nPaths List of file system paths which contain the translation files
	 * @param string $lang ISO language code like "en" or "en_GB"
	 * @return string JSON encoded translation object
	 */
	protected function getJsonClientI18n( array $i18nPaths, $lang )
	{
		$i18n = new \Aimeos\MW\Translation\Zend2( $i18nPaths, 'gettext', $lang, array( 'disableNotices' => true ) );

		$content = array(
			'admin' => $i18n->getAll( 'admin' ),
			'admin/ext' => $i18n->getAll( 'admin/ext' ),
		);

		return json_encode( $content, JSON_FORCE_OBJECT );
	}


	/**
	 * Returns the JSON encoded site item.
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context item object
	 * @param string $site Unique site code
	 * @return string JSON encoded site item object
	 * @throws Exception If no site item was found for the code
	 */
	protected function getJsonSiteItem( \Aimeos\MShop\Context\Item\Iface $context, $site )
	{
		$manager = \Aimeos\MShop\Factory::createManager( $context, 'locale/site' );

		$criteria = $manager->createSearch();
		$criteria->setConditions( $criteria->compare( '==', 'locale.site.code', $site ) );
		$items = $manager->searchItems( $criteria );

		if( ( $item = reset( $items ) ) === false ) {
			throw new \Exception( sprintf( 'No site found for code "%1$s"', $site ) );
		}

		return json_encode( $item->toArray() );
	}


	/**
	 * Sets the locale item in the given context
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object
	 * @param string $sitecode Unique site code
	 * @param string $locale ISO language code, e.g. "en" or "en_GB"
	 * @return \Aimeos\MShop\Context\Item\Iface Modified context object
	 */
	protected function setLocale( \Aimeos\MShop\Context\Item\Iface $context, $sitecode = 'default', $locale = null )
	{
		$localeManager = \Aimeos\MShop\Factory::createManager( $context, 'locale' );

		try {
			$localeItem = $localeManager->bootstrap( $sitecode, $locale, '', false );
		} catch( \Aimeos\MShop\Locale\Exception $e ) {
			$localeItem = $localeManager->createItem();
		}

		$context->setLocale( $localeItem );

		return $context;
	}
}
