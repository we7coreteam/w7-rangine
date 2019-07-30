<?php

namespace W7\WebSocket\Message;

use W7\Http\Message\Server\Request as Psr7Request;
use Swoole\WebSocket\Frame;
use W7\Http\Message\Stream\SwooleStream;

/**
 * Class Request
 *
 * @since 2.0
 * @Bean(scope=Bean::PROTOTYPE)
 */
class Request extends Psr7Request {
    /**
     * @var Frame
     */
    private $frame;

    /**
     * @param Frame $frame
     *
     * @return Request
     * @throws ReflectionException
     * @throws ContainerException
     */
    public static function loadFromWebSocketRequest(Frame $frame): self {
	    $body = new SwooleStream();
	    $protocol = 'HTTP/1.1';
	    $request = new static('POST', null, [], $body, $protocol);
	    $request->frame = $frame;
	    return $request->withParsedBody($swooleRequest->post ?? [])
		    ->withUploadedFiles([]);
    }

    /**
     * @return int
     */
    public function getFd(): int {
        return $this->frame->fd;
    }

    /**
     * @return int
     */
    public function getOpcode(): int {
        return $this->frame->opcode;
    }

    /**
     * @return Frame
     */
    public function getFrame(): Frame {
        return $this->frame;
    }
}
