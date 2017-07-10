<?php

/**
 * @license LGPLv3, http://www.gnu.org/copyleft/lgpl.html
 * @copyright Aimeos (aimeos.org), 2015-2016
 * @package flow
 * @subpackage Controller
 */


namespace Aimeos\Shop\Controller;

use Neos\Flow\Annotations as Flow;


/**
 * Controller for JQuery based adminisration interface.
 * @package flow
 * @subpackage Controller
 */
class JqadmController extends \Neos\Flow\Mvc\Controller\ActionController
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
	 * @var \Aimeos\Shop\Base\Locale
	 * @Flow\Inject
	 */
	protected $locale;

	/**
	 * @var \Aimeos\Shop\Base\View
	 * @Flow\Inject
	 */
	protected $viewbase;


	/**
	 * Returns the JS file content
	 *
	 * @return \Neos\Flow\Http\Response Response object
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
	}


	/**
	 * Returns the HTML code for a copy of a resource object
	 *
	 * @param string Resource location, e.g. "product"
	 * @param string $site Unique site code
	 * @param integer $id Unique resource ID
	 * @return string Generated output
	 */
	public function copyAction( $site = 'default', $resource )
	{
		$cntl = $this->createClient( $site, $resource );
		return $this->getHtml( $cntl->copy() );
	}


	/**
	 * Returns the HTML code for a new resource object
	 *
	 * @param string Resource location, e.g. "product"
	 * @param string $site Unique site code
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
	 * @param string $site Unique site code
	 * @param integer $id Unique resource ID
	 * @return string Generated output
	 */
	public function deleteAction( $site = 'default', $resource )
	{
		$cntl = $this->createClient( $site, $resource );

		if( ( $html = $cntl->delete() ) == '' ) {
			return $this->setPsrResponse( $cntl->getView()->response() );
		}

		return $this->getHtml( $html );
	}


	/**
	 * Returns the HTML code for the requested resource object
	 *
	 * @param string Resource location, e.g. "product"
	 * @param string $site Unique site code
	 * @param integer $id Unique resource ID
	 * @return string Generated output
	 */
	public function getAction( $site = 'default', $resource )
	{
		$cntl = $this->createClient( $site, $resource );
		return $this->getHtml( $cntl->get() );
	}


	/**
	 * Saves a new resource object
	 *
	 * @param string Resource location, e.g. "product"
	 * @param string $site Unique site code
	 * @return string Generated output
	 */
	public function saveAction( $site = 'default', $resource )
	{
		$cntl = $this->createClient( $site, $resource );

		if( ( $html = $cntl->save() ) == '' ) {
			return $this->setPsrResponse( $cntl->getView()->response() );
		}

		return $this->getHtml( $html );
	}


	/**
	 * Returns the HTML code for a list of resource objects
	 *
	 * @param string Resource location, e.g. "product"
	 * @param string $site Unique site code
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
		$aimeos = $this->aimeos->get();
		$lang = ( $this->request->hasArgument( 'lang' ) ? $this->request->getArgument( 'lang' ) : 'en' );
		$templatePaths = $aimeos->getCustomPaths( 'admin/jqadm/templates' );

		$context = $this->context->get( null, 'backend' );
		$context->setI18n( $this->i18n->get( array( $lang, 'en' ) ) );
		$context->setLocale( $this->locale->getBackend( $context, $sitecode ) );
		$context->setView( $this->viewbase->create( $context, $this->uriBuilder, $templatePaths, $this->request, $lang ) );

		return \Aimeos\Admin\JQAdm\Factory::createClient( $context, $templatePaths, $resource )->setAimeos( $aimeos );
	}


	/**
	 * Returns the generated view including the HTML code
	 *
	 * @param string $content Content from admin client
	 */
	protected function getHtml( $content )
	{
		$version = $this->aimeos->getVersion();
		$extnames = implode( ',', $this->aimeos->get()->getExtensions() );
		$content = str_replace( ['{type}', '{version}', '{extensions}'], ['Flow', $version, $extnames], $content );

		$this->view->assign( 'content', $content );
		return $this->view->render( 'index' );
	}


	/**
	 * Set the response data from a PSR-7 response object
	 *
	 * @param \Psr\Http\Message\ResponseInterface $response PSR-7 response object
	 * @return string Response message content
	 */
	protected function setPsrResponse( \Psr\Http\Message\ResponseInterface $response )
	{
		$this->response->setStatus( $response->getStatusCode() );

		foreach( $response->getHeaders() as $key => $value ) {
			$this->response->setHeader( $key, $value );
		}

		return (string) $response->getBody();
	}
}
