<?php

namespace W7\Tests\Database\Migration;

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\ArgvInput;
use W7\Console\Application;
use W7\Facade\Container;
use W7\Facade\Context;
use W7\Facade\DB;
use W7\Tests\Database\DatabaseTestCase;

class MigrationTest extends DatabaseTestCase {
	private function addMigrates() {
		/**
		 * @var Application $application
		 */
		$application = Container::get(Application::class);
		$application->run(new ArgvInput([
			'migrate:make',
			'migrate:make',
			'create_user'
		]));

		$application->run(new ArgvInput([
			'migrate:make',
			'migrate:make',
			'create_fans'
		]));
	}

	private function addMigrateForRollback() {
		/**
		 * @var Application $application
		 */
		$application = Container::get(Application::class);
		$application->run(new ArgvInput([
			'migrate:make',
			'migrate:make',
			'create_rollback_user'
		]));

		$application->run(new ArgvInput([
			'migrate:make',
			'migrate:make',
			'create_rollback_fans'
		]));
	}

	private function addMigrateForRefresh() {
		/**
		 * @var Application $application
		 */
		$application = Container::get(Application::class);
		$application->run(new ArgvInput([
			'migrate:make',
			'migrate:make',
			'create_refresh_user'
		]));

		$application->run(new ArgvInput([
			'migrate:make',
			'migrate:make',
			'create_refresh_fans'
		]));
	}

	private function addMigrateForReset() {
		/**
		 * @var Application $application
		 */
		$application = Container::get(Application::class);
		$application->run(new ArgvInput([
			'migrate:make',
			'migrate:make',
			'create_reset_user'
		]));

		$application->run(new ArgvInput([
			'migrate:make',
			'migrate:make',
			'create_reset_fans'
		]));
	}

	private function addMigrateForPretend() {
		/**
		 * @var Application $application
		 */
		$application = Container::get(Application::class);
		$application->run(new ArgvInput([
			'migrate:make',
			'migrate:make',
			'create_pretend_user'
		]));

		$application->run(new ArgvInput([
			'migrate:make',
			'migrate:make',
			'create_pretend_fans'
		]));
	}

	public function testMigrate() {
		$this->addMigrates();
		/**
		 * @var Application $application
		 */
		try {
			$connection = DB::connection('sqlite');
			$application = Container::get(Application::class);
			$application->run(new ArgvInput([
				'migrate:migrate',
				'migrate:migrate',
				'--database=sqlite'
			]));
			$isExists = $connection->table('migration')->exists();
			$this->assertTrue($isExists);

			$tables = $connection->getDoctrineSchemaManager()->listTableNames();
			$this->assertContains('ims_user', $tables);
			$this->assertContains('ims_fans', $tables);
		} catch (\Throwable $e) {
			//
		} finally {
			$filesystem = new Filesystem();
			$files = glob(BASE_PATH . '/database/migrations/*.php');
			$filesystem->delete($files);
		}
	}

	public function testRollback() {
		$this->addMigrateForRollback();
		/**
		 * @var Application $application
		 */
		try {
			$connection = DB::connection('sqlite');
			$application = Container::get(Application::class);
			$application->run(new ArgvInput([
				'migrate:migrate',
				'migrate:migrate',
				'--database=sqlite'
			]));
			$isExists = $connection->table('migration')->exists();
			$this->assertTrue($isExists);

			$tables = $connection->getDoctrineSchemaManager()->listTableNames();
			$this->assertContains('ims_rollback_user', $tables);
			$this->assertContains('ims_rollback_fans', $tables);


			$application->run(new ArgvInput([
				'migrate:make',
				'migrate:rollback',
				'--database=sqlite'
			]));
			$tables = $connection->getDoctrineSchemaManager()->listTableNames();
			$this->assertContains('ims_migration', $tables);
			$this->assertNotContains('ims_rollback_user', $tables);
			$this->assertNotContains('ims_rollback_fans', $tables);
		} catch (\Throwable $e) {
			//
		} finally {
			$filesystem = new Filesystem();
			$files = glob(BASE_PATH . '/database/migrations/*.php');
			$filesystem->delete($files);
		}
	}

	public function testReFresh() {
		$this->addMigrateForRefresh();
		/**
		 * @var Application $application
		 */
		try {
			$connection = DB::connection('sqlite');
			$application = Container::get(Application::class);
			$application->run(new ArgvInput([
				'migrate:migrate',
				'migrate:migrate',
				'--database=sqlite'
			]));
			$isExists = $connection->table('migration')->exists();
			$this->assertTrue($isExists);

			$tables = $connection->getDoctrineSchemaManager()->listTableNames();
			$this->assertContains('ims_refresh_user', $tables);
			$this->assertContains('ims_refresh_fans', $tables);


			$application->run(new ArgvInput([
				'migrate:make',
				'migrate:refresh',
				'--database=sqlite'
			]));
			$tables = $connection->getDoctrineSchemaManager()->listTableNames();
			$this->assertContains('ims_migration', $tables);
			$this->assertContains('ims_refresh_user', $tables);
			$this->assertContains('ims_refresh_fans', $tables);
		} catch (\Throwable $e) {
			//
		} finally {
			$filesystem = new Filesystem();
			$files = glob(BASE_PATH . '/database/migrations/*.php');
			$filesystem->delete($files);
		}
	}

	public function testReset() {
		$this->addMigrateForReset();
		/**
		 * @var Application $application
		 */
		try {
			$connection = DB::connection('sqlite');
			$application = Container::get(Application::class);
			$application->run(new ArgvInput([
				'migrate:migrate',
				'migrate:migrate',
				'--database=sqlite'
			]));
			$isExists = $connection->table('migration')->exists();
			$this->assertTrue($isExists);

			$tables = $connection->getDoctrineSchemaManager()->listTableNames();
			$this->assertContains('ims_reset_user', $tables);
			$this->assertContains('ims_reset_fans', $tables);

			$application->run(new ArgvInput([
				'migrate:reset',
				'migrate:reset',
				'--database=sqlite'
			]));
			$tables = $connection->getDoctrineSchemaManager()->listTableNames();
			$this->assertContains('ims_migration', $tables);
			$this->assertNotContains('ims_reset_user', $tables);
			$this->assertNotContains('ims_reset_fans', $tables);
		} catch (\Throwable $e) {
			//
		} finally {
			$filesystem = new Filesystem();
			$files = glob(BASE_PATH . '/database/migrations/*.php');
			$filesystem->delete($files);
		}
	}

	public function testPretend() {
		$this->addMigrateForPretend();
		try {
			$connection = DB::connection('sqlite');
			$application = Container::get(Application::class);
			$application->run(new ArgvInput([
				'migrate:migrate',
				'migrate:migrate',
				'--database=sqlite',
				'--pretend'
			]));
			$tables = $connection->getDoctrineSchemaManager()->listTableNames();;
			$this->assertContains('ims_migration', $tables);
			$this->assertNotContains('ims_pretend_user', $tables);
			$this->assertNotContains('ims_pretend_fans', $tables);
		} catch (\Throwable $e) {
			//
		} finally {
			$filesystem = new Filesystem();
			$files = glob(BASE_PATH . '/database/migrations/*.php');
			$filesystem->delete($files);
		}
	}
}