<?php


namespace Aimeos\Shop\Tests\Unit\Controller;


class BasketControllerTest extends \Neos\Flow\Tests\UnitTestCase
{
	private $object;
	private $response;
	private $view;


	public function setUp()
	{
		$this->object = $this->getMockBuilder( '\Aimeos\Shop\Controller\BasketController' )
			->setMethods( array( 'getOutput', 'getSections' ) )
			->disableOriginalConstructor()
			->getMock();

		$this->view = $this->getMockBuilder( '\Neos\Flow\Mvc\View\JsonView' )
			->disableOriginalConstructor()
			->getMock();

		$this->response = $this->getMockBuilder( '\Neos\Flow\Http\Response' )
			->disableOriginalConstructor()
			->getMock();

		$this->inject( $this->object, 'view', $this->view );
	}


	/**
	 * @test
	 */
	public function indexAction()
	{
		$expected = array( 'aibody' => 'body', 'aiheader' => 'header' );

		$this->object->expects( $this->once() )->method( 'getSections' )
			->will( $this->returnValue( $expected ) );

		$this->view->expects( $this->once() )->method( 'assignMultiple' )
			->with( $this->equalTo( $expected ) );

		$this->response->expects( $this->once() )->method( 'setHeader' );

		$this->object->indexAction();
	}


	/**
	 * @test
	 */
	public function miniComponentAction()
	{
		$this->object->expects( $this->once() )->method( 'getOutput' )
			->will( $this->returnValue( 'body' ) );

		$this->view->expects( $this->once() )->method( 'assign' )
			->with( $this->equalTo( 'output' ), $this->equalTo( 'body' ) );

		$this->response->expects( $this->once() )->method( 'setHeader' );

		$this->object->miniComponentAction();
	}


	/**
	 * @test
	 */
	public function relatedComponentAction()
	{
		$this->object->expects( $this->once() )->method( 'getOutput' )
			->will( $this->returnValue( 'body' ) );

		$this->view->expects( $this->once() )->method( 'assign' )
			->with( $this->equalTo( 'output' ), $this->equalTo( 'body' ) );

		$this->response->expects( $this->once() )->method( 'setHeader' );

		$this->object->relatedComponentAction();
	}


	/**
	 * @test
	 */
	public function standardComponentAction()
	{
		$this->object->expects( $this->once() )->method( 'getOutput' )
			->will( $this->returnValue( 'body' ) );

		$this->view->expects( $this->once() )->method( 'assign' )
			->with( $this->equalTo( 'output' ), $this->equalTo( 'body' ) );

		$this->response->expects( $this->once() )->method( 'setHeader' );

		$this->object->standardComponentAction();
	}
}