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
		\MShop_Factory::clear();
	}


	/**
	 * @test
	 */
	public function indexAction()
	{
		$this->object = $this->getMockBuilder( '\Aimeos\Shop\Controller\AdminController' )
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

		$ctx = new \MShop_Context_Item_Default();
		$ctx->setConfig( new \MW_Config_Array() );
		$ctx->setLocale( new \MShop_Locale_Item_Default( array( 'langid' => 'de' ) ) );

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
		$this->object = $this->getMockBuilder( '\Aimeos\Shop\Controller\AdminController' )
			->setMethods( array( 'setLocale' ) )
			->disableOriginalConstructor()
			->getMock();

		$this->object->expects( $this->once() )->method( 'setLocale' )
			->will( $this->returnArgument( 0 ) );


		$context = $this->getMockBuilder( '\Aimeos\Shop\Base\Context' )
			->setMethods( array( 'get' ) )
			->disableOriginalConstructor()
			->getMock();

		$ctx = new \MShop_Context_Item_Default();
		$ctx->setConfig( new \MW_Config_Array() );
		$ctx->setLocale( new \MShop_Locale_Item_Default( array( 'langid' => 'de' ) ) );

		$context->expects( $this->once() )->method( 'get' )
			->will( $this->returnValue( $ctx ) );

		$this->inject( $this->object, 'context', $context );


		$aimeos = new \Aimeos\Shop\Base\Aimeos();
		$this->inject( $this->object, 'aimeos', $aimeos );


		$this->request->expects( $this->once() )->method( 'getArguments' )
			->will( $this->returnValue( array()  ) );

		$this->inject( $this->object, 'request', $this->request );


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
		$this->object = $this->getMockBuilder( '\Aimeos\Shop\Controller\AdminController' )
			->setMethods( array( 'getLanguages' ) )
			->disableOriginalConstructor()
			->getMock();

		$this->object->expects( $this->once() )->method( 'getLanguages' )
			->will( $this->returnValue( array( 'id' => 'en', 'label' => 'English' ) ) );

		$ctx = $this->getMock( 'MShop_Context_Item_Default' );

		$aimeos = new \Aimeos\Shop\Base\Aimeos();
		$this->inject( $this->object, 'aimeos', $aimeos );


		$class = new \ReflectionClass( '\Aimeos\Shop\Controller\AdminController' );
		$method = $class->getMethod( 'getJsonLanguages' );
		$method->setAccessible( true );


		$result = json_decode( $method->invoke( $this->object, $ctx ), true );

		$this->assertEquals( array( 'id' => 'en', 'label' => 'English' ), $result );
	}


	/**
	 * @test
	 */
	public function getJsonClientConfig()
	{
		$ctx = $this->getMockBuilder( 'MShop_Context_Item_Default' )
			->setMethods( array( 'setEditor' ) )
			->getMock();

		$ctx->setConfig( new \MW_Config_Array() );


		$class = new \ReflectionClass( '\Aimeos\Shop\Controller\AdminController' );
		$method = $class->getMethod( 'getJsonClientConfig' );
		$method->setAccessible( true );


		$result = json_decode( $method->invoke( $this->object, $ctx ), true );

		$this->assertInternalType( 'array', $result );
		$this->assertArrayHasKey( 'client', $result );
	}


	/**
	 * @test
	 */
	public function getJsonClientI18n()
	{
		$aimeos = new \Aimeos\Shop\Base\Aimeos();
		$i18nPaths = $aimeos->get()->getI18nPaths();


		$class = new \ReflectionClass( '\Aimeos\Shop\Controller\AdminController' );
		$method = $class->getMethod( 'getJsonClientI18n' );
		$method->setAccessible( true );


		$result = json_decode( $method->invoke( $this->object, $i18nPaths, 'de' ), true );

		$this->assertInternalType( 'array', $result );
		$this->assertArrayHasKey( 'client/extjs', $result );
		$this->assertArrayHasKey( 'client/extjs/ext', $result );
	}


	/**
	 * @test
	 */
	public function getJsonSiteItem()
	{
		$ctx = $this->getMock( 'MShop_Context_Item_Default' );


		$siteManager = $this->getMockBuilder( 'MShop_Locale_Manager_Site_Default' )
			->setMethods( array( 'searchItems' ) )
			->disableOriginalConstructor()
			->getMock();

		$items = array( new \MShop_Locale_Item_Site_Default( array( 'id' => '1', 'label' => 'default' ) ) );

		$siteManager->expects( $this->once() )->method( 'searchItems' )
			->will( $this->returnValue( $items ) );

		\MShop_Factory::injectManager( $ctx, 'locale/site', $siteManager );


		$class = new \ReflectionClass( '\Aimeos\Shop\Controller\AdminController' );
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
	public function getLanguages()
	{
		$ctx = $this->getMock( 'MShop_Context_Item_Default' );

		$manager = $this->getMockBuilder( 'MShop_Locale_Manager_Language_Default' )
			->setMethods( array( 'searchItems' ) )
			->disableOriginalConstructor()
			->getMock();

		$items = array(
			'de' => new \MShop_Locale_Item_Language_Default( array( 'id' => 'de', 'label' => 'German' ) ),
			'en' => new \MShop_Locale_Item_Language_Default( array( 'id' => 'en', 'label' => 'English' ) ),
		);

		$manager->expects( $this->once() )->method( 'searchItems' )
			->will( $this->returnValue( $items ) );

		\MShop_Factory::injectManager( $ctx, 'locale/language', $manager );


		$class = new \ReflectionClass( '\Aimeos\Shop\Controller\AdminController' );
		$method = $class->getMethod( 'getLanguages' );
		$method->setAccessible( true );


		$result = $method->invoke( $this->object, $ctx, array( 'de', 'en' ) );

		$this->assertInternalType( 'array', $result );
		$this->assertEquals( 2, count( $result ) );
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
		$ctx = $this->getMockBuilder( 'MShop_Context_Item_Default' )
			->setMethods( array( 'setLocale' ) )
			->getMock();

		$ctx->expects( $this->once() )->method( 'setLocale' );

		$ctx->setConfig( new \MW_Config_Array() );


		$localeManager = $this->getMockBuilder( 'MShop_Locale_Manager_Default' )
			->setMethods( array( 'bootstrap' ) )
			->disableOriginalConstructor()
			->getMock();

		$localeManager->expects( $this->once() )->method( 'bootstrap' )
			->will( $this->returnValue( new \MShop_Locale_Item_Default() ) );

		\MShop_Locale_Manager_Factory::injectManager( 'MShop_Locale_Manager_Default', $localeManager );


		$class = new \ReflectionClass( '\Aimeos\Shop\Controller\AdminController' );
		$method = $class->getMethod( 'setLocale' );
		$method->setAccessible( true );

		$result = $method->invokeArgs( $this->object, array( $ctx ) );

		$this->assertInstanceOf( 'MShop_Context_Item_Interface', $result );
	}


	/**
	 * @test
	 */
	public function setLocaleException()
	{
		$ctx = $this->getMockBuilder( 'MShop_Context_Item_Default' )
			->setMethods( array( 'setLocale' ) )
			->getMock();

		$ctx->expects( $this->once() )->method( 'setLocale' );

		$ctx->setConfig( new \MW_Config_Array() );


		$localeManager = $this->getMockBuilder( 'MShop_Locale_Manager_Default' )
			->setMethods( array( 'bootstrap', 'createItem' ) )
			->disableOriginalConstructor()
			->getMock();

		$localeManager->expects( $this->once() )->method( 'bootstrap' )
			->will( $this->throwException( new \MShop_Locale_Exception() ) );

		$localeManager->expects( $this->once() )->method( 'createItem' )
			->will( $this->returnValue( new \MShop_Locale_Item_Default() ) );

		\MShop_Locale_Manager_Factory::injectManager( 'MShop_Locale_Manager_Default', $localeManager );


		$class = new \ReflectionClass( '\Aimeos\Shop\Controller\AdminController' );
		$method = $class->getMethod( 'setLocale' );
		$method->setAccessible( true );

		$result = $method->invokeArgs( $this->object, array( $ctx ) );

		$this->assertInstanceOf( 'MShop_Context_Item_Interface', $result );
	}
}