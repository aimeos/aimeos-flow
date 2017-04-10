<?php

namespace Aimeos\Shop\Tests\Functional\Controller;


class JsonapiControllerTest extends \Neos\Flow\Tests\FunctionalTestCase
{
	public function testOptionsAction()
	{
		$response = $this->browser->request( 'http://localhost/unittest/jsonapi', 'OPTIONS' );
		$json = json_decode( $response->getContent(), true );

		$this->assertNotNull( $json );
		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertArrayHasKey( 'resources', $json['meta'] );
		$this->assertGreaterThan( 1, count( $json['meta']['resources'] ) );
	}


	public function testGetAction()
	{
		$params = ['filter' => ['f_search' => 'Cafe Noire Cap', 'f_listtype' => 'unittype19']];
		$response = $this->browser->request( 'http://localhost/unittest/jsonapi/product', 'GET', $params );
		$json = json_decode( $response->getContent(), true );

		$this->assertNotNull( $json );
		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertEquals( 1, $json['meta']['total'] );
		$this->assertEquals( 1, count( $json['data'] ) );
		$this->assertArrayHasKey( 'id', $json['data'][0] );
		$this->assertEquals( 'CNC', $json['data'][0]['attributes']['product.code'] );

		$id = $json['data'][0]['id'];


		$response = $this->browser->request( 'http://localhost/unittest/jsonapi/product/' . $id, 'GET' );
		$json = json_decode( $response->getContent(), true );

		$this->assertNotNull( $json );
		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertEquals( 1, $json['meta']['total'] );
		$this->assertArrayHasKey( 'id', $json['data'] );
		$this->assertEquals( 'CNC', $json['data']['attributes']['product.code'] );
	}


	public function testPostPatchDeleteAction()
	{
		// get CNC product
		$params = ['filter' => ['f_search' => 'Cafe Noire Cap', 'f_listtype' => 'unittype19']];
		$response = $this->browser->request( 'http://localhost/unittest/jsonapi/product', 'GET', $params );
		$json = json_decode( $response->getContent(), true );
		$this->assertEquals( 'CNC', $json['data'][0]['attributes']['product.code'] );

		// add CNC product to basket
		$content = json_encode( ['data' => ['attributes' => ['product.id' => $json['data'][0]['id']]]] );
		$response = $this->browser->request( 'http://localhost/unittest/jsonapi/basket/default/product', 'POST', [], [], [], $content );
		$json = json_decode( $response->getContent(), true );
		$this->assertEquals( 'CNC', $json['included'][0]['attributes']['order.base.product.prodcode'] );

		// change product quantity in basket
		$content = json_encode( ['data' => ['attributes' => ['quantity' => 2]]] );
		$response = $this->browser->request( 'http://localhost/unittest/jsonapi/basket/default/product/0', 'PATCH', [], [], [], $content );
		$json = json_decode( $response->getContent(), true );
		$this->assertEquals( 2, $json['included'][0]['attributes']['order.base.product.quantity'] );

		// delete product from basket
		$response = $this->browser->request( 'http://localhost/unittest/jsonapi/basket/default/product/0', 'DELETE' );
		$json = json_decode( $response->getContent(), true );
		$this->assertEquals( 0, count( $json['included'] ) );
	}


	public function testPutAction()
	{
		$response = $this->browser->request( 'http://localhost/unittest/jsonapi/basket', 'PUT' );
		$json = json_decode( $response->getContent(), true );
		$this->assertArrayHasKey( 'errors', $json );
	}
}
