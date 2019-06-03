<?php


namespace W7\Console\Command;

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;
use W7\Core\Exception\CommandException;

abstract class GeneratorCommandAbstract extends CommandAbstract {
	/**
	 * @var Filesystem
	 */
	protected $filesystem;
	protected $type;
	protected $name;


	protected function configure() {
		$this->addArgument('name');
		$this->filesystem = new Filesystem();
	}

	protected function handle($options) {
		$this->name = $this->input->getArgument('name');

		$this->before();

		$this->copyStub();
		$this->replaceStub();
		$this->renameStubs();

		$this->after();

		$this->output->info($this->name.' created successfully.');
	}

	protected function before() {}

	/**
	 * Get the stub file for the generator.
	 *
	 * @return string
	 */
	abstract protected function getStub();

	protected function copyStub() {
		if ($this->filesystem->isDirectory($this->getStub())) {
			$this->filesystem->copyDirectory($this->getStub(), $this->rootPath());
		} else {
			if (!$this->filesystem->exists($this->rootPath())) {
				$this->filesystem->makeDirectory($this->rootPath());
			}
			$this->filesystem->copy($this->getStub(), $this->rootPath() . '/' . $this->name . '.stub');
		}
	}

	abstract protected function replaceStub();

	/**
	 * Get the array of stubs that need PHP file extensions.
	 *
	 * @return array
	 */
	protected function stubsToRename() {
		$stubs = [];
		if ($this->filesystem->isDirectory($this->getStub())) {
			foreach ((new Finder)->in($this->rootPath())->files() as $file) {
				if ($file->getExtension() == 'stub') {
					$stubs[] = $file->getPathname();
				}
			}
		} else {
			$stubs[] = $this->rootPath() . '/' . $this->name . '.stub';
		}

		return $stubs;
	}

	protected function renameStubs() {
		foreach ($this->stubsToRename() as $stub) {
			$this->filesystem->move($stub, str_replace('.stub', '.php', $stub));
		}
	}

	protected function after(){}

	/**
	 * Replace the given string in the given file.
	 *
	 * @param  string  $search
	 * @param  string  $replace
	 * @param  string  $path
	 * @return void
	 */
	protected function replace($search, $replace, $path) {
		$path = $this->rootPath() . '/' . $path;
		file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
	}

	protected function savePath() {
		return '';
	}

	/**
	 * Get the path to the tool.
	 *
	 * @return string
	 */
	protected function rootPath() {
		return BASE_PATH . $this->savePath();
	}
}
