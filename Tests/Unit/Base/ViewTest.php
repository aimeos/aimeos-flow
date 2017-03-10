<?php


namespace Aimeos\Shop\Tests\Unit\Base;


class ViewTest extends \Neos\Flow\Tests\UnitTestCase
{
	private $object;


	public function setUp()
	{
		$i18n = new \Aimeos\Shop\Base\I18n();
		$aimeos = new \Aimeos\Shop\Base\Aimeos();
		$view = new \Neos\FluidAdaptor\View\StandaloneView();
		$security = new \Neos\Flow\Security\DummyContext();

		$this->object = new \Aimeos\Shop\Base\View();

		$this->inject( $i18n, 'aimeos', $aimeos );
		$this->inject( $this->object, 'i18n', $i18n );
		$this->inject( $this->object, 'view', $view );
		$this->inject( $this->object, 'security', $security );
	}


	/**
	 * @test
	 */
	public function create()
	{
		$context = new \Aimeos\MShop\Context\Item\Standard();
		$context->setConfig( new \Aimeos\MW\Config\PHPArray() );

		$uriBuilder = $this->getMockBuilder('\Neos\Flow\Mvc\Routing\UriBuilder')
			->disableOriginalConstructor()
			->getMock();

		$view = $this->object->create( $context, $uriBuilder, array() );

		$this->assertInstanceOf( '\Aimeos\MW\View\Iface', $view );
	}


	/**
	 * @test
	 */
	public function createWithRequest()
	{
		$context = new \Aimeos\MShop\Context\Item\Standard();
		$context->setConfig( new \Aimeos\MW\Config\PHPArray() );

		$uriBuilder = $this->getMockBuilder( '\Neos\Flow\Mvc\Routing\UriBuilder' )
			->disableOriginalConstructor()
			->getMock();

		$httpRequest = new \Neos\Flow\Http\Request( array(), array(), array(), array() );

		$request = $this->getMockBuilder( '\Neos\Flow\Mvc\ActionRequest' )
			->setMethods( array( 'getArguments', 'getHttpRequest' ) )
			->disableOriginalConstructor()
			->getMock();

		$request->expects( $this->exactly( 2 ) )->method( 'getArguments' )
			->will( $this->returnValue( array( 'site' => 'unittest', 'locale' => 'de', 'currency' => 'EUR' ) ) );

		$request->expects( $this->once() )->method( 'getHttpRequest' )
			->will( $this->returnValue( $httpRequest ) );

		$view = $this->object->create( $context, $uriBuilder, array(), $request, 'de' );

		$this->assertInstanceOf( '\Aimeos\MW\View\Iface', $view );
	}
}