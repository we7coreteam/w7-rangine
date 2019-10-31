<?php

/**
 * This file is part of Rangine
 *
 * (c) We7Team 2019 <https://www.rangine.com/>
 *
 * document http://s.w7.cc/index.php?c=wiki&do=view&id=317&list=2284
 *
 * visited https://www.rangine.com/ for more details
 */

namespace W7\Core\Database\Command\Migrate;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\InputOption;
use W7\Core\Database\Migrate\Migrator;
use W7\Core\Dispatcher\EventDispatcher;

class StatusCommand extends MigrateCommandAbstract {
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Show the status of each migration';

	/**
	 * The migrator instance.
	 *
	 * @var Migrator
	 */
	protected $migrator;

	protected function configure() {
		$this->addOption('--database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use');
		$this->addOption('--path', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'The path to the migrations files to be executed');
		$this->addOption('--realpath', null, InputOption::VALUE_NONE, 'Indicate any provided migration file paths are pre-resolved absolute paths');
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	protected function handle($options) {
		igo(function () {
			$database = $this->getConnection();
			$this->migrator = new Migrator(new DatabaseMigrationRepository($database, MigrateCommandAbstract::MIGRATE_TABLE_NAME), $database, new Filesystem(), iloader()->get(EventDispatcher::class));
			$this->migrator->setConnection($this->option('database'));

			if (! $this->migrator->repositoryExists()) {
				return $this->output->error('Migration table not found.');
			}

			$ran = $this->migrator->getRepository()->getRan();

			$batches = $this->migrator->getRepository()->getMigrationBatches();

			if (count($migrations = $this->getStatusFor($ran, $batches)) > 0) {
				$this->output->table(['Ran?', 'Migration', 'Batch'], $migrations->toArray());
			} else {
				$this->output->error('No migrations found');
			}
		}, true);
	}

	/**
	 * Get the status for the given ran migrations.
	 *
	 * @param  array  $ran
	 * @param  array  $batches
	 * @return \Illuminate\Support\Collection
	 */
	protected function getStatusFor(array $ran, array $batches) {
		return Collection::make($this->getAllMigrationFiles())
					->map(function ($migration) use ($ran, $batches) {
						$migrationName = $this->migrator->getMigrationName($migration);

						return in_array($migrationName, $ran)
								? ['<info>Yes</info>', $migrationName, $batches[$migrationName]]
								: ['<fg=red>No</fg=red>', $migrationName];
					});
	}

	/**
	 * Get an array of all of the migration files.
	 *
	 * @return array
	 */
	protected function getAllMigrationFiles() {
		return $this->migrator->getMigrationFiles($this->getMigrationPaths());
	}
}
