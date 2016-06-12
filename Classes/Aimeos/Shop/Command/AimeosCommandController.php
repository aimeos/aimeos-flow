<?php

/**
 * @license LGPLv3, http://www.gnu.org/copyleft/lgpl.html
 * @copyright Aimeos (aimeos.org), 2015-2016
 * @package flow
 * @subpackage Command
 */


namespace Aimeos\Shop\Command;

use TYPO3\Flow\Annotations as Flow;


/**
 * Aimeos CLI controller for cronjobs
 *
 * @package flow
 * @subpackage Command
 * @Flow\Scope("singleton")
 */
class AimeosCommandController extends \TYPO3\Flow\Cli\CommandController
{
	/**
	 * @var \TYPO3\Flow\Cache\Frontend\StringFrontend
	 */
	protected $cache;

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
		$context = $this->objectManager->get( '\\Aimeos\\Shop\\Base\\Context' )->get();
		$context->setEditor( 'aimeos:cache' );

		$config = $context->getConfig();
		$name = $config->get( 'flow/cache/name', 'Flow' );

		$localeManager = \Aimeos\MShop\Locale\Manager\Factory::createManager( $context );

		foreach( $this->getSiteItems( $context, $sites ) as $siteItem )
		{
			$localeItem = $localeManager->bootstrap( $siteItem->getCode(), '', '', false );

			$lcontext = clone $context;
			$lcontext->setLocale( $localeItem );

			switch( $name )
			{
				case 'None':
					$config->set( 'client/html/basket/cache/enable', false );
					$cache = \Aimeos\MW\Cache\Factory::createManager( 'None', array(), null );
					break;
				case 'Flow':
					$cache = new \Aimeos\MAdmin\Cache\Proxy\Flow( $lcontext, $this->cache );
					break;
				default:
					$cache = new \Aimeos\MAdmin\Cache\Proxy\Standard( $lcontext );
					break;
			}

			$this->outputFormatted( 'Clearing the Aimeos cache for site <b>%s</b>', array( $siteItem->getCode() ) );

			$cache->flush();
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
	 * - customer/email/watch (send customers e-mails if their watched products have changed)
	 * - index/rebuild (rebuild the catalog index once a day after midnight)
	 * - index/optimize (optimize the catalog index once a day one hour after the rebuild)
	 * - order/cleanup/unfinished (remove unfinised orders once a day)
	 * - order/cleanup/unfinised (remove unpaid orders once a day)
	 * - order/email/delivery (send delivery status update e-mails to the customers every few hours)
	 * - order/email/payment (send payment status update e-mails to the customers every few hours)
	 * - order/service/async (import batch delivery or payment status updates if necessary)
	 * - order/service/delivery (sends paid orders to the ERP system or logistic partner)
	 * - order/service/payment (captures authorized payments after the configured amount of time automatically)
	 * - product/bought (updates the suggested products based on what other customers bought once a day)
	 * - product/export (export products)
	 * - product/export/sitemap (generate product sitemaps for search engines)
	 * - product/import/csv (import products from CSV files)
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
		$aimeos = $this->objectManager->get( '\\Aimeos\\Shop\\Base\\Aimeos' )->get();
		$context = $this->getContext();

		$jobs = explode( ' ', $jobs );
		$localeManager = \Aimeos\MShop\Factory::createManager( $context, 'locale' );

		foreach( $this->getSiteItems( $context, $sites ) as $siteItem )
		{
			$localeItem = $localeManager->bootstrap( $siteItem->getCode(), '', '', false );
			$localeItem->setLanguageId( null );
			$localeItem->setCurrencyId( null );

			$context->setLocale( $localeItem );

			$this->outputFormatted( 'Executing jobs for site <b>%s</b>', array( $siteItem->getCode() ) );

			foreach( $jobs as $jobname )
			{
				$this->outputFormatted( '  <b>%s</b>', array( $jobname ) );
				\Aimeos\Controller\Jobs\Factory::createController( $context, $aimeos, $jobname )->run();
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
	 * @param string $tplsite Template site for creating or updating database entries
	 * @param array $option Optional setup configuration, name and value are separated by ":" like "setup/default/demo:1".
	 * @param string|null $task Setup task name to execute
	 * @param string $action Setup task action, e.g. "migrate", "rollback" or "clean"
	 * @return void
	 */
	public function setupCommand( $site = 'default', $tplsite = 'default', array $option = array(), $task = null, $action = 'migrate' )
	{
		$context = $this->objectManager->get( '\\Aimeos\\Shop\\Base\\Context' )->get();
		$context->setEditor( 'aimeos:setup' );

		$config = $context->getConfig();
		$config->set( 'setup/site', $site );
		$dbconfig = $this->getDbConfig( $config );
		$this->setOptions( $config, $option );

		$taskPaths = $this->objectManager->get( '\\Aimeos\\Shop\\Base\\Aimeos' )->get()->getSetupPaths( $tplsite );

		$includePaths = $taskPaths;
		$includePaths[] = get_include_path();

		if( set_include_path( implode( PATH_SEPARATOR, $includePaths ) ) === false ) {
			throw new Exception( 'Unable to extend include path' );
		}

		spl_autoload_register( '\Aimeos\Shop\Command\AimeosCommandController::autoload', true );

		$manager = new \Aimeos\MW\Setup\Manager\Multiple( $context->getDatabaseManager(), $dbconfig, $taskPaths, $context );

		$this->outputFormatted( 'Initializing or updating the Aimeos database tables for site <b>%s</b>', array( $site ) );

		switch( $action )
		{
			case 'migrate':
				$manager->migrate( $task );
				break;
			case 'rollback':
				$manager->rollback( $task );
				break;
			case 'clean':
				$manager->clean( $task );
				break;
			default:
				throw new \Exception( sprintf( 'Invalid setup action "%1$s"', $action ) );
		}
	}


	/**
	 * Loads the requested setup task class
	 *
	 * @param string $classname Name of the setup task class
	 * @return boolean True if class is found, false if not
	 */
	public static function autoload( $classname )
	{
		if( strncmp( $classname, 'Aimeos\\MW\\Setup\\Task\\', 21 ) === 0 )
		{
		    $fileName = substr( $classname, 21 ) . '.php';
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
	 * Returns a context object for the jobs command
	 *
	 * @return \Aimeos\MShop\Context\Item\Standard Context object
	 */
	protected function getContext()
	{
		$aimeos = $this->objectManager->get( '\\Aimeos\\Shop\\Base\\Aimeos' )->get();
		$context = $this->objectManager->get( '\\Aimeos\\Shop\\Base\\Context' )->get();
		$uriBuilder = $this->objectManager->get( '\\TYPO3\\Flow\\Mvc\\Routing\\UriBuilder' );

		$tmplPaths = $aimeos->getCustomPaths( 'controller/jobs/templates' );

		$langManager = \Aimeos\MShop\Locale\Manager\Factory::createManager( $context )->getSubManager( 'language' );
		$langids = array_keys( $langManager->searchItems( $langManager->createSearch( true ) ) );

		$i18n = $this->objectManager->get( '\\Aimeos\\Shop\\Base\\I18n' )->get( $langids );
		$view = $this->objectManager->get( '\\Aimeos\\Shop\\Base\\View' )->create( $context->getConfig(), $uriBuilder, $tmplPaths );

		$context->setEditor( 'aimeos:jobs' );
		$context->setView( $view );
		$context->setI18n( $i18n );

		return $context;
	}


	/**
	 * Returns the database configuration from the config object.
	 *
	 * @param \Aimeos\MW\Config\Iface $conf Config object
	 * @return array Multi-dimensional associative list of database configuration parameters
	 */
	protected function getDbConfig( \Aimeos\MW\Config\Iface $conf )
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
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context item object
	 * @param string $sites Unique site codes
	 * @return \Aimeos\MShop\Locale\Item\Site\Iface[] List of site items
	 */
	protected function getSiteItems( \Aimeos\MShop\Context\Item\Iface $context, $sites )
	{
		$manager = \Aimeos\MShop\Factory::createManager( $context, 'locale/site' );
		$search = $manager->createSearch();

		if( $sites !== '' ) {
			$search->setConditions( $search->compare( '==', 'locale.site.code', explode( ' ', $sites ) ) );
		}

		return $manager->searchItems( $search );
	}


	/**
	 * @param \TYPO3\Flow\Cache\Frontend\StringFrontend $cache
	 * @return void
	 */
	public function injectCache( \TYPO3\Flow\Cache\Frontend\StringFrontend $cache )
	{
		$this->cache = $cache;
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
	 * Extracts the configuration options from the input object and updates the configuration values in the config object.
	 *
	 * @param \Aimeos\MW\Config\Iface $conf Configuration object
	 * @param array $options List of option key/value pairs
	 * @param array Associative list of database configurations
	 */
	protected function setOptions( \Aimeos\MW\Config\Iface $conf, array $options )
	{
		foreach( $options as $option )
		{
			list( $name, $value ) = explode( ':', $option );
			$conf->set( $name, $value );
		}
	}
}
