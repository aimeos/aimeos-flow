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
			->setMethods( array( 'forward' ) )
			->disableOriginalConstructor()
			->getMock();

		$this->request = $this->getMockBuilder( '\TYPO3\Flow\Mvc\ActionRequest' )
			->setMethods( array( 'getArgument', 'hasArgument' ) )
			->disableOriginalConstructor()
			->getMock();

		$this->inject( $this->object, 'request', $this->request );
	}


	/**
	 * @test
	 */
	public function indexAction()
	{
		$this->request->expects( $this->once() )->method( 'hasArgument' );
		$this->object->expects( $this->once() )->method( 'forward' );

		$this->object->indexAction();
	}
}