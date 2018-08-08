<?php
/**
 * author: alex
 * date: 18-7-30 下午5:50
 */

namespace W7\Core\Listener;

use W7\Core\Log\LogHelper;

class AfterRequestListener implements ListenerInterface
{
	public function run()
	{
		/**
		 * @var LogHelper $logHandler
		 */
		$logHandler = iloader()->singleton(LogHelper::class);
		$logHandler->appendNoticeLog();
	}
}
