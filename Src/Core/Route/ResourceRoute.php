<?php
/**
 * @author donknap
 * @date 19-4-26 下午2:18
 */

namespace W7\Core\Route;


class ResourceRoute {

	protected $router;

	protected $resourceDefaults = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];
}