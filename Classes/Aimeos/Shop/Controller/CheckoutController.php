<?php

/**
 * @license LGPLv3, http://www.gnu.org/copyleft/lgpl.html
 * @copyright Aimeos (aimeos.org), 2015
 */


namespace Aimeos\Shop\Controller;

use TYPO3\Flow\Annotations as Flow;


/**
 * Aimeos checkout controller.
 */
class CheckoutController extends AbstractController
{
	/**
	 * Content for checkout standard page
	 *
	 * @Flow\Session(autoStart = TRUE)
	 */
	public function indexAction()
	{
		$this->view->assignMultiple( $this->getSections( 'checkout-index' ) );
	}


	/**
	 * Content for checkout confirm page
	 *
	 * @Flow\Session(autoStart = TRUE)
	 */
	public function confirmAction()
	{
		$this->view->assignMultiple( $this->getSections( 'checkout-confirm' ) );
	}


	/**
	 * Returns the view for the order update page.
	 *
	 * @return Response Response object containing the generated output
	 */
	public function updateAction()
	{
		$this->view->assignMultiple( $this->getSections( 'checkout-update' ) );
	}
}
