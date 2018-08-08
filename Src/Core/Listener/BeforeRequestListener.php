<?php
/**
 * author: alex
 * date: 18-7-30 下午5:50
 */

namespace W7\Core\Listener;

use W7\Core\Base\Listener\ListenerInterface;
use W7\Core\Helper\Log\LogHelper;

class BeforeRequestListener implements ListenerInterface
{
	public function run()
	{
		/**
		 * @var LogHelper $logHanler
		 */
		$logHanler = iloader()->singleton(LogHelper::class);
		$logHanler->beforeRequestInit();
	}
}
