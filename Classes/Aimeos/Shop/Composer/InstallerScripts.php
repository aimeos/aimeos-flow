<?php

/**
 * @license LGPLv3, http://www.gnu.org/copyleft/lgpl.html
 * @copyright Aimeos (aimeos.org), 2014
 * @package flow
 */


namespace Aimeos\Shop\Composer;

use Composer\Script\CommandEvent;


/**
 * Class for Composer install scripts
 * @package flow
 */
class InstallerScripts
{
	/**
	 * Post composer install or update tasks
	 *
	 * @param \Composer\Script\CommandEvent $event
	 */
	public static function postUpdateAndInstall( CommandEvent $event )
	{
		$options = array();

		if( $event->isDevMode() ) {
			$options['options'] = 'setup/default/demo:1';
		}

		\Neos\Flow\Core\Booting\Scripts::executeCommand( 'aimeos.shop:aimeos:setup', array(), true, $options );
		\Neos\Flow\Core\Booting\Scripts::executeCommand( 'aimeos.shop:aimeos:cache', array() );
	}
}
