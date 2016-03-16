<?php


namespace Aimeos\Shop\Tests\Unit\Controller;


class ExtadmControllerTest extends \TYPO3\Flow\Tests\UnitTestCase
{
	private $object;
	private $request;
	private $view;


	public function setUp()
	{
		$this->object = $this->getMockBuilder( '\Aimeos\Shop\Controller\ExtadmController' )
			->setMethods( null )
			->disableOriginalConstructor()
			->getMock();

		$this->view = $this->getMockBuilder( '\TYPO3\Flow\Mvc\View\JsonView' )
			->setMethods( array( 'assign', 'assignMultiple' ) )
			->disableOriginalConstructor()
			->getMock();

		$this->inject( $this->object, 'view', $this->view );

		$this->request = $this->getMockBuilder( '\TYPO3\Flow\Mvc\ActionRequest' )
			->setMethods( array( 'getArguments', 'getHttpRequest' ) )
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
		$this->object = $this->getMockBuilder( '\Aimeos\Shop\Controller\ExtadmController' )
			->setMethods( array( 'setLocale', 'getJsonLanguages', 'getJsonClientConfig', 'getJsonSiteItem', 'getJsonClientI18n' ) )
			->disableOriginalConstructor()
			->getMock();

		$this->inject( $this->object, 'view', $this->view );
		$this->inject( $this->object, 'request', $this->request );


		$aimeos = new \Aimeos\Shop\Base\Aimeos();
		$this->inject( $this->object, 'aimeos', $aimeos );


		$context = $this->getMockBuilder( '\Aimeos\Shop\Base\Context' )
			->setMethods( array( 'get' ) )
			->disableOriginalConstructor()
			->getMock();

		$ctx = new \Aimeos\MShop\Context\Item\Standard();
		$ctx->setConfig( new \Aimeos\MW\Config\PHPArray() );
		$ctx->setLocale( new \Aimeos\MShop\Locale\Item\Standard( array( 'langid' => 'de' ) ) );

		$this->inject( $this->object, 'context', $context );


		$uriBuilder = $this->getMockBuilder('\TYPO3\Flow\Mvc\Routing\UriBuilder')
			->setMethods( array( 'uriFor' ) )
			->disableOriginalConstructor()
			->getMock();

		$this->inject( $this->object, 'uriBuilder', $uriBuilder );


		$uriBuilder->expects( $this->exactly( 2 ) )->method( 'uriFor' )
			->will( $this->returnValue( '/test/uri' ) );

		$context->expects( $this->once() )->method( 'get' )
			->will( $this->returnValue( $ctx ) );

		$this->object->expects( $this->once() )->method( 'setLocale' )
			->will( $this->returnArgument( 0 ) );

		$this->view->expects( $this->once() )->method( 'assignMultiple' );


		$this->object->indexAction();
	}


	/**
	 * @test
	 */
	public function doAction()
	{
		$this->object = $this->getMockBuilder( '\Aimeos\Shop\Controller\ExtadmController' )
			->setMethods( array( 'setLocale' ) )
			->disableOriginalConstructor()
			->getMock();

		$this->object->expects( $this->once() )->method( 'setLocale' )
			->will( $this->returnArgument( 0 ) );


		$context = $this->getMockBuilder( '\Aimeos\Shop\Base\Context' )
			->setMethods( array( 'get' ) )
			->disableOriginalConstructor()
			->getMock();

		$ctx = new \Aimeos\MShop\Context\Item\Standard();
		$ctx->setConfig( new \Aimeos\MW\Config\PHPArray() );
		$ctx->setLocale( new \Aimeos\MShop\Locale\Item\Standard( array( 'langid' => 'de' ) ) );

		$context->expects( $this->once() )->method( 'get' )
			->will( $this->returnValue( $ctx ) );
		$this->inject( $this->object, 'context', $context );


		$aimeos = new \Aimeos\Shop\Base\Aimeos();
		$this->inject( $this->object, 'aimeos', $aimeos );

		$view = new \Aimeos\Shop\Base\View();
		$this->inject( $this->object, 'viewcontainer', $view );

		$uriBuilder = $this->getMockBuilder('\TYPO3\Flow\Mvc\Routing\UriBuilder')
			->setMethods( array( 'uriFor' ) )
			->disableOriginalConstructor()
			->getMock();
		$this->inject( $this->object, 'uriBuilder', $uriBuilder );

		$this->request->expects( $this->once() )->method( 'getArguments' )
			->will( $this->returnValue( array()  ) );
		$this->inject( $this->object, 'request', $this->request );

		$request = new \TYPO3\Flow\Http\Request( array(), array(), array(), array() );
		$this->request->expects( $this->once() )->method( 'getHttpRequest' )
			->will( $this->returnValue( $request ) );


		$this->assertStringStartsWith( '{', $this->object->doAction() );
	}


	/**
	 * @test
	 */
	public function fileAction()
	{
		$aimeos = new \Aimeos\Shop\Base\Aimeos();
		$this->inject( $this->object, 'aimeos', $aimeos );


		$response = $this->getMockBuilder( '\TYPO3\Flow\Http\Response' )
			->disableOriginalConstructor()
			->getMock();

		$this->inject( $this->object, 'response', $response );


		$this->assertInstanceOf( '\TYPO3\Flow\Mvc\ResponseInterface', $this->object->fileAction() );
	}


	/**
	 * @test
	 */
	public function getJsonLanguages()
	{
		$aimeos = new \Aimeos\Shop\Base\Aimeos();
		$this->inject( $this->object, 'aimeos', $aimeos );

		$class = new \ReflectionClass( '\Aimeos\Shop\Controller\ExtadmController' );
		$method = $class->getMethod( 'getJsonLanguages' );
		$method->setAccessible( true );


		$result = json_decode( $method->invoke( $this->object ), true );

		$this->assertGreaterThan( 0, count( $result ) );
	}


	/**
	 * @test
	 */
	public function getJsonClientConfig()
	{
		$ctx = $this->getMockBuilder( '\Aimeos\MShop\Context\Item\Standard' )
			->setMethods( array( 'setEditor' ) )
			->getMock();

		$ctx->setConfig( new \Aimeos\MW\Config\PHPArray() );


		$class = new \ReflectionClass( '\Aimeos\Shop\Controller\ExtadmController' );
		$method = $class->getMethod( 'getJsonClientConfig' );
		$method->setAccessible( true );


		$result = json_decode( $method->invoke( $this->object, $ctx ), true );

		$this->assertInternalType( 'array', $result );
		$this->assertArrayHasKey( 'admin', $result );
	}


	/**
	 * @test
	 */
	public function getJsonClientI18n()
	{
		$aimeos = new \Aimeos\Shop\Base\Aimeos();
		$i18nPaths = $aimeos->get()->getI18nPaths();


		$class = new \ReflectionClass( '\Aimeos\Shop\Controller\ExtadmController' );
		$method = $class->getMethod( 'getJsonClientI18n' );
		$method->setAccessible( true );


		$result = json_decode( $method->invoke( $this->object, $i18nPaths, 'de' ), true );

		$this->assertInternalType( 'array', $result );
		$this->assertArrayHasKey( 'admin', $result );
		$this->assertArrayHasKey( 'admin/ext', $result );
	}


	/**
	 * @test
	 */
	public function getJsonSiteItem()
	{
		$ctx = $this->getMock( '\Aimeos\MShop\Context\Item\Standard' );


		$siteManager = $this->getMockBuilder( '\Aimeos\MShop\Locale\Manager\Site\Standard' )
			->setMethods( array( 'searchItems' ) )
			->disableOriginalConstructor()
			->getMock();

		$items = array( new \Aimeos\MShop\Locale\Item\Site\Standard( array( 'id' => '1', 'label' => 'default' ) ) );

		$siteManager->expects( $this->once() )->method( 'searchItems' )
			->will( $this->returnValue( $items ) );

		\Aimeos\MShop\Factory::injectManager( $ctx, 'locale/site', $siteManager );


		$class = new \ReflectionClass( '\Aimeos\Shop\Controller\ExtadmController' );
		$method = $class->getMethod( 'getJsonSiteItem' );
		$method->setAccessible( true );


		$result = json_decode( $method->invoke( $this->object, $ctx, 'default' ), true );

		$this->assertInternalType( 'array', $result );
		$this->assertArrayHasKey( 'locale.site.id', $result );
		$this->assertArrayHasKey( 'locale.site.label', $result );
	}


	/**
	 * @test
	 */
	public function setLocale()
	{
		$ctx = $this->getMockBuilder( '\Aimeos\MShop\Context\Item\Standard' )
			->setMethods( array( 'setLocale' ) )
			->getMock();

		$ctx->expects( $this->once() )->method( 'setLocale' );

		$ctx->setConfig( new \Aimeos\MW\Config\PHPArray() );


		$localeManager = $this->getMockBuilder( '\Aimeos\MShop\Locale\Manager\Standard' )
			->setMethods( array( 'bootstrap' ) )
			->disableOriginalConstructor()
			->getMock();

		$localeManager->expects( $this->once() )->method( 'bootstrap' )
			->will( $this->returnValue( new \Aimeos\MShop\Locale\Item\Standard() ) );

		\Aimeos\MShop\Locale\Manager\Factory::injectManager( '\Aimeos\MShop\Locale\Manager\Standard', $localeManager );


		$class = new \ReflectionClass( '\Aimeos\Shop\Controller\ExtadmController' );
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


		$class = new \ReflectionClass( '\Aimeos\Shop\Controller\ExtadmController' );
		$method = $class->getMethod( 'setLocale' );
		$method->setAccessible( true );

		$result = $method->invokeArgs( $this->object, array( $ctx ) );

		$this->assertInstanceOf( '\Aimeos\MShop\Context\Item\Iface', $result );
	}
}