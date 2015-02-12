<?php

namespace Aimeos\Shop\Composer;

use Composer\Script\CommandEvent;


/**
 * Class for Composer install scripts
 */
class InstallerScripts
{
	/**
	 * Post composer install or update tasks
	 *
	 * @param \Composer\Script\CommandEvent $event
	 * @throws \Exception If an error occured
	 */
	public static function postUpdateAndInstall( CommandEvent $event )
	{
		if( $event->isDevMode() ) {
			$options['options'] = 'setup/default/demo:1';
		}

		\TYPO3\Flow\Core\Booting\Scripts::executeCommand( 'aimeos.shop:aimeos:setup', array(), true, $options );
		\TYPO3\Flow\Core\Booting\Scripts::executeCommand( 'aimeos.shop:aimeos:cache', array() );
	}
}
