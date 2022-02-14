<?php

namespace W7\Tests;

use Illuminate\Filesystem\Filesystem;
use W7\Core\View\View;
use W7\Core\View\Handler\HandlerAbstract;

class TestHandler extends HandlerAbstract {
	public function registerFunction($name, \Closure $callback) {
		// TODO: Implement registerFunction() method.
	}

	public function registerConst($name, $value) {
		// TODO: Implement registerConst() method.
	}

	public function registerObject($name, $object) {
		// TODO: Implement registerObject() method.
	}

	public function render($namespace, $name, $context = []): string {
		return serialize([
			$namespace,
			$name
		]);
	}
}

class ViewTest extends TestCase {
	public function testRender() {
		copy(__DIR__ . '/tmp/view/index.html', APP_PATH . '/View/test.html');

		$content = (new View([
			'template_path' => [
				'__main__' => APP_PATH . '/View'
			]
		]))->render('test');

		$this->assertSame('ok', $content);

		unlink(APP_PATH . '/View/test.html');
	}

	public function testNamespace() {
		$config = [
			'debug' => false,
			'template_path' => [
				'test' => __DIR__ . '/tmp/view'
			]
		];

		$view = new View($config);

		$content = $view->render('@test/index');

		$this->assertSame('ok', $content);
	}

	public function testHandler() {
		$view = new View([
			'debug' => false,
			'handler' => TestHandler::class
		]);
		$content = $view->render('index');

		$this->assertSame('a:2:{i:0;s:8:"__main__";i:1;s:10:"index.html";}', $content);
	}
}