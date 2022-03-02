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
use Simps\MQTT\Config\ClientConfig;
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
 * 和传统的监听不太一样，所以这里要启一个 process 替代 listener
 */
class SubscribeListener extends ProcessAbstract {
	private $client;

	/**
	 * 配置默认连接mqtt服务配置
	 * @return Client
	 */
	protected function getClient() {
		$serverSetting = App::$server->setting;

		$clientConfig = [
			'clean_session' => 1,
			'user_name' => '',
			'password' => '',
			'keep_alive' => 50,
			'protocol_name' => MQTT_PROTOCOL_NAME,
			'protocol_level' => MQTT_PROTOCOL_LEVEL_3_1_1,
			'client_id' => uniqid('w7-rangine-mqtt-client-'),
		];
		foreach ($clientConfig as $key => $value) {
			if (isset($serverSetting[$key])) {
				$clientConfig[$key] = $serverSetting[$key];
			}
		}
		return new Client($serverSetting['host'], $serverSetting['port'], new ClientConfig($clientConfig));
	}

	public function check() {
		return true;
	}

	protected function run(Process $process) {
		if (empty($this->client)) {
			$this->client = $this->getClient();
			$this->client->connect(true);
			$this->client->subscribe([
				$this->name =>2,
			]);
		}
		$timeSincePing = time();

		while (true) {
			$frameData = $this->client->recv();
			if ($frameData && $frameData !== true) {
				switch ($frameData['type']) {
					case Types::PUBLISH:
						try {
							$psr7Request = new Psr7Request(Router::METHOD_SUBSCRIBE_TOPIC_POST, $frameData['topic']);
							$psr7Request = $psr7Request->withBody(new SwooleStream($frameData['message']))->withBodyParams($frameData['message'])->withParsedBody(json_decode($frameData['message'], true));
							$psr7Response = new Psr7Response();

							/**
							 * @var RequestDispatcher $dispatcher
							 */
							$dispatcher = $this->getContainer()->get(RequestDispatcher::class);
							$dispatcher->dispatch($psr7Request, $psr7Response);

							if ($frameData['qos'] >= 1) {
								$this->client->send(
									[
										'type' => $frameData['qos'] === 1 ? Types::PUBACK :Types::PUBREC,
										'message_id' => $frameData['message_id'],
									],
									false
								);
							}
						} catch (\Exception $e) {
							$this->getContainer()->get(HandlerExceptions::class)->getHandler()->report($e);
						}
						break;
					case Types::PUBREL:
						$this->client->send(
							[
								'type' => Types::PUBCOMP,
								'message_id' => $frameData['message_id'],
							],
							false
						);
						break;
				}
			}

			if ($timeSincePing <= (time() - $this->client->getConfig()->getKeepAlive())) {
				if ($this->client->ping()) {
					$timeSincePing = time();
				}
			}
		}
	}
}
