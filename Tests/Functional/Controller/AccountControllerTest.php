<?php

namespace Aimeos\Shop\Tests\Functional\Controller;


class AccountControllerTest extends \Neos\Flow\Tests\FunctionalTestCase
{
	public function testDownloadAction()
	{
		$response = $this->browser->request( 'http://localhost/unittest/myaccount/download/0', 'GET' );

		$this->assertEquals( 401, $response->getStatusCode() );
	}
}
