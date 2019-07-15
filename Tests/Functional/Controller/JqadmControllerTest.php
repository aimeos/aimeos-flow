<?php

namespace Aimeos\Shop\Tests\Functional\Controller;

use Neos\Flow\Annotations as Flow;


class JqadmControllerTest extends \Neos\Flow\Tests\FunctionalTestCase
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


	/*
	 * @Flow\Session(autoStart = TRUE)
	 */
	public function testCopyAction()
	{
		$response = $this->browser->request( 'http://localhost/unittest/jqadm/copy/product?id=0', 'GET' );

		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertContains( 'item-product', $response->getContent() );
	}


	/*
	 * @Flow\Session(autoStart = TRUE)
	 */
	public function testCreateAction()
	{
		$response = $this->browser->request( 'http://localhost/unittest/jqadm/create/product', 'GET' );

		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertContains( 'item-product', $response->getContent() );
	}


	/*
	 * @Flow\Session(autoStart = TRUE)
	 */
	public function testDeleteAction()
	{
		$response = $this->browser->request( 'http://localhost/unittest/jqadm/delete/product?id=0', 'GET' );

		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertContains( 'list-items', $response->getContent() );
	}


	/*
	 * @Flow\Session(autoStart = TRUE)
	 */
	public function testExportAction()
	{
		$response = $this->browser->request( 'http://localhost/unittest/jqadm/export/order', 'GET' );

		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertContains( 'list-items', $response->getContent() );
	}


	/*
	 * @Flow\Session(autoStart = TRUE)
	 */
	public function testGetAction()
	{
		$response = $this->browser->request( 'http://localhost/unittest/jqadm/get/product?id=0', 'GET' );

		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertContains( 'item-product', $response->getContent() );
	}


	/*
	 * @Flow\Session(autoStart = TRUE)
	 */
	public function testSaveAction()
	{
		$response = $this->browser->request( 'http://localhost/unittest/jqadm/save/product', 'POST' );

		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertContains( 'item-product', $response->getContent() );
	}


	/*
	 * @Flow\Session(autoStart = TRUE)
	 */
	public function testSearchAction()
	{
		$response = $this->browser->request( 'http://localhost/unittest/jqadm/search/product', 'GET' );

		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertContains( 'list-items', $response->getContent() );
	}


	public function testSearchActionSite()
	{
		$response = $this->browser->request( 'http://localhost/invalid/jqadm/search/product', 'GET' );

		$this->assertEquals( 500, $response->getStatusCode() );
	}
}
