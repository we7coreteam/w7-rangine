<?php

namespace W7\Tests;

use W7\Facade\Container;

class TestContainerDestruct {
	public function __destruct() {
		echo 'test container destruct';
	}
}

class TestContainer {
}

class ArgsClass {
	public $obj;
	public $sum;

	public function __construct(TestContainer $container, $test2) {
		$this->obj = $container;
		$this->sum = $test2;
	}
}

class ContainerTest extends TestCase {
	public function testSet() {
		Container::set('test', function () {
			return new TestContainer();
		});

		$this->assertInstanceOf(TestContainer::class, Container::get('test'));
	}

	public function testHas() {
		Container::set('test1', function () {
			return new TestContainer();
		});

		$this->assertTrue(Container::has('test1'));
	}

	public function testDelete() {
		Container::set('test2', function () {
			return new TestContainer();
		});
		$this->assertInstanceOf(TestContainer::class, Container::get('test2'));

		Container::delete('test2');
		$this->assertFalse(Container::has('test2'));
	}

	public function testClear() {
		ob_start();
		Container::set('test_destruct', function () {
			return new TestContainerDestruct();
		});
		$this->assertInstanceOf(TestContainerDestruct::class, Container::get('test_destruct'));
		Container::clear();
		$echo = ob_get_clean();
		$this->assertSame('test container destruct', $echo);
	}

	public function testGetByArgs() {
		/**
		 * @var \W7\Core\Container\Container $container
		 */
		$container = Container::getFacadeRoot();
		/**
		 * @var ArgsClass $instance
		 */
		$instance = $container->make(ArgsClass::class, [
			'test2' => 2
		]);
		$this->assertSame(2, $instance->sum);
		$this->assertInstanceOf(TestContainer::class, $instance->obj);
	}
}