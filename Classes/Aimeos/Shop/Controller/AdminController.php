<?php

/**
 * @license LGPLv3, http://www.gnu.org/copyleft/lgpl.html
 * @copyright Aimeos (aimeos.org), 2015
 */


namespace Aimeos\Shop\Controller;

use TYPO3\Flow\Annotations as Flow;


/**
 * Controller for adminisration interface.
 */
class AdminController extends \TYPO3\Flow\Mvc\Controller\ActionController
{
	private $_controller;

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
		$controller = new \Controller_ExtJS_JsonRpc( $context, $cntlPaths );
		$cssFiles = $jsFiles = array();

		foreach( $aimeos->getCustomPaths( 'client/extjs' ) as $base => $paths )
		{
			foreach( $paths as $path )
			{
				$jsbAbsPath = $base . '/' . $path;

				if( !is_file( $jsbAbsPath ) ) {
					throw new Exception( sprintf( 'JSB2 file "%1$s" not found', $jsbAbsPath ) );
				}

				$jsb2 = new \MW_Jsb2_Default( $jsbAbsPath, dirname( $path ) );

				$cssFiles = array_merge( $cssFiles, $jsb2->getUrls( 'css' ) );
				$jsFiles = array_merge( $jsFiles, $jsb2->getUrls( 'js' ) );
			}
		}

		$params = array( 'site' => '{site}', 'lang' => '{lang}', 'tab' => '{tab}' );
		$adminUrl = $this->uriBuilder->uriFor( 'index', $params, 'admin' );
		$jsonUrl = $this->uriBuilder->uriFor( 'do' );

		$vars = array(
			'lang' => $lang,
			'jsFiles' => $jsFiles,
			'cssFiles' => $cssFiles,
			'languages' => $this->getJsonLanguages( $context),
			'config' => $this->getJsonClientConfig( $context ),
			'site' => $this->getJsonSiteItem( $context, $site ),
			'i18nContent' => $this->getJsonClientI18n( $aimeos->getI18nPaths(), $lang ),
			'searchSchemas' => $controller->getJsonSearchSchemas(),
			'itemSchemas' => $controller->getJsonItemSchemas(),
			'smd' => $controller->getJsonSmd( $jsonUrl ),
			'urlTemplate' => urldecode( $adminUrl ),
			'uploaddir' => $context->getConfig()->get( 'flow/uploaddir', '/.' ),
			'version' => $this->getVersion(),
			'activeTab' => $tab,
		);

		$this->view->assignMultiple( $vars );
		$this->view->assign( 'jsFiles', $jsFiles );
		$this->view->assign( 'cssFiles', $cssFiles );
	}


	/**
	 * Single entry point for all JSON admin requests.
	 */
	public function doAction()
	{
		$context = $this->context->get( $this->request );
		$context = $this->setLocale( $context );
		$cntlPaths = $this->aimeos->get()->getCustomPaths( 'controller/extjs' );

		$controller = new \Controller_ExtJS_JsonRpc( $context, $cntlPaths );

		return $controller->process( $this->request->getArguments(), 'php://input' );
	}


	/**
	 * Creates a list of all available translations.
	 *
	 * @param \MShop_Context_Item_Interface $context Context object
	 * @return array List of language IDs with labels
	 */
	protected function getJsonLanguages( \MShop_Context_Item_Interface $context )
	{
		$paths = $this->aimeos->get()->getI18nPaths();
		$langs = array();

		if( !isset( $paths['client/extjs'] ) ) {
			return json_encode( array() );
		}

		foreach( $paths['client/extjs'] as $path )
		{
			$iter = new \DirectoryIterator( $path );

			foreach( $iter as $file )
			{
				$name = $file->getFilename();

				if( preg_match('/^[a-z]{2,3}(_[A-Z]{2})?$/', $name ) ) {
					$langs[$name] = null;
				}
			}
		}

		return json_encode( $this->getLanguages( $context, array_keys( $langs ) ) );
	}


	/**
	 * Returns the JSON encoded configuration for the ExtJS client.
	 *
	 * @param \MShop_Context_Item_Interface $context Context item object
	 * @return string JSON encoded configuration object
	 */
	protected function getJsonClientConfig( \MShop_Context_Item_Interface $context )
	{
		$config = $context->getConfig()->get( 'client/extjs', array() );
		return json_encode( array( 'client' => array( 'extjs' => $config ) ), JSON_FORCE_OBJECT );
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
		$i18n = new \MW_Translation_Zend2( $i18nPaths, 'gettext', $lang, array( 'disableNotices' => true ) );

		$content = array(
			'client/extjs' => $i18n->getAll( 'client/extjs' ),
			'client/extjs/ext' => $i18n->getAll( 'client/extjs/ext' ),
		);

		return json_encode( $content, JSON_FORCE_OBJECT );
	}


	/**
	 * Returns the JSON encoded site item.
	 *
	 * @param \MShop_Context_Item_Interface $context Context item object
	 * @param string $site Unique site code
	 * @return string JSON encoded site item object
	 * @throws Exception If no site item was found for the code
	 */
	protected function getJsonSiteItem( \MShop_Context_Item_Interface $context, $site )
	{
		$manager = \MShop_Factory::createManager( $context, 'locale/site' );

		$criteria = $manager->createSearch();
		$criteria->setConditions( $criteria->compare( '==', 'locale.site.code', $site ) );
		$items = $manager->searchItems( $criteria );

		if( ( $item = reset( $items ) ) === false ) {
			throw new \Exception( sprintf( 'No site found for code "%1$s"', $site ) );
		}

		return json_encode( $item->toArray() );
	}


	/**
	 * Returns a list of arrays with "id" and "label"
	 *
	 * @param \MShop_Context_Item_Interface $context Context object
	 * @param array $langIds List of language IDs
	 * @return array List of associative lists with "id" and "label" as keys
	 */
	protected function getLanguages( \MShop_Context_Item_Interface $context, array $langIds )
	{
		$languageManager = \MShop_Factory::createManager( $context, 'locale/language' );
		$result = array();

		$search = $languageManager->createSearch();
		$search->setConditions( $search->compare('==', 'locale.language.id', $langIds ) );
		$search->setSortations( array( $search->sort( '-', 'locale.language.status' ), $search->sort( '+', 'locale.language.label' ) ) );
		$langItems = $languageManager->searchItems( $search );

		foreach( $langItems as $id => $item ) {
			$result[] = array( 'id' => $id, 'label' => $item->getLabel() );
		}

		return $result;
	}


	/**
	 * Returns the version of the Aimeos package
	 *
	 * @return string Version string
	 */
	protected function getVersion()
	{
		if( ( $content = @file_get_contents( FLOW_PATH_ROOT . 'composer.lock' ) ) !== false
				&& ( $content = json_decode( $content, true ) ) !== null && isset( $content['packages'] )
		) {
			foreach( (array) $content['packages'] as $item )
			{
				if( $item['name'] === 'aimeos/aimeos-flow' ) {
					return $item['version'];
				}
			}
		}
		return '';
	}


	/**
	 * Sets the locale item in the given context
	 *
	 * @param \MShop_Context_Item_Interface $context Context object
	 * @param string $sitecode Unique site code
	 * @param string $language ISO language code, e.g. "en" or "en_GB"
	 * @return \MShop_Context_Item_Interface Modified context object
	 */
	protected function setLocale( \MShop_Context_Item_Interface $context, $sitecode = 'default', $locale = null )
	{
		$localeManager = \MShop_Factory::createManager( $context, 'locale' );

		try {
			$localeItem = $localeManager->bootstrap( $sitecode, $locale, '', false );
		} catch( \MShop_Locale_Exception $e ) {
			$localeItem = $localeManager->createItem();
		}

		$context->setLocale( $localeItem );

		return $context;
	}
}
