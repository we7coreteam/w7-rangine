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

namespace W7\Core\Log;

use Monolog\Handler\BufferHandler as MonologBufferHandler;
use W7\Core\Log\Handler\HandlerInterface;

class LogBuffer extends MonologBufferHandler {
	/**
	 * @var HandlerInterface
	 */
	protected $handler;

	public function handle(array $record) : bool {
		$record = $this->handler->preProcess($record);
		return parent::handle($record);
	}
}
