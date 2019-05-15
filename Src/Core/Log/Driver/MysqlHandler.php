<?php

namespace W7\Core\Log\Driver;

use Monolog\Handler\AbstractProcessingHandler;
use W7\Core\Database\ModelAbstract;

class Log extends ModelAbstract {
	public $timestamps = false;
	protected $table = 'log';
	protected $primaryKey = 'id';
	protected $connection = 'default';
	protected $fillable = [];
}

class MysqlHandler extends AbstractProcessingHandler {
	protected $table;
	protected $connection;

	static public function getHandler($config) {
		$handle = new static();
		$handle->table = $config['table'] ?? 'log';
		$handle->connection = $config['connection'] ?? 'default';
		return $handle;
	}

	protected function write(array $record) {
		$dbLog = new Log();
		$dbLog->setTable($this->table);
		$dbLog->setConnection($this->connection);
		$dbLog->channel = (string)$record['channel'];
		$dbLog->level = (string)strtolower($record['level_name']);
		$dbLog->message = (string)"{$record['message']}";
		$dbLog->time = $record['datetime']->format('U');

		$dbLog->save();
	}
}