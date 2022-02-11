<?php

namespace W7\Tests;

use Swoole\Process;
use W7\Core\Process\Pool\DependentPool;
use W7\Core\Process\Pool\IndependentPool;
use W7\Core\Process\ProcessAbstract;
use W7\Core\Process\ProcessFactory;

$checkReturn = true;

class TestProcess extends ProcessAbstract {
	public function check() : bool {
		global $checkReturn;
		return $checkReturn;
	}

	protected function run(Process $process) {
		// TODO: Implement run() method.
	}
}

class Test1Process extends ProcessAbstract {
	public function check() : bool {
		global $checkReturn;
		return $checkReturn;
	}

	protected function run(Process $process) {
		// TODO: Implement run() method.
	}
}

class ProcessPoolTest extends TestCase {
	public function testIndependentRegister() {
		$pool = new IndependentPool(new ProcessFactory(), [
			'pid_file' => __DIR__ . '/process.pid'
		]);
		global $checkReturn;
		$checkReturn = true;

		$pool->registerProcess('test', TestProcess::class, 1);
		$pool->registerProcess('test1', Test1Process::class, 1);

		$this->assertInstanceOf(TestProcess::class, $pool->getProcessFactory()->getById(0));
		$this->assertInstanceOf(Test1Process::class, $pool->getProcessFactory()->getById(1));
	}

	public function testIndependentRegisterCheck() {
		$pool = new IndependentPool(new ProcessFactory(), [
			'pid_file' => __DIR__ . '/process.pid'
		]);
		global $checkReturn;
		$checkReturn = false;

		$pool->registerProcess('dtest', TestProcess::class, 1);
		$pool->registerProcess('dtest1', Test1Process::class, 1);

		$factory = $pool->getProcessFactory();
		$reflect = new \ReflectionClass($factory);
		$property = $reflect->getProperty('processMap');
		$property->setAccessible(true);
		$map = $property->getValue($factory);

		$this->assertCount(0, $map);
	}

	public function testDependentRegister() {
		$pool = new DependentPool(new ProcessFactory(), [
			'pid_file' => __DIR__ . '/process.pid'
		]);
		global $checkReturn;
		$checkReturn = true;

		$pool->registerProcess('test', TestProcess::class, 1);
		$pool->registerProcess('test1', Test1Process::class, 1);

		$this->assertInstanceOf(TestProcess::class, $pool->getProcessFactory()->getById(0));
		$this->assertInstanceOf(Test1Process::class, $pool->getProcessFactory()->getById(1));
	}

	public function testDependentRegisterCheck() {
		$pool = new DependentPool(new ProcessFactory(), [
			'pid_file' => __DIR__ . '/process.pid'
		]);
		global $checkReturn;
		$checkReturn = false;

		$pool->registerProcess('dtest', TestProcess::class, 1);
		$pool->registerProcess('dtest1', Test1Process::class, 1);

		$factory = $pool->getProcessFactory();
		$reflect = new \ReflectionClass($factory);
		$property = $reflect->getProperty('processMap');
		$property->setAccessible(true);
		$map = $property->getValue($factory);

		$this->assertCount(0, $map);
	}
}