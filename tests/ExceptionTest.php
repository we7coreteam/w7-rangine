<?php

namespace W7\Tests;

use Symfony\Component\Console\Input\ArgvInput;
use W7\Console\Application;
use W7\Core\Exception\HandlerExceptions;
use W7\Facade\Container;
use W7\Facade\Context;
use W7\Facade\Output;
use W7\Http\Message\Server\Response;
use W7\App\Exception\Test\IndexException;

class ExceptionTest extends TestCase {
	public function testMake() {
		/**
		 * @var Application $application
		 */
		$application = Container::get(Application::class);
		$command = $application->get('make:exception');
		$command->run(new ArgvInput([
			'input',
			'--name=test/index'
		]), Output::getFacadeRoot());

		$this->assertFileExists(APP_PATH . '/Exception/Test/IndexException.php');

		unlink(APP_PATH . '/Exception/Test/IndexException.php');
		rmdir(APP_PATH . '/Exception/Test');


		//测试生成 ResponseExcepiton

		$command = $application->get('make:exception');
		$command->run(new ArgvInput([
			'input',
			'--name=httpErrorTest',
			'--type=response'
		]), Output::getFacadeRoot());

		$this->assertFileExists(APP_PATH . '/Exception/HttpErrorTestException.php');
		$this->assertStringContainsString('extends ResponseExceptionAbstract', file_get_contents(APP_PATH . '/Exception/HttpErrorTestException.php'));

		unlink(APP_PATH . '/Exception/HttpErrorTestException.php');
	}

	public function testDebugException() {
		Context::setResponse(new Response());
		$response = (new HandlerExceptions())->handle(new \RuntimeException('test'), 'http');
		$this->assertContains('test', $response->getBody()->getContents());
	}

	public function testReleaseException() {
		!defined('ENV') && define('ENV', RELEASE);
		Context::setResponse(new Response());
		$response = (new HandlerExceptions())->handle(new \RuntimeException('test'), 'http');
		$content = json_decode($response->getBody()->getContents(), true);
		$this->assertSame('系统内部错误', $content['error']);
	}
}