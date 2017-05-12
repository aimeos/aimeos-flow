<?php


namespace Aimeos\Shop\Tests\Unit\Controller;


class AccountControllerTest extends \Neos\Flow\Tests\UnitTestCase
{
	private $object;
	private $response;
	private $view;


	public function setUp()
	{
		$this->object = $this->getMockBuilder( '\Aimeos\Shop\Controller\AccountController' )
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
		$this->inject( $this->object, 'response', $this->response );
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

		$this->response->expects( $this->once() )->method( 'setHeader' )
			->with( $this->equalTo( 'Cache-Control' ) );

		$this->object->indexAction();
	}


	/**
	 * @test
	 */
	public function favoriteComponentAction()
	{
		$this->object->expects( $this->once() )->method( 'getOutput' )
			->will( $this->returnValue( 'body' ) );

		$this->view->expects( $this->once() )->method( 'assign' )
			->with( $this->equalTo( 'output' ), $this->equalTo( 'body' ) );

		$this->response->expects( $this->once() )->method( 'setHeader' )
			->with( $this->equalTo( 'Cache-Control' ) );

		$this->object->favoriteComponentAction();
	}


	/**
	 * @test
	 */
	public function historyComponentAction()
	{
		$this->object->expects( $this->once() )->method( 'getOutput' )
			->will( $this->returnValue( 'body' ) );

		$this->view->expects( $this->once() )->method( 'assign' )
			->with( $this->equalTo( 'output' ), $this->equalTo( 'body' ) );

		$this->response->expects( $this->once() )->method( 'setHeader' )
			->with( $this->equalTo( 'Cache-Control' ) );

		$this->object->historyComponentAction();
	}


	/**
	 * @test
	 */
	public function profileComponentAction()
	{
		$this->object->expects( $this->once() )->method( 'getOutput' )
			->will( $this->returnValue( 'body' ) );

		$this->view->expects( $this->once() )->method( 'assign' )
			->with( $this->equalTo( 'output' ), $this->equalTo( 'body' ) );

		$this->response->expects( $this->once() )->method( 'setHeader' )
			->with( $this->equalTo( 'Cache-Control' ) );

		$this->object->profileComponentAction();
	}


	/**
	 * @test
	 */
	public function watchComponentAction()
	{
		$this->object->expects( $this->once() )->method( 'getOutput' )
			->will( $this->returnValue( 'body' ) );

		$this->view->expects( $this->once() )->method( 'assign' )
			->with( $this->equalTo( 'output' ), $this->equalTo( 'body' ) );

		$this->response->expects( $this->once() )->method( 'setHeader' )
			->with( $this->equalTo( 'Cache-Control' ) );

		$this->object->watchComponentAction();
	}
}