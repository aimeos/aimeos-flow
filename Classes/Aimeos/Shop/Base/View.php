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
	 * @param \Aimeos\MW\Config\Iface $config Config object
	 * @param \TYPO3\Flow\Mvc\Routing\UriBuilder $uriBuilder URL builder object
	 * @param array $templatePaths List of base path names with relative template paths as key/value pairs
	 * @param \TYPO3\Flow\Mvc\RequestInterface|null $request Request object
	 * @param string|null $langid Language ID
	 * @return \Aimeos\MW\View\Iface View object
	 */
	public function create( \Aimeos\MW\Config\Iface $config,
		\TYPO3\Flow\Mvc\Routing\UriBuilder $uriBuilder, array $templatePaths,
		\TYPO3\Flow\Mvc\RequestInterface $request = null, $langid = null )
	{
		$params = $fixed = array();

		if( $request !== null && $langid !== null )
		{
			$params = $request->getArguments();
			$fixed = $this->getFixedParams( $request );

			$i18n = $this->i18n->get( array( $langid ) );
			$translation = $i18n[$langid];
		}
		else
		{
			$translation = new \Aimeos\MW\Translation\None( 'en' );
		}


		$view = new \Aimeos\MW\View\Standard( $templatePaths );

		$helper = new \Aimeos\MW\View\Helper\Translate\Standard( $view, $translation );
		$view->addHelper( 'translate', $helper );

		$helper = new \Aimeos\MW\View\Helper\Url\Flow( $view, $uriBuilder, $fixed );
		$view->addHelper( 'url', $helper );

		$helper = new \Aimeos\MW\View\Helper\Param\Standard( $view, $params );
		$view->addHelper( 'param', $helper );

		$helper = new \Aimeos\MW\View\Helper\Config\Standard( $view, $config );
		$view->addHelper( 'config', $helper );

		$sepDec = $config->get( 'client/html/common/format/seperatorDecimal', '.' );
		$sep1000 = $config->get( 'client/html/common/format/seperator1000', ' ' );
		$helper = new \Aimeos\MW\View\Helper\Number\Standard( $view, $sepDec, $sep1000 );
		$view->addHelper( 'number', $helper );

		if( $request !== null )
		{
			$helper = new \Aimeos\MW\View\Helper\Request\Flow( $view, $request->getHttpRequest() );
			$view->addHelper( 'request', $helper );
		}

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