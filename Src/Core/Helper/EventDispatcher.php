<?php
/**
 * @author donknap
 * @date 18-7-25 下午4:35
 */

namespace W7\Core\Helper;

use W7\App;
use W7\Core\Base\EventInterface;
use W7\Core\Config\Event;

class EventDispatcher {


    // 通配符 - 所有触发的事件都会流过
    const MATCH_ALL = '*';

    /**
     * @var self
     */
    private $parent;

    /**
     * @var EventInterface
     */
    private $basicEvent;

    /**
     * 预定义的事件存储
     * @var EventInterface[]
     * [
     *     'event name' => (object)EventInterface -- event description
     * ]
     */
    protected $events = [];


    protected $suffix = "Listener";

//    /**
//     * 监听器存储
//     * @var ListenerQueue[]
//     */
//    protected $listeners = [];
    static $sysevent = [
        'start',
        'workerStart',
        'managerStart',
        'request',
        'task',
        'finish',
        'pipeMessage',
        'connect',
        'receive',
        'close',
    ];
	public function trigger() {
	    $customEvent = [];
	    $eventReflectionClass = new \ReflectionClass(Event::class);
	    $eventArray = $eventReflectionClass->getConstants();
	    foreach($eventArray as $event=>$prefix)
	    {
	        if (in_array($prefix, static::$sysevent)){
	            continue;
            }
            $customEvent[$event] = $prefix;
        }
        $prefix = ucwords($prefix);
	    $server = App::$server;
	    switch ($server::$type)
        {
            case $server::TYPE_HTTP:
                $namepaces = "W7\Http\Listener";
                break;
            default:
                break;
        }
        $eventObj = new $namepaces. $prefix . $this->suffix;
	    return call_user_func([$eventObj, "run"]);
	}
}