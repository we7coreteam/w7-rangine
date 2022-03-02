<?php

namespace W7\Tests\Database\Migration;

use Symfony\Component\Console\Input\ArgvInput;
use W7\Console\Application;
use W7\Facade\Container;
use W7\Tests\Database\DatabaseTestCase;

class MakeTest extends DatabaseTestCase {
	public function testMake() {
		/**
		 * @var Application $application
		 */
		$application = Container::get(Application::class);
		$application->run(new ArgvInput([
			'migrate:make',
			'migrate:make',
			'create_user'
		]));

		$files = glob(BASE_PATH . '/database/migrations/*create_user.php');
		$this->assertCount(1, $files);
		unlink($files[0]);

		$application->run(new ArgvInput([
			'migrate:make',
			'migrate:make',
			'test',
			'--table=user'
		]));

		$files = glob(BASE_PATH . '/database/migrations/*test.php');
		$this->assertCount(1, $files);
		unlink($files[0]);

		$application->run(new ArgvInput([
			'migrate:make',
			'migrate:make',
			'test1',
			'--create=user'
		]));

		$files = glob(BASE_PATH . '/database/migrations/*test1.php');
		$this->assertCount(1, $files);
		unlink($files[0]);
	}
}