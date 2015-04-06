<?php

/**
 * @license LGPLv3, http://www.gnu.org/copyleft/lgpl.html
 * @copyright Aimeos (aimeos.org), 2015
 * @package aimeos-flow
 * @subpackage Base
 */


namespace Aimeos\Shop\Base;

use TYPO3\Flow\Annotations as Flow;


/**
 * Class providing the view objects
 *
 * @package aimeos-flow
 * @subpackage Base
 * @Flow\Scope("singleton")
 */
class View
{
	/**
	 * @var \Aimeos\Shop\Base\I18n
	 * @Flow\Inject
	 */
	protected $i18n;


	/**
	 * Creates the view object for the HTML client.
	 *
	 * @param \MShop_Context_Item_Interface $context Context object
	 * @param \TYPO3\Flow\Mvc\Routing\UriBuilder $uriBuilder URL builder object
	 * @param array $templatePaths List of base path names with relative template paths as key/value pairs
	 * @param \TYPO3\Flow\Mvc\RequestInterface|null $request Request object
	 * @param string|null $langid Language ID
	 * @return \MW_View_Interface View object
	 */
	public function create( \MW_Config_Interface $config,
		\TYPO3\Flow\Mvc\Routing\UriBuilder $uriBuilder, array $templatePaths,
		\TYPO3\Flow\Mvc\RequestInterface $request = null, $langid = null )
	{
		$params = $fixed = array();

		if( $request !== null && $locale !== null )
		{
			$params = $request->getArguments();
			$fixed = $this->getFixedParams( $request );

			// required for reloading to the current page
			$params['target'] = $request->get( '_route' );

			$i18n = $this->i18n->get( array( $langid ) );
			$translation = $i18n[$langid];
		}
		else
		{
			$translation = new \MW_Translation_None( 'en' );
		}


		$view = new \MW_View_Default();

		$helper = new \MW_View_Helper_Translate_Default( $view, $translation );
		$view->addHelper( 'translate', $helper );

		$helper = new \MW_View_Helper_Url_Flow( $view, $uriBuilder, $fixed );
		$view->addHelper( 'url', $helper );

		$helper = new \MW_View_Helper_Partial_Default( $view, $config, $templatePaths );
		$view->addHelper( 'partial', $helper );

		$helper = new \MW_View_Helper_Parameter_Default( $view, $params );
		$view->addHelper( 'param', $helper );

		$helper = new \MW_View_Helper_Config_Default( $view, $config );
		$view->addHelper( 'config', $helper );

		$sepDec = $config->get( 'client/html/common/format/seperatorDecimal', '.' );
		$sep1000 = $config->get( 'client/html/common/format/seperator1000', ' ' );
		$helper = new \MW_View_Helper_Number_Default( $view, $sepDec, $sep1000 );
		$view->addHelper( 'number', $helper );

		$helper = new \MW_View_Helper_FormParam_Default( $view, array() );
		$view->addHelper( 'formparam', $helper );

		$helper = new \MW_View_Helper_Encoder_Default( $view );
		$view->addHelper( 'encoder', $helper );

		return $view;
	}


	/**
	 * Returns the fixed parameters that should be included in every URL
	 *
	 * @param \TYPO3\Flow\Mvc\RequestInterface $request Request object
	 * @return array Associative list of site, language and currency if available
	 */
	protected function getFixedParams( \TYPO3\Flow\Mvc\RequestInterface $request )
	{
		$fixed = array();

		$params = $request->getArguments();

		if( isset( $params['site'] ) ) {
			$fixed['site'] = $params['site'];
		}

		if( isset( $params['locale'] ) ) {
			$fixed['locale'] = $params['locale'];
		}

		if( isset( $params['currency'] ) ) {
			$fixed['currency'] = $params['currency'];
		}

		return $fixed;
	}
}