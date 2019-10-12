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
 * Class providing the context object
 *
 * @package flow
 * @subpackage Base
 * @Flow\Scope("singleton")
 */
class Locale
{
	/**
	 * @var \Aimeos\MShop\Locale\Item\Iface
	 */
	private $locale;

	/**
	 * @var array
	 */
	private $settings;


	/**
	 * Returns the locale item for the current request
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object
	 * @param \Neos\Flow\Mvc\RequestInterface $request Request object
	 * @return \Aimeos\MShop\Locale\Item\Iface Locale item object
	 */
	public function get( \Aimeos\MShop\Context\Item\Iface $context, \Neos\Flow\Mvc\RequestInterface $request )
	{
		if( $this->locale === null )
		{
			$params = $request->getArguments();

			$site = ( isset( $params['site'] ) ? $params['site'] : 'default' );
			$lang = ( isset( $params['locale'] ) ? $params['locale'] : '' );
			$currency = ( isset( $params['currency'] ) ? $params['currency'] : '' );

			$disableSites = (bool) ( isset( $this->settings['flow']['disableSites'] ) ? $this->settings['flow']['disableSites'] : true );

			$localeManager = \Aimeos\MShop::create( $context, 'locale' );
			$this->locale = $localeManager->bootstrap( $site, $lang, $currency, $disableSites );
		}

		return $this->locale;
	}


	/**
	 * Returns the locale item for the current request
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object
	 * @param string $site Unique site code
	 * @return \Aimeos\MShop\Locale\Item\Iface Locale item object
	 */
	public function getBackend( \Aimeos\MShop\Context\Item\Iface $context, $site )
	{
		$localeManager = \Aimeos\MShop::create( $context, 'locale' );

		try {
			$localeItem = $localeManager->bootstrap( $site, '', '', false, null, true );
		} catch( \Aimeos\MShop\Exception $e ) {
			$localeItem = $localeManager->createItem();
		}

		return $localeItem->setCurrencyId( null )->setLanguageId( null );
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
