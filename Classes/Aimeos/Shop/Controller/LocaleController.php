<?php

/**
 * @license LGPLv3, http://www.gnu.org/copyleft/lgpl.html
 * @copyright Aimeos (aimeos.org), 2015
 */


namespace Aimeos\Shop\Controller;

use TYPO3\Flow\Annotations as Flow;


/**
 * Aimeos locale controller.
 */
class LocaleController extends AbstractController
{
	/**
	 * Returns the output of the locale select component
	 *
	 * @return string Rendered HTML for the body
	 */
	public function cmpselectAction()
	{
		return $this->getOutput( 'locale/select' );
	}
}
