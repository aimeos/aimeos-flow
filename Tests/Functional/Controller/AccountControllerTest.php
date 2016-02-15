<?php

namespace Aimeos\Shop\Tests\Functional\Controller;


class AccountControllerTest extends \TYPO3\Flow\Tests\FunctionalTestCase
{
	public function testAccountAction()
	{
		$response = $this->browser->request( 'http://localhost/unittest/myaccount/download/0', 'GET' );

		$this->assertEquals( 401, $response->getStatusCode() );
	}
}
