<?php

/**
 * @license LGPLv3, http://www.gnu.org/copyleft/lgpl.html
 * @copyright Aimeos (aimeos.org), 2015
 */


namespace Aimeos\Shop\Controller;

use TYPO3\Flow\Annotations as Flow;


/**
 * Controller for JQuery based adminisration interface.
 */
class JqadmController extends AdminController
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
	 * @var \Aimeos\Shop\Base\View
	 * @Flow\Inject
	 */
	protected $viewbase;


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

		$context = $this->context->get( $this->request );
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
		$content = str_replace( ['{type}', '{version}'], ['Flow', $this->getVersion()], $content );

		$this->view->assign( 'content', $content );
		return $this->view->render( 'index' );
	}
}
