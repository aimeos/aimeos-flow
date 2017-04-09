<?php

/**
 * @license LGPLv3, http://www.gnu.org/copyleft/lgpl.html
 * @copyright Aimeos (aimeos.org), 2017
 * @package flow
 * @subpackage Controller
 */


namespace Aimeos\Shop\Controller;

use Neos\Flow\Annotations as Flow;
use Zend\Diactoros\Response;


/**
 * Aimeos controller for the frontend JSON REST API
 *
 * @package flow
 * @subpackage Controller
 */
class JsonapiController extends \Neos\Flow\Mvc\Controller\ActionController
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
	 * @var \Aimeos\Shop\Base\View
	 * @Flow\Inject
	 */
	protected $viewContainer;


	/**
	 * Deletes the resource object or a list of resource objects
	 *
	 * @param string Resource location, e.g. "customer"
	 * @param string Related resource location, e.g. "address"
	 * @return string Response message content
	 */
	public function deleteAction( $resource, $related = '' )
	{
		$client = $this->createClient( $resource, $related );
		$psrResponse = $client->delete( $this->getPsrRequest(), new Response() );

		return $this->setPsrResponse( $psrResponse );
	}


	/**
	 * Returns the requested resource object or list of resource objects
	 *
	 * @param string Resource location, e.g. "customer"
	 * @param string Related resource location, e.g. "address"
	 * @return string Response message content
	 */
	public function getAction( $resource, $related = '' )
	{
		$client = $this->createClient( $resource, $related );
		$psrResponse = $client->get( $this->getPsrRequest(), new Response() );

		return $this->setPsrResponse( $psrResponse );
	}


	/**
	 * Updates a resource object or a list of resource objects
	 *
	 * @param string Resource location, e.g. "customer"
	 * @param string Related resource location, e.g. "address"
	 * @return string Response message content
	 */
	public function patchAction( $resource, $related = '' )
	{
		$client = $this->createClient( $resource, $related );
		$psrResponse = $client->patch( $this->getPsrRequest(), new Response() );

		return $this->setPsrResponse( $psrResponse );
	}


	/**
	 * Creates a new resource object or a list of resource objects
	 *
	 * @param string Resource location, e.g. "customer"
	 * @param string Related resource location, e.g. "address"
	 * @return string Response message content
	 */
	public function postAction( $resource, $related = '' )
	{
		$client = $this->createClient( $resource, $related );
		$psrResponse = $client->post( $this->getPsrRequest(), new Response() );

		return $this->setPsrResponse( $psrResponse );
	}


	/**
	 * Creates or updates a single resource object
	 *
	 * @param string Resource location, e.g. "customer"
	 * @param string Related resource location, e.g. "address"
	 * @return string Response message content
	 */
	public function putAction( $resource, $related = '' )
	{
		$client = $this->createClient( $resource, $related );
		$psrResponse = $client->put( $this->getPsrRequest(), new Response() );

		return $this->setPsrResponse( $psrResponse );
	}


	/**
	 * Returns the available HTTP verbs and the resource URLs
	 *
	 * @param string $resource Resource location, e.g. "product"
	 * @return string Response message content
	 */
	public function optionsAction( $resource = '' )
	{
		$client = $this->createClient( $resource );
		$psrResponse = $client->options( $this->getPsrRequest(), new Response() );

		return $this->setPsrResponse( $psrResponse );
	}


	/**
	 * Returns the resource controller
	 *
	 * @param string Resource location, e.g. "customer"
	 * @param string Related resource location, e.g. "address"
	 * @return \Aimeos\Client\JsonApi\Iface JsonApi client
	 */
	protected function createClient( $resource, $related = null )
	{
		$tmplPaths = $this->aimeos->get()->getCustomPaths( 'client/jsonapi/templates' );

		$context = $this->context->get( $this->request );
		$langid = $context->getLocale()->getLanguageId();

		$view =$this->viewContainer->create( $context, $this->uriBuilder, $tmplPaths, $this->request, $langid );
		$context->setView( $view );

		return \Aimeos\Client\JsonApi\Factory::createClient( $context, $tmplPaths, $resource . '/' . $related );
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
