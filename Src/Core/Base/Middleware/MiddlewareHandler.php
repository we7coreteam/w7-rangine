<?php
/**
 * 由Dispatch调用，传入准备好的中间件和处理回调，调用中间件队列
 * @author donknap
 * @date 18-7-21 上午11:08
 */


namespace W7\Core\Base\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MiddlewareHandler implements RequestHandlerInterface
{
	/**
	* @var array
	*/
	private $middlewares;

	/**
	 * @var string
	 */
	private $default;

	/**
	 * @var integer
	 *
	 */
	private $offset = 0;

	/**
	 * MiddlewareHandler constructor.
	 *
	 * @param array $middleware
	 * @param string $default
	 */
	public function __construct(array $middleware)
	{
		$this->middlewares = \array_unique($middleware);
	}

	/**
	 * Process the request using the current middleware.
	 *
	 * @param ServerRequestInterface $request
	 * @return ResponseInterface
	 * @throws \InvalidArgumentException
	 */
	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		$handler = $this->middlewares[$this->offset];
		$handler = iloader()->singleton($handler);


		if (!$handler instanceof MiddlewareInterface) {
			throw new \InvalidArgumentException('Invalid Handler. It must be an instance of MiddlewareInterface');
		}

		return $handler->process($request, $this->next());
	}

	/**
	 * Get a handler pointing to the next middleware.
	 *
	 * @return static
	 */
	private function next()
	{
		$clone = clone $this;
		$clone->offset++;
		return $clone;
	}
}
