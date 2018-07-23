<?php
/**
 * @author donknap
 * @date 18-7-20 下午3:55
 */

namespace W7\Core\Base;

use W7\Core\Listener\ManageServerListener;

class SwooleHttpServer extends \swoole_http_server {
	use ManageServerListener;
}