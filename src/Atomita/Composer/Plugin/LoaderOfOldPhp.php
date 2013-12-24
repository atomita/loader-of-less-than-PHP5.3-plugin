<?php

namespace \Atomita\Composer\Plugin;

use Composer\Composer;	
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;
use Composer\Script\Event as ScriptEvent;
use Composer\Util\Filesystem;

class LoaderOfOldPhp implements PluginInterface, EventSubscriberInterface {

	protected $composer;
	protected $io;
	
	public function activate(Composer $composer, IOInterface $io) {
		$this->composer = $composer;
		$this->io = $io;
	}
	
	public static function getSubscribedEvents() {
		return array(
			ScriptEvents::POST_AUTOLOAD_DUMP => array(
				array('onPostAutoloadDump', 0)
			),
		);
	}
	
	public function onPostAutoloadDump(ScriptEvent $event) {
		$config = $event->getComposer()->getConfig();
		
		$filesystem = new Filesystem();
		$vendorPath = $filesystem->normalizePath(realpath($config->get('vendor-dir')));
		$targetDir  = $vendorPath . '/composer';
		
		// @todo
	}

}
