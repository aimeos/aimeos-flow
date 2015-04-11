<?php

/**
 * @license LGPLv3, http://www.gnu.org/copyleft/lgpl.html
 * @copyright Aimeos (aimeos.org), 2014
 */


namespace Aimeos\Shop\Controller;

use TYPO3\Flow\Annotations as Flow;


/**
 * Abstract class with common functionality for all controllers.
 */
abstract class AbstractController extends \TYPO3\Flow\Mvc\Controller\ActionController
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
	 * @var \Aimeos\Shop\Base\Page
	 * @Flow\Inject
	 */
	protected $page;

	/**
	 * @var \Aimeos\Shop\Base\View
	 * @Flow\Inject
	 */
	protected $viewContainer;


	/**
	 * Returns the output of the client and adds the header.
	 *
	 * @param Client_Html_Interface $client Html client object
	 * @return string HTML code for inserting into the HTML body
	 */
	protected function getOutput( $clientName )
	{
		$tmplPaths = $this->aimeos->get()->getCustomPaths( 'client/html' );
		$context = $this->context->get( $this->request );
		$langid = $context->getLocale()->getLanguageId();
		$view = $this->viewContainer->create( $context->getConfig(), $this->uriBuilder, $tmplPaths, $this->request, $langid );

		$client = \Client_Html_Factory::createClient( $context, $tmplPaths, $clientName );
		$client->setView( $view );
		$client->process();

		// $this->response->addAdditionalHeaderData( (string) $client->getHeader() );

		return $client->getBody();
	}


	/**
	 * Returns the body and header output for the given page name
	 *
	 * @param string $pageName Page name as defined in the Settings.yaml file
	 */
	protected function getSections( $pageName )
	{
		return $this->page->getSections( $this->request, $pageName );
	}
}
