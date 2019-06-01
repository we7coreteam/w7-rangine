<?php

namespace W7\Core\Exception;

use W7\App;
use W7\Core\Service\ServiceAbstract;
use Whoops\Handler\PlainTextHandler;
use Whoops\Run;

class ExceptionRegister extends ServiceAbstract {
	public function register() {
		$this->registerErrorHandle();
		iloader()->set(ExceptionHandle::class, function () {
			return new ExceptionHandle(App::$server->type);
		});
	}

	private function registerErrorHandle() {
		$processer = new Run();
		$handle = new PlainTextHandler(ilogger());
		$processer->pushHandler($handle);
		$processer->register();
	}
}