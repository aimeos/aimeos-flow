<?php

/**
 * @license LGPLv3, http://www.gnu.org/copyleft/lgpl.html
 * @copyright Aimeos (aimeos.org), 2015-2016
 * @package flow
 * @subpackage Base
 */


namespace Aimeos\Shop\Base;

use TYPO3\Flow\Annotations as Flow;


/**
 * Class providing the Aimeos object
 *
 * @package flow
 * @subpackage Base
 * @Flow\Scope("singleton")
 */
class Aimeos
{
	/**
	 * @var \Aimeos\Bootstrap
	 */
	private $aimeos;

	/**
	 * @var array
	 */
	private $settings;


	/**
	 * Returns the Aimeos object.
	 *
	 * @return \Aimeos\Bootstrap Aimeos object
	 */
	public function get()
	{
		if( $this->aimeos === null )
		{
			$extDirs = ( isset( $this->settings['flow']['extdir'] ) ? (array) $this->settings['flow']['extdir'] : array() );
			$this->aimeos = new \Aimeos\Bootstrap( $extDirs, false );
		}

		return $this->aimeos;
	}


	/**
	 * Returns the version of the Aimeos package
	 *
	 * @return string Version string
	 */
	public function getVersion()
	{
		if( ( $content = @file_get_contents( FLOW_PATH_ROOT . 'composer.lock' ) ) !== false
				&& ( $content = json_decode( $content, true ) ) !== null && isset( $content['packages'] )
		) {
			foreach( (array) $content['packages'] as $item )
			{
				if( $item['name'] === 'aimeos/aimeos-flow' ) {
					return $item['version'];
				}
			}
		}
		return '';
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
