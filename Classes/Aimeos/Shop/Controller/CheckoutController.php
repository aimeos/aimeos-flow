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
	 * Returns the output of the checkout confirm component
	 *
	 * @return string Rendered HTML for the body
	 */
	public function cmpconfirmAction()
	{
		return $this->getOutput( 'checkout/confirm' );
	}


	/**
	 * Returns the output of the checkout standard component
	 *
	 * @return string Rendered HTML for the body
	 */
	public function cmpstandardAction()
	{
		return $this->getOutput( 'checkout/standard' );
	}


	/**
	 * Returns the output of the checkout update component
	 *
	 * @return string Rendered HTML for the body
	 */
	public function cmpupdateAction()
	{
		return $this->getOutput( 'checkout/update' );
	}


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
