<?php

namespace W7\Core\Exception;

use W7\App;
use W7\Core\Provider\ProviderAbstract;
use Whoops\Handler\PlainTextHandler;
use Whoops\Run;

class ExceptionProvider extends ProviderAbstract {
	public function register() {
		$this->registerErrorHandle();
		iloader()->set(ExceptionHandle::class, function () {
			return new ExceptionHandle(App::$server->type);
		});
	}

	private function registerErrorHandle() {
		$processer = new Run();
		$handle = new PlainTextHandler(ilogger());
		if ((ENV & BACKTRACE) !== BACKTRACE) {
			$handle->addTraceToOutput(false);
			$handle->addPreviousToOutput(false);
		}
		$processer->pushHandler($handle);
		$processer->register();
	}
}