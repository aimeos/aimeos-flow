<?php

namespace Aimeos\Shop\Command;

use TYPO3\Flow\Annotations as Flow;


/**
 * @Flow\Scope("singleton")
 */
class AimeosCommandController extends \TYPO3\Flow\Cli\CommandController
{
	/**
	 * @var \TYPO3\Flow\Object\ObjectManagerInterface
	 */
	protected $objectManager;


	/**
	 * Clears the content cache
	 *
	 * @param string $sites List of sites separated by a space character the jobs should be executed for, e.g. "default unittest"
	 * @return void
	 */
	public function cacheCommand( $sites = '' )
	{
		$base = $this->objectManager->get( '\\Aimeos\\Shop\\Base' );

		$context = $base->getContext( false );
		$context->setEditor( 'aimeos:cache' );

		$localeManager = \MShop_Factory::createManager( $context, 'locale' );

		$this->outputFormatted( 'Clearing the Aimeos content cache for site' );

		foreach( $this->getSiteItems( $context, $sites ) as $siteItem )
		{
			$localeItem = $localeManager->bootstrap( $siteItem->getCode(), '', '', false );
			$context->setLocale( $localeItem );

			$this->outputFormatted( '  <b>%s</b>', array( $siteItem->getCode() ) );

			\MAdmin_Cache_Manager_Factory::createManager( $context )->getCache()->flush();
		}
	}


	/**
	 * Executes the Aimeos maintenance jobs
	 *
	 * The Aimeos shop system needs some maintenance tasks that must be
	 * regularly executed. These include
	 *
	 * - admin/cache (remove expired cache entries once a day)
	 * - admin/job (process import/export jobs created in the admin interface every five minutes)
	 * - admin/log (archivate and delete old log entries once a day)
	 * - catalog/index/rebuild (rebuild the catalog index once a day after midnight)
	 * - catalog/index/optimize (optimize the catalog index once a day one hour after the rebuild)
	 * - customer/email/watch (send customers e-mails if their watched products have changed)
	 * - order/cleanup/unfinished (remove unfinised orders once a day)
	 * - order/cleanup/unfinised (remove unpaid orders once a day)
	 * - order/email/delivery (send delivery status update e-mails to the customers every few hours)
	 * - order/email/payment (send payment status update e-mails to the customers every few hours)
	 * - order/service/async (import batch delivery or payment status updates if necessary)
	 * - order/service/delivery (sends paid orders to the ERP system or logistic partner)
	 * - order/service/payment (captures authorized payments after the configured amount of time automatically)
	 * - product/bought (updates the suggested products based on what other customers bought once a day)
	 *
	 * Each of these maintenance tasks must be executed for all shop instances
	 * if you have more than one site in your installation. The sites parameter
	 * should contain a list of site codes in this case. If you only have one
	 * site named "default" then you don't need to specify the site.
	 *
	 * @param string $jobs List of job names separated by a space character like "admin/job catalog/index/rebuild"
	 * @param string $sites List of sites separated by a space character the jobs should be executed for, e.g. "default unittest"
	 * @return void
	 */
	public function jobsCommand( $jobs, $sites = 'default' )
	{
		$base = $this->objectManager->get( '\\Aimeos\\Shop\\Base' );
		$aimeos = $base->getAimeos();
		
		$context = $base->getContext( false );
		$context->setI18n( $this->createI18n( $context, $aimeos->getI18nPaths() ) );
		$context->setEditor( 'aimeos:jobs' );

		$jobs = explode( ' ', $jobs );
		$localeManager = \MShop_Factory::createManager( $context, 'locale' );

		foreach( explode( ' ', $sites ) as $siteCode )
		{
			$localeItem = $localeManager->bootstrap( $siteCode, 'en', '', false );
			$context->setLocale( $localeItem );

			$this->outputFormatted( 'Executing jobs for site <b>%s</b>', array( $siteCode ) );

			foreach( $jobs as $jobname )
			{
				$this->outputFormatted( '  <b>%s</b>', array( $jobname ) );
				\Controller_Jobs_Factory::createController( $context, $aimeos, $jobname )->run();
			}
		}
	}


	/**
	 * Initialize or update the Aimeos database tables
	 *
	 * After installing and updating the Aimeos package, the database structure
	 * must be created or upgraded to the current version. Depending on the size
	 * of the database, this may take a while.
	 *
	 * @param string $site Site for updating database entries
	 * @param array $options Optional setup configuration, name and value are separated by ":" like "setup/default/demo:1".
	 * @return void
	 */
	public function setupCommand( $site = 'default', array $options = array() )
	{
		$base = $this->objectManager->get( '\\Aimeos\\Shop\\Base' );

		$ctx = $base->getContext( false );
		$ctx->setEditor( 'aimeos:setup' );

		$config = $base->getConfig();
		$config->set( 'setup/site', $site );
		$dbconfig = $this->getDbConfig( $config );
		$this->setOptions( $config, $options );

		$taskPaths = $base->getAimeos()->getSetupPaths( $site );

		$includePaths = $taskPaths;
		$includePaths[] = get_include_path();

		if( set_include_path( implode( PATH_SEPARATOR, $includePaths ) ) === false ) {
			throw new Exception( 'Unable to extend include path' );
		}

		$manager = new \MW_Setup_Manager_Multiple( $ctx->getDatabaseManager(), $dbconfig, $taskPaths, $ctx );

		$this->outputFormatted( 'Initializing or updating the Aimeos database tables for site <b>%s</b>', array( $site ) );

		$manager->run( 'mysql' );
	}


	/**
	 * @param \TYPO3\Flow\Object\ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function injectObjectManager( \TYPO3\Flow\Object\ObjectManagerInterface $objectManager )
	{
		$this->objectManager = $objectManager;
	}


	/**
	 * Loads the requested setup task class
	 *
	 * @param string $classname Name of the setup task class
	 * @return boolean True if class is found, false if not
	 */
	public static function autoload( $classname )
	{
		if( strncmp( $classname, 'MW_Setup_Task_', 14 ) === 0 )
		{
		    $fileName = substr( $classname, 14 ) . '.php';
			$paths = explode( PATH_SEPARATOR, get_include_path() );

			foreach( $paths as $path )
			{
				$file = $path . DIRECTORY_SEPARATOR . $fileName;

				if( file_exists( $file ) === true && ( include_once $file ) !== false ) {
					return true;
				}
			}
		}

		return false;
	}


	/**
	 * Creates new translation objects
	 *
	 * @param MShop_Context_Item_Interface $context Context object
	 * @param array List of paths to the i18n files
	 * @return array List of translation objects implementing MW_Translation_Interface
	 */
	protected function createI18n( \MShop_Context_Item_Interface $context, array $i18nPaths )
	{
		$list = array();
		$translations = array();
		$langManager = \MShop_Factory::createManager( $context, 'locale/language' );

		foreach( $langManager->searchItems( $langManager->createSearch( true ) ) as $id => $langItem )
		{
			$i18n = new \MW_Translation_Zend2( $i18nPaths, 'gettext', $id, array( 'disableNotices' => true ) );

			if( isset( $translations[$id] ) ) {
				$i18n = new \MW_Translation_Decorator_Memory( $i18n, $translations[$id] );
			}

			$list[$id] = $i18n;
		}

		return $list;
	}


	/**
	 * Returns the database configuration from the config object.
	 *
	 * @param \MW_Config_Interface $conf Config object
	 * @return array Multi-dimensional associative list of database configuration parameters
	 */
	protected function getDbConfig( \MW_Config_Interface $conf )
	{
		$dbconfig = $conf->get( 'resource', array() );
	
		foreach( $dbconfig as $rname => $dbconf )
		{
			if( strncmp( $rname, 'db', 2 ) !== 0 ) {
				unset( $dbconfig[$rname] );
			}
		}
	
		return $dbconfig;
	}


	/**
	 * Returns the enabled site items which may be limited by the input arguments.
	 *
	 * @param \MShop_Context_Item_Interface $context Context item object
	 * @param string $sites Unique site codes
	 * @return \MShop_Locale_Item_Site_Interface[] List of site items
	 */
	protected function getSiteItems( \MShop_Context_Item_Interface $context, $sites )
	{
		$manager = \MShop_Factory::createManager( $context, 'locale/site' );
		$search = $manager->createSearch();

		if( $sites !== '' ) {
			$search->setConditions( $search->compare( '==', 'locale.site.code', explode( ' ', $sites ) ) );
		}

		return $manager->searchItems( $search );
	}


	/**
	 * Extracts the configuration options from the input object and updates the configuration values in the config object.
	 *
	 * @param \MW_Config_Interface $conf Configuration object
	 * @param array $options List of option key/value pairs
	 * @param array Associative list of database configurations
	 */
	protected function setOptions( \MW_Config_Interface $conf, array $options )
	{
		foreach( $options as $option )
		{
			list( $name, $value ) = explode( ':', $option );
			$conf->set( $name, $value );
		}
	}
}