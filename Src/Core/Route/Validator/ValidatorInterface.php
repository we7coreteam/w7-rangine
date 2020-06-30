<?php

namespace W7\Core\Route\Validator;

interface ValidatorInterface {
	public function match($httpMethod, $route, $handler) : bool ;
}