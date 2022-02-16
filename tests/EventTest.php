<?php

namespace W7\Tests;

use Symfony\Component\Console\Input\ArgvInput;
use W7\Console\Application;
use W7\Core\Event\Dispatcher;
use W7\Core\Listener\ListenerAbstract;
use W7\Facade\Container;
use W7\Facade\Output;

class ArgsEvent {

}

class ArgsListener extends ListenerAbstract {
	public function __construct(...$params) {
		EventTest::$testArg = $params[0];
	}

	public function run(...$params) {

	}
}

class EventTest extends TestCase {
	public static $testArg = 0;
	public function testMakeListenerAndEvent() {
		/**
		 * @var Application $application
		 */
		$application = Container::get(Application::class);
		$command = $application->get('make:listener');

		$command->run(new ArgvInput([
			'input',
			'--name=test'
		]), Output::getFacadeRoot());

		$this->assertFileExists(APP_PATH . '/Listener/TestListener.php');
		$this->assertFileExists(APP_PATH . '/Event/TestEvent.php');

		unlink(APP_PATH . '/Listener/TestListener.php');
		unlink(APP_PATH . '/Event/TestEvent.php');
	}

	public function testListener() {
		$event = new Dispatcher();
		$event->listen('test', function () {
			return 'test';
		});

		$this->assertTrue($event->hasListeners('test'));
	}

	public function testMultiEvent() {
		$event = new Dispatcher();
		$event->listen('test', function () {
			return 'test';
		});
		$event->listen('test', function () {
			return 'test1';
		});
		$event->listen('test', function () {
			return 'test2';
		});

		$this->assertSame('test', $event->dispatch('test')[0]);
		$this->assertSame('test1', $event->dispatch('test')[1]);
		$this->assertSame('test2', $event->dispatch('test')[2]);
	}

	public function testDispatcherOne() {
		$event = new Dispatcher();
		$event->listen('test', function () {
			return 'test';
		});
		$event->listen('test', function () {
			return 'test1';
		});

		$this->assertSame('test', $event->dispatch('test', [], true));
	}

	public function testArgEvent() {
		$event = new Dispatcher();
		$event->listen(ArgsEvent::class, ArgsListener::class);

		$event->dispatch(new ArgsEvent());

		$this->assertInstanceOf(ArgsEvent::class, static::$testArg);

		static::$testArg = 0;
	}
}