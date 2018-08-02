<?php
/**
 * author: alex
 * date: 18-8-2 下午2:57
 */

namespace W7\Core\Base;


use Psr\Http\Message\ResponseInterface;

/**
 * Class Response
 * @package W7\Core\Base
 * @method getHeader($name)
 * @method getBody()
 * @method getHeaders()
 * @method getReasonPhrase()
 */
interface Response extends ResponseInterface
{
}