<?php

namespace Aimeos\Shop\Tests\Functional\Command;


class AimeosCommandControllerTest extends \Neos\Flow\Tests\FunctionalTestCase
{
	/**
	 * @test
	 */
	public function setupCommand()
	{
		$controller = new \Aimeos\Shop\Command\AimeosCommandController();
		$controller->setupCommand( 'unittest', 'unittest' );
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
		$controller->jobsCommand( 'index/optimize', 'unittest' );
	}
}