<?php

namespace W7\Core\Crontab;

use W7\Core\Crontab\Process\CrontabDispatcher;
use W7\Core\Crontab\Process\CrontabExecutor;

class Register {
	public function __invoke() : array {
		//追加crontab 的process 到process 的配置中
		$crontabSetting = iconfig()->getUserConfig('crontab')['setting'] ?? [];
		$processConfig = iconfig()->getUserConfig('process');
		$crontabSetting['message_queue_key'] =(int)($crontabSetting['message_queue_key'] ?? 0);
		$crontabSetting['message_queue_key'] = $crontabSetting['message_queue_key'] > 0 ? $crontabSetting['message_queue_key'] : irandom(6, true);

		$processConfig['process']['crontab_dispatch'] = [
			'class' => CrontabDispatcher::class,
			'message_queue_key' => $crontabSetting['message_queue_key'],
			'number' => 1
		];
		$processConfig['process']['crontab_executor'] = [
			'class' => CrontabExecutor::class,
			'message_queue_key' => $crontabSetting['message_queue_key'],
			'number' => $crontabSetting['worker_num'] ?? 1
		];
		iconfig()->setUserConfig('process', $processConfig);

		return [
			'crontab_dispatch',
			'crontab_executor'
		];
	}
}