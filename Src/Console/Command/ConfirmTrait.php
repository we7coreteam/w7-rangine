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

namespace W7\Console\Command;

use Closure;

trait ConfirmTrait {
	/**
	 * Confirm before proceeding with the action.
	 *
	 * This method only asks for confirmation in production.
	 *
	 * @param string $warning
	 * @param bool|\Closure|null $callback
	 * @return bool
	 */
	public function confirmToProceed(string $warning = 'Application In Production!', bool|Closure $callback = null): bool {
		$callback = is_null($callback) ? $this->getDefaultConfirmCallback() : $callback;

		$shouldConfirm = $callback instanceof Closure ? $callback() : $callback;

		if ($shouldConfirm) {
			if ($this->input->hasOption('force') && $this->input->getOption('force')) {
				return true;
			}

			$this->output->warning($warning);

			$confirmed = $this->output->confirm('Do you really wish to run this command?');

			if (! $confirmed) {
				$this->output->info('Command Cancelled!');

				return false;
			}
		}
		return true;
	}

	/**
	 * Get the default confirmation callback.
	 *
	 * @return \Closure
	 */
	protected function getDefaultConfirmCallback(): Closure {
		return static function () {
			return (ENV & RELEASE) === RELEASE;
		};
	}
}
