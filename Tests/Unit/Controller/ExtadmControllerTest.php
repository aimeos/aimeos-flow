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
			->setMethods( array( 'getJsonLanguages', 'getJsonClientConfig', 'getJsonSiteItem', 'getJsonClientI18n' ) )
			->disableOriginalConstructor()
			->getMock();


		$this->view->expects( $this->once() )->method( 'assignMultiple' );

		$this->inject( $this->object, 'view', $this->view );
		$this->inject( $this->object, 'request', $this->request );
		$this->inject( $this->object, 'security', new \TYPO3\Flow\Security\DummyContext() );


		$aimeos = new \Aimeos\Shop\Base\Aimeos();
		$this->inject( $this->object, 'aimeos', $aimeos );

		$locale = new \Aimeos\Shop\Base\Locale();
		$this->inject( $this->object, 'locale', $locale );


		$context = $this->getMockBuilder( '\Aimeos\Shop\Base\Context' )
			->setMethods( array( 'get' ) )
			->disableOriginalConstructor()
			->getMock();

		$ctx = new \Aimeos\MShop\Context\Item\Standard();
		$ctx->setConfig( new \Aimeos\MW\Config\PHPArray() );

		$context->expects( $this->once() )->method( 'get' )
			->will( $this->returnValue( $ctx ) );

		$this->inject( $this->object, 'context', $context );


		$locale = $this->getMockBuilder( '\Aimeos\Shop\Base\Locale' )
			->setMethods( array( 'getBackend' ) )
			->disableOriginalConstructor()
			->getMock();

		$loc = new \Aimeos\MShop\Locale\Item\Standard();

		$locale->expects( $this->once() )->method( 'getBackend' )
			->will( $this->returnValue( $loc ) );

		$this->inject( $this->object, 'locale', $locale );


		$uriBuilder = $this->getMockBuilder('\TYPO3\Flow\Mvc\Routing\UriBuilder')
			->setMethods( array( 'uriFor' ) )
			->disableOriginalConstructor()
			->getMock();

		$uriBuilder->expects( $this->exactly( 3 ) )->method( 'uriFor' )
			->will( $this->returnValue( '/test/uri' ) );

		$this->inject( $this->object, 'uriBuilder', $uriBuilder );


		$this->object->indexAction();
	}


	/**
	 * @test
	 */
	public function doAction()
	{
		$this->object = $this->getMockBuilder( '\Aimeos\Shop\Controller\ExtadmController' )
			->setMethods( array( 'getJsonSiteItem' ) )
			->disableOriginalConstructor()
			->getMock();


		$aimeos = new \Aimeos\Shop\Base\Aimeos();
		$this->inject( $this->object, 'aimeos', $aimeos );

		$view = new \Aimeos\Shop\Base\View();
		$this->inject( $view, 'view', new \TYPO3\Fluid\View\StandaloneView() );
		$this->inject( $view, 'security', new \TYPO3\Flow\Security\DummyContext() );
		$this->inject( $this->object, 'viewcontainer', $view );


		$context = $this->getMockBuilder( '\Aimeos\Shop\Base\Context' )
			->disableOriginalConstructor()
			->setMethods( array( 'get' ) )
			->getMock();

		$ctx = new \Aimeos\MShop\Context\Item\Standard();
		$ctx->setConfig( new \Aimeos\MW\Config\PHPArray() );

		$context->expects( $this->once() )->method( 'get' )
			->will( $this->returnValue( $ctx ) );

		$this->inject( $this->object, 'context', $context );


		$locale = $this->getMockBuilder( '\Aimeos\Shop\Base\Locale' )
			->setMethods( array( 'getBackend' ) )
			->disableOriginalConstructor()
			->getMock();

		$loc = new \Aimeos\MShop\Locale\Item\Standard();

		$locale->expects( $this->once() )->method( 'getBackend' )
			->will( $this->returnValue( $loc ) );

		$this->inject( $this->object, 'locale', $locale );


		$uriBuilder = $this->getMockBuilder('\TYPO3\Flow\Mvc\Routing\UriBuilder')
			->setMethods( array( 'uriFor' ) )
			->disableOriginalConstructor()
			->getMock();

		$this->inject( $this->object, 'uriBuilder', $uriBuilder );


		$request = new \TYPO3\Flow\Http\Request( array(), array(), array(), array() );

		$this->request->expects( $this->once() )->method( 'getHttpRequest' )
			->will( $this->returnValue( $request ) );

		$this->request->expects( $this->any() )->method( 'getArguments' )
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


		$this->object->fileAction();
	}


	/**
	 * @test
	 */
	public function getJsonLanguages()
	{
		$aimeos = new \Aimeos\Shop\Base\Aimeos();
		$aimeos->injectSettings( array( 'flow' => array( 'extdir' => FLOW_PATH_PACKAGES . 'Extensions' ) ) );
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
		$aimeos->injectSettings( array( 'flow' => array( 'extdir' => FLOW_PATH_PACKAGES . 'Extensions' ) ) );
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
		$ctx = $this->getMockBuilder( '\Aimeos\MShop\Context\Item\Standard' )->getMock();


		$siteManager = $this->getMockBuilder( '\Aimeos\MShop\Locale\Manager\Site\Standard' )
			->setMethods( array( 'createSearch', 'searchItems' ) )
			->disableOriginalConstructor()
			->getMock();

		$items = array( new \Aimeos\MShop\Locale\Item\Site\Standard( array( 'id' => '1', 'label' => 'default' ) ) );

		$siteManager->expects( $this->once() )->method( 'createSearch' )
			->will( $this->returnValue( new \Aimeos\MW\Criteria\PHP() ) );

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
}