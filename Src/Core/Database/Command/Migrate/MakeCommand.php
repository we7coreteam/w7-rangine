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

use Illuminate\Database\Console\Migrations\TableGuesser;
use Symfony\Component\Console\Input\InputOption;
use W7\Core\Database\Migrate\MigrationCreator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use W7\Core\Exception\CommandException;
use W7\Core\Helper\StringHelper;

class MakeCommand extends MigrateCommandAbstract {
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a new migration file';

	/**
	 * The migration creator instance.
	 *
	 * @var MigrationCreator
	 */
	protected $creator;

	/**
	 * The Composer instance.
	 *
	 * @var \Illuminate\Support\Composer
	 */
	protected $composer;

	protected function configure() {
		$this->addArgument('name', InputOption::VALUE_REQUIRED, 'The name of the migration');
		$this->addOption('--create', null, InputOption::VALUE_REQUIRED, 'The table to be created');
		$this->addOption('--table', null, InputOption::VALUE_REQUIRED, 'The table to migrate');
		$this->addOption('--path', null, InputOption::VALUE_REQUIRED, 'The location where the migration file should be created');
		$this->addOption('--realpath', null, InputOption::VALUE_REQUIRED, 'Indicate any provided migration file paths are pre-resolved absolute paths');
		$this->addOption('--fullpath', null, InputOption::VALUE_REQUIRED, 'Output the full path of the migration');
	}

	/**
	 * @param $options
	 * @throws \Exception
	 */
	protected function handle($options) {
		if (!$this->input->getArgument('name')) {
			throw new CommandException('argument name error');
		}
		// It's possible for the developer to specify the tables to modify in this
		// schema operation. The developer may also specify if this table needs
		// to be freshly created so we can create the appropriate migrations.
		$name = StringHelper::snake(trim($this->input->getArgument('name')));

		$table = $this->input->getOption('table');

		$create = $this->input->getOption('create') ?: false;

		// If no table was given as an option but a create option is given then we
		// will use the "create" option as the table name. This allows the devs
		// to pass a table name into this option as a short-cut for creating.
		if (! $table && is_string($create)) {
			$table = $create;

			$create = true;
		}

		// Next, we will attempt to guess the table name if this the migration has
		// "create" in the name. This will allow us to provide a convenient way
		// of creating migrations that create new tables for the application.
		if (! $table) {
			[$table, $create] = TableGuesser::guess($name);
		}

		// Now we are ready to write the migration out to disk. Once we've written
		// the migration out, we will dump-autoload for the entire framework to
		// make sure that the migrations are registered by the class loaders.
		$filesystem = new Filesystem();
		$this->creator = new MigrationCreator($filesystem);
		$this->composer = new Composer($filesystem, __DIR__);

		$this->writeMigration($name, $table, $create);

		$this->composer->dumpAutoloads();
	}

	/**
	 * @param $name
	 * @param $table
	 * @param $create
	 * @throws \Exception
	 */
	protected function writeMigration($name, $table, $create) {
		$file = $this->creator->create(
			$name,
			$this->getMigrationPath(),
			$table,
			$create
		);

		if (! $this->option('fullpath')) {
			$file = pathinfo($file, PATHINFO_FILENAME);
		}

		$this->output->info("Created Migration: {$file}");
	}

	/**
	 * Get migration path (either specified by '--path' option or default location).
	 *
	 * @return string
	 */
	protected function getMigrationPath() {
		if (! is_null($targetPath = $this->input->getOption('path'))) {
			return ! $this->usingRealPath()
							? BASE_PATH.'/'.$targetPath
							: $targetPath;
		}

		return parent::getMigrationPath();
	}

	/**
	 * Determine if the given path(s) are pre-resolved "real" paths.
	 *
	 * @return bool
	 */
	protected function usingRealPath() {
		return $this->input->hasOption('realpath') && $this->option('realpath');
	}
}
