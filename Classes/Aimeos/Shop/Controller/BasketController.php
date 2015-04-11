<?php

/**
 * @license LGPLv3, http://www.gnu.org/copyleft/lgpl.html
 * @copyright Aimeos (aimeos.org), 2015
 */


namespace Aimeos\Shop\Controller;

use TYPO3\Flow\Annotations as Flow;


/**
 * Account controller
 */
class BasketController extends AbstractController
{
	/**
	 * Returns the output of the basket mini component
	 *
	 * @return string Rendered HTML for the body
	 */
	public function cmpminiAction()
	{
		return $this->getOutput( 'basket/mini' );
	}


	/**
	 * Returns the output of the basket related component
	 *
	 * @return string Rendered HTML for the body
	 */
	public function cmprelatedAction()
	{
		return $this->getOutput( 'basket/related' );
	}


	/**
	 * Returns the output of the basket standard component
	 *
	 * @return string Rendered HTML for the body
	 */
	public function cmpstandardAction()
	{
		return $this->getOutput( 'basket/standard' );
	}


	/**
	 * Content for MyAccount page
	 *
	 * @Flow\Session(autoStart = TRUE)
	 */
	public function indexAction()
	{
		$this->view->assignMultiple( $this->getSections( 'basket-index' ) );
	}
}