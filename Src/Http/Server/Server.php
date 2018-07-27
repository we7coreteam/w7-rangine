<?php
/**
 * @author donknap
 * @date 18-7-20 上午9:14
 */

namespace W7\Http\Server;

use W7\Core\Base\ServerAbstract;
use W7\Core\Base\SwooleHttpServer;
use W7\Core\Helper\Context;
use W7\Core\Helper\Middleware;


class Server extends ServerAbstract {

	public $type = parent::TYPE_HTTP;

	public function start() {
		if (!empty($this->setting['open_http2_protocol'])) {
			$this->connection['type'] = SWOOLE_SOCK_TCP|SWOOLE_SSL;
		}
		//Dispather::addRoute();
		$this->server = new SwooleHttpServer($this->connection['host'], $this->connection['port'], $this->connection['mode'], $this->connection['sock_type']);
		$this->server->set($this->setting);
		Context::setContextDataByKey('test', '1123');
		$this->registerEventListener();
		$this->registerProcesser();
		$this->registerBeforeStartListener();
		$this->registerServerContext();

		$this->server->start();
	}

	private function registerBeforeStartListener()
    {
        $event = iconfig()->getUserConfig("event");
        foreach ($event['beforeStart'] as $prefix => $listener)
        {
            call_user_func([$listener, 'run']);
        }
    }

    private function registerServerContext()
    {
        /**
         * @var Context $contextObj
         */
        $contextObj = iloader()->singleton(Context::class);
        $this->server->context[Middleware::MIDDLEWARE_MEMORY_TABLE_NAME] = $contextObj->getContextDataByKey(Middleware::MIDDLEWARE_MEMORY_TABLE_NAME);
        $this->server->context[Dispather::ROUTE_CONTEXT_KEY] = $contextObj->getContextDataByKey(Dispather::ROUTE_CONTEXT_KEY);
    }
}