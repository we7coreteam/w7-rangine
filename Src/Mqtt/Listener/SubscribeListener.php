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

namespace W7\Mqtt\Listener;

use Simps\MQTT\Client;
use Simps\MQTT\Message\PingResp;
use Simps\MQTT\Message\PubAck;
use Simps\MQTT\Message\PubComp;
use Simps\MQTT\Message\PubRec;
use Simps\MQTT\Protocol\Types;
use Swoole\Process;
use W7\App;
use W7\Core\Exception\HandlerExceptions;
use W7\Core\Process\ProcessAbstract;
use W7\Core\Route\Router;
use W7\Http\Message\Server\Request as Psr7Request;
use W7\Http\Message\Server\Response as Psr7Response;
use W7\Http\Message\Stream\SwooleStream;
use W7\Mqtt\Server\Dispatcher as RequestDispatcher;

/**
 * mqtt 监听机制是在服务启动的时候进行订阅注册
 * 先不添加ping包响应检测, recv会触发断线重连
 * 和传统的监听不太一样，所以这里要启一个 process 替代 listener
 */
class SubscribeListener extends ProcessAbstract {
	/**
	 * @var Client
	 */
	private $client;

	/**
	 * 配置默认连接mqtt服务配置
	 * clean_session=0断开连接后，服务器不会清除该client_id的数据，　下次重新连接会继续收到断线期间的订阅数据
	 * @return Client
	 */
	protected function getClient() {
		$serverSetting = App::$server->setting;
		$serverSetting['client_id'] = $serverSetting['client_id'] ?? 'w7-rangine-mqtt-client-' . md5($this->name);
		return imqttClient($serverSetting['host'], $serverSetting['port'], $serverSetting);
	}

	public function check() {
		return true;
	}

	protected function run(Process $process) {
		$this->client = $this->getClient();
		$this->client->connect(App::$server->setting['clean_session'] ?? false);
		//这里设置qos为2,按照mqtt 最小原则　由发布方决定最后的qos
		$this->client->subscribe([
			$this->name =>2,
		]);
		/**
		 * @var RequestDispatcher $dispatcher
		 */
		$dispatcher = $this->getContainer()->get(RequestDispatcher::class);
		$route = $dispatcher->getRouteByMethodAndUrl(Router::METHOD_SUBSCRIBE_TOPIC, $this->getName());
		$dispatcher->setRoute($route);

		itimeTick($this->client->getConfig()->getKeepAlive() * 1000, function () {
			$this->client->ping();
		});
		while (true) {
			$frameData = $this->client->recv();
			if ($frameData && $frameData !== true) {
				switch ($frameData['type']) {
					case Types::PUBLISH:
						try {
							$psr7Request = new Psr7Request(Router::METHOD_SUBSCRIBE_TOPIC, $frameData['topic']);
							$psr7Request = $psr7Request->withBody(new SwooleStream($frameData['message']))->withBodyParams($frameData['message'])->withParsedBody(json_decode($frameData['message'], true));
							$psr7Response = new Psr7Response();

							$dispatcher->dispatch($psr7Request, $psr7Response);

							if ($frameData['qos'] >= MQTT_QOS_1) {
								if ($frameData['qos'] === MQTT_QOS_1) {
									$message = new PubAck();
								} else {
									$message = new PubRec();
								}
								$message->setMessageId($frameData['message_id']);
								$this->client->send($message->getContents(true), false);
							}
						} catch (\Exception $e) {
							$this->getContainer()->get(HandlerExceptions::class)->getHandler()->report($e);
						}
						break;
					case Types::PUBREL:
						$message = new PubComp();
						$message->setMessageId($frameData['message_id']);
						$this->client->send($message->getContents(true), false);
						break;
					case Types::PINGREQ:
						$message = new PingResp();
						$this->client->send($message->getContents(true), false);
						break;
				}
			}
		}
	}
}
