<?php

namespace Aimeos\Shop\Tests\Functional\Controller;


class JsonadmControllerTest extends \TYPO3\Flow\Tests\FunctionalTestCase
{
	public function testOptionsAction()
	{
		$response = $this->browser->request( 'http://localhost/unittest/jsonadm/product', 'OPTIONS' );
		$json = json_decode( $response->getContent(), true );
print_r( $response->getContent() );

		$this->assertNotNull( $json );
		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertArrayHasKey( 'resources', $json['meta'] );
		$this->assertGreaterThan( 1, count( $json['meta']['resources'] ) );


		$response = $this->browser->request( 'http://localhost/unittest/jsonadm', 'OPTIONS' );
		$json = json_decode( $response->getContent(), true );

		$this->assertNotNull( $json );
		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertArrayHasKey( 'resources', $json['meta'] );
		$this->assertGreaterThan( 1, count( $json['meta']['resources'] ) );
	}


	public function testActionsSingle()
	{
		$content = '{"data":{"type":"product/stock/warehouse","attributes":{"product.stock.warehouse.code":"flow","product.stock.warehouse.label":"flow"}}}';
		$response = $this->browser->request( 'http://localhost/unittest/jsonadm/product%2Fstock%2Fwarehouse', 'POST', array(), array(), array(), $content );
		$json = json_decode( $response->getContent(), true );

		$this->assertNotNull( $json );
		$this->assertEquals( 201, $response->getStatusCode() );
		$this->assertArrayHasKey( 'product.stock.warehouse.id', $json['data']['attributes'] );
		$this->assertEquals( 'flow', $json['data']['attributes']['product.stock.warehouse.code'] );
		$this->assertEquals( 'flow', $json['data']['attributes']['product.stock.warehouse.label'] );
		$this->assertEquals( 1, $json['meta']['total'] );

		$id = $json['data']['attributes']['product.stock.warehouse.id'];


		$content = '{"data":{"type":"product/stock/warehouse","attributes":{"product.stock.warehouse.code":"flow2","product.stock.warehouse.label":"flow2"}}}';
		$response = $this->browser->request( 'http://localhost/unittest/jsonadm/product%2Fstock%2Fwarehouse/' . $id, 'PATCH', array(), array(), array(), $content );
		$json = json_decode( $response->getContent(), true );

		$this->assertNotNull( $json );
		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertArrayHasKey( 'product.stock.warehouse.id', $json['data']['attributes'] );
		$this->assertEquals( 'flow2', $json['data']['attributes']['product.stock.warehouse.code'] );
		$this->assertEquals( 'flow2', $json['data']['attributes']['product.stock.warehouse.label'] );
		$this->assertEquals( $id, $json['data']['attributes']['product.stock.warehouse.id'] );
		$this->assertEquals( 1, $json['meta']['total'] );


		$response = $this->browser->request( 'http://localhost/unittest/jsonadm/product%2Fstock%2Fwarehouse/' . $id, 'GET' );
		$json = json_decode( $response->getContent(), true );

		$this->assertNotNull( $json );
		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertArrayHasKey( 'product.stock.warehouse.id', $json['data']['attributes'] );
		$this->assertEquals( 'flow2', $json['data']['attributes']['product.stock.warehouse.code'] );
		$this->assertEquals( 'flow2', $json['data']['attributes']['product.stock.warehouse.label'] );
		$this->assertEquals( $id, $json['data']['attributes']['product.stock.warehouse.id'] );
		$this->assertEquals( 1, $json['meta']['total'] );


		$response = $this->browser->request( 'http://localhost/unittest/jsonadm/product%2Fstock%2Fwarehouse/' . $id, 'DELETE' );
		$json = json_decode( $response->getContent(), true );

		$this->assertNotNull( $json );
		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertEquals( 1, $json['meta']['total'] );
	}


	public function testActionsBulk()
	{
		$content = '{"data":[
			{"type":"product/stock/warehouse","attributes":{"product.stock.warehouse.code":"flow","product.stock.warehouse.label":"flow"}},
			{"type":"product/stock/warehouse","attributes":{"product.stock.warehouse.code":"flow2","product.stock.warehouse.label":"flow"}}
		]}';
		$response = $this->browser->request( 'http://localhost/unittest/jsonadm/product%2Fstock%2Fwarehouse', 'POST', array(), array(), array(), $content );
		$json = json_decode( $response->getContent(), true );

		$this->assertNotNull( $json );
		$this->assertEquals( 201, $response->getStatusCode() );
		$this->assertEquals( 2, count( $json['data'] ) );
		$this->assertArrayHasKey( 'product.stock.warehouse.id', $json['data'][0]['attributes'] );
		$this->assertArrayHasKey( 'product.stock.warehouse.id', $json['data'][1]['attributes'] );
		$this->assertEquals( 'flow', $json['data'][0]['attributes']['product.stock.warehouse.label'] );
		$this->assertEquals( 'flow', $json['data'][1]['attributes']['product.stock.warehouse.label'] );
		$this->assertEquals( 2, $json['meta']['total'] );

		$ids = array( $json['data'][0]['attributes']['product.stock.warehouse.id'], $json['data'][1]['attributes']['product.stock.warehouse.id'] );


		$content = '{"data":[
			{"type":"product/stock/warehouse","id":' . $ids[0] . ',"attributes":{"product.stock.warehouse.label":"flow2"}},
			{"type":"product/stock/warehouse","id":' . $ids[1] . ',"attributes":{"product.stock.warehouse.label":"flow2"}}
		]}';
		$response = $this->browser->request( 'http://localhost/unittest/jsonadm/product%2Fstock%2Fwarehouse', 'PATCH', array(), array(), array(), $content );
		$json = json_decode( $response->getContent(), true );

		$this->assertNotNull( $json );
		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertEquals( 2, count( $json['data'] ) );
		$this->assertArrayHasKey( 'product.stock.warehouse.id', $json['data'][0]['attributes'] );
		$this->assertArrayHasKey( 'product.stock.warehouse.id', $json['data'][1]['attributes'] );
		$this->assertEquals( 'flow2', $json['data'][0]['attributes']['product.stock.warehouse.label'] );
		$this->assertEquals( 'flow2', $json['data'][1]['attributes']['product.stock.warehouse.label'] );
		$this->assertTrue( in_array( $json['data'][0]['attributes']['product.stock.warehouse.id'], $ids ) );
		$this->assertTrue( in_array( $json['data'][1]['attributes']['product.stock.warehouse.id'], $ids ) );
		$this->assertEquals( 2, $json['meta']['total'] );


		$getParams = array( 'filter' => array( '&&' => array(
			array( '=~' => array( 'product.stock.warehouse.code' => 'flow' ) ),
			array( '==' => array( 'product.stock.warehouse.label' => 'flow2' ) )
			) ),
			'sort' => 'product.stock.warehouse.code', 'page' => array( 'offset' => 0, 'limit' => 3 )
		);
		$response = $this->browser->request( 'http://localhost/unittest/jsonadm/product%2Fstock%2Fwarehouse', 'GET', $getParams );
		$json = json_decode( $response->getContent(), true );

		$this->assertNotNull( $json );
		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertEquals( 2, count( $json['data'] ) );
		$this->assertEquals( 'flow', $json['data'][0]['attributes']['product.stock.warehouse.code'] );
		$this->assertEquals( 'flow2', $json['data'][1]['attributes']['product.stock.warehouse.code'] );
		$this->assertEquals( 'flow2', $json['data'][0]['attributes']['product.stock.warehouse.label'] );
		$this->assertEquals( 'flow2', $json['data'][1]['attributes']['product.stock.warehouse.label'] );
		$this->assertTrue( in_array( $json['data'][0]['attributes']['product.stock.warehouse.id'], $ids ) );
		$this->assertTrue( in_array( $json['data'][1]['attributes']['product.stock.warehouse.id'], $ids ) );
		$this->assertEquals( 2, $json['meta']['total'] );


		$content = '{"data":[
			{"type":"product/stock/warehouse","id":' . $ids[0] . '},
			{"type":"product/stock/warehouse","id":' . $ids[1] . '}
		]}';
		$response = $this->browser->request( 'http://localhost/unittest/jsonadm/product%2Fstock%2Fwarehouse', 'DELETE', array(), array(), array(), $content );
		$json = json_decode( $response->getContent(), true );

		$this->assertNotNull( $json );
		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertEquals( 2, $json['meta']['total'] );
	}
}
