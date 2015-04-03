<?php

/**
 * @license LGPLv3, http://www.gnu.org/copyleft/lgpl.html
 * @copyright Aimeos (aimeos.org), 2014
 */


namespace Aimeos\Shop;

use TYPO3\Flow\Annotations as Flow;


/**
 * Base class for Aimeos Flow package
 *
 * @Flow\Scope("singleton")
 */
class Base
{
	/**
	 * @var \MShop_Context_Item_Interface
	 */
	private static $context;

	/**
	 * @var \Arcavias
	 */
	private $aimeos;

	/**
	 * @var \TYPO3\Flow\Cache\Frontend\StringFrontend
	 */
	private $cache;

	/**
	 * @var array
	 */
	private $i18n = array();

	/**
	 * @var \MShop_Locale_Item_Interface
	 */
	private $locale;

	/**
	 * @var array
	 */
	private $settings;

	/**
	 * @var array
	 * @Flow\Inject(setting="persistence.backendOptions", package="TYPO3.Flow")
	 */
	protected $resource;

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
	 * Returns the Arcavias object.
	 *
	 * @return \Arcavias Arcavias object
	 */
	public function getAimeos()
	{
		if( $this->aimeos === null )
		{
			$extDirs = ( isset( $this->setting['flow']['extdir'] ) ? (array) $this->setting['flow']['extdir'] : array() );
			$this->aimeos = new \Arcavias( $extDirs, false );
		}

		return $this->aimeos;
	}


	/**
	 * Returns the current context.
	 *
	 * @param \TYPO3\Flow\Mvc\Routing\UriBuilder $uriBuilder URL builder object
	 * @param \TYPO3\Flow\Mvc\RequestInterface $request Request object
	 */
	public function getContext( \TYPO3\Flow\Mvc\Routing\UriBuilder $uriBuilder, \TYPO3\Flow\Mvc\RequestInterface $request = null )
	{
		if( self::$context === null )
		{
			$context = new \MShop_Context_Item_Default();

			$config = $this->getConfig();
			$context->setConfig( $config );

			$dbm = new \MW_DB_Manager_PDO( $config );
			$context->setDatabaseManager( $dbm );

			$cache = new \MW_Cache_None();
			$context->setCache( $cache );

			$mail = new \MW_Mail_Swift( $this->mailer );
			$context->setMail( $mail );

			$logger = \MAdmin_Log_Manager_Factory::createManager( $context );
			$context->setLogger( $logger );

			self::$context = $context;
		}

		$context = self::$context;

		if( $request !== null )
		{
			$localeItem = $this->getLocale( $context, $request );

			$context->setLocale( $localeItem );
			$context->setI18n( $this->getI18n( $context, array( $localeItem->getLanguageId() ) ) );

			$cache = $this->getCache( $context, $localeItem->getSiteId() );
			$context->setCache( $cache );
		}

		$session = new \MW_Session_Flow( $this->session );
		$context->setSession( $session );

		$view = $this->createView( $context, $uriBuilder, $request );
		$context->setView( $view );

		$this->addUser( $context );

		return $context;
	}


	/**
	 * Returns the body and header sections created by the clients configured for the given page name.
	 *
	 * @param \MShop_Context_Item_Interface $context Context object
	 * @param string $name Name of the configured page
	 * @return array Associative list with body and header output separated by client name
	 */
	public function getPageSections( \MShop_Context_Item_Interface $context, $pageName )
	{
		$aimeos = $this->getAimeos();
		$templatePaths = $aimeos->getCustomPaths( 'client/html' );
		$pagesConfig = $this->settings['page'];
		$result = array( 'aibody' => array(), 'aiheader' => array() );

		if( isset( $pagesConfig[$pageName] ) )
		{
			foreach( (array) $pagesConfig[$pageName] as $clientName )
			{
				$client = \Client_Html_Factory::createClient( $context, $templatePaths, $clientName );
				$client->setView( $context->getView() );
				$client->process();

				$varName = str_replace( '/', '_', $clientName );

				$result['aibody'][$varName] = $client->getBody();
				$result['aiheader'][$varName] = $client->getHeader();
			}
		}

		return $result;
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
	 * Creates the view object for the HTML client.
	 *
	 * @param \MShop_Context_Item_Interface $context Context object
	 * @param \TYPO3\Flow\Mvc\Routing\UriBuilder $uriBuilder URL builder object
	 * @param \TYPO3\Flow\Mvc\RequestInterface $request Request object
	 * @return \MW_View_Interface View object
	 */
	protected function createView( \MShop_Context_Item_Interface $context,
			\TYPO3\Flow\Mvc\Routing\UriBuilder $uriBuilder,
			\TYPO3\Flow\Mvc\RequestInterface $request = null )
	{
		$params = $fixed = array();
		$config = $context->getConfig();

		if( $request !== null )
		{
			$params = $request->getArguments();
			$fixed = $this->getFixedParams( $request );

			$langid = $context->getLocale()->getLanguageId();
			$i18n = $this->getI18n( $context, array( $langid ) );

			$translation = $i18n[$langid];
		}
		else
		{
			$translation = new \MW_Translation_None( 'en' );
		}


		$view = new \MW_View_Default();

		$helper = new \MW_View_Helper_Translate_Default( $view, $translation );
		$view->addHelper( 'translate', $helper );

		$helper = new \MW_View_Helper_Url_Flow( $view, $uriBuilder, $fixed );
		$view->addHelper( 'url', $helper );

		$helper = new \MW_View_Helper_Parameter_Default( $view, $params );
		$view->addHelper( 'param', $helper );

		$helper = new \MW_View_Helper_Config_Default( $view, $config );
		$view->addHelper( 'config', $helper );

		$sepDec = $config->get( 'client/html/common/format/seperatorDecimal', '.' );
		$sep1000 = $config->get( 'client/html/common/format/seperator1000', ' ' );
		$helper = new \MW_View_Helper_Number_Default( $view, $sepDec, $sep1000 );
		$view->addHelper( 'number', $helper );

		$helper = new \MW_View_Helper_FormParam_Default( $view, array() );
		$view->addHelper( 'formparam', $helper );

		$helper = new \MW_View_Helper_Encoder_Default( $view );
		$view->addHelper( 'encoder', $helper );

		return $view;
	}


	/**
	 * Returns the cache object for the context
	 *
	 * @param \MShop_Context_Item_Interface $context Context object including config
	 * @param string $siteid Unique site ID
	 * @return \MW_Cache_Interface Cache object
	 */
	protected function getCache( \MShop_Context_Item_Interface $context, $siteid )
	{
		$config = $context->getConfig();

		switch( $config->get( 'flow/cache/name', 'Flow' ) )
		{
			case 'Flow':
				$conf = array( 'siteid' => $config->get( 'flow/cache/prefix' ) . $siteid );
				return \MW_Cache_Factory::createManager( 'Flow', $conf, $this->cache );

			case 'None':
				$config->set( 'client/html/basket/cache/enable', false );
				return \MW_Cache_Factory::createManager( 'None', array(), null );

			default:
				return new MAdmin_Cache_Proxy_Default( $context );
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

		$configPaths = $this->getAimeos()->getConfigPaths( 'mysql' );
		$config = new \MW_Config_Array( $this->settings, $configPaths );

		if( function_exists( 'apc_store' ) === true && $config->get( 'flow/apc/enabled', false ) == true ) {
			$config = new \MW_Config_Decorator_APC( $config, $config->get( 'flow/apc/prefix', 'flow:' ) );
		}

		return $config;
	}
	

	/**
	 * Creates new translation objects.
	 *
	 * @param \MShop_Context_Item_Interface $context Context object including config
	 * @param array $languageIds List of two letter ISO language IDs
	 * @return \MW_Translation_Interface[] List of translation objects
	 */
	protected function getI18n( \MShop_Context_Item_Interface $context, array $languageIds )
	{
		$i18nPaths = $this->getAimeos()->getI18nPaths();

		foreach( $languageIds as $langid )
		{
			if( !isset( $this->i18n[$langid] ) )
			{
				$conf = $context->getConfig();
				$i18n = new \MW_Translation_Zend2( $i18nPaths, 'gettext', $langid, array( 'disableNotices' => true ) );

				if( function_exists( 'apc_store' ) === true && $conf->get( 'flow/apc/enabled', false ) == true ) {
					$i18n = new \MW_Translation_Decorator_APC( $i18n, $conf->get( 'flow/apc/prefix', 'flow:' ) );
				}

				if( isset( $this->settings['i18n'][$langid] ) ) {
					$i18n = new \MW_Translation_Decorator_Memory( $i18n, $this->settings['i18n'][$langid] );
				}

				$this->i18n[$langid] = $i18n;
			}
		}

		return $this->i18n;
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

			$disableSites = ( isset( $this->settings['disableSites'] ) ? true : false );

			$localeManager = \MShop_Locale_Manager_Factory::createManager( $context );
			$this->locale = $localeManager->bootstrap( $site, $lang, $currency, $disableSites );
		}

		return $this->locale;
	}


	/**
	 * Returns the fixed parameters that should be included in every URL
	 *
	 * @param \TYPO3\Flow\Mvc\RequestInterface $request Request object
	 * @return array Associative list of site, language and currency if available
	 */
	protected function getFixedParams( \TYPO3\Flow\Mvc\RequestInterface $request )
	{
		$fixed = array();

		$params = $request->getArguments();

		if( isset( $params['site'] ) ) {
			$fixed['site'] = $params['site'];
		}

		if( isset( $params['locale'] ) ) {
			$fixed['locale'] = $params['locale'];
		}

		if( isset( $params['currency'] ) ) {
			$fixed['currency'] = $params['currency'];
		}

		return $fixed;
	}
}
