<?php


namespace W7\Tests;

use Symfony\Component\Console\Input\ArgvInput;
use W7\Console\Application;
use W7\Facade\Container;
use W7\Facade\Output;

class TaskTest extends TestCase {
	public function testMake() {
		/**
		 * @var Application $application
		 */
		$application = Container::get(Application::class);
		$command = $application->get('make:task');

		$command->run(new ArgvInput([
			'input',
			'--name=test'
		]), Output::getFacadeRoot());

		$file = APP_PATH . '/Task/TestTask.php';

		$this->assertFileExists($file);

		unlink($file);
	}
}