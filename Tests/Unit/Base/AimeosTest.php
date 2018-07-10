<?php


namespace Aimeos\Shop\Tests\Unit\Base;


class AimeosTest extends \Neos\Flow\Tests\UnitTestCase
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
	public function getVersion()
	{
		$this->assertInternalType( 'string', $this->object->getVersion() );
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