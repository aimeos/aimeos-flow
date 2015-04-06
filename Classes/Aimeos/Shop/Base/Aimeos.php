<?php

/**
 * @license LGPLv3, http://www.gnu.org/copyleft/lgpl.html
 * @copyright Aimeos (aimeos.org), 2015
 * @package aimeos-flow
 * @subpackage Base
 */


namespace Aimeos\Shop\Base;

use TYPO3\Flow\Annotations as Flow;


/**
 * Class providing the Aimeos object
 *
 * @package aimeos-flow
 * @subpackage Base
 * @Flow\Scope("singleton")
 */
class Aimeos
{
	/**
	 * @var \Arcavias
	 */
	private $aimeos;

	/**
	 * @var array
	 */
	private $settings;


	/**
	 * Returns the Arcavias object.
	 *
	 * @return \Arcavias Arcavias object
	 */
	public function get()
	{
		if( $this->aimeos === null )
		{
			$extDirs = ( isset( $this->settings['flow']['extdir'] ) ? (array) $this->settings['flow']['extdir'] : array() );
			$this->aimeos = new \Arcavias( $extDirs, false );
		}

		return $this->aimeos;
	}


	/**
	 * Inject the settings
	 *
	 * @param array $settings
	 * @return void
	 */
	public function injectSettings( array $settings )
	{
		$this->settings = $settings;
	}
}
