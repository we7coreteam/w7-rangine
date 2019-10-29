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
use W7\Console\Command\ConfirmTrait;

class FreshCommand extends CommandAbstract {
	use ConfirmTrait;

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Drop all tables and re-run all migrations';

	protected function configure() {
		$this->addOption('--database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use');
		$this->addOption('--drop-views', null, InputOption::VALUE_NONE, 'Drop all tables and views');
		$this->addOption('--drop-types', null, InputOption::VALUE_NONE, 'Drop all tables and types (Postgres only)');
		$this->addOption('--force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production');
		$this->addOption('--path', null, InputOption::VALUE_OPTIONAL, 'The path to the migrations files to be executed');
		$this->addOption('--realpath', null, InputOption::VALUE_NONE, 'Indicate any provided migration file paths are pre-resolved absolute paths');
		$this->addOption('--seed', null, InputOption::VALUE_NONE, 'Indicates if the seed task should be re-run');
		$this->addOption('--seeder', null, InputOption::VALUE_OPTIONAL, 'The class name of the root seeder');
		$this->addOption('--step', null, InputOption::VALUE_NONE, 'Force the migrations to be run so they can be rolled back individually');
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	protected function handle($options) {
		if (! $this->confirmToProceed()) {
			return;
		}

		$database = $this->input->getOption('database');

		$this->call('db:wipe', array_filter([
			'--database' => $database,
			'--drop-views' => $this->option('drop-views'),
			'--drop-types' => $this->option('drop-types'),
			'--force' => true,
		]));

		$this->call('migrate', array_filter([
			'--database' => $database,
			'--path' => $this->input->getOption('path'),
			'--realpath' => $this->input->getOption('realpath'),
			'--force' => true,
			'--step' => $this->option('step'),
		]));

		if ($this->needsSeeding()) {
			$this->runSeeder($database);
		}
	}

	/**
	 * Determine if the developer has requested database seeding.
	 *
	 * @return bool
	 */
	protected function needsSeeding() {
		return $this->option('seed') || $this->option('seeder');
	}

	/**
	 * Run the database seeder command.
	 *
	 * @param  string  $database
	 * @return void
	 */
	protected function runSeeder($database) {
		$this->call('db:seed', array_filter([
			'--database' => $database,
			'--class' => $this->option('seeder') ?: 'DatabaseSeeder',
			'--force' => true,
		]));
	}
}
