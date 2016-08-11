<?php

/**
 * @license LGPLv3, http://www.gnu.org/copyleft/lgpl.html
 * @copyright Aimeos (aimeos.org), 2015-2016
 * @package flow
 * @subpackage Base
 */


namespace Aimeos\Shop\Base;

use TYPO3\Flow\Annotations as Flow;


/**
 * Class providing the view objects
 *
 * @package flow
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

		$config = new \Aimeos\MW\Config\Decorator\Protect( clone $config, array( 'admin', 'client' ) );
		$helper = new \Aimeos\MW\View\Helper\Config\Standard( $view, $config );
		$view->addHelper( 'config', $helper );

		$sepDec = $config->get( 'client/html/common/format/seperatorDecimal', '.' );
		$sep1000 = $config->get( 'client/html/common/format/seperator1000', ' ' );
		$decimals = $config->get( 'client/html/common/format/decimals', 2 );
		$helper = new \Aimeos\MW\View\Helper\Number\Standard( $view, $sepDec, $sep1000, $decimals );
		$view->addHelper( 'number', $helper );

		if( $request !== null )
		{
			$req = $request->getHttpRequest();

			$files = ( is_array( $_FILES ) ? $_FILES : array() );
			$query = ( is_array( $_GET ) ? $_GET : array() );
			$post = ( is_array( $_POST ) ? $_POST : array() );
			$cookie = ( is_array( $_COOKIE ) ? $_COOKIE : array() );
			$server = ( is_array( $_SERVER ) ? $_SERVER : array() );

			$helper = new \Aimeos\MW\View\Helper\Request\Flow( $view, $req, $files, $query, $post, $cookie, $server );
			$view->addHelper( 'request', $helper );
		}

		$helper = new \Aimeos\MW\View\Helper\Response\Flow( $view );
		$view->addHelper( 'response', $helper );

		$helper = new \Aimeos\MW\View\Helper\Access\All( $view );
		$view->addHelper( 'access', $helper );

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