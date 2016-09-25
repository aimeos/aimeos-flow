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
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object
	 * @param \TYPO3\Flow\Mvc\Routing\UriBuilder $uriBuilder URL builder object
	 * @param array $templatePaths List of base path names with relative template paths as key/value pairs
	 * @param \TYPO3\Flow\Mvc\RequestInterface|null $request Request object
	 * @param string|null $langid Language ID
	 * @return \Aimeos\MW\View\Iface View object
	 */
	public function create( \Aimeos\MShop\Context\Item\Iface $context,
		\TYPO3\Flow\Mvc\Routing\UriBuilder $uriBuilder, array $templatePaths,
		\TYPO3\Flow\Mvc\RequestInterface $request = null, $langid = null )
	{
		$config = $context->getConfig();
		$view = new \Aimeos\MW\View\Standard( $templatePaths );

		$this->addCsrf( $view );
		$this->addAccess( $view, $context );
		$this->addConfig( $view, $config );
		$this->addNumber( $view, $config );
		$this->addParam( $view, $request );
		$this->addRequest( $view, $request );
		$this->addResponse( $view );
		$this->addTranslate( $view, $langid );
		$this->addUrl( $view, $uriBuilder, $request );

		return $view;
	}


	/**
	 * Adds the "access" helper to the view object
	 *
	 * @param \Aimeos\MW\View\Iface $view View object
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object
	 * @return \Aimeos\MW\View\Iface Modified view object
	 */
	protected function addAccess( \Aimeos\MW\View\Iface $view, \Aimeos\MShop\Context\Item\Iface $context )
	{
		$helper = new \Aimeos\MW\View\Helper\Access\All( $view );
		$view->addHelper( 'access', $helper );

		return $view;
	}


	/**
	 * Adds the "config" helper to the view object
	 *
	 * @param \Aimeos\MW\View\Iface $view View object
	 * @param \Aimeos\MW\Config\Iface $config Configuration object
	 * @return \Aimeos\MW\View\Iface Modified view object
	 */
	protected function addConfig( \Aimeos\MW\View\Iface $view, \Aimeos\MW\Config\Iface $config )
	{
		$config = new \Aimeos\MW\Config\Decorator\Protect( clone $config, array( 'admin', 'client' ) );
		$helper = new \Aimeos\MW\View\Helper\Config\Standard( $view, $config );
		$view->addHelper( 'config', $helper );

		return $view;
	}


	/**
	 * Adds the "access" helper to the view object
	 *
	 * @param \Aimeos\MW\View\Iface $view View object
	 * @return \Aimeos\MW\View\Iface Modified view object
	 */
	protected function addCsrf( \Aimeos\MW\View\Iface $view )
	{
		return $view;
	}


	/**
	 * Adds the "number" helper to the view object
	 *
	 * @param \Aimeos\MW\View\Iface $view View object
	 * @param \Aimeos\MW\Config\Iface $config Configuration object
	 * @return \Aimeos\MW\View\Iface Modified view object
	 */
	protected function addNumber( \Aimeos\MW\View\Iface $view, \Aimeos\MW\Config\Iface $config )
	{
		$sepDec = $config->get( 'client/html/common/format/seperatorDecimal', '.' );
		$sep1000 = $config->get( 'client/html/common/format/seperator1000', ' ' );
		$decimals = $config->get( 'client/html/common/format/decimals', 2 );

		$helper = new \Aimeos\MW\View\Helper\Number\Standard( $view, $sepDec, $sep1000, $decimals );
		$view->addHelper( 'number', $helper );

		return $view;
	}


	/**
	 * Adds the "param" helper to the view object
	 *
	 * @param \Aimeos\MW\View\Iface $view View object
	 * @param \TYPO3\Flow\Mvc\RequestInterface|null $request Request object
	 * @return \Aimeos\MW\View\Iface Modified view object
	 */
	protected function addParam( \Aimeos\MW\View\Iface $view, \TYPO3\Flow\Mvc\RequestInterface $request = null )
	{
		$params = ( $request !== null ? $request->getArguments() : array() );
		$helper = new \Aimeos\MW\View\Helper\Param\Standard( $view, $params );
		$view->addHelper( 'param', $helper );

		return $view;
	}


	/**
	 * Adds the "request" helper to the view object
	 *
	 * @param \Aimeos\MW\View\Iface $view View object
	 * @param \TYPO3\Flow\Mvc\RequestInterface|null $request Request object
	 * @return \Aimeos\MW\View\Iface Modified view object
	 */
	protected function addRequest( \Aimeos\MW\View\Iface $view, \TYPO3\Flow\Mvc\RequestInterface $request = null )
	{
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

		return $view;
	}


	/**
	 * Adds the "response" helper to the view object
	 *
	 * @param \Aimeos\MW\View\Iface $view View object
	 * @return \Aimeos\MW\View\Iface Modified view object
	 */
	protected function addResponse( \Aimeos\MW\View\Iface $view )
	{
		$helper = new \Aimeos\MW\View\Helper\Response\Flow( $view );
		$view->addHelper( 'response', $helper );

		return $view;
	}


	/**
	 * Adds the "translate" helper to the view object
	 *
	 * @param \Aimeos\MW\View\Iface $view View object
	 * @param string|null $langid ISO language code, e.g. "de" or "de_CH"
	 * @return \Aimeos\MW\View\Iface Modified view object
	 */
	protected function addTranslate( \Aimeos\MW\View\Iface $view, $langid )
	{
		if( $langid !== null )
		{
			$i18n = $this->i18n->get( array( $langid ) );
			$translation = $i18n[$langid];
		}
		else
		{
			$translation = new \Aimeos\MW\Translation\None( 'en' );
		}

		$helper = new \Aimeos\MW\View\Helper\Translate\Standard( $view, $translation );
		$view->addHelper( 'translate', $helper );

		return $view;
	}


	/**
	 * Adds the "url" helper to the view object
	 *
	 * @param \Aimeos\MW\View\Iface $view View object
	 * @param \TYPO3\Flow\Mvc\Routing\UriBuilder $uriBuilder URL builder object
	 * @param \TYPO3\Flow\Mvc\RequestInterface|null $request Request object
	 * @return \Aimeos\MW\View\Iface Modified view object
	 */
	protected function addUrl( \Aimeos\MW\View\Iface $view,
		\TYPO3\Flow\Mvc\Routing\UriBuilder $uriBuilder,
		\TYPO3\Flow\Mvc\RequestInterface $request = null )
	{
		$fixed = array();

		if( $request !== null )
		{
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
		}

		$helper = new \Aimeos\MW\View\Helper\Url\Flow( $view, $uriBuilder, $fixed );
		$view->addHelper( 'url', $helper );

		return $view;
	}
}