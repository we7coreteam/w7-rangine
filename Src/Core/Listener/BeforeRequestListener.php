<?php
/**
 * author: alex
 * date: 18-7-30 下午5:50
 */

namespace W7\Core\Listener;

use W7\Core\Base\ListenerInterface;
use W7\Core\Helper\Context;
use W7\Core\Helper\LogHelper;
use W7\Http\Server\Dispather;

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
