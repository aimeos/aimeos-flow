<?php

/**
 * @license LGPLv3, http://www.gnu.org/copyleft/lgpl.html
 * @copyright Aimeos (aimeos.org), 2015
 */


namespace Aimeos\Shop\Controller;

use TYPO3\Flow\Annotations as Flow;


/**
 * Aimeos catalog controller.
 */
class CatalogController extends AbstractController
{
	/**
	 * Returns the output of the catalog count component
	 *
	 * @return string Rendered HTML for the body
	 */
	public function countComponentAction()
	{
		$this->view->assign( 'output', $this->getOutput( 'catalog/count' ) );
	}


	/**
	 * Returns the output of the catalog detail component
	 *
	 * @return string Rendered HTML for the body
	 */
	public function detailComponentAction()
	{
		$this->view->assign( 'output', $this->getOutput( 'catalog/detail' ) );
	}


	/**
	 * Returns the output of the catalog filter component
	 *
	 * @return string Rendered HTML for the body
	 */
	public function filterComponentAction()
	{
		$this->view->assign( 'output', $this->getOutput( 'catalog/filter' ) );
	}


	/**
	 * Returns the output of the catalog list component
	 *
	 * @return string Rendered HTML for the body
	 */
	public function listComponentAction()
	{
		$this->view->assign( 'output',  $this->getOutput( 'catalog/list' ) );
	}


	/**
	 * Returns the output of the catalog session component
	 *
	 * @return string Rendered HTML for the body
	 */
	public function sessionComponentAction()
	{
		$this->view->assign( 'output',  $this->getOutput( 'catalog/session' ) );
	}


	/**
	 * Returns the output of the catalog stage component
	 *
	 * @return string Rendered HTML for the body
	 */
	public function stageComponentAction()
	{
		$this->view->assign( 'output',  $this->getOutput( 'catalog/stage' ) );
	}


	/**
	 * Returns the output of the catalog stock component
	 *
	 * @return string Rendered HTML for the body
	 */
	public function stockComponentAction()
	{
		$this->view->assign( 'output', $this->getOutput( 'catalog/stock' ) );
	}


	/**
	 * Returns the output of the catalog suggest component
	 *
	 * @return string Rendered HTML for the body
	 */
	public function suggestComponentAction()
	{
		$this->view->assign( 'output', $this->getOutput( 'catalog/suggest' ) );
	}


	/**
	 * Renders the catalog counts.
	 */
	public function countAction()
	{
		$this->view->assignMultiple( $this->getSections( 'catalog-count' ) );
	}


	/**
	 * Content for catalog detail page
	 *
	 * @Flow\Session(autoStart = TRUE)
	 */
	public function detailAction()
	{
		$this->view->assignMultiple( $this->getSections( 'catalog-detail' ) );
	}


	/**
	 * Content for catalog list page
	 *
	 * @Flow\Session(autoStart = TRUE)
	 */
	public function listAction()
	{
		$this->view->assignMultiple( $this->getSections( 'catalog-list' ) );
	}


	/**
	 * Renders the catalog stock section.
	 */
	public function stockAction()
	{
		$this->view->assignMultiple( $this->getSections( 'catalog-stock' ) );
	}


	/**
	 * Renders a list of product names in JSON format.
	 */
	public function suggestAction()
	{
		$this->view->assignMultiple( $this->getSections( 'catalog-suggest' ) );
	}
}
