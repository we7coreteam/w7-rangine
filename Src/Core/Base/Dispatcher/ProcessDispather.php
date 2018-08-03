<?php
/**
 * author: alex
 * date: 18-8-3 上午10:46
 */

namespace W7\Core\Base\Dispatcher;



use W7\Core\Base\Provider\ProcessProvider;

class ProcessDispather extends DispatcherFacade
{
    public function getFacadeAccessor()
    {
        return iloader()->singleton(ProcessProvider::class);
    }
}