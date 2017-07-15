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
	 * @return string Response message content
	 * @Flow\Session(autoStart = TRUE)
	 */
	public function indexAction( $resource = '' )
	{
		$client = $this->createClient( $resource );

		switch( $this->request->getHttpRequest()->getMethod() )
		{
			case 'DELETE':
				return $this->setPsrResponse( $client->delete( $this->getPsrRequest(), new Response() ) );
			case 'GET':
				return $this->setPsrResponse( $client->get( $this->getPsrRequest(), new Response() ) );
			case 'PATCH':
				return $this->setPsrResponse( $client->patch( $this->getPsrRequest(), new Response() ) );
			case 'POST':
				return $this->setPsrResponse( $client->post( $this->getPsrRequest(), new Response() ) );
			case 'PUT':
				return $this->setPsrResponse( $client->put( $this->getPsrRequest(), new Response() ) );
			default:
				return $this->setPsrResponse( $client->options( $this->getPsrRequest(), new Response() ) );
		}
	}


	/**
	 * Returns the resource controller
	 *
	 * @param string Resource location, e.g. "customer"
	 * @return \Aimeos\Client\JsonApi\Iface JsonApi client
	 */
	protected function createClient( $resource )
	{
		$related = '';
		$tmplPaths = $this->aimeos->get()->getCustomPaths( 'client/jsonapi/templates' );

		if( $this->request->hasArgument( 'related' ) ) {
			$related = $this->request->getArgument( 'related' );
		}

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
