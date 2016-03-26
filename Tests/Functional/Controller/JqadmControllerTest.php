<?php

namespace Aimeos\Shop\Tests\Functional\Controller;


class JqadmControllerTest extends \TYPO3\Flow\Tests\FunctionalTestCase
{
	public function testFileActionCss()
	{
		$response = $this->browser->request( 'http://localhost/unittest/jqadm/file/css', 'GET' );

		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertContains( '.aimeos', $response->getContent() );
	}


	public function testFileActionJs()
	{
		$response = $this->browser->request( 'http://localhost/unittest/jqadm/file/js', 'GET' );

		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertContains( 'Aimeos = {', $response->getContent() );
	}


	public function testCopyAction()
	{
		$response = $this->browser->request( 'http://localhost/unittest/jqadm/copy/product/0', 'GET' );

		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertContains( '<div class="product-item', $response->getContent() );
	}


	public function testCreateAction()
	{
		$response = $this->browser->request( 'http://localhost/unittest/jqadm/create/product', 'GET' );

		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertContains( '<div class="product-item', $response->getContent() );
	}


	public function testDeleteAction()
	{
		$response = $this->browser->request( 'http://localhost/unittest/jqadm/delete/product/0', 'GET' );

		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertContains( '<table class="list-items', $response->getContent() );
	}


	public function testGetAction()
	{
		$response = $this->browser->request( 'http://localhost/unittest/jqadm/get/product/0', 'GET' );

		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertContains( '<div class="product-item', $response->getContent() );
	}


	public function testSaveAction()
	{
		$response = $this->browser->request( 'http://localhost/unittest/jqadm/save/product/0', 'POST' );

		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertContains( '<div class="product-item', $response->getContent() );
	}


	public function testSearchAction()
	{
		$response = $this->browser->request( 'http://localhost/unittest/jqadm/search/product', 'GET' );

		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertContains( '<table class="list-items', $response->getContent() );
	}


	public function testSearchActionSite()
	{
		$response = $this->browser->request( 'http://localhost/invalid/jqadm/search/product', 'GET' );

		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertContains( '<table class="list-items', $response->getContent() );
	}
}
