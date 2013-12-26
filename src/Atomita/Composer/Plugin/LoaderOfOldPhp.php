<?php

namespace Atomita\Composer\Plugin;

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
		$this->composer	 = $composer;
		$this->io		 = $io;
	}

	public static function getSubscribedEvents() {
		return array(
			ScriptEvents::POST_AUTOLOAD_DUMP => array(
				array('onPostAutoloadDump', 0)
			),
		);
	}

	public function onPostAutoloadDump(ScriptEvent $event) {
		$banner = 'by atomita/loader-of-less-than-php5.3-plugin';

		$config = $event->getComposer()->getConfig();

		$filesystem	 = new Filesystem();
		$vendorPath	 = $filesystem->normalizePath(realpath($config->get('vendor-dir')));
		$targetDir	 = $vendorPath . DIRECTORY_SEPARATOR . 'composer' . DIRECTORY_SEPARATOR;

		$file = $vendorPath . DIRECTORY_SEPARATOR . 'autoload.php';
		if (file_exists($file)) {
			$content = explode('<?php', file_get_contents($file), 2);
			$content = end($content);
			$append	 = <<<EOD
<?php

// @appended {$banner}
require dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, explode('/', '/atomita/loader-of-less-than-php5.3-plugin/src/autoload-wrapper.php'));

EOD;
			file_put_contents($file, $append . $content);


			foreach (glob($targetDir . '*.php') as $path) {
				$content = file_get_contents($path);
				switch (true) {
					case $this->endsWith(DIRECTORY_SEPARATOR . 'ClassLoader.php', $path):
						$content = str_replace('namespace Composer\\Autoload;', '// namespace Composer\\Autoload;', $content);
						break;
					case $this->endsWith(DIRECTORY_SEPARATOR . 'autoload_real.php', $path):
						$content = str_replace('\'Composer\\Autoload\\ClassLoader\'', '\'ClassLoader\'', $content);
						$content = str_replace('\\Composer\\Autoload\\ClassLoader', 'ClassLoader', $content);
					default:
						$content = str_replace('__DIR__', 'dirname(__FILE__)', $content);
						break;
				}
				file_put_contents($path, $content);
			}
		}
	}

	private function endsWith($search, $subject) {
		$l = strlen($search);
		return ($l <= strlen($subject) && $search == substr($subject, -1 * $l));
	}

}
