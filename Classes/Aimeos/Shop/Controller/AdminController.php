<?php

/**
 * @license LGPLv3, http://www.gnu.org/copyleft/lgpl.html
 * @copyright Aimeos (aimeos.org), 2015
 */


namespace Aimeos\Shop\Controller;

use TYPO3\Flow\Annotations as Flow;


/**
 * Controller for ExtJS adminisration interface.
 */
class AdminController extends \TYPO3\Flow\Mvc\Controller\ActionController
{
	/**
	 * @var \Aimeos\Shop\Base\I18n
	 * @Flow\Inject
	 */
	protected $i18n;


	/**
	 * Creates the initial HTML view for the admin interface.
	 */
	public function indexAction()
	{
		$param = array(
			'site' => 'default',
			'resource' => 'product',
			'lang' => ( $this->request->hasArgument( 'lang' ) ? $this->request->getArgument( 'lang' ) : 'en' ),
		);

		$this->forward( 'search', 'jqadm', null, $param );
	}


	/**
	 * Returns the version of the Aimeos package
	 *
	 * @return string Version string
	 */
	protected function getVersion()
	{
		if( ( $content = @file_get_contents( FLOW_PATH_ROOT . 'composer.lock' ) ) !== false
				&& ( $content = json_decode( $content, true ) ) !== null && isset( $content['packages'] )
		) {
			foreach( (array) $content['packages'] as $item )
			{
				if( $item['name'] === 'aimeos/aimeos-flow' ) {
					return $item['version'];
				}
			}
		}
		return '';
	}


	/**
	 * Sets the locale item in the given context
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object
	 * @param string $sitecode Unique site code
	 * @param string $locale ISO language code, e.g. "en" or "en_GB"
	 * @return \Aimeos\MShop\Context\Item\Iface Modified context object
	 */
	protected function setLocale( \Aimeos\MShop\Context\Item\Iface $context, $sitecode = 'default', $locale = null )
	{
		$localeManager = \Aimeos\MShop\Factory::createManager( $context, 'locale' );

		try
		{
			$localeItem = $localeManager->bootstrap( $sitecode, '', '', false );
			$localeItem->setLanguageId( null );
			$localeItem->setCurrencyId( null );
		}
		catch( \Aimeos\MShop\Locale\Exception $e )
		{
			$localeItem = $localeManager->createItem();
		}

		$context->setLocale( $localeItem );
		$context->setI18n( $this->i18n->get( array( $lang ) ) );

		return $context;
	}
}
