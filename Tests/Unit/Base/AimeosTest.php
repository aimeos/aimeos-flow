<?php


namespace Aimeos\Tests\Unit\Base;


class AimeosTest extends \TYPO3\Flow\Tests\UnitTestCase
{
	private $object;


	public function setUp()
	{
		$this->object = new \Aimeos\Shop\Base\Aimeos();
	}


	/**
	 * @test
	 */
	public function get()
	{
		$this->assertInstanceOf( '\\Aimeos\\Bootstrap', $this->object->get() );
	}


	/**
	 * @test
	 */
	public function injectSettings()
	{
		$this->object->injectSettings( array( 'test' ) );

		$this->assertEquals( array( 'test' ), \PHPUnit_Framework_Assert::readAttribute( $this->object, 'settings' ) );
	}
}