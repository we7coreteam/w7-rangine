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

use Symfony\Component\Console\Input\InputOption;
use W7\Core\Database\Migrate\DatabaseMigrationRepository;

class InstallCommand extends MigrateCommandAbstract {
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create the migration repository';

	protected function configure() {
		$this->addOption('--database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use');
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	protected function handle($options) {
		igo(function () {
			$database = $this->getConnection();
			$repository = new DatabaseMigrationRepository($database, MigrateCommandAbstract::MIGRATE_TABLE_NAME);
			$repository->setSource($this->input->getOption('database'));

			$repository->createRepository();

			$this->output->info('Migration table created successfully.');
		}, true);
	}
}
