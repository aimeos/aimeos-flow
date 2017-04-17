<?php

/**
 * @license LGPLv3, http://www.gnu.org/copyleft/lgpl.html
 * @copyright Aimeos (aimeos.org), 2015-2016
 * @package flow
 * @subpackage Base
 */


namespace Aimeos\Shop\Base;

use Neos\Flow\Annotations as Flow;


/**
 * Class providing the internationalization objects
 *
 * @package flow
 * @subpackage Base
 * @Flow\Scope("singleton")
 */
class I18n
{
	/**
	 * @var array
	 */
	private $settings;

	/**
	 * @var array List of \Aimeos\MW\Translation\Iface objects
	 */
	private $i18n = array();

	/**
	 * @var \Aimeos\Shop\Base\Aimeos
	 * @Flow\Inject
	 */
	protected $aimeos;


	/**
	 * Creates new translation objects.
	 *
	 * @param array $languageIds List of two letter ISO language IDs
	 * @return \Aimeos\MW\Translation\Iface[] List of translation objects
	 */
	public function get( array $languageIds )
	{
		$i18nPaths = $this->aimeos->get()->getI18nPaths();

		foreach( $languageIds as $langid )
		{
			if( !isset( $this->i18n[$langid] ) )
			{
				$i18n = new \Aimeos\MW\Translation\Gettext( $i18nPaths, $langid );

				$apc = (bool) ( isset( $this->settings['flow']['apc']['enable'] ) ? $this->settings['flow']['apc']['enable'] : false );
				$prefix = (string) ( isset( $this->settings['flow']['apc']['prefix'] ) ? $this->settings['flow']['apc']['prefix'] : 'flow:' );

				if( $apc === true ) {
					$i18n = new \Aimeos\MW\Translation\Decorator\APC( $i18n, $prefix );
				}

				if( isset( $this->settings['i18n'][$langid] ) ) {
					$i18n = new \Aimeos\MW\Translation\Decorator\Memory( $i18n, $this->settings['i18n'][$langid] );
				}

				$this->i18n[$langid] = $i18n;
			}
		}

		return $this->i18n;
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
