<?php

namespace Aimeos\Shop\Tests\Functional\Controller;


class JqadmControllerTest extends \TYPO3\Flow\Tests\FunctionalTestCase
{
	public function testCopyAction()
	{
		$params = ['site' => 'unittest', 'resource' => 'product', 'id' => '0'];
		$response = $this->browser->request( 'http://localhost/unittest/jqadm/product', 'GET', $params );

		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertContains( '<div class="product-item', $response->getContent() );
	}


	public function testCreateAction()
	{
		$params = ['site' => 'unittest', 'resource' => 'product'];
		$response = $this->browser->request( 'http://localhost/unittest/jqadm/product', 'GET', $params );

		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertContains( '<div class="product-item', $response->getContent() );
	}


	public function testDeleteAction()
	{
		$params = ['site' => 'unittest', 'resource' => 'product', 'id' => '0'];
		$response = $this->browser->request( 'http://localhost/unittest/jqadm/product', 'GET', $params );

		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertContains( '<table class="list-items', $response->getContent() );
	}


	public function testGetAction()
	{
		$params = ['site' => 'unittest', 'resource' => 'product', 'id' => '0'];
		$response = $this->browser->request( 'http://localhost/unittest/jqadm/product', 'GET', $params );

		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertContains( '<div class="product-item', $response->getContent() );
	}


	public function testSaveAction()
	{
		$params = ['site' => 'unittest', 'resource' => 'product', 'id' => '0'];
		$response = $this->browser->request( 'http://localhost/unittest/jqadm/product', 'POST', $params );

		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertContains( '<div class="product-item', $response->getContent() );
	}


	public function testSearchAction()
	{
		$params = ['site' => 'unittest', 'resource' => 'product'];
		$response = $this->browser->request( 'http://localhost/unittest/jqadm/product', 'GET', $params );

		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertContains( '<table class="list-items', $response->getContent() );
	}


	public function testSearchActionSite()
	{
		$params = ['site' => 'invalid', 'resource' => 'product'];
		$response = $this->browser->request( 'http://localhost/unittest/jqadm/product', 'GET', $params );

		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertContains( '<table class="list-items', $response->getContent() );
	}
}
