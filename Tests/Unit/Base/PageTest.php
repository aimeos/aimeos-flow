<?php


namespace Aimeos\Shop\Tests\Unit\Base;


class ShopTest extends \Neos\Flow\Tests\UnitTestCase
{
	private $object;


	public function setUp()
	{
		$this->object = new \Aimeos\Shop\Base\Shop();

		$aimeos = new \Aimeos\Shop\Base\Aimeos();

		$uriBuilder = $this->getMockBuilder( '\Neos\Flow\Mvc\Routing\UriBuilder' )
			->disableOriginalConstructor()
			->getMock();

		$this->inject( $this->object, 'aimeos', $aimeos );
		$this->inject( $this->object, 'uriBuilder', $uriBuilder );
	}


	/**
	 * @test
	 */
	public function get()
	{
		$view = $this->getMockBuilder( '\Aimeos\Shop\Base\View' )
			->setMethods( array( 'create' ) )
			->disableOriginalConstructor()
			->getMock();

		$mwView = new \Aimeos\MW\View\Standard();

		$view->expects( $this->once() )->method( 'create' )
			->will( $this->returnValue( $mwView ) );

		$this->inject( $this->object, 'view', $view );


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


		$settings = array(
			'flow' => array( 'cache' => array( 'name' => 'None' ) ),
			'page' => array(),
		);
		$this->inject( $this->object, 'settings', $settings );


		$request = $this->getMockBuilder( '\Neos\Flow\Mvc\ActionRequest' )
			->setMethods( array( 'getArguments' ) )
			->disableOriginalConstructor()
			->getMock();


		$result = $this->object->get( $request, 'catalog/list' );

		$this->assertInternalType( 'array', $result );
		$this->assertArrayHasKey( 'aibody', $result );
		$this->assertArrayHasKey( 'aiheader', $result );
	}


	/**
	 * @test
	 */
	public function injectSettings()
	{
		$this->object->injectSettings( array( 'test' ) );

		$this->assertEquals( array( 'test' ), \PHPUnit\Framework\Assert::readAttribute( $this->object, 'settings' ) );
	}
}