<?php


namespace W7\Core\Log\Driver;


use Monolog\Handler\AbstractProcessingHandler;
use W7\Core\Database\ModelAbstract;

class Log extends ModelAbstract {
	public $timestamps = false;
	protected $table = 'log';
	protected $primaryKey = 'id';
	protected $fillable = [];
}

class MysqlHandler extends AbstractProcessingHandler {
	static public function getHandler($config) {
		return new static();
	}

	protected function write(array $record) {
		$dbLog = new Log();
		$dbLog->channel = (string)$record['channel'];
		$dbLog->level = (string)strtolower($record['level_name']);
		$dbLog->message = (string)"{$record['message']}";
		$dbLog->time = $record['datetime']->format('U');

		$dbLog->save();
	}
}