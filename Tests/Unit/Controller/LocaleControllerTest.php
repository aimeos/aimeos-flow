<?php


namespace Aimeos\Shop\Tests\Unit\Controller;


class LocaleControllerTest extends \Neos\Flow\Tests\UnitTestCase
{
	private $object;
	private $view;


	public function setUp()
	{
		$this->object = $this->getMockBuilder( '\Aimeos\Shop\Controller\LocaleController' )
			->setMethods( array( 'getOutput' ) )
			->disableOriginalConstructor()
			->getMock();

		$this->view = $this->getMockBuilder( '\Neos\Flow\Mvc\View\JsonView' )
			->disableOriginalConstructor()
			->getMock();

		$this->inject( $this->object, 'view', $this->view );
	}


	/**
	 * @test
	 */
	public function selectComponentAction()
	{
		$this->object->expects( $this->once() )->method( 'getOutput' )
			->will( $this->returnValue( 'body' ) );

		$this->view->expects( $this->once() )->method( 'assign' )
			->with( $this->equalTo( 'output' ), $this->equalTo( 'body' ) );

		$this->object->selectComponentAction();
	}
}