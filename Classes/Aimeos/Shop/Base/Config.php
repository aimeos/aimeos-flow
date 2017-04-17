<?php

/**
 * @license LGPLv3, http://www.gnu.org/copyleft/lgpl.html
 * @copyright Aimeos (aimeos.org), 2016
 * @package flow
 * @subpackage Base
 */


namespace Aimeos\Shop\Base;

use Neos\Flow\Annotations as Flow;


/**
 * Class providing the config object
 *
 * @package flow
 * @subpackage Base
 * @Flow\Scope("singleton")
 */
class Config
{
	/**
	 * @var \Aimeos\Shop\Base\Aimeos
	 * @Flow\Inject
	 */
	protected $aimeos;

	/**
	 * @var array
	 * @Flow\InjectConfiguration(path="persistence.backendOptions", package="Neos.Flow")
	 */
	protected $resource;

	/**
	 * @var array
	 */
	private $settings;


	/**
	 * Returns the Aimeos object.
	 *
	 * @return \Aimeos\Bootstrap Aimeos object
	 */
	public function get( $type = 'frontend' )
	{
		$this->settings['resource']['db']['host'] = $this->resource['host'];
		$this->settings['resource']['db']['database'] = $this->resource['dbname'];
		$this->settings['resource']['db']['username'] = $this->resource['user'];
		$this->settings['resource']['db']['password'] = $this->resource['password'];

		$configPaths = $this->aimeos->get()->getConfigPaths();
		$config = new \Aimeos\MW\Config\PHPArray( array(), $configPaths );

		$apc = (bool) ( isset( $this->settings['flow']['apc']['enable'] ) ? $this->settings['flow']['apc']['enable'] : false );
		$prefix = (string) ( isset( $this->settings['flow']['apc']['prefix'] ) ? $this->settings['flow']['apc']['prefix'] : 'flow:' );

		if( $apc === true ) {
			$config = new \Aimeos\MW\Config\Decorator\APC( $config, $prefix );
		}

		$config = new \Aimeos\MW\Config\Decorator\Memory( $config, $this->settings );

		if( isset( $this->settings[$type] ) ) {
			$config = new \Aimeos\MW\Config\Decorator\Memory( $config, $this->settings[$type] );
		}

		return $config;
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
