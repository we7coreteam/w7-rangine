<?php


namespace W7\Tests;

use Symfony\Component\Console\Input\ArgvInput;
use W7\Console\Application;
use W7\Facade\Container;
use W7\Facade\Output;

class ProcessTest extends TestCase {
	public function testMake() {
		/**
		 * @var Application $application
		 */
		$application = Container::get(Application::class);
		$command = $application->get('make:process');

		$command->run(new ArgvInput([
			'input',
			'--name=test'
		]), Output::getFacadeRoot());

		$file = APP_PATH . '/Process/TestProcess.php';

		$this->assertFileExists($file);

		unlink($file);
	}
}