<?php


namespace Aimeos\Shop\Tests\Unit\Base;


class I18nTest extends \TYPO3\Flow\Tests\UnitTestCase
{
	private $object;


	public function setUp()
	{
		$aimeos = new \Aimeos\Shop\Base\Aimeos();
		$this->object = new \Aimeos\Shop\Base\I18n();

		$this->inject( $this->object, 'aimeos', $aimeos );
	}


	/**
	 * @test
	 */
	public function get()
	{
		$settings = array(
			'i18n' => array( 'de' => array( 'test' => 1 ) ),
			'flow' => array( 'apc' => array( 'enable' => true ) ),
		);
		$this->object->injectSettings( $settings );

		$list = $this->object->get( array( 'de', 'en' ) );

		$this->assertInternalType( 'array', $list );
		$this->assertArrayHasKey( 'de', $list );
		$this->assertArrayHasKey( 'en', $list );
		$this->assertInstanceOf( 'MW_Translation_Interface', $list['de'] );
		$this->assertInstanceOf( 'MW_Translation_Interface', $list['en'] );
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