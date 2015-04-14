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
class AccountController extends AbstractController
{
	/**
	 * Returns the output of the account favorite component
	 *
	 * @return string Rendered HTML for the body
	 */
	public function cmpfavoriteAction()
	{
		$this->view->assign( 'output', $this->getOutput( 'account/favorite' ) );
	}


	/**
	 * Returns the output of the account history component
	 *
	 * @return string Rendered HTML for the body
	 */
	public function cmphistoryAction()
	{
		$this->view->assign( 'output', $this->getOutput( 'account/history' ) );
	}


	/**
	 * Returns the output of the account watch component
	 *
	 * @return string Rendered HTML for the body
	 */
	public function cmpwatchAction()
	{
		$this->view->assign( 'output', $this->getOutput( 'account/watch' ) );
	}


	/**
	 * Content for MyAccount page
	 *
	 * @Flow\Session(autoStart = TRUE)
	 */
	public function indexAction()
	{
		$this->view->assignMultiple( $this->getSections( 'account-index' ) );
	}
}
