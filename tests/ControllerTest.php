<?php

namespace W7\Tests;

use Symfony\Component\Console\Input\ArgvInput;
use W7\Console\Application;
use W7\Facade\Container;
use W7\Facade\Output;

class ControllerTest extends TestCase {
	public function testMakeWithOutDir() {
		/**
		 * @var Application $application
		 */
		$application = Container::get(Application::class);
		$command = $application->get('make:controller');
		$command->run(new ArgvInput([
			'input',
			'--name=user'
		]), Output::getFacadeRoot());

		$this->assertFileExists(APP_PATH . '/Controller/UserController.php');
		$this->assertFileExists(BASE_PATH . '/route/common.php');

		unlink(APP_PATH . '/Controller/UserController.php');
		unlink(BASE_PATH . '/route/common.php');
	}

	public function testMakeWithDir() {
		/**
		 * @var Application $application
		 */
		$application = Container::get(Application::class);
		$command = $application->get('make:controller');
		$command->run(new ArgvInput([
			'input',
			'--name=test\index'
		]), Output::getFacadeRoot());

		$this->assertFileExists(APP_PATH . '/Controller/Test/IndexController.php');
		$this->assertFileExists(BASE_PATH . '/route/test.php');

		unlink(BASE_PATH . '/route/test.php');
		unlink(APP_PATH . '/Controller/Test/IndexController.php');
		rmdir(APP_PATH . '/Controller/Test');
	}
}