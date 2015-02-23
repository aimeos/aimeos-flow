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
	 * Content for MyAccount page
	 */
	public function indexAction()
	{
		$this->view->assignMultiple( $this->getPageSections( 'account-index' ) );
	}
}
