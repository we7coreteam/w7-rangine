<?php

namespace W7\Console\Command;

abstract class CommandAbstract {
	protected $cmds = [
		'h|help|-h|-help' => 'help'
	];
	protected $cmdProcesser = [];

	public function run ($command) {
		if ($command['action']) {
			try{
				$this->dispatch($command['action'], $command['option']);
				return true;
			} catch (\Throwable $e) {
				\ioutputer()->writeln($e->getMessage());
			} finally {
				$command['option'] = [];
			}
		}

		$this->parseCmds();
		$this->process($command['option']);
	}

	/**
	 * 处理有具体操作的command，即arguments存在的command
	 * @param $action
	 * @param $options
	 */
	public function dispatch($action, $options) {

	}

	/**
	 *处理没有具体操作的command，即arguments为空的command
	 * @param $cmds
	 */
	private function process($cmds) {
		if (!$cmds) {
			$cmds = [
				'-h' => true
			];
		}

		foreach ($cmds as $cmd => $value) {
			if (!empty($this->cmdProcesser[$cmd])) {
				$processer = $this->cmdProcesser[$cmd];
				$this->$processer($value);
				unset($cmds[$cmd]);
			}
		}

		/**
		 * 处理未注册的command
		 */
		if ($cmds && get_called_class() !== DefaultCommand::class) {
			$defaultCommand = new DefaultCommand();
			$defaultCommand->run([
				'action' => null,
				'option' => $cmds
			]);
		}
	}

	private function parseCmds() {
		foreach ($this->cmds as $cmd => $process) {
			$cmd = explode('|', $cmd);
			foreach ($cmd as $key) {
				$this->cmdProcesser[$key] = $process;
			}
		}
	}
}