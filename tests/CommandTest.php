<?php

namespace W7\Tests;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputOption;
use W7\App\Command\Test\IndexCommand;
use W7\Console\Application;
use W7\Console\Command\CommandAbstract;
use W7\Facade\Config;
use W7\Facade\Container;
use W7\Facade\Output;

class TestCommand extends CommandAbstract {
	public $name;

	protected function configure() {
		$this->addOption('--config-app-setting-overwrite', '-w', InputOption::VALUE_REQUIRED);
		$this->addOption('--name', null, InputOption::VALUE_REQUIRED);
	}

	protected function handle($options) {
		$this->name = $options['name'];
	}
}

class Call1Command extends CommandAbstract {
	protected function configure() {
		$this->addOption('test', null, InputOption::VALUE_REQUIRED);
	}

	protected function handle($options) {
		echo $options['test'];
	}
}

class CallCommand extends CommandAbstract {
	protected function handle($options) {
		$this->call('call:call1', [
			'--test' => '1'
		]);
	}
}

class CommandTest extends TestCase {
	public function testMake() {
		/**
		 * @var Application $application
		 */
		$application = Container::get(Application::class);
		$command = $application->get('make:command');
		$command->run(new ArgvInput([
			'input',
			'--name=test/index'
		]), Output::getFacadeRoot());

		$this->assertTrue(file_exists(APP_PATH . '/Command/Test/IndexCommand.php'));

		unlink(APP_PATH . '/Command/Test/IndexCommand.php');
		rmdir(APP_PATH . '/Command/Test');
		rmdir(APP_PATH . '/Command');
	}

	public function testRun() {
		/**
		 * @var Application $application
		 */
		$application = Container::get(Application::class);
		$command = new TestCommand('test:command');
		$application->add($command);
		$application->get('test:command')->run(new ArgvInput([
			'test',
			'--name=test'
		]), Output::getFacadeRoot());

		$this->assertSame('test', $command->name);
	}

	public function testErrorOption() {
		/**
		 * @var Application $application
		 */
		$application = Container::get(Application::class);
		$command = new TestCommand('test:command');
		$application->add($command);

		try{
			$application->get('test:command')->run(new ArgvInput([
				'test',
				'--name1=test'
			]), Output::getFacadeRoot());
		} catch (\Throwable $e) {
			$this->assertSame('The "--name1" option does not exist.', $e->getMessage());
		}
	}

	public function testCallCommand() {
		/**
		 * @var Application $application
		 */
		$application = Container::get(Application::class);
		$command = new CallCommand('call:call');
		$command1 = new Call1Command('call:call1');
		$application->add($command);
		$application->add($command1);

		ob_start();
		$application->get('call:call')->run(new ArgvInput([
			'test'
		]), Output::getFacadeRoot());
		$result = ob_get_clean();
		$this->assertSame('1', $result);
	}

	public function testOverwriteConfig() {
		$this->assertSame('', Config::get('app.setting.overwrite', ''));
		/**
		 * @var Application $application
		 */
		$application = Container::get(Application::class);
		$command = new TestCommand('test:command');
		$application->add($command);
		$application->get('test:command')->run(new ArgvInput([
			'test',
			'--name=test',
			'--config-app-setting-overwrite=1'
		]), Output::getFacadeRoot());

		$this->assertSame('test', $command->name);
		$this->assertSame('1', Config::get('app.setting.overwrite'));
	}
}