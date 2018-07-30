<?php
/**
 * author: alex
 * date: 18-7-30 下午5:50
 */

namespace W7\Core\Listener;


use W7\Core\Base\ListenerInterface;
use W7\Http\Handler\LogHandler;

class AfterRequestListener implements ListenerInterface
{
    public function run()
    {
        /**
         * @var LogHandler $logHandler
         */
      $logHandler = iloader()->singleton(LogHandler::class);
      $logHandler->appendNoticeLog();
    }

}