<?php

namespace W7\Core\Filesystem;

use Illuminate\Filesystem\Filesystem;
use W7\Core\Provider\ProviderAbstract;

class FilesystemProvider extends  ProviderAbstract {
	public function register() {
		iloader()->set('filesystem', function () {
			return new Filesystem();
		});
	}
}