<?php

namespace W7\Tests;

use Swoole\Process;
use W7\Core\Process\ProcessAbstract;
use W7\Core\Process\ProcessFactory;

class TestFactoryProcess extends ProcessAbstract {
	public function check() {
		// TODO: Implement check() method.
	}

	protected function run(Process $process) {
		// TODO: Implement run() method.
	}
}

class TestFactory1Process extends ProcessAbstract {
	public function check() {
		// TODO: Implement check() method.
	}

	protected function run(Process $process) {
		// TODO: Implement run() method.
	}
}

class ProcessFactoryTest extends TestCase {
	public function testRegister() {
		$processFactory = new ProcessFactory();
		$processFactory->add( new TestFactoryProcess('test'));
		$processFactory->add(new TestFactory1Process('test1'));

		$this->assertInstanceOf(TestFactoryProcess::class, $processFactory->getById(0));
		$this->assertSame('test', $processFactory->getById(0)->getName());
		$this->assertInstanceOf(TestFactory1Process::class, $processFactory->getById(1));
		$this->assertSame(2, $processFactory->count());
	}
}