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
	 * @var \MShop_Context_Item_Interface
	 */
	private static $context;

	/**
	 * @var \MShop_Locale_Item_Interface
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
	 * @return \MShop_Context_Item_Interface
	 */
	public function get( \TYPO3\Flow\Mvc\RequestInterface $request = null )
	{
		if( self::$context === null )
		{
			$context = new \MShop_Context_Item_Default();

			$config = $this->getConfig();
			$context->setConfig( $config );

			$dbm = new \MW_DB_Manager_PDO( $config );
			$context->setDatabaseManager( $dbm );

			$mail = new \MW_Mail_Swift( $this->mailer );
			$context->setMail( $mail );

			$logger = \MAdmin_Log_Manager_Factory::createManager( $context );
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

		$session = new \MW_Session_Flow( $this->session );
		$context->setSession( $session );

		$this->addUser( $context );

		return $context;
	}


	/**
	 * Adds the user ID and name if available
	 *
	 * @param \MShop_Context_Item_Interface $context Context object
	 */
	protected function addUser( \MShop_Context_Item_Interface $context )
	{
		$username = '';

		$context->setEditor( $username );
	}


	/**
	 * Returns the cache object for the context
	 *
	 * @param \MShop_Context_Item_Interface $context Context object
	 * @return \MW_Cache_Interface Cache object
	 */
	protected function getCache( \MShop_Context_Item_Interface $context )
	{
		switch( $context->getConfig()->get( 'flow/cache/name', 'Flow' ) )
		{
			case 'None':
				$config->set( 'client/html/basket/cache/enable', false );
				return \MW_Cache_Factory::createManager( 'None', array(), null );

			case 'Flow':
				return new \MAdmin_Cache_Proxy_Flow( $context, $this->cache );

			default:
				return new \MAdmin_Cache_Proxy_Default( $context );
		}
	}


	/**
	 * Creates a new configuration object.
	 *
	 * @return \MW_Config_Interface Configuration object
	 */
	protected function getConfig()
	{
		$this->settings['resource']['db']['host'] = $this->resource['host'];
		$this->settings['resource']['db']['database'] = $this->resource['dbname'];
		$this->settings['resource']['db']['username'] = $this->resource['user'];
		$this->settings['resource']['db']['password'] = $this->resource['password'];

		$configPaths = $this->aimeos->get()->getConfigPaths( 'mysql' );
		$config = new \MW_Config_Array( $this->settings, $configPaths );

		$apc = (bool) ( isset( $this->settings['flow']['apc']['enable'] ) ? $this->settings['flow']['apc']['enable'] : false );
		$prefix = (string) ( isset( $this->settings['flow']['apc']['prefix'] ) ? $this->settings['flow']['apc']['prefix'] : 'flow:' );

		if( function_exists( 'apc_store' ) === true && $apc == true ) {
			$config = new \MW_Config_Decorator_APC( $config, $prefix );
		}

		return $config;
	}


	/**
	 * Returns the locale item for the current request
	 *
	 * @param \MShop_Context_Item_Interface $context Context object
	 * @param \TYPO3\Flow\Mvc\RequestInterface $request Request object
	 * @return \MShop_Locale_Item_Interface Locale item object
	 */
	protected function getLocale( \MShop_Context_Item_Interface $context, \TYPO3\Flow\Mvc\RequestInterface $request )
	{
		if( $this->locale === null )
		{
			$params = $request->getArguments();

			$site = ( isset( $params['site'] ) ? $params['site'] : 'default' );
			$lang = ( isset( $params['locale'] ) ? $params['locale'] : '' );
			$currency = ( isset( $params['currency'] ) ? $params['currency'] : '' );

			$disableSites = (bool) ( isset( $this->settings['flow']['disableSites'] ) ? $this->settings['flow']['disableSites'] : false );

			$localeManager = \MShop_Locale_Manager_Factory::createManager( $context );
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
