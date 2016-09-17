<?php


namespace Aimeos\Shop\Tests\Unit\Base;


class ConfigTest extends \TYPO3\Flow\Tests\UnitTestCase
{
	private $object;


	public function setUp()
	{
		$this->object = new \Aimeos\Shop\Base\Config();

		$aimeos = new \Aimeos\Shop\Base\Aimeos();

		$resource = array(
			'host' => '127.0.0.1',
			'dbname' => 'flow',
			'user' => 'root',
			'password' => '',
		);

		$settings = array(
			'backend' => array( 'test' => 1 ),
			'frontend' => array( 'test' => 0 ),
		);

		$this->inject( $this->object, 'aimeos', $aimeos );
		$this->inject( $this->object, 'resource', $resource );
		$this->inject( $this->object, 'settings', $settings );
	}


	/**
	 * @test
	 */
	public function getFrontend()
	{
		$config = $this->object->get();

		$this->assertInstanceOf( '\Aimeos\MW\Config\Iface', $config );
		$this->assertEquals( 0, $config->get( 'test', -1 ) );
	}


	/**
	 * @test
	 */
	public function getBackend()
	{
		$config = $this->object->get( 'backend' );

		$this->assertInstanceOf( '\Aimeos\MW\Config\Iface', $config );
		$this->assertEquals( 1, $config->get( 'test', -1 ) );
	}
}