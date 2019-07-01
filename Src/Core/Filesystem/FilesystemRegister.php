<?php

namespace W7\Core\Filesystem;

use Illuminate\Filesystem\Filesystem;
use W7\Core\Service\ServiceAbstract;

class FilesystemRegister extends  ServiceAbstract {
	public function register() {
		iloader()->set('filesystem', function () {
			return new Filesystem();
		});
	}
}