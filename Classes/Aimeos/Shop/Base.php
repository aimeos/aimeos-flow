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
	 * @var \Arcavias
	 */
	private $aimeos;

	/**
	 * @var \MW_Config_Interface
	 */
	private $config;

	/**
	 * @var \MShop_Context_Item_Interface
	 */
	private $context;

	/**
	 * @var array
	 */
	private $i18n;

	/**
	 * @var \MShop_Locale_Item_Interface
	 */
	private $locale;

	/**
	 * @var \TYPO3\Flow\Session\SessionInterface
	 */
	private $session;

	/**
	 * @var array
	 */
	private $settings;

	/**
	 * @var \TYPO3\Flow\Mvc\Routing\UriBuilder
	 */
	private $uriBuilder;

	/**
	 * @var array
	 * @Flow\Inject(setting="persistence.backendOptions", package="TYPO3.Flow")
	 */
	protected $resource;


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
	 * @return \MW_View_Interface View object
	 */
	public function createView( $useParams = true )
	{
		$params = $urlParams = array();

/*		if( $useParams === true )
		{
			$request = $this->requestStack->getMasterRequest();
			$params = $request->request->all() + $request->query->all() + $request->attributes->get( '_route_params' );

			// required for reloading to the current page
			$params['target'] = $request->get( '_route' );

			$urlParams = $this->getUrlParams();
		}
*/

		$context = $this->getContext();
		$config = $context->getConfig();

		$langid = $context->getLocale()->getLanguageId();
		$i18n = $this->getI18n( array( $langid ) );


		$view = new \MW_View_Default();

		$helper = new \MW_View_Helper_Url_Flow( $view, $this->uriBuilder, $urlParams );
		$view->addHelper( 'url', $helper );

		$helper = new \MW_View_Helper_Translate_Default( $view, $i18n[$langid] );
		$view->addHelper( 'translate', $helper );

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
	 * Returns the Arcavias object.
	 *
	 * @return Arcavias Arcavias object
	 */
	public function getAimeos()
	{
		if( $this->aimeos === null )
		{
			$extDirs = (array) $this->getValue( 'extDir', array() );
			$this->aimeos = new \Arcavias( $extDirs, false );
		}
	
		return $this->aimeos;
	}


	/**
	 * Creates a new configuration object.
	 *
	 * @param array $local Multi-dimensional associative list with local configuration
	 * @return MW_Config_Interface Configuration object
	 */
	public function getConfig( array $local = array() )
	{
		if( $this->config === null )
		{
			$this->settings['resource']['db']['host'] = $this->resource['host'];
			$this->settings['resource']['db']['database'] = $this->resource['dbname'];
			$this->settings['resource']['db']['username'] = $this->resource['user'];
			$this->settings['resource']['db']['password'] = $this->resource['password'];

			$configPaths = $this->getAimeos()->getConfigPaths( 'mysql' );
			$conf = new \MW_Config_Array( $this->settings, $configPaths );

			if( function_exists( 'apc_store' ) === true && $this->getValue( 'useApc', false ) == true ) {
				$conf = new \MW_Config_Decorator_APC( $conf, $this->getValue( 'apcPrefix', 'aimeos:' ) );
			}

			$this->config = $conf;
		}

		if( !empty( $local ) ) {
			return new \MW_Config_Decorator_Memory( $this->config, $local );
		}

		return $this->config;
	}


	/**
	 * Returns the current context.
	 *
	 * @return \MShop_Context_Item_Interface Context object
	 */
	public function getContext( $locale = true )
	{
		if( $this->context === null )
		{
			$context = new \MShop_Context_Item_Default();
	
			$config = $this->getConfig();
			$context->setConfig( $config );
	
			$dbm = new \MW_DB_Manager_PDO( $config );
			$context->setDatabaseManager( $dbm );
	
			$cache = new \MW_Cache_None();
			$context->setCache( $cache );
	
			$session = new \MW_Session_Flow( $this->session );
			$context->setSession( $session );
	
			$logger = \MAdmin_Log_Manager_Factory::createManager( $context );
			$context->setLogger( $logger );
	
			$this->addUser( $context );
	
			$this->context = $context;
		}
	
		if( $locale === true )
		{
			$locale = $this->getLocale( $this->context );
	
			$this->context->setLocale( $locale );
			$this->context->setI18n( $this->getI18n( array( $locale->getLanguageId() ) ) );
	
//			$cache = new \MAdmin_Cache_Proxy_Default( $this->context );
//			$this->context->setCache( $cache );
		}
	
		return $this->context;
	}


	/**
	 * Creates new translation objects.
	 *
	 * @param array $languageIds List of two letter ISO language IDs
	 * @return \MW_Translation_Interface[] List of translation objects
	 */
	public function getI18n( array $languageIds )
	{
		$i18nPaths = $this->getAimeos()->getI18nPaths();
	
		foreach( $languageIds as $langid )
		{
			if( !isset( $this->i18n[$langid] ) )
			{
				$i18n = new \MW_Translation_Zend2( $i18nPaths, 'gettext', $langid, array( 'disableNotices' => true ) );
				
				if( function_exists( 'apc_store' ) === true && $this->getValue( 'useApc', false ) == true ) {
					$conf = new \MW_Translation_Decorator_APC( $i18n, $this->getValue( 'apcPrefix', 'aimeos:' ) );
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
	 * @return \MShop_Locale_Item_Interface Locale item object
	 */
	protected function getLocale( \MShop_Context_Item_Interface $context )
	{
		if( $this->locale === null )
		{
/*			$attr = $this->requestStack->getMasterRequest()->attributes;

			$currency = $attr->get( 'currency', 'EUR' );
			$site = $attr->get( 'site', 'default' );
			$lang = $attr->get( 'locale', 'en' );
*/
			$localeManager = \MShop_Locale_Manager_Factory::createManager( $context );
			$this->locale = $localeManager->bootstrap( 'default', 'en', 'EUR', false );
		}
	
		return $this->locale;
	}


	/**
	 * Injects the Flow session object
	 *
	 * @param \TYPO3\Flow\Session\SessionInterface $session
	 * @return void
	 */
	public function injectSession( \TYPO3\Flow\Session\SessionInterface $session )
	{
		$this->session = $session;
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
	 * Injects the Flow URI builder
	 *
	 * @param \TYPO3\Flow\Mvc\Routing\UriBuilder $objectManager
	 * @return void
	 */
	public function injectUriBuilder( \TYPO3\Flow\Mvc\Routing\UriBuilder $uriBuilder )
	{
		$this->uriBuilder = $uriBuilder;
	}


	/**
	 * Returns the value for the given setting name
	 *
	 * @param string $name Setting name
	 * @param string $default Default value returned if no setting is available
	 * @return string|array|null Setting value
	 */
	protected function getValue( $name, $default = null )
	{
		if( isset( $this->settings[$name] ) ) {
			return $this->settings[$name];
		}
		
		return $default;
	}
}
