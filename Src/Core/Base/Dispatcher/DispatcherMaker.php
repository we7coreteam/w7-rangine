<?php
/**
 * author: alex
 * date: 18-8-3 上午10:41
 */

namespace W7\Core\Base\Dispatcher;

class DispatcherMaker
{

    /**
     * @var TaskDispatcher
     */
    private $taskInstance ;


    /**
     * @var EventDispatcher
     */
    private $evnetInstance ;

    /**
     * @var ProcessDispather
     */
    private $processInstance ;

    public function __construct()
    {
        $this-> taskInstance = iloader()->singleton(TaskDispatcher::class);
        $this-> evnetInstance= iloader()->singleton(EventDispatcher::class);
        $this-> processInstance = iloader()->singleton(ProcessDispather::class);
    }

    public function eventDispatcher(...$param)
    {
        return $this->evnetInstance->dispatch(...$param);
    }

    public function taskDispatcher(...$param)
    {
        return $this->taskInstance->dispatch(...$param);
    }

    public function processDispatcher(...$param)
    {
        return $this->processInstance->dispatch(...$param);
    }
}
