<?php
/**
 * @author donknap
 * @date 18-8-1 下午5:21
 */

namespace W7\Core\Database\Connector;

use Illuminate\Database\Connectors\Connector;
use Illuminate\Database\Connectors\ConnectorInterface;

class SwooleMySqlConnector extends Connector implements ConnectorInterface
{
    public function connect(array $config)
    {
        $connection = new \Swoole\Coroutine\MySQL();
        $connection->connect([
            'host' => $config['host'],
            'port' => !empty($config['port']) ? $config['port'] : '3306',
            'user' => $config['username'],
            'password' => $config['password'],
            'database' => $config['database'],
            'charset' => $config['charset'],
            'strict_type' => false,
            'fetch_mode' => true,
        ]);
        ilogger()->info('connection id ' . spl_object_hash($connection));
        if ($connection === false || !empty($connection->connect_errno)) {
            throw new \RuntimeException($connection->connect_error);
        }
        return $connection;
    }
}
