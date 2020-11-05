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

namespace W7\Console;

use Symfony\Component\Console\Application as SymfontApplication;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use W7\App;
use W7\Console\Io\Output;

class Application extends SymfontApplication {
	public function __construct() {
		$version = $this->version();
		parent::__construct(App::NAME, $version);

		$this->setAutoExit(false);
		$this->registerCommands();
	}

	/**
	 * Gets the default input definition.
	 *
	 * @return InputDefinition An InputDefinition instance
	 */
	protected function getDefaultInputDefinition() {
		return new InputDefinition([
			new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'),

			new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display this help message'),
			new InputOption('--quiet', '-q', InputOption::VALUE_NONE, 'Do not output any message'),
			new InputOption('--version', '-v', InputOption::VALUE_NONE, 'Display this application version'),
			new InputOption('--ansi', '', InputOption::VALUE_NONE, 'Force ANSI output'),
			new InputOption('--no-ansi', '', InputOption::VALUE_NONE, 'Disable ANSI output'),
			new InputOption('--no-interaction', '-n', InputOption::VALUE_NONE, 'Do not ask any interactive question'),
		]);
	}

	public function run(InputInterface $input = null, OutputInterface $output = null) {
		return parent::run($input, new Output());
	}

	public function doRun(InputInterface $input, OutputInterface $output) {
		if (true === $input->hasParameterOption(['--version', '-v'], true)) {
			$output->writeln($this->logo());
			$output->writeln($this->getLongVersion());
			return 0;
		}

		if (!$this->checkCommand($input)) {
			$output->writeln($this->logo());
			$input = new ArgvInput(['command' => 'list']);
		} elseif (true === $input->hasParameterOption(['--help', '-h'], true)) {
			$output->writeln($this->logo());
		}

		try {
			return parent::doRun($input, $output);
		} catch (\Throwable $e) {
			$this->renderThrowable($e, $output);
		}
	}

	private function registerCommands() {
		$this->autoRegisterCommands(__DIR__. '/Command', '\\W7\\Console');
		$this->autoRegisterCommands(APP_PATH  . '/Command', '\\W7\\App');
	}

	public function autoRegisterCommands($path, $classNamespace, $commandNamespace = null) {
		if (!file_exists($path)) {
			return false;
		}
		$commands = $this->findCommands($path, $classNamespace, $commandNamespace);
		foreach ($commands as $name => $class) {
			$commandObj = new $class($name);
			$this->add($commandObj);
		}
	}

	/**
	 * 自动在指定的目录下找command, 命令文件必须保存在command下的目录中, 最后的command名称，是按照目录和文件名称命名
	 * 例如 app/Command/Test/FirstCommand.php  命令名称为 test:first
	 * @param $path
	 * @param $classNamespace
	 * @param $commandNamespace
	 * @return array
	 */
	private function findCommands($path, $classNamespace, $commandNamespace) {
		$commands = [];

		$files = Finder::create()
			->in($path)
			->files()
			->ignoreDotFiles(true)
			->name('/^[\w\W\d]+Command.php$/');

		/**
		 * @var SplFileInfo $file
		 */
		foreach ($files as $file) {
			$dir = trim(str_replace([$path, DIRECTORY_SEPARATOR], ['', '\\'], $file->getPath()), '\\');
			if (!$dir) {
				continue;
			}
			//如果command没有组,默认属于$defaultGroup下
			$parent = str_replace('\\', ':', $commandNamespace ? $commandNamespace . ':' . $dir  :  $dir);
			$name = strtolower($parent . ':' . $file->getBasename('Command.php'));

			$commands[$name] = $classNamespace . '\\Command\\' . ($dir !== '' ? $dir . '\\' : '') . $file->getBasename('.php');
		}

		return $commands;
	}

	private function checkCommand($input) {
		$command = $this->getCommandName($input) ?? '';
		if ($this->has($command) && strpos($command, ':') !== false) {
			return true;
		}
		return false;
	}

	private function logo() {
		return "
__      _______ _______                   _      
\ \    / /  ___  / ___|_      _____   ___ | | ___ 
 \ \ /\ / /   / /\___ \ \ /\ / / _ \ / _ \| |/ _ \
  \ V  V /   / /  ___) \ V  V / (_) | (_) | |  __/
   \_/\_/   /_/  |____/ \_/\_/ \___/ \___/|_|\___|
";
	}

	private function version() {
		$frameworkVersion = App::VERSION;
		$phpVersion = PHP_VERSION;
		$swooleVersion = SWOOLE_VERSION;
		$version = "framework: $frameworkVersion, php: $phpVersion, swoole: $swooleVersion";

		return $version;
	}
}
