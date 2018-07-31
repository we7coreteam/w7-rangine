<?php
/**
 * author: alex
 * date: 18-7-31 上午9:16
 */

namespace W7\Core\Helper;


use Dotenv\Dotenv;

class EnvHelper
{
    /**
     * EnvHelper constructor.
     */
    public function __construct()
    {
        if (file_exists(BASE_PATH . DIRECTORY_SEPARATOR . ".env")) {
            $dotenv = new Dotenv(BASE_PATH);
            $dotenv->load();
        }
    }

    /**
     * @param array $config
     * @return array
     */
    public function overWrite(array $config)
    {
        foreach ($config as $key=>$value)
        {
            if (is_array($value)){
                $config[$key] = $this->overWrite($value);
                continue;
            }
            $config[$key] = env(strtoupper($key), $value);
        }
        return $config;
    }
}