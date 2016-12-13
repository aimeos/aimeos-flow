<?php

namespace Aimeos\Shop\Tests\Functional\Controller;


class JsonadmControllerTest extends \TYPO3\Flow\Tests\FunctionalTestCase
{
	public function testOptionsAction()
	{
		$response = $this->browser->request( 'http://localhost/unittest/jsonadm/product', 'OPTIONS' );
		$json = json_decode( $response->getContent(), true );

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
		$content = '{"data":{"type":"stock/type","attributes":{"stock.type.code":"flow","stock.type.label":"flow"}}}';
		$response = $this->browser->request( 'http://localhost/unittest/jsonadm/product%2Fstock%2Ftype', 'POST', array(), array(), array(), $content );
		$json = json_decode( $response->getContent(), true );

		$this->assertNotNull( $json );
		$this->assertEquals( 201, $response->getStatusCode() );
		$this->assertArrayHasKey( 'stock.type.id', $json['data']['attributes'] );
		$this->assertEquals( 'flow', $json['data']['attributes']['stock.type.code'] );
		$this->assertEquals( 'flow', $json['data']['attributes']['stock.type.label'] );
		$this->assertEquals( 1, $json['meta']['total'] );

		$id = $json['data']['attributes']['stock.type.id'];


		$content = '{"data":{"type":"stock/type","attributes":{"stock.type.code":"flow2","stock.type.label":"flow2"}}}';
		$response = $this->browser->request( 'http://localhost/unittest/jsonadm/product%2Fstock%2Ftype/' . $id, 'PATCH', array(), array(), array(), $content );
		$json = json_decode( $response->getContent(), true );

		$this->assertNotNull( $json );
		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertArrayHasKey( 'stock.type.id', $json['data']['attributes'] );
		$this->assertEquals( 'flow2', $json['data']['attributes']['stock.type.code'] );
		$this->assertEquals( 'flow2', $json['data']['attributes']['stock.type.label'] );
		$this->assertEquals( $id, $json['data']['attributes']['stock.type.id'] );
		$this->assertEquals( 1, $json['meta']['total'] );


		$response = $this->browser->request( 'http://localhost/unittest/jsonadm/product%2Fstock%2Ftype/' . $id, 'GET' );
		$json = json_decode( $response->getContent(), true );

		$this->assertNotNull( $json );
		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertArrayHasKey( 'stock.type.id', $json['data']['attributes'] );
		$this->assertEquals( 'flow2', $json['data']['attributes']['stock.type.code'] );
		$this->assertEquals( 'flow2', $json['data']['attributes']['stock.type.label'] );
		$this->assertEquals( $id, $json['data']['attributes']['stock.type.id'] );
		$this->assertEquals( 1, $json['meta']['total'] );


		$response = $this->browser->request( 'http://localhost/unittest/jsonadm/product%2Fstock%2Ftype/' . $id, 'DELETE' );
		$json = json_decode( $response->getContent(), true );

		$this->assertNotNull( $json );
		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertEquals( 1, $json['meta']['total'] );
	}


	public function testActionsBulk()
	{
		$content = '{"data":[
			{"type":"stock/type","attributes":{"stock.type.code":"flow","stock.type.label":"flow"}},
			{"type":"stock/type","attributes":{"stock.type.code":"flow2","stock.type.label":"flow"}}
		]}';
		$response = $this->browser->request( 'http://localhost/unittest/jsonadm/product%2Fstock%2Ftype', 'POST', array(), array(), array(), $content );
		$json = json_decode( $response->getContent(), true );

		$this->assertNotNull( $json );
		$this->assertEquals( 201, $response->getStatusCode() );
		$this->assertEquals( 2, count( $json['data'] ) );
		$this->assertArrayHasKey( 'stock.type.id', $json['data'][0]['attributes'] );
		$this->assertArrayHasKey( 'stock.type.id', $json['data'][1]['attributes'] );
		$this->assertEquals( 'flow', $json['data'][0]['attributes']['stock.type.label'] );
		$this->assertEquals( 'flow', $json['data'][1]['attributes']['stock.type.label'] );
		$this->assertEquals( 2, $json['meta']['total'] );

		$ids = array( $json['data'][0]['attributes']['stock.type.id'], $json['data'][1]['attributes']['stock.type.id'] );


		$content = '{"data":[
			{"type":"stock/type","id":' . $ids[0] . ',"attributes":{"stock.type.label":"flow2"}},
			{"type":"stock/type","id":' . $ids[1] . ',"attributes":{"stock.type.label":"flow2"}}
		]}';
		$response = $this->browser->request( 'http://localhost/unittest/jsonadm/product%2Fstock%2Ftype', 'PATCH', array(), array(), array(), $content );
		$json = json_decode( $response->getContent(), true );

		$this->assertNotNull( $json );
		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertEquals( 2, count( $json['data'] ) );
		$this->assertArrayHasKey( 'stock.type.id', $json['data'][0]['attributes'] );
		$this->assertArrayHasKey( 'stock.type.id', $json['data'][1]['attributes'] );
		$this->assertEquals( 'flow2', $json['data'][0]['attributes']['stock.type.label'] );
		$this->assertEquals( 'flow2', $json['data'][1]['attributes']['stock.type.label'] );
		$this->assertTrue( in_array( $json['data'][0]['attributes']['stock.type.id'], $ids ) );
		$this->assertTrue( in_array( $json['data'][1]['attributes']['stock.type.id'], $ids ) );
		$this->assertEquals( 2, $json['meta']['total'] );


		$getParams = array( 'filter' => array( '&&' => array(
			array( '=~' => array( 'stock.type.code' => 'flow' ) ),
			array( '==' => array( 'stock.type.label' => 'flow2' ) )
			) ),
			'sort' => 'stock.type.code', 'page' => array( 'offset' => 0, 'limit' => 3 )
		);
		$response = $this->browser->request( 'http://localhost/unittest/jsonadm/product%2Fstock%2Ftype', 'GET', $getParams );
		$json = json_decode( $response->getContent(), true );

		$this->assertNotNull( $json );
		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertEquals( 2, count( $json['data'] ) );
		$this->assertEquals( 'flow', $json['data'][0]['attributes']['stock.type.code'] );
		$this->assertEquals( 'flow2', $json['data'][1]['attributes']['stock.type.code'] );
		$this->assertEquals( 'flow2', $json['data'][0]['attributes']['stock.type.label'] );
		$this->assertEquals( 'flow2', $json['data'][1]['attributes']['stock.type.label'] );
		$this->assertTrue( in_array( $json['data'][0]['attributes']['stock.type.id'], $ids ) );
		$this->assertTrue( in_array( $json['data'][1]['attributes']['stock.type.id'], $ids ) );
		$this->assertEquals( 2, $json['meta']['total'] );


		$content = '{"data":[
			{"type":"stock/type","id":' . $ids[0] . '},
			{"type":"stock/type","id":' . $ids[1] . '}
		]}';
		$response = $this->browser->request( 'http://localhost/unittest/jsonadm/product%2Fstock%2Ftype', 'DELETE', array(), array(), array(), $content );
		$json = json_decode( $response->getContent(), true );

		$this->assertNotNull( $json );
		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertEquals( 2, $json['meta']['total'] );
	}
}
