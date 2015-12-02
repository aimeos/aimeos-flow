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
 * Class providing the context object
 *
 * @package aimeos-flow
 * @subpackage Base
 * @Flow\Scope("singleton")
 */
class Context
{
	/**
	 * @var \Aimeos\MShop\Context\Item\Iface
	 */
	private static $context;

	/**
	 * @var \Aimeos\MShop\Locale\Item\Iface
	 */
	private $locale;

	/**
	 * @var array
	 */
	private $settings;

	/**
	 * @var \TYPO3\Flow\Cache\Frontend\StringFrontend
	 */
	private $cache;

	/**
	 * @var array
	 * @Flow\Inject(setting="persistence.backendOptions", package="TYPO3.Flow")
	 */
	protected $resource;

	/**
	 * @var \Aimeos\Shop\Base\Aimeos
	 * @Flow\Inject
	 */
	protected $aimeos;

	/**
	 * @var \Aimeos\Shop\Base\I18n
	 * @Flow\Inject
	 */
	protected $i18n;

	/**
	 * @var \TYPO3\SwiftMailer\MailerInterface
	 * @Flow\Inject
	 */
	protected $mailer;

	/**
	 * @var \TYPO3\Flow\Session\SessionInterface
	 * @Flow\Inject(lazy = FALSE)
	 */
	protected $session;


	/**
	 * Returns the current context.
	 *
	 * @param \TYPO3\Flow\Mvc\RequestInterface $request Request object
	 * @return \Aimeos\MShop\Context\Item\Iface
	 */
	public function get( \TYPO3\Flow\Mvc\RequestInterface $request = null )
	{
		if( self::$context === null )
		{
			$context = new \Aimeos\MShop\Context\Item\Standard();

			$config = $this->getConfig();
			$context->setConfig( $config );

			$dbm = new \Aimeos\MW\DB\Manager\PDO( $config );
			$context->setDatabaseManager( $dbm );

			$fsm = new \Aimeos\MW\Filesystem\Manager\Standard( $config );
			$context->setFilesystemManager( $fsm );

			$mail = new \Aimeos\MW\Mail\Swift( $this->mailer );
			$context->setMail( $mail );

			$logger = \Aimeos\MAdmin\Log\Manager\Factory::createManager( $context );
			$context->setLogger( $logger );

			$cache = $this->getCache( $context );
			$context->setCache( $cache );

			self::$context = $context;
		}

		$context = self::$context;

		if( $request !== null )
		{
			$localeItem = $this->getLocale( $context, $request );
			$context->setLocale( $localeItem );

			$i18n = $this->i18n->get( array( $localeItem->getLanguageId() ) );
			$context->setI18n( $i18n );
		}

		$session = new \Aimeos\MW\Session\Flow( $this->session );
		$context->setSession( $session );

		$this->addUser( $context );

		return $context;
	}


	/**
	 * Adds the user ID and name if available
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object
	 */
	protected function addUser( \Aimeos\MShop\Context\Item\Iface $context )
	{
		$username = '';

		$context->setEditor( $username );
	}


	/**
	 * Returns the cache object for the context
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object
	 * @return \Aimeos\MW\Cache\Iface Cache object
	 */
	protected function getCache( \Aimeos\MShop\Context\Item\Iface $context )
	{
		$config = $context->getConfig();

		switch( $config->get( 'flow/cache/name', 'Flow' ) )
		{
			case 'None':
				$config->set( 'client/html/basket/cache/enable', false );
				return \Aimeos\MW\Cache\Factory::createManager( 'None', array(), null );

			case 'Flow':
				return new \Aimeos\MAdmin\Cache\Proxy\Flow( $context, $this->cache );

			default:
				return new \Aimeos\MAdmin\Cache\Proxy\Standard( $context );
		}
	}


	/**
	 * Creates a new configuration object.
	 *
	 * @return \Aimeos\MW\Config\Iface Configuration object
	 */
	protected function getConfig()
	{
		$this->settings['resource']['db']['host'] = $this->resource['host'];
		$this->settings['resource']['db']['database'] = $this->resource['dbname'];
		$this->settings['resource']['db']['username'] = $this->resource['user'];
		$this->settings['resource']['db']['password'] = $this->resource['password'];

		$configPaths = $this->aimeos->get()->getConfigPaths();
		$config = new \Aimeos\MW\Config\PHPArray( $this->settings, $configPaths );

		$apc = (bool) ( isset( $this->settings['flow']['apc']['enable'] ) ? $this->settings['flow']['apc']['enable'] : false );
		$prefix = (string) ( isset( $this->settings['flow']['apc']['prefix'] ) ? $this->settings['flow']['apc']['prefix'] : 'flow:' );

		if( function_exists( 'apc_store' ) === true && $apc === true ) {
			$config = new \Aimeos\MW\Config\Decorator\APC( $config, $prefix );
		}

		return $config;
	}


	/**
	 * Returns the locale item for the current request
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object
	 * @param \TYPO3\Flow\Mvc\RequestInterface $request Request object
	 * @return \Aimeos\MShop\Locale\Item\Iface Locale item object
	 */
	protected function getLocale( \Aimeos\MShop\Context\Item\Iface $context, \TYPO3\Flow\Mvc\RequestInterface $request )
	{
		if( $this->locale === null )
		{
			$params = $request->getArguments();

			$site = ( isset( $params['site'] ) ? $params['site'] : 'default' );
			$lang = ( isset( $params['locale'] ) ? $params['locale'] : '' );
			$currency = ( isset( $params['currency'] ) ? $params['currency'] : '' );

			$disableSites = (bool) ( isset( $this->settings['flow']['disableSites'] ) ? $this->settings['flow']['disableSites'] : false );

			$localeManager = \Aimeos\MShop\Locale\Manager\Factory::createManager( $context );
			$this->locale = $localeManager->bootstrap( $site, $lang, $currency, $disableSites );
		}

		return $this->locale;
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


	/**
	 * Sets the Aimeos shop cache
	 *
	 * @param \TYPO3\Flow\Cache\Frontend\StringFrontend $cache Cache for shop data
	 * @return void
	 */
	public function setCache( \TYPO3\Flow\Cache\Frontend\StringFrontend $cache )
	{
		$this->cache = $cache;
	}
}
