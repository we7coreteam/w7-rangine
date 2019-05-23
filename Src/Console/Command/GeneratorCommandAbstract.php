<?php


namespace W7\Console\Command;

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;
use W7\Core\Exception\CommandException;

abstract class GeneratorCommandAbstract extends CommandAbstract {
	protected $file;
	protected $type;
	protected $dir = 'app';


	protected function configure() {
		$this->addOption('--name', null, InputOption::VALUE_REQUIRED, 'the generator name');
		$this->addOption('--dir', '-d', null, 'the generator root path');
		$this->addOption('--force', '-f', null, 'override the original');
		$this->file = new Filesystem();
	}

	/**
	 * Get the stub file for the generator.
	 *
	 * @return string
	 */
	abstract protected function getStub();

	protected function handle($options) {
		if (empty($options['name'])) {
			throw new CommandException('option name not be empty');
		}
		if (!empty($options['dir'])) {
			$this->dir = $options['dir'];
		}

		$name = $this->qualifyClass($options['name']);
		$path = $this->getPath($name);

		if (empty($options['force']) && $this->alreadyExists($options['name'])) {
			$this->output->error($this->type.' already exists!');
			return false;
		}

		$this->makeDirectory($path);

		$this->file->put($path, $this->buildClass($name));

		$this->output->info($this->type.' created successfully.');
	}

	/**
	 * Parse the class name and format according to the root namespace.
	 *
	 * @param  string  $name
	 * @return string
	 */
	protected function qualifyClass($name) {
		$name = ltrim($name, '\\/');
		$rootNamespace = $this->rootNamespace();
		if (substr($name, 0, strlen($rootNamespace)) === $rootNamespace) {
			return $name;
		}

		$name = str_replace('/', '\\', $name);

		return $this->qualifyClass(
			$this->getDefaultNamespace(trim($rootNamespace, '\\')).'\\'.$name
		);
	}

	/**
	 * Get the default namespace for the class.
	 *
	 * @param  string  $rootNamespace
	 * @return string
	 */
	protected function getDefaultNamespace($rootNamespace) {
		return $rootNamespace;
	}

	/**
	 * Determine if the class already exists.
	 *
	 * @param  string  $rawName
	 * @return bool
	 */
	protected function alreadyExists($rawName) {
		return $this->file->exists($this->getPath($this->qualifyClass($rawName)));
	}

	/**
	 * Get the destination class path.
	 *
	 * @param  string  $name
	 * @return string
	 */
	protected function getPath($name) {
		$position = strpos($name, $this->rootNamespace());
		if ($position !== false) {
			$name = substr_replace($name, '', $position, strlen($this->rootNamespace()));
		}

		return BASE_PATH . '/' . $this->dir . '/' . str_replace('\\', '/', $name).'.php';
	}

	/**
	 * Build the directory for the class if necessary.
	 *
	 * @param  string  $path
	 * @return string
	 */
	protected function makeDirectory($path) {
		if (! $this->file->isDirectory(dirname($path))) {
			$this->file->makeDirectory(dirname($path), 0777, true, true);
		}

		return $path;
	}

	/**
	 * Build the class with the given name.
	 *
	 * @param  string  $name
	 * @return string
	 * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
	 */
	protected function buildClass($name) {
		$stub = $this->file->get($this->getStub());

		return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
	}

	/**
	 * Replace the namespace for the given stub.
	 *
	 * @param  string  $stub
	 * @param  string  $name
	 * @return $this
	 */
	protected function replaceNamespace(&$stub, $name) {
		$stub = str_replace(
			['DummyNamespace', 'DummyRootNamespace'],
			[$this->getNamespace($name), $this->rootNamespace()],
			$stub
		);

		return $this;
	}

	/**
	 * Get the full namespace for a given class, without the class name.
	 *
	 * @param  string  $name
	 * @return string
	 */
	protected function getNamespace($name) {
		return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
	}

	/**
	 * Replace the class name for the given stub.
	 *
	 * @param  string  $stub
	 * @param  string  $name
	 * @return string
	 */
	protected function replaceClass($stub, $name) {
		$class = str_replace($this->getNamespace($name).'\\', '', $name);

		return str_replace('DummyClass', $class, $stub);
	}

	/**
	 * Get the root namespace for the class.
	 *
	 * @return string
	 */
	protected function rootNamespace() {
		return 'W7\App';
	}
}