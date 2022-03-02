<?php

namespace W7\Core\Redis\Connectors;

use Illuminate\Support\Arr;
use W7\Core\Redis\Connections\PhpRedisConnection;
use W7\Core\Redis\Connections\PhpRedisClusterConnection;

class PhpRedisConnector extends \Illuminate\Redis\Connectors\PhpRedisConnector {
    public function connect(array $config, array $options) {
        $connector = function () use ($config, $options) {
            return $this->createClient(array_merge(
                $config, $options, Arr::pull($config, 'options', [])
            ));
        };

        return new PhpRedisConnection($connector(), $connector, $config);
    }

    public function connectToCluster(array $config, array $clusterOptions, array $options) {
        $options = array_merge($options, $clusterOptions, Arr::pull($config, 'options', []));

        return new PhpRedisClusterConnection($this->createRedisClusterInstance(
            array_map([$this, 'buildClusterConnectionString'], $config), $options
        ));
    }
}
