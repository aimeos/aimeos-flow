<?php

namespace Aimeos\Shop\Tests\Functional\Command;


class AimeosCommandControllerTest extends \TYPO3\Flow\Tests\FunctionalTestCase
{
	/**
	 * @test
	 */
	public function setupCommand()
	{
		$controller = new \Aimeos\Shop\Command\AimeosCommandController();
		$controller->setupCommand( 'unittest' );
	}


	/**
	 * @test
	 */
	public function cacheCommand()
	{
		$controller = new \Aimeos\Shop\Command\AimeosCommandController();
		$controller->cacheCommand( 'unittest' );
	}


	/**
	 * @test
	 */
	public function jobsCommand()
	{
		$controller = new \Aimeos\Shop\Command\AimeosCommandController();
		$controller->jobsCommand( 'catalog/index/optimize', 'unittest' );
	}
}