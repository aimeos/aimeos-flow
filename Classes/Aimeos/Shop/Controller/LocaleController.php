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
 * Aimeos locale controller.
 * @package flow
 * @subpackage Controller
 */
class LocaleController extends AbstractController
{
	/**
	 * Returns the output of the locale select component
	 *
	 * @return string Rendered HTML for the body
	 */
	public function selectComponentAction()
	{
		$this->view->assign( 'output', $this->getOutput( 'locale/select' ) );
		$this->response->setHeader( 'Cache-Control', 'max-age=43200' );
	}
}
