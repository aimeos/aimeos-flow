<?php

/**
 * @license GPLv3, http://www.gnu.org/copyleft/gpl.html
 * @copyright Aimeos (aimeos.org), 2017
 * @package Flow
 */


namespace Aimeos\Shop\ViewHelper;


use Neos\FluidAdaptor\Core\ViewHelper\Exception;
use Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper;


/**
 * Aimeos block view helper
 *
 * @package Flow
 * @subpackage ViewHelper
 */
class BlockViewHelper extends AbstractViewHelper
{
	protected $escapeChildren = false;


	/**
	 * Registers the known arguments
	 */
	public function initializeArguments()
	{
		$this->registerArgument( 'name', 'string', 'Name of the content block' );
	}


	/**
	 * Adds the rendered Fluid section to the Aimeos block view helper
	 */
	public function render()
	{
		$iface = '\Aimeos\MW\View\Iface';
		$view = $this->templateVariableContainer->get( '_aimeos_view' );

		if( !is_object( $view ) || !( $view instanceof $iface ) ) {
			throw new Exception( 'Aimeos view object is missing' );
		}

		if( !isset( $this->arguments['name'] ) ) {
			throw new Exception( 'Attribute "name" missing for Aimeos translate view helper' );
		}

		$view->block()->set( $this->arguments['name'], $this->renderChildren() );
	}
}