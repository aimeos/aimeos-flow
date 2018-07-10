<?php

/**
 * @license LGPLv3, http://www.gnu.org/copyleft/lgpl.html
 * @copyright Aimeos (aimeos.org), 2015-2016
 * @package flow
 * @subpackage Command
 */


namespace Aimeos\Shop\Command;

use Neos\Flow\Annotations as Flow;


/**
 * Aimeos CLI controller for cronjobs
 *
 * @package flow
 * @subpackage Command
 * @Flow\Scope("singleton")
 */
class AimeosCommandController extends \Neos\Flow\Cli\CommandController
{
	/**
	 * @var string
	 * @Flow\InjectConfiguration(path="http.baseUri", package="Neos.Flow")
	 */
	protected $baseUri;

	/**
	 * @var \Neos\Cache\Frontend\StringFrontend
	 */
	protected $cache;

	/**
	 * @var \Neos\Flow\ObjectManagement\ObjectManagerInterface
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
		$context = $this->objectManager->get( '\\Aimeos\\Shop\\Base\\Context' )->get( null, 'command' );
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

			$cache->clear();
		}
	}


	/**
	 * Executes the Aimeos maintenance jobs
	 *
	 * The Aimeos shop system needs some maintenance tasks that must be
	 * regularly executed. These include
	 *
	 * - admin/cache (remove expired cache entries once a day)
	 * - admin/log (archivate and delete old log entries once a day)
	 * - catalog/import/csv (import categories from CSV files)
	 * - customer/email/account (create new customer accounts and send e-mails)
	 * - customer/email/watch (send customers e-mails if their watched products have changed)
	 * - index/rebuild (rebuild the catalog index once a day after midnight)
	 * - index/optimize (optimize the catalog index once a day one hour after the rebuild)
	 * - media/scale (rescales the product images to the new sizes)
	 * - order/cleanup/unfinished (remove unfinised orders once a day)
	 * - order/cleanup/unfinised (remove unpaid orders once a day)
	 * - order/email/delivery (send delivery status update e-mails to the customers every few hours)
	 * - order/email/payment (send payment status update e-mails to the customers every few hours)
	 * - order/export/csv (export orders in admin interface)
	 * - order/service/async (import batch delivery or payment status updates if necessary)
	 * - order/service/delivery (sends paid orders to the ERP system or logistic partner)
	 * - order/service/payment (captures authorized payments after the configured amount of time automatically)
	 * - product/bought (updates the suggested products based on what other customers bought once a day)
	 * - product/export (export products)
	 * - product/export/sitemap (generate product sitemaps for search engines)
	 * - product/import/csv (import products from CSV files)
	 * - subscription/export/csv (export subscriptions in admin interface)
	 * - subscription/process/begin (start subscription period and add permissions if applicable)
	 * - subscription/process/renew (renew subscriptions on next date)
	 * - subscription/process/end (finish subscription period and revoke permissions if applicable)
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
		$process = $context->getProcess();

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
				$fcn = function( $context, $aimeos, $jobname ) {
					\Aimeos\Controller\Jobs\Factory::createController( $context, $aimeos, $jobname )->run();
				};

				$process->start( $fcn, [$context, $aimeos, $jobname], true );
			}
		}

		$process->wait();
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
		$context = $this->objectManager->get( '\\Aimeos\\Shop\\Base\\Context' )->get( null, 'command' );
		$context->setEditor( 'aimeos:setup' );

		$config = $context->getConfig();
		$config->set( 'setup/site', $site );
		$dbconfig = $this->getDbConfig( $config );
		$this->setOptions( $config, $option );

		$taskPaths = $this->objectManager->get( '\\Aimeos\\Shop\\Base\\Aimeos' )->get()->getSetupPaths( $tplsite );
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
	 * Returns a context object for the jobs command
	 *
	 * @return \Aimeos\MShop\Context\Item\Standard Context object
	 */
	protected function getContext()
	{
		$aimeos = $this->objectManager->get( '\\Aimeos\\Shop\\Base\\Aimeos' )->get();
		$context = $this->objectManager->get( '\\Aimeos\\Shop\\Base\\Context' )->get( null, 'command' );
		$uriBuilder = $this->objectManager->get( '\\Neos\\Flow\\Mvc\\Routing\\UriBuilder' );

		$request = \Neos\Flow\Http\Request::createFromEnvironment();
		$request->setBaseUri( new \Neos\Flow\Http\Uri( $this->baseUri ) );
		$uriBuilder->setRequest( new \Neos\Flow\Mvc\ActionRequest( $request ) );

		$tmplPaths = $aimeos->getCustomPaths( 'controller/jobs/templates' );

		$langManager = \Aimeos\MShop\Locale\Manager\Factory::createManager( $context )->getSubManager( 'language' );
		$langids = array_keys( $langManager->searchItems( $langManager->createSearch( true ) ) );

		$i18n = $this->objectManager->get( '\\Aimeos\\Shop\\Base\\I18n' )->get( $langids );
		$view = $this->objectManager->get( '\\Aimeos\\Shop\\Base\\View' )->create( $context, $uriBuilder, $tmplPaths );

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
	 * @param \Neos\Cache\Frontend\StringFrontend $cache
	 * @return void
	 */
	public function injectCache( \Neos\Cache\Frontend\StringFrontend $cache )
	{
		$this->cache = $cache;
	}


	/**
	 * @param \Neos\Flow\ObjectManagement\ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function injectObjectManager( \Neos\Flow\ObjectManagement\ObjectManagerInterface $objectManager )
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
			$conf->set( str_replace( '\\', '/', $name ), $value );
		}
	}
}
