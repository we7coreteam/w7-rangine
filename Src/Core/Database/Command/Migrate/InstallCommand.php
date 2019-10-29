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
use W7\Console\Command\CommandAbstract;
use W7\Core\Database\Migrate\DatabaseMigrationRepository;

class InstallCommand extends CommandAbstract {
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create the migration repository';

	/**
	 * The repository instance.
	 *
	 * @var DatabaseMigrationRepository
	 */
	protected $repository;

	/**
	 * Create a new migration install command instance.
	 * @return void
	 */
	public function __construct($name) {
		parent::__construct($name);

		$this->repository = new DatabaseMigrationRepository(idb(), MigrateCommandAbstract::MIGRATE_TABLE_NAME);
	}

	protected function configure() {
		$this->addOption('--database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use');
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	protected function handle($options) {
		$this->repository->setSource($this->input->getOption('database'));

		$this->repository->createRepository();

		$this->output->info('Migration table created successfully.');
	}
}
