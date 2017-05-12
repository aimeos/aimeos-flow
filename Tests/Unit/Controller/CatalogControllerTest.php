<?php


namespace Aimeos\Shop\Tests\Unit\Controller;


class CatalogControllerTest extends \Neos\Flow\Tests\UnitTestCase
{
	private $object;
	private $response;
	private $view;


	public function setUp()
	{
		$this->object = $this->getMockBuilder( '\Aimeos\Shop\Controller\CatalogController' )
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
	public function countAction()
	{
		$expected = array( 'aibody' => 'body', 'aiheader' => 'header' );

		$this->object->expects( $this->once() )->method( 'getSections' )
			->will( $this->returnValue( $expected ) );

		$this->view->expects( $this->once() )->method( 'assignMultiple' )
			->with( $this->equalTo( $expected ) );

		$this->response->expects( exactly( 2 ) )->method( 'setHeader' );

		$this->object->countAction();
	}


	/**
	 * @test
	 */
	public function detailAction()
	{
		$expected = array( 'aibody' => 'body', 'aiheader' => 'header' );

		$this->object->expects( $this->once() )->method( 'getSections' )
			->will( $this->returnValue( $expected ) );

		$this->view->expects( $this->once() )->method( 'assignMultiple' )
			->with( $this->equalTo( $expected ) );

		$this->response->expects( $this->once() )->method( 'setHeader' );

		$this->object->detailAction();
	}


	/**
	 * @test
	 */
	public function listAction()
	{
		$expected = array( 'aibody' => 'body', 'aiheader' => 'header' );

		$this->object->expects( $this->once() )->method( 'getSections' )
			->will( $this->returnValue( $expected ) );

		$this->view->expects( $this->once() )->method( 'assignMultiple' )
			->with( $this->equalTo( $expected ) );

		$this->response->expects( $this->once() )->method( 'setHeader' );

		$this->object->listAction();
	}


	/**
	 * @test
	 */
	public function stockAction()
	{
		$expected = array( 'aibody' => 'body', 'aiheader' => 'header' );

		$this->object->expects( $this->once() )->method( 'getSections' )
			->will( $this->returnValue( $expected ) );

		$this->view->expects( $this->once() )->method( 'assignMultiple' )
			->with( $this->equalTo( $expected ) );

		$this->response->expects( exactly( 2 ) )->method( 'setHeader' );

		$this->object->stockAction();
	}


	/**
	 * @test
	 */
	public function suggestAction()
	{
		$expected = array( 'aibody' => 'body', 'aiheader' => 'header' );

		$this->object->expects( $this->once() )->method( 'getSections' )
			->will( $this->returnValue( $expected ) );

		$this->view->expects( $this->once() )->method( 'assignMultiple' )
			->with( $this->equalTo( $expected ) );

		$this->response->expects( exactly( 2 ) )->method( 'setHeader' );

		$this->object->suggestAction();
	}


	/**
	 * @test
	 */
	public function countComponentAction()
	{
		$this->object->expects( $this->once() )->method( 'getOutput' )
			->will( $this->returnValue( 'body' ) );

		$this->view->expects( $this->once() )->method( 'assign' )
			->with( $this->equalTo( 'output' ), $this->equalTo( 'body' ) );

		$this->response->expects( exactly( 2 ) )->method( 'setHeader' );

		$this->object->countComponentAction();
	}


	/**
	 * @test
	 */
	public function detailComponentAction()
	{
		$this->object->expects( $this->once() )->method( 'getOutput' )
			->will( $this->returnValue( 'body' ) );

		$this->view->expects( $this->once() )->method( 'assign' )
			->with( $this->equalTo( 'output' ), $this->equalTo( 'body' ) );

		$this->response->expects( $this->once() )->method( 'setHeader' );

		$this->object->detailComponentAction();
	}


	/**
	 * @test
	 */
	public function filterComponentAction()
	{
		$this->object->expects( $this->once() )->method( 'getOutput' )
			->will( $this->returnValue( 'body' ) );

		$this->view->expects( $this->once() )->method( 'assign' )
			->with( $this->equalTo( 'output' ), $this->equalTo( 'body' ) );

		$this->response->expects( $this->once() )->method( 'setHeader' );

		$this->object->filterComponentAction();
	}


	/**
	 * @test
	 */
	public function listComponentAction()
	{
		$this->object->expects( $this->once() )->method( 'getOutput' )
			->will( $this->returnValue( 'body' ) );

		$this->view->expects( $this->once() )->method( 'assign' )
			->with( $this->equalTo( 'output' ), $this->equalTo( 'body' ) );

		$this->response->expects( $this->once() )->method( 'setHeader' );

		$this->object->listComponentAction();
	}


	/**
	 * @test
	 */
	public function sessionComponentAction()
	{
		$this->object->expects( $this->once() )->method( 'getOutput' )
			->will( $this->returnValue( 'body' ) );

		$this->view->expects( $this->once() )->method( 'assign' )
			->with( $this->equalTo( 'output' ), $this->equalTo( 'body' ) );

		$this->response->expects( $this->once() )->method( 'setHeader' );

		$this->object->sessionComponentAction();
	}


	/**
	 * @test
	 */
	public function stageComponentAction()
	{
		$this->object->expects( $this->once() )->method( 'getOutput' )
			->will( $this->returnValue( 'body' ) );

		$this->view->expects( $this->once() )->method( 'assign' )
			->with( $this->equalTo( 'output' ), $this->equalTo( 'body' ) );

		$this->response->expects( $this->once() )->method( 'setHeader' );

		$this->object->stageComponentAction();
	}


	/**
	 * @test
	 */
	public function stockComponentAction()
	{
		$this->object->expects( $this->once() )->method( 'getOutput' )
			->will( $this->returnValue( 'body' ) );

		$this->view->expects( $this->once() )->method( 'assign' )
			->with( $this->equalTo( 'output' ), $this->equalTo( 'body' ) );

		$this->response->expects( $this->exactly( 2 ) )->method( 'setHeader' );

		$this->object->stockComponentAction();
	}


	/**
	 * @test
	 */
	public function suggestComponentAction()
	{
		$this->object->expects( $this->once() )->method( 'getOutput' )
			->will( $this->returnValue( 'body' ) );

		$this->view->expects( $this->once() )->method( 'assign' )
			->with( $this->equalTo( 'output' ), $this->equalTo( 'body' ) );

		$this->response->expects( $this->exactly( 2 ) )->method( 'setHeader' );

		$this->object->suggestComponentAction();
	}
}