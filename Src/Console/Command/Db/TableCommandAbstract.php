<?php

namespace W7\Console\Command\Db;

use Illuminate\Database\Schema\Blueprint;
use Symfony\Component\Console\Input\InputOption;

abstract class TableCommandAbstract extends DatabaseCommandAbstract {
	protected $table;
	protected $force = false;
	protected $description = 'database table operate';

	protected function configure() {
		parent::configure(); // TODO: Change the autogenerated stub

		$this->addOption('force', '-f', null, 'force exec the operate');
		$this->addOption('--alias', null, InputOption::VALUE_REQUIRED, 'the table name');
	}

	protected function handle($options) {
		if (!empty($options['alias'])) {
			$this->table = $options['alias'];
		}
		$this->force == $options['force'] ?? false;

		$result = parent::handle($options);

		$result && $this->output->success($this->operate . ' table ' . $this->table . ' success');
	}

	protected function create($options) {
		if (!$this->force && $this->schema->hasTable($this->table)) {
			$this->output->error('the table ' . $this->table . ' is existed');
			return false;
		}

		$this->schema->create($this->table, function (Blueprint $table) {
			$this->tableStruct($table);
		});

		return true;
	}

	protected function tableStruct(Blueprint $table) {

	}

	protected function drop($options) {
		$this->schema->drop($this->table);
		return true;
	}
}