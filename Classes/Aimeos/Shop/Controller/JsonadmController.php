<?php

/**
 * @license LGPLv3, http://www.gnu.org/copyleft/lgpl.html
 * @copyright Aimeos (aimeos.org), 2015-2016
 * @package flow
 * @subpackage Controller
 */


namespace Aimeos\Shop\Controller;

use TYPO3\Flow\Annotations as Flow;


/**
 * Aimeos controller for the JSON REST API
 *
 * @package flow
 * @subpackage Controller
 */
class JsonadmController extends \TYPO3\Flow\Mvc\Controller\ActionController
{
	/**
	 * @var \Aimeos\Shop\Base\Aimeos
	 * @Flow\Inject
	 */
	protected $aimeos;

	/**
	 * @var \Aimeos\Shop\Base\Context
	 * @Flow\Inject
	 */
	protected $context;

	/**
	 * @var \Aimeos\Shop\Base\I18n
	 * @Flow\Inject
	 */
	protected $i18n;

	/**
	 * @var \Aimeos\Shop\Base\Locale
	 * @Flow\Inject
	 */
	protected $locale;

	/**
	 * @var \Aimeos\Shop\Base\View
	 * @Flow\Inject
	 */
	protected $viewContainer;


	/**
	 * Deletes the resource object or a list of resource objects
	 *
	 * @param string Resource location, e.g. "product/stock/wareshouse"
	 * @param string $sitecode Unique site code
	 * @param integer $id Unique resource ID
	 * @return string Generated output
	 */
	public function deleteAction( $resource, $site = 'default', $id = '' )
	{
		$request = $this->request->getHttpRequest();
		$header = $request->getHeaders()->getAll();
		$status = 500;

		$client = $this->createClient( $site, $resource, $request->getArgument( 'lang' ) );
		$result = $client->delete( $request->getContent(), $header, $status );

		$this->setResponse( $status, $header );
		return $result;
	}


	/**
	 * Returns the requested resource object or list of resource objects
	 *
	 * @param string Resource location, e.g. "product/stock/wareshouse"
	 * @param string $sitecode Unique site code
	 * @param integer $id Unique resource ID
	 * @return string Generated output
	 */
	public function getAction( $resource, $site = 'default', $id = '' )
	{
		$request = $this->request->getHttpRequest();
		$header = $request->getHeaders()->getAll();
		$status = 500;

		$client = $this->createClient( $site, $resource, $request->getArgument( 'lang' ) );
		$result = $client->get( $request->getContent(), $header, $status );

		$this->setResponse( $status, $header );
		return $result;
	}


	/**
	 * Updates a resource object or a list of resource objects
	 *
	 * @param string Resource location, e.g. "product/stock/wareshouse"
	 * @param string $sitecode Unique site code
	 * @param integer $id Unique resource ID
	 * @return string Generated output
	 */
	public function patchAction( $resource, $site = 'default', $id = '' )
	{
		$request = $this->request->getHttpRequest();
		$header = $request->getHeaders()->getAll();
		$status = 500;

		$client = $this->createClient( $site, $resource, $request->getArgument( 'lang' ) );
		$result = $client->patch( $request->getContent(), $header, $status );

		$this->setResponse( $status, $header );
		return $result;
	}


	/**
	 * Creates a new resource object or a list of resource objects
	 *
	 * @param string Resource location, e.g. "product/stock/wareshouse"
	 * @param string $sitecode Unique site code
	 * @param integer $id Unique ID of the resource
	 * @return string Generated output
	 */
	public function postAction( $resource, $site = 'default', $id = '' )
	{
		$request = $this->request->getHttpRequest();
		$header = $request->getHeaders()->getAll();
		$status = 500;

		$client = $this->createClient( $site, $resource, $request->getArgument( 'lang' ) );
		$result = $client->post( $request->getContent(), $header, $status );

		$this->setResponse( $status, $header );
		return $result;
	}


	/**
	 * Creates or updates a single resource object
	 *
	 * @param string Resource location, e.g. "product/stock/wareshouse"
	 * @param string $sitecode Unique site code
	 * @param integer $id Unique resource ID
	 * @return string Generated output
	 */
	public function putAction( $resource, $site = 'default', $id = '' )
	{
		$request = $this->request->getHttpRequest();
		$header = $request->getHeaders()->getAll();
		$status = 500;

		$client = $this->createClient( $site, $resource, $request->getArgument( 'lang' ) );
		$result = $client->put( $request->getContent(), $header, $status );

		$this->setResponse( $status, $header );
		return $result;
	}


	/**
	 * Returns the available HTTP verbs and the resource URLs
	 *
	 * @param string Resource location, e.g. "product/stock/wareshouse"
	 * @param string $sitecode Unique site code
	 * @return string Generated output
	 */
	public function optionsAction( $resource = '', $site = 'default' )
	{
		$request = $this->request->getHttpRequest();
		$header = $request->getHeaders()->getAll();
		$status = 500;

		$client = $this->createClient( $site, $resource, $request->getArgument( 'lang' ) );
		$result = $client->options( $request->getContent(), $header, $status );

		$this->setResponse( $status, $header );
		return $result;
	}


	/**
	 * Returns the resource controller
	 *
	 * @param string $sitecode Unique site code
	 * @param string Resource location, e.g. "product/stock/wareshouse"
	 * @param string $lang Language code
	 * @return \Aimeos\Admin\JsonAdm\Iface JsonAdm client
	 */
	protected function createClient( $sitecode, $resource, $lang )
	{
		$lang = ( $lang ? $lang : 'en' );
		$templatePaths = $this->aimeos->get()->getCustomPaths( 'admin/jsonadm/templates' );

		$context = $this->context->get( null, 'backend' );
		$context->setI18n( $this->i18n->get( array( $lang, 'en' ) ) );
		$context->setLocale( $this->locale->getBackend( $context, $sitecode ) );
		$context->setView( $this->viewContainer->create( $context, $this->uriBuilder, $templatePaths, $this->request, $lang ) );

		return \Aimeos\Admin\JsonAdm\Factory::createClient( $context, $templatePaths, $resource );
	}


	/**
	 * Creates a new response object
	 *
	 * @param integer $status HTTP status
	 * @param array $header List of HTTP headers
	 * @return \TYPO3\Flow\Http\Response HTTP response object
	 */
	protected function setResponse( $status, array $header )
	{
		$this->response->setStatus( $status );

		foreach( $header as $key => $value ) {
			$this->response->setHeader( $key, $value );
		}
	}
}
