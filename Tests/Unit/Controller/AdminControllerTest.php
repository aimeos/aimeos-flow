<?php


namespace Aimeos\Shop\Tests\Unit\Controller;


class AdminControllerTest extends \TYPO3\Flow\Tests\UnitTestCase
{
	private $object;
	private $request;
	private $view;


	public function setUp()
	{
		$this->object = $this->getMockBuilder( '\Aimeos\Shop\Controller\AdminController' )
			->setMethods( null )
			->disableOriginalConstructor()
			->getMock();

		$this->view = $this->getMockBuilder( '\TYPO3\Flow\Mvc\View\JsonView' )
			->setMethods( array( 'assign', 'assignMultiple' ) )
			->disableOriginalConstructor()
			->getMock();

		$this->inject( $this->object, 'view', $this->view );

		$this->request = $this->getMockBuilder( '\TYPO3\Flow\Mvc\ActionRequest' )
			->setMethods( array( 'getArguments' ) )
			->disableOriginalConstructor()
			->getMock();

		$this->inject( $this->object, 'request', $this->request );
	}


	public function tearDown()
	{
		\Aimeos\MShop\Factory::clear();
	}


	/**
	 * @test
	 */
	public function indexAction()
	{
		$this->object = $this->getMockBuilder( '\Aimeos\Shop\Controller\AdminController' )
			->disableOriginalConstructor()
			->getMock();

		$this->inject( $this->object, 'request', $this->request );

		$this->request->expects( $this->once() )->method( 'hasArgument' );
		$this->request->expects( $this->once() )->method( 'getArgument' );
		$this->object->expects( $this->once() )->method( 'forward' );

		$this->object->indexAction();
	}


	/**
	 * @test
	 */
	public function getVersion()
	{
		$class = new \ReflectionClass( '\Aimeos\Shop\Controller\AdminController' );
		$method = $class->getMethod( 'getVersion' );
		$method->setAccessible( true );

		$result = $method->invokeArgs( $this->object, array() );

		$this->assertInternalType( 'string', $result );
	}


	/**
	 * @test
	 */
	public function setLocale()
	{
		$ctx = $this->getMockBuilder( '\Aimeos\MShop\Context\Item\Standard' )
			->setMethods( array( 'setLocale' ) )
			->getMock();

		$ctx->setConfig( new \Aimeos\MW\Config\PHPArray() );
		$ctx->expects( $this->once() )->method( 'setLocale' );

		$i18n = new \Aimeos\Shop\Base\I18n();
		$this->inject( $this->object, 'i18n', $i18n );


		$localeManager = $this->getMockBuilder( '\Aimeos\MShop\Locale\Manager\Standard' )
			->setMethods( array( 'bootstrap' ) )
			->disableOriginalConstructor()
			->getMock();

		$localeManager->expects( $this->once() )->method( 'bootstrap' )
			->will( $this->returnValue( new \Aimeos\MShop\Locale\Item\Standard() ) );

		\Aimeos\MShop\Locale\Manager\Factory::injectManager( '\Aimeos\MShop\Locale\Manager\Standard', $localeManager );


		$class = new \ReflectionClass( '\Aimeos\Shop\Controller\AdminController' );
		$method = $class->getMethod( 'setLocale' );
		$method->setAccessible( true );

		$result = $method->invokeArgs( $this->object, array( $ctx ) );

		$this->assertInstanceOf( '\Aimeos\MShop\Context\Item\Iface', $result );
	}


	/**
	 * @test
	 */
	public function setLocaleException()
	{
		$ctx = $this->getMockBuilder( '\Aimeos\MShop\Context\Item\Standard' )
			->setMethods( array( 'setLocale' ) )
			->getMock();

		$ctx->expects( $this->once() )->method( 'setLocale' );

		$ctx->setConfig( new \Aimeos\MW\Config\PHPArray() );


		$localeManager = $this->getMockBuilder( '\Aimeos\MShop\Locale\Manager\Standard' )
			->setMethods( array( 'bootstrap', 'createItem' ) )
			->disableOriginalConstructor()
			->getMock();

		$localeManager->expects( $this->once() )->method( 'bootstrap' )
			->will( $this->throwException( new \Aimeos\MShop\Locale\Exception() ) );

		$localeManager->expects( $this->once() )->method( 'createItem' )
			->will( $this->returnValue( new \Aimeos\MShop\Locale\Item\Standard() ) );

		\Aimeos\MShop\Locale\Manager\Factory::injectManager( '\Aimeos\MShop\Locale\Manager\Standard', $localeManager );


		$class = new \ReflectionClass( '\Aimeos\Shop\Controller\AdminController' );
		$method = $class->getMethod( 'setLocale' );
		$method->setAccessible( true );

		$result = $method->invokeArgs( $this->object, array( $ctx ) );

		$this->assertInstanceOf( '\Aimeos\MShop\Context\Item\Iface', $result );
	}
}