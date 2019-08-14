<?php

namespace W7\Core\Log\Handler;

use Monolog\Handler\HandlerInterface as MonologInterface;

class MysqlHandler extends HandlerAbstract {
	protected $table;
	protected $connection;

	public static function getHandler($config): MonologInterface {
		$handle = new static();
		$handle->table = $config['table'] ?? 'log';
		$handle->connection = $config['connection'] ?? 'default';
		return $handle;
	}

	public function handleBatch(array $records) {
		foreach ($records as &$record) {
			$record = [
				'channel' => $record['channel'],
				'level' => $record['level'],
				'message' => $record['message'],
				'created_at' => $record['datetime']->format('U')
			];
		}
		idb()->connection($this->connection)->table($this->table)->insert($records);
	}
}