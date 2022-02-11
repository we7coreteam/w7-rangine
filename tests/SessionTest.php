<?php

namespace W7\Tests;

use Illuminate\Filesystem\Filesystem;
use W7\Core\Session\Session;
use W7\Facade\Config;
use W7\Http\Message\Server\Request;

use W7\Core\Session\Handler\HandlerAbstract;

class TestHandler extends HandlerAbstract {
	private $data;

	public function write($session_id, $session_data) {
		$this->data[$session_id] = $session_data;
	}

	public function read($session_id) {
		return $this->data[$session_id];
	}

	public function destroy($session_id, $flag = SESSION_DESTROY) {
		unset($this->data[$session_id]);
	}

	public function gc($maxlifetime) {
		//清理过期的session, 根据具体的存储方式清理
	}
}

class SessionTest extends TestCase {
	public function testSet() {
		$session = new Session();
		$session->start(new Request('GET', '/'));
		$session->set('test', 1);

		$this->assertSame(1, $session->get('test'));
	}

	public function testDestroy() {
		$session = new Session();
		$session->start(new Request('GET', '/'));
		$session->set('test', 1);
		$session->destroy();

		$this->assertSame('', $session->get('test'));
	}

	public function testGc() {
		session_reset();
		$config = [
			'gc_divisor' => 1,
			'gc_probability' => 1,
			'expires' => 1
		];

		$session = new Session($config);
		$sessionReflect = new \ReflectionClass($session);
		$property = $sessionReflect->getProperty('handler');
		$property->setAccessible(true);
		$property->setValue($session, null);

		$session->start(new Request('GET', '/'));
		$session->set('test', 1);

		sleep(2);
		$session->gc();
		$session->gc();

		$property = $sessionReflect->getProperty('cache');
		$property->setAccessible(true);
		$property->setValue($session, null);

		$this->assertSame('', $sessionReflect->getMethod('get')->invokeArgs($session, ['test']));
	}

	public function testUserHandler() {
		$filesystem = new Filesystem();
		$filesystem->copyDirectory(__DIR__ . '/Util/Handler/Session', APP_PATH . '/Handler/Session');

		$config = Config::get('app.session', []);
		$config['handler'] = TestHandler::class;

		$session = new Session($config);
		$sessionReflect = new \ReflectionClass($session);
		$property = $sessionReflect->getProperty('handler');
		$property->setAccessible(true);
		$property->setValue($session, null);

		$session->start(new Request('GET', '/'));
		$session->set('test', 1);

		$this->assertSame(1, $session->get('test'));
		$property = $sessionReflect->getProperty('handler');
		$property->setAccessible(true);
		$handler = $property->getValue($session);
		$this->assertInstanceOf(TestHandler::class, $handler);

		$filesystem->delete(APP_PATH . '/Handler/Session/TestHandler.php');
	}

	public function testHas() {
		$session = new Session();
		$session->start(new Request('GET', '/'));
		$session->set('test', 1);

		$this->assertTrue($session->has('test'));
		$this->assertFalse($session->has('test1'));
	}

	public function testAll() {
		$session = new Session();
		$session->start(new Request('GET', '/'));
		$session->set('test', 1);
		$session->set('test1', 2);

		$data = $session->all();

		$this->assertSame(1, $data['test']);
		$this->assertSame(2, $data['test1']);
	}

	public function testSetSessionId() {
		$session = new Session();
		$session->start(new Request('GET', '/'));
		$id = '12345678fhdgsrewufngsherff';
		$session->setId($id);

		$this->assertSame($id, $session->getId());
	}
}