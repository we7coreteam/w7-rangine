<?php

namespace W7\Tcp\Server;

use W7\Http\Message\Helper\JsonHelper;

/**
 * Class Response
 * @package W7\Tcp\Server
 * test
 */
class Response extends \W7\Http\Message\Base\Response {
    protected $content;

    public function json($data = [], $status = 200, int $encodingOptions = JSON_UNESCAPED_UNICODE) {
        $this->content = JsonHelper::encode([
            'data' => $data,
            'code' => $status
        ], $encodingOptions);
        return $this;
    }

    public function getContent() {
        return $this->content;
    }
}