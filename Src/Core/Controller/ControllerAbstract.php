<?php

/**
 * This file is part of Rangine
 *
 * (c) We7Team 2019 <https://www.rangine.com/>
 *
 * document http://s.w7.cc/index.php?c=wiki&do=view&id=317&list=2284
 *
 * visited https://www.rangine.com/ for more details
 */

namespace W7\Core\Controller;

use W7\Core\Exception\ValidatorException;
use W7\Core\Facades\Container;
use W7\Core\Facades\Context;
use W7\Core\View\View;
use W7\Http\Message\Server\Request;

abstract class ControllerAbstract {
	/**
	 * 获取一个response对象
	 * @return null|\W7\Http\Message\Server\Response
	 */
	protected function response() {
		$response = Context::getResponse();
		if (empty($response)) {
			throw new \RuntimeException('There are no response objects in this context');
		}
		return $response;
	}

	/**
	 * 获取一个Request对象
	 * @return null|Request
	 */
	protected function request() {
		$request = Context::getRequest();
		if (empty($request)) {
			throw new \RuntimeException('There are no request objects in this context');
		}
		return $request;
	}

	protected function responseRaw(string $data) {
		return $this->response()->raw($data);
	}

	protected function responseJson($data) {
		return $this->response()->json($data);
	}

	protected function responseHtml($data) {
		return $this->response()->html($data);
	}

	protected function render($name, $context = []) {
		return $this->responseHtml(Container::singleton(View::class)->render($name, $context));
	}

	public function validate(Request $request, array $rules, array $messages = [], array $customAttributes = []) {
		if (empty($request)) {
			throw new ValidatorException('Request object not found');
		}
		$requestData = array_merge([], $request->getQueryParams(), $request->post(), $request->getUploadedFiles());
		return ivalidate($requestData, $rules, $messages, $customAttributes);
	}
}
