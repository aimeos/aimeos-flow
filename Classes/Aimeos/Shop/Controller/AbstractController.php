<?php

/**
 * @license LGPLv3, http://www.gnu.org/copyleft/lgpl.html
 * @copyright Aimeos (aimeos.org), 2014-2016
 * @package flow
 * @subpackage Controller
 */


namespace Aimeos\Shop\Controller;

use Neos\Flow\Annotations as Flow;


/**
 * Abstract class with common functionality for all controllers.
 * @package flow
 * @subpackage Controller
 */
abstract class AbstractController extends \Neos\Flow\Mvc\Controller\ActionController
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
	 * @var \Aimeos\Shop\Base\Shop
	 * @Flow\Inject
	 */
	protected $shop;

	/**
	 * @var \Aimeos\Shop\Base\View
	 * @Flow\Inject
	 */
	protected $viewContainer;


	/**
	 * Returns the output of the client and adds the header.
	 *
	 * @param string $clientName Html client name
	 * @return string HTML code for inserting into the HTML body
	 */
	protected function getOutput( $clientName )
	{
		$tmplPaths = $this->aimeos->get()->getCustomPaths( 'client/html/templates' );
		$context = $this->context->get( $this->request );
		$langid = $context->getLocale()->getLanguageId();
		$view = $this->viewContainer->create( $context, $this->uriBuilder, $tmplPaths, $this->request, $langid );

		$client = \Aimeos\Client\Html::create( $context, $clientName );
		$client->setView( $view );
		$client->process();

		$this->view->assign( 'aimeos_component_header', (string) $client->getHeader() );

		return $client->getBody();
	}


	/**
	 * Returns the body and header output for the given page name
	 *
	 * @param string $name Page name as defined in the Settings.yaml file
	 */
	protected function get( $name )
	{
		return $this->shop->get( $this->request, $name );
	}
}
