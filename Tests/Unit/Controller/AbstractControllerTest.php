<?php


namespace Aimeos\Shop\Tests\Unit\Controller;


class AbstractControllerTest extends \TYPO3\Flow\Tests\UnitTestCase
{
	private $object;
	private $view;


	public function setUp()
	{
		$this->object = $this->getMockBuilder( '\Aimeos\Shop\Controller\AbstractController' )
			->disableOriginalConstructor()
			->getMock();

		$this->view = $this->getMockBuilder( '\TYPO3\Flow\Mvc\View\JsonView' )
			->setMethods( array( 'assign' ) )
			->disableOriginalConstructor()
			->getMock();

		$this->inject( $this->object, 'view', $this->view );

		$request = $this->getMockBuilder( '\TYPO3\Flow\Mvc\ActionRequest' )
			->disableOriginalConstructor()
			->getMock();

		$this->inject( $this->object, 'request', $request );
	}


	/**
	 * @test
	 */
	public function getOutput()
	{
		$aimeos = new \Aimeos\Shop\Base\Aimeos();
		$this->inject( $this->object, 'aimeos', $aimeos );


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


		$viewContainer = $this->getMockBuilder( '\Aimeos\Shop\Base\View' )
			->setMethods( array( 'create' ) )
			->disableOriginalConstructor()
			->getMock();

		$mwView = new \Aimeos\MW\View\Standard();

		$viewContainer->expects( $this->once() )->method( 'create' )
			->will( $this->returnValue( $mwView ) );

		$this->inject( $this->object, 'viewContainer', $viewContainer );


		$uriBuilder = $this->getMockBuilder( '\TYPO3\Flow\Mvc\Routing\UriBuilder' )
			->disableOriginalConstructor()
			->getMock();

		$this->inject( $this->object, 'uriBuilder', $uriBuilder );


		$client = $this->getMockBuilder( '\Aimeos\Client\Html\Catalog\Lists\Standard' )
			->setMethods( array( 'getBody', 'getHeader', 'process') )
			->disableOriginalConstructor()
			->getMock();

		$client->expects( $this->once() )->method( 'getBody' )
			->will( $this->returnValue( 'body' ) );

		$client->expects( $this->once() )->method( 'getHeader' )
			->will( $this->returnValue( 'header' ) );

		$client->expects( $this->once() )->method( 'process' );

		\Aimeos\Client\Html\Catalog\Lists\Factory::injectClient( '\Aimeos\Client\Html\Catalog\Lists\Standard', $client );


		$this->view->expects( $this->once() )->method( 'assign' )
			->with( $this->equalTo( 'aimeos_component_header' ), $this->equalTo( 'header' ) );


		$class = new \ReflectionClass( '\Aimeos\Shop\Controller\AbstractController' );
		$method = $class->getMethod( 'getOutput' );
		$method->setAccessible( true );


		$result = $method->invokeArgs( $this->object, array( 'catalog/lists' ) );

		$this->assertEquals( 'body', $result );
	}


	/**
	 * @test
	 */
	public function getSections()
	{
		$expected = array( 'aibody' => 'body', 'aiheader' => 'header' );

		$page = $this->getMockBuilder( '\Aimeos\Shop\Base\Page' )
			->setMethods( array( 'getSections' ) )
			->disableOriginalConstructor()
			->getMock();

		$this->inject( $this->object, 'page', $page );

		$page->expects( $this->once() )->method( 'getSections' )
			->will( $this->returnValue( $expected ) );


		$class = new \ReflectionClass( '\Aimeos\Shop\Controller\AbstractController' );
		$method = $class->getMethod( 'getSections' );
		$method->setAccessible( true );


		$result = $method->invokeArgs( $this->object, array( 'test' ) );

		$this->assertEquals( $expected, $result );
	}
}