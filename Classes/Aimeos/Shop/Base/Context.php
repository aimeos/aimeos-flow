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
class Context
{
	/**
	 * @var \Aimeos\MShop\Context\Item\Iface
	 */
	private static $context;

	/**
	 * @var array
	 */
	private $settings;

	/**
	 * @var \Neos\Cache\Frontend\StringFrontend
	 */
	private $cache;

	/**
	 * @var \Aimeos\Shop\Base\Aimeos
	 * @Flow\Inject
	 */
	protected $aimeos;

	/**
	 * @var \Aimeos\Shop\Base\Config
	 * @Flow\Inject
	 */
	protected $config;

	/**
	 * @var \Aimeos\Shop\Base\I18n
	 * @Flow\Inject
	 */
	protected $i18n;

	/**
	 * @var \Aimeos\Shop\Base\Locale
	 * @Flow\Inject
	 */
	protected $locale;

	/**
	 * @var \Neos\SwiftMailer\MailerInterface
	 * @Flow\Inject
	 */
	protected $mailer;

	/**
	 * @var \Neos\Flow\Session\SessionInterface
	 * @Flow\Inject(lazy = FALSE)
	 */
	protected $session;


	/**
	 * Returns the current context.
	 *
	 * @param \Neos\Flow\Mvc\RequestInterface|null $request Request object
	 * @return \Aimeos\MShop\Context\Item\Iface
	 */
	public function get( \Neos\Flow\Mvc\RequestInterface $request = null, $type = 'frontend' )
	{
		$config = $this->config->get( $type );

		if( self::$context === null )
		{
			$context = new \Aimeos\MShop\Context\Item\Standard();
			$context->setConfig( $config );

			$this->addDataBaseManager( $context );
			$this->addFilesystemManager( $context );
			$this->addMessageQueueManager( $context );
			$this->addCache( $context );
			$this->addLogger( $context );
			$this->addMailer( $context );
			$this->addProcess( $context );

			self::$context = $context;
		}

		$context = self::$context;
		$context->setConfig( $config );

		if( $request !== null )
		{
			$localeItem = $this->locale->get( $context, $request );
			$context->setI18n( $this->i18n->get( array( $localeItem->getLanguageId() ) ) );
			$context->setLocale( $localeItem );
		}

		$this->addSession( $context );
		$this->addUser( $context, $request );

		return $context;
	}


	/**
	 * Adds the cache object to the context
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object including config
	 * @return \Aimeos\MShop\Context\Item\Iface Modified context object
	 */
	protected function addCache( \Aimeos\MShop\Context\Item\Iface $context )
	{
		$config = $context->getConfig();

		switch( $config->get( 'flow/cache/name', 'Flow' ) )
		{
			case 'None':
				$config->set( 'client/html/basket/cache/enable', false );
				return $context->setCache( \Aimeos\MW\Cache\Factory::createManager( 'None', array(), null ) );

			case 'Flow':
				return $context->setCache( new \Aimeos\MAdmin\Cache\Proxy\Flow( $context, $this->cache ) );

			default:
				return $context->setCache( new \Aimeos\MAdmin\Cache\Proxy\Standard( $context ) );
		}
	}


	/**
	 * Adds the database manager object to the context
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object
	 * @return \Aimeos\MShop\Context\Item\Iface Modified context object
	 */
	protected function addDatabaseManager( \Aimeos\MShop\Context\Item\Iface $context )
	{
		$dbm = new \Aimeos\MW\DB\Manager\DBAL( $context->getConfig() );

		return $context->setDatabaseManager( $dbm );
	}


	/**
	 * Adds the filesystem manager object to the context
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object
	 * @return \Aimeos\MShop\Context\Item\Iface Modified context object
	 */
	protected function addFilesystemManager( \Aimeos\MShop\Context\Item\Iface $context )
	{
		$fs = new \Aimeos\MW\Filesystem\Manager\Standard( $context->getConfig() );

		return $context->setFilesystemManager( $fs );
	}


	/**
	 * Adds the logger object to the context
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object
	 * @return \Aimeos\MShop\Context\Item\Iface Modified context object
	 */
	protected function addLogger( \Aimeos\MShop\Context\Item\Iface $context )
	{
		$logger = \Aimeos\MAdmin\Log\Manager\Factory::createManager( $context );

		return $context->setLogger( $logger );
	}



	/**
	 * Adds the mailer object to the context
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object
	 * @return \Aimeos\MShop\Context\Item\Iface Modified context object
	 */
	protected function addMailer( \Aimeos\MShop\Context\Item\Iface $context )
	{
		$mail = new \Aimeos\MW\Mail\Swift( $this->mailer );

		return $context->setMail( $mail );
	}


	/**
	 * Adds the message queue manager object to the context
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object
	 * @return \Aimeos\MShop\Context\Item\Iface Modified context object
	 */
	protected function addMessageQueueManager( \Aimeos\MShop\Context\Item\Iface $context )
	{
		$mq = new \Aimeos\MW\MQueue\Manager\Standard( $context->getConfig() );

		return $context->setMessageQueueManager( $mq );
	}


	/**
	 * Adds the process object to the context
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object
	 * @return \Aimeos\MShop\Context\Item\Iface Modified context object
	 */
	protected function addProcess( \Aimeos\MShop\Context\Item\Iface $context )
	{
		$config = $context->getConfig();
		$max = $config->get( 'pcntl_max', 4 );
		$prio = $config->get( 'pcntl_priority', 19 );

		$process = new \Aimeos\MW\Process\None( $max, $prio );
		$process = new \Aimeos\MW\Process\Decorator\Check( $process );

		return $context->setProcess( $process );
	}


	/**
	 * Adds the session object to the context
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object
	 * @return \Aimeos\MShop\Context\Item\Iface Modified context object
	 */
	protected function addSession( \Aimeos\MShop\Context\Item\Iface $context )
	{
		$session = new \Aimeos\MW\Session\Flow( $this->session );

		return $context->setSession( $session );
	}


	/**
	 * Adds the user ID and name if available
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object
	 * @param \Neos\Flow\Mvc\RequestInterface|null $request Request object
	 */
	protected function addUser( \Aimeos\MShop\Context\Item\Iface $context, \Neos\Flow\Mvc\RequestInterface $request = null )
	{
		if( $request instanceof \Neos\Flow\Mvc\ActionRequest ) {
			$context->setEditor( $request->getHttpRequest()->getClientIpAddress() );
		}
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
	 * @param \Neos\Cache\Frontend\StringFrontend $cache Cache for shop data
	 * @return void
	 */
	public function setCache( \Neos\Cache\Frontend\StringFrontend $cache )
	{
		$this->cache = $cache;
	}
}
