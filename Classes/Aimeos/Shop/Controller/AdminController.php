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
 * Controller for ExtJS adminisration interface.
 * @package flow
 * @subpackage Controller
 */
class AdminController extends \TYPO3\Flow\Mvc\Controller\ActionController
{
	/**
	 * Creates the initial HTML view for the admin interface.
	 */
	public function indexAction()
	{
		$param = array(
			'site' => 'default',
			'resource' => 'product',
			'lang' => ( $this->request->hasArgument( 'lang' ) ? $this->request->getArgument( 'lang' ) : 'en' ),
		);

		$this->forward( 'search', 'jqadm', null, $param );
	}
}
