<?php


namespace Aimeos\Shop\Tests\Unit\Base;


class ContextTest extends \Neos\Flow\Tests\UnitTestCase
{
	private $object;


	public function setUp()
	{
		$this->object = new \Aimeos\Shop\Base\Context();

		$aimeos = new \Aimeos\Shop\Base\Aimeos();
		$config = new \Aimeos\Shop\Base\Config();
		$i18n = new \Aimeos\Shop\Base\I18n();
		$locale = new \Aimeos\Shop\Base\Locale();

		$mailer = function() {};

		$session = $this->getMockBuilder( 'Neos\Flow\Session\Session' )
			->disableOriginalConstructor()
			->getMock();

		$resource = array(
			'host' => '127.0.0.1',
			'dbname' => 'flow',
			'user' => 'root',
			'password' => '',
		);

		$settings = array( 'flow' => array( 'apc' => array ( 'enable' => true ) ) );

		$this->inject( $config, 'aimeos', $aimeos );
		$this->inject( $i18n, 'aimeos', $aimeos );

		$this->inject( $this->object, 'i18n', $i18n );
		$this->inject( $this->object, 'aimeos', $aimeos );
		$this->inject( $this->object, 'config', $config );
		$this->inject( $this->object, 'locale', $locale );
		$this->inject( $this->object, 'mailer', $mailer );
		$this->inject( $this->object, 'session', $session );
		$this->inject( $this->object, 'settings', $settings );
	}


	public function tearDown()
	{
		\Aimeos\MShop\Locale\Manager\Factory::injectManager( '\Aimeos\MShop\Locale\Manager\Standard', null );
	}


	/**
	 * @test
	 */
	public function get()
	{
		$request = $this->getMockBuilder( '\Neos\Flow\Mvc\ActionRequest' )
			->setMethods( array( 'getArguments', 'getHttpRequest' ) )
			->disableOriginalConstructor()
			->getMock();

		$request->expects( $this->once() )->method( 'getArguments' )
			->will( $this->returnValue( array( 'site' => 'unittest', 'locale' => 'de', 'currency' => 'EUR' ) ) );

		$httpRequest = $this->getMockBuilder( '\Neos\Flow\Http\Request' )
			->disableOriginalConstructor()
			->getMock();

		$request->expects( $this->once() )->method( 'getHttpRequest' )
			->will( $this->returnValue( $httpRequest ) ) ;


		$localeManager = $this->getMockBuilder( '\Aimeos\MShop\Locale\Manager\Standard' )
			->setMethods( array( 'bootstrap' ) )
			->disableOriginalConstructor()
			->getMock();

		$localeManager->expects( $this->once() )->method( 'bootstrap' )
			->will( $this->returnValue( new \Aimeos\MShop\Locale\Item\Standard( array( 'locale.languageid' => 'de' ) ) ) );

		\Aimeos\MShop\Locale\Manager\Factory::injectManager( '\Aimeos\MShop\Locale\Manager\Standard', $localeManager );

		$cache = $this->getMockBuilder( '\Neos\Cache\Frontend\StringFrontend' )
			->disableOriginalConstructor()
			->getMock();

		$this->object->setCache( $cache );


		$context = $this->object->get( $request );

		$this->assertInstanceOf( '\Aimeos\MShop\Context\Item\Iface', $context );
	}


	/**
	 * @test
	 */
	public function getNoCache()
	{
		$request = $this->getMockBuilder( '\Neos\Flow\Mvc\ActionRequest' )
			->setMethods( array( 'getArguments', 'getHttpRequest' ) )
			->disableOriginalConstructor()
			->getMock();

		$request->expects( $this->once() )->method( 'getArguments' )
			->will( $this->returnValue( array() ) );

		$httpRequest = $this->getMockBuilder( '\Neos\Flow\Http\Request' )
			->disableOriginalConstructor()
			->getMock();

		$request->expects( $this->once() )->method( 'getHttpRequest' )
			->will( $this->returnValue( $httpRequest ) );


		$localeManager = $this->getMockBuilder( '\Aimeos\MShop\Locale\Manager\Standard' )
			->setMethods( array( 'bootstrap' ) )
			->disableOriginalConstructor()
			->getMock();

		$localeManager->expects( $this->once() )->method( 'bootstrap' )
			->will( $this->returnValue( new \Aimeos\MShop\Locale\Item\Standard( array( 'locale.languageid' => 'de' ) ) ) );

		\Aimeos\MShop\Locale\Manager\Factory::injectManager( '\Aimeos\MShop\Locale\Manager\Standard', $localeManager );

		$this->object->injectSettings( array( 'flow' => array( 'cache' => array( 'name' => 'None' ) ) ) );


		$context = $this->object->get( $request );

		$this->assertInstanceOf( '\Aimeos\MShop\Context\Item\Iface', $context );
	}


	/**
	 * @test
	 */
	public function getCustomCache()
	{
		$request = $this->getMockBuilder( '\Neos\Flow\Mvc\ActionRequest' )
			->setMethods( array( 'getArguments', 'getHttpRequest' ) )
			->disableOriginalConstructor()
			->getMock();

		$request->expects( $this->once() )->method( 'getArguments' )
			->will( $this->returnValue( array() ) );

		$httpRequest = $this->getMockBuilder( '\Neos\Flow\Http\Request' )
			->disableOriginalConstructor()
			->getMock();

		$request->expects( $this->once() )->method( 'getHttpRequest' )
			->will( $this->returnValue( $httpRequest ) );


		$localeManager = $this->getMockBuilder( '\Aimeos\MShop\Locale\Manager\Standard' )
			->setMethods( array( 'bootstrap' ) )
			->disableOriginalConstructor()
			->getMock();

		$localeManager->expects( $this->once() )->method( 'bootstrap' )
			->will( $this->returnValue( new \Aimeos\MShop\Locale\Item\Standard( array( 'locale.languageid' => 'de' ) ) ) );

		\Aimeos\MShop\Locale\Manager\Factory::injectManager( '\Aimeos\MShop\Locale\Manager\Standard', $localeManager );

		$this->object->injectSettings( array( 'flow' => array( 'cache' => array( 'name' => 'Custom' ) ) ) );


		$context = $this->object->get( $request );

		$this->assertInstanceOf( '\Aimeos\MShop\Context\Item\Iface', $context );
	}


	/**
	 * @test
	 */
	public function injectSettings()
	{
		$this->object->injectSettings( array( 'test' ) );

		$this->assertEquals( array( 'test' ), \PHPUnit\Framework\Assert::readAttribute( $this->object, 'settings' ) );
	}


	/**
	 * @test
	 */
	public function setCache()
	{
		$cache = $this->getMockBuilder( '\Neos\Cache\Frontend\StringFrontend' )
			->disableOriginalConstructor()
			->getMock();

		$this->object->setCache( $cache );

		$this->assertEquals( $cache, \PHPUnit\Framework\Assert::readAttribute( $this->object, 'cache' ) );
	}
}