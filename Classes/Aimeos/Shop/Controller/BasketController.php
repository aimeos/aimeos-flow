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
 * Account controller
 * @package flow
 * @subpackage Controller
 */
class BasketController extends AbstractController
{
	/**
	 * Returns the output of the basket mini component
	 *
	 * @return string Rendered HTML for the body
	 */
	public function miniComponentAction()
	{
		$this->view->assign( 'output', $this->getOutput( 'basket/mini' ) );
		$this->response->setHeader( 'Cache-Control', 'no-store' );
	}


	/**
	 * Returns the output of the basket related component
	 *
	 * @return string Rendered HTML for the body
	 */
	public function relatedComponentAction()
	{
		$this->view->assign( 'output', $this->getOutput( 'basket/related' ) );
		$this->response->setHeader( 'Cache-Control', 'no-store' );
	}


	/**
	 * Returns the output of the basket standard component
	 *
	 * @return string Rendered HTML for the body
	 */
	public function standardComponentAction()
	{
		$this->view->assign( 'output', $this->getOutput( 'basket/standard' ) );
		$this->response->setHeader( 'Cache-Control', 'no-store' );
	}


	/**
	 * Content for MyAccount page
	 *
	 * @Flow\Session(autoStart = TRUE)
	 */
	public function indexAction()
	{
		$this->view->assignMultiple( $this->getSections( 'basket-index' ) );
		$this->response->setHeader( 'Cache-Control', 'no-store' );
	}
}