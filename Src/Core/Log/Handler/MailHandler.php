<?php
/**
 * @author donknap
 * @date 18-10-18 下午6:27
 */

namespace W7\Core\Log\Handler;

use Monolog\Handler\HandlerInterface as MonologInterface;
use Monolog\Handler\SwiftMailerHandler;

class MailHandler extends HandlerAbstract {
	public static function getHandler($config): MonologInterface {
		if (empty($config['to']) || empty($config['subject']) || empty($config['username']) || empty($config['password'])) {
			return null;
		}
		$transport = new \Swift_SmtpTransport($config['server']['host'], $config['server']['port'], $config['server']['scheme']);
		$transport->setUsername($config['username']);
		$transport->setPassword($config['password']);

		$message = new \Swift_Message($config['subject']);
		$message->setTo($config['to']);
		$message->setFrom($config['username']);

		$swiftMailer = new \Swift_Mailer($transport);
		return new SwiftMailerHandler($swiftMailer, $message, $config['level']);
	}

	public function handleBatch(array $records) {

	}
}