<?php

namespace W7\Console\Command\Db\Table;

use Symfony\Component\Console\Input\InputOption;
use W7\Console\Command\Db\DatabaseCommandAbstract;
use W7\Core\Exception\CommandException;

class CreateCommand extends DatabaseCommandAbstract {
	protected function configure() {
		$this->setDescription('create database table log');
		$this->addOption('--name', null, InputOption::VALUE_REQUIRED, 'the mysql table name');
		$this->addOption('--alias', null, InputOption::VALUE_REQUIRED, 'the mysql table alias');
	}

	protected function handle($options) {
		if (empty($options['name'])) {
			throw new CommandException('params name not be empty');
		}
		if (!method_exists($this, 'create' . ucfirst($options['name']))) {
			throw new CommandException('not support create table ' . $options['name']);
		}

		$options['connection'] = $options['connection'] ?? 'default';
		$result = $this->{'create' . ucfirst($options['name'])}($options['name'], $options);

		$result && $this->output->writeln('create table ' . $options['name'] . ' success');
	}

	private function createLog($table, $options) {
		if (!empty($options['alias'])) {
			$table = $options['alias'];
		}
		$schema = idb()->connection($options['connection'])->getSchemaBuilder();
		if ($schema->hasTable($table)) {
			$this->output->writeln('the table ' . $table . ' is exists');
			return false;
		}
		$schema->create($table, function ($table) {
			$table->increments('id');
			$table->string('channel', 30);
			$table->string('level', 15);
			$table->text('message');
			$table->addColumn('integer','time', ['length' => 11]);
		});

		return true;
	}
}