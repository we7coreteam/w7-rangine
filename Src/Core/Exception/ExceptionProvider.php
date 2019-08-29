<?php

namespace W7\Core\Exception;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use W7\Core\Provider\ProviderAbstract;

class ExceptionProvider extends ProviderAbstract {
	public function register() {
		$this->registerUserException();
	}

	private function registerUserException() {
		$map = [];

		$dir = __DIR__;
		$files = Finder::create()
			->in($dir)
			->files()
			->ignoreDotFiles(true)
			->name('/^[\w\W\d]+Exception.php$/');

		/**
		 * @var SplFileInfo $file
		 */
		foreach ($files as $file) {
			if (file_exists(APP_PATH . '/Exception/' . $file->getFilename())) {
				$map['W7\Core\Exception\\' . $file->getBasename('.php')] = 'W7\App\Exception\\' . $file->getBasename('.php');
			}
		}

		ExceptionHandle::registerUserExceptionMap($map);
	}
}