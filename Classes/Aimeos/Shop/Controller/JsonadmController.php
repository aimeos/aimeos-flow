<?php

/**
 * @license LGPLv3, http://www.gnu.org/copyleft/lgpl.html
 * @copyright Aimeos (aimeos.org), 2015-2016
 * @package flow
 * @subpackage Controller
 */


namespace Aimeos\Shop\Controller;

use Neos\Flow\Annotations as Flow;
use Zend\Diactoros\Response;


/**
 * Aimeos controller for the JSON REST API
 *
 * @package flow
 * @subpackage Controller
 */
class JsonadmController extends \Neos\Flow\Mvc\Controller\ActionController
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
	 * @param string $resource Resource location, e.g. "product/property/type"
	 * @param string $site Unique site code
	 * @return string Response message content
	 */
	public function deleteAction( $resource, $site = 'default' )
	{
		$request = $this->request->getHttpRequest();

		$client = $this->createAdmin( $site, $resource, $request->getArgument( 'lang' ) );
		$psrResponse = $client->delete( $this->getPsrRequest(), new Response() );

		return $this->setPsrResponse( $psrResponse );
	}


	/**
	 * Returns the requested resource object or list of resource objects
	 *
	 * @param string $resource Resource location, e.g. "product/property/type"
	 * @param string $site Unique site code
	 * @return string Response message content
	 */
	public function getAction( $resource, $site = 'default' )
	{
		$request = $this->request->getHttpRequest();

		$client = $this->createAdmin( $site, $resource, $request->getArgument( 'lang' ) );
		$psrResponse = $client->get( $this->getPsrRequest(), new Response() );

		return $this->setPsrResponse( $psrResponse );
	}


	/**
	 * Updates a resource object or a list of resource objects
	 *
	 * @param string $resource Resource location, e.g. "product/property/type"
	 * @param string $site Unique site code
	 * @return string Response message content
	 */
	public function patchAction( $resource, $site = 'default' )
	{
		$request = $this->request->getHttpRequest();

		$client = $this->createAdmin( $site, $resource, $request->getArgument( 'lang' ) );
		$psrResponse = $client->patch( $this->getPsrRequest(), new Response() );

		return $this->setPsrResponse( $psrResponse );
	}


	/**
	 * Creates a new resource object or a list of resource objects
	 *
	 * @param string $resource Resource location, e.g. "product/property/type"
	 * @param string $site Unique site code
	 * @return string Response message content
	 */
	public function postAction( $resource, $site = 'default' )
	{
		$request = $this->request->getHttpRequest();

		$client = $this->createAdmin( $site, $resource, $request->getArgument( 'lang' ) );
		$psrResponse = $client->post( $this->getPsrRequest(), new Response() );

		return $this->setPsrResponse( $psrResponse );
	}


	/**
	 * Creates or updates a single resource object
	 *
	 * @param string $resource Resource location, e.g. "product/property/type"
	 * @param string $site Unique site code
	 * @return string Response message content
	 */
	public function putAction( $resource, $site = 'default' )
	{
		$request = $this->request->getHttpRequest();

		$client = $this->createAdmin( $site, $resource, $request->getArgument( 'lang' ) );
		$psrResponse = $client->put( $this->getPsrRequest(), new Response() );

		return $this->setPsrResponse( $psrResponse );
	}


	/**
	 * Returns the available HTTP verbs and the resource URLs
	 *
	 * @param string $resource Resource location, e.g. "product/property/type"
	 * @param string $site Unique site code
	 * @return string Response message content
	 */
	public function optionsAction( $resource = '', $site = 'default' )
	{
		$request = $this->request->getHttpRequest();

		$client = $this->createAdmin( $site, $resource, $request->getArgument( 'lang' ) );
		$psrResponse = $client->options( $this->getPsrRequest(), new Response() );

		return $this->setPsrResponse( $psrResponse );
	}


	/**
	 * Returns the resource controller
	 *
	 * @param string $sitecode Unique site code
	 * @param string Resource location, e.g. "product/property/type"
	 * @param string $lang Language code
	 * @return \Aimeos\Admin\JsonAdm\Iface JsonAdm client
	 */
	protected function createAdmin( $sitecode, $resource, $lang )
	{
		$aimeos = $this->aimeos->get();
		$lang = ( $lang ? $lang : 'en' );
		$templatePaths = $aimeos->getCustomPaths( 'admin/jsonadm/templates' );

		$context = $this->context->get( null, 'backend' );
		$context->setI18n( $this->i18n->get( array( $lang, 'en' ) ) );
		$context->setLocale( $this->locale->getBackend( $context, $sitecode ) );
		$context->setView( $this->viewContainer->create( $context, $this->uriBuilder, $templatePaths, $this->request, $lang ) );

		return \Aimeos\Admin\JsonAdm::create( $context, $aimeos, $resource );
	}


	/**
	 * Returns a PSR-7 request object for the current request
	 *
	 * @return \Psr\Http\Message\ServerRequestInterface PSR-7 request object
	 */
	protected function getPsrRequest()
	{
		$psrRequest = new \Zend\Diactoros\ServerRequest();
		$flowRequest = $this->request->getHttpRequest();

		try {
			$resource = $flowRequest->getContent( true );
		} catch( \Neos\Flow\Http\Exception $exception ) {
			$resource = fopen( 'php://temp', 'rw' );
			fwrite( $resource, $flowRequest->getContent() );
		}

		$psrRequest = $psrRequest->withBody( new \Zend\Diactoros\Stream( $resource ) );

		foreach( $flowRequest->getHeaders()->getAll() as $headerName => $headerValues ) {
			$psrRequest = $psrRequest->withHeader( $headerName, $headerValues );
		}

		return $psrRequest;
	}


	/**
	 * Set the response data from a PSR-7 response object
	 *
	 * @param \Psr\Http\Message\ResponseInterface $response PSR-7 response object
	 * @return string Response message content
	 */
	protected function setPsrResponse( \Psr\Http\Message\ResponseInterface $response )
	{
		$this->response->setStatus( $response->getStatusCode() );

		foreach( $response->getHeaders() as $key => $value ) {
			$this->response->setHeader( $key, $value );
		}

		return (string) $response->getBody();
	}
}
