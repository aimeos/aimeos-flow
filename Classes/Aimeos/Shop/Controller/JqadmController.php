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
 * Controller for JQuery based adminisration interface.
 * @package flow
 * @subpackage Controller
 */
class JqadmController extends \TYPO3\Flow\Mvc\Controller\ActionController
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
	 * @var \Aimeos\Shop\Base\I18n
	 * @Flow\Inject
	 */
	protected $i18n;

	/**
	 * @var \Aimeos\Shop\Base\View
	 * @Flow\Inject
	 */
	protected $viewbase;


	/**
	 * Returns the JS file content
	 *
	 * @return \TYPO3\Flow\Http\Response Response object
	 */
	public function fileAction()
	{
		$files = array();
		$aimeos = $this->aimeos->get();
		$type = $this->request->getArgument( 'type' );

		foreach( $aimeos->getCustomPaths( 'admin/jqadm' ) as $base => $paths )
		{
			foreach( $paths as $path )
			{
				$jsbAbsPath = $base . '/' . $path;
				$jsb2 = new \Aimeos\MW\Jsb2\Standard( $jsbAbsPath, dirname( $jsbAbsPath ) );
				$files = array_merge( $files, $jsb2->getFiles( $type ) );
			}
		}

		foreach( $files as $file )
		{
			if( ( $content = file_get_contents( $file ) ) !== false ) {
				$this->response->appendContent( $content );
			}
		}

		if( $type === 'js' ) {
			$this->response->setHeader( 'Content-Type', 'application/javascript' );
		} elseif( $type === 'css' ) {
			$this->response->setHeader( 'Content-Type', 'text/css' );
		}

		return $this->response;
	}


	/**
	 * Returns the HTML code for a copy of a resource object
	 *
	 * @param string Resource location, e.g. "product"
	 * @param string $sitecode Unique site code
	 * @param integer $id Unique resource ID
	 * @return string Generated output
	 */
	public function copyAction( $site = 'default', $resource, $id )
	{
		$cntl = $this->createClient( $site, $resource );
		return $this->getHtml( $cntl->copy( $id ) );
	}


	/**
	 * Returns the HTML code for a new resource object
	 *
	 * @param string Resource location, e.g. "product"
	 * @param string $sitecode Unique site code
	 * @return string Generated output
	 */
	public function createAction( $site = 'default', $resource )
	{
		$cntl = $this->createClient( $site, $resource );
		return $this->getHtml( $cntl->create() );
	}


	/**
	 * Deletes the resource object or a list of resource objects
	 *
	 * @param string Resource location, e.g. "product"
	 * @param string $sitecode Unique site code
	 * @param integer $id Unique resource ID
	 * @return string Generated output
	 */
	public function deleteAction( $site = 'default', $resource, $id )
	{
		$cntl = $this->createClient( $site, $resource );
		return $this->getHtml( $cntl->delete( $id ) . $cntl->search() );
	}


	/**
	 * Returns the HTML code for the requested resource object
	 *
	 * @param string Resource location, e.g. "product"
	 * @param string $sitecode Unique site code
	 * @param integer $id Unique resource ID
	 * @return string Generated output
	 */
	public function getAction( $site = 'default', $resource, $id )
	{
		$cntl = $this->createClient( $site, $resource );
		return $this->getHtml( $cntl->get( $id ) );
	}


	/**
	 * Saves a new resource object
	 *
	 * @param string Resource location, e.g. "product"
	 * @param string $sitecode Unique site code
	 * @return string Generated output
	 */
	public function saveAction( $site = 'default', $resource )
	{
		$cntl = $this->createClient( $site, $resource );
		return $this->getHtml( ( $cntl->save() ? : $cntl->search() ) );
	}


	/**
	 * Returns the HTML code for a list of resource objects
	 *
	 * @param string Resource location, e.g. "product"
	 * @param string $sitecode Unique site code
	 * @return string Generated output
	 */
	public function searchAction( $site = 'default', $resource )
	{
		$cntl = $this->createClient( $site, $resource );
		return $this->getHtml( $cntl->search() );
	}


	/**
	 * Returns the resource controller
	 *
	 * @param string $sitecode Unique site code
	 * @return \Aimeos\MShop\Context\Item\Iface Context item
	 */
	protected function createClient( $sitecode, $resource )
	{
		$lang = ( $this->request->hasArgument( 'lang' ) ? $this->request->getArgument( 'lang' ) : 'en' );
		$templatePaths = $this->aimeos->get()->getCustomPaths( 'admin/jqadm/templates' );

		$context = $this->context->get();
		$context = $this->setLocale( $context, $sitecode, $lang );

		$config = $context->getConfig();
		$view = $this->viewbase->create( $config, $this->uriBuilder, $templatePaths, $this->request, $lang );
		$context->setView( $view );

		return \Aimeos\Admin\JQAdm\Factory::createClient( $context, $templatePaths, $resource );
	}


	/**
	 * Returns the generated view including the HTML code
	 *
	 * @param string $content Content from admin client
	 */
	protected function getHtml( $content )
	{
		$version = $this->aimeos->getVersion();
		$content = str_replace( ['{type}', '{version}'], ['Flow', $version], $content );

		$this->view->assign( 'content', $content );
		return $this->view->render( 'index' );
	}


	/**
	 * Sets the locale item in the given context
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object
	 * @param string $site Unique site code
	 * @param string $lang ISO language code, e.g. "en" or "en_GB"
	 * @return \Aimeos\MShop\Context\Item\Iface Modified context object
	 */
	protected function setLocale( \Aimeos\MShop\Context\Item\Iface $context, $site, $lang )
	{
		$localeManager = \Aimeos\MShop\Factory::createManager( $context, 'locale' );

		try
		{
			$localeItem = $localeManager->bootstrap( $site, '', '', false );
			$localeItem->setLanguageId( null );
			$localeItem->setCurrencyId( null );
		}
		catch( \Aimeos\MShop\Locale\Exception $e )
		{
			$localeItem = $localeManager->createItem();
		}

		$context->setLocale( $localeItem );
		$context->setI18n( $this->i18n->get( array( $lang, 'en' ) ) );

		return $context;
	}
}
