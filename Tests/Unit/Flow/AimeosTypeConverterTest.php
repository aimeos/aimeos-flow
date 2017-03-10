<?php


namespace Aimeos\Shop\Tests\Unit\Flow;


class AimeosTypeConverterTest extends \Neos\Flow\Tests\UnitTestCase
{
	private $object;


	public function setUp()
	{
		$this->object = new \Aimeos\Flow\AimeosTypeConverter();
	}


	/**
	 * @test
	 */
	public function convertFrom()
	{
		$src = 'f_catid=1&f_name=Test';
		$expected = array( 'f_catid' => 1, 'f_name' => 'Test' );

		$this->assertEquals( $expected, $this->object->convertFrom( $src, 'request' ) );
	}
}