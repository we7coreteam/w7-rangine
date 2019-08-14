<?php

namespace W7\Core\Log\Handler;

use Monolog\Handler\AbstractProcessingHandler;

abstract class HandlerAbstract extends AbstractProcessingHandler implements HandlerInterface {
	protected function write(array $record) {
		// TODO: Implement write() method.
	}
}