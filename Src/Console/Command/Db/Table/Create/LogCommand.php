<?php

namespace W7\Console\Command\Db\Table\Create;

use Symfony\Component\Console\Input\InputOption;
use W7\Console\Command\Db\DatabaseCommandAbstract;

class LogCommand extends DatabaseCommandAbstract {
	protected function configure() {
		$this->setDescription('create database table log');
		$this->addOption('--name', null, InputOption::VALUE_REQUIRED, 'the mysql log table name');
	}

	protected function handle($options) {
		$table = $options['name'] ?? ienv('LOG_MYSQL_TABLE_NAME', 'log');
		$connection = $options['connection'] ?? ienv('LOG_MYSQL_CONNECTION', 'default');

		$schema = idb()->connection($connection)->getSchemaBuilder();
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
		$this->output->writeln('create table ' . $table . ' success');
	}
}