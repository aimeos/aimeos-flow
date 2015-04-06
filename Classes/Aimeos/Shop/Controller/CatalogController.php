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
