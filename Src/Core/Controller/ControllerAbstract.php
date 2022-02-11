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

use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use W7\Contract\Validation\ValidatorFactoryInterface;
use W7\Contract\View\ViewInterface;
use W7\Core\Exception\ValidatorException;
use W7\Core\Helper\Traiter\AppCommonTrait;
use W7\Core\Middleware\ControllerMiddlewareOptions;
use W7\Http\Message\Server\Request;

abstract class ControllerAbstract {
	use AppCommonTrait;

	/**
	 * The middleware registered on the controller.
	 *
	 * @var array
	 */
	protected $middleware = [];

	public function middleware($middleware, array $options = []) {
		if (isset($middleware['class'])) {
			$middleware = [$middleware];
		}
		foreach ((array) $middleware as $m) {
			$this->middleware[] = [
				'middleware' => $m,
				'options' => &$options,
			];
		}

		return new ControllerMiddlewareOptions($options);
	}

	public function getMiddleware() {
		return $this->middleware;
	}

	/**
	 * @return null|\W7\Http\Message\Server\Response
	 */
	protected function response() {
		$response = $this->getContext()->getResponse();
		if (empty($response)) {
			throw new \RuntimeException('There are no response objects in this context');
		}
		return $response;
	}

	/**
	 * @return null|Request
	 */
	protected function request() {
		$request = $this->getContext()->getRequest();
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
		return $this->responseHtml($this->getContainer()->get(ViewInterface::class)->render($name, $context));
	}

	public function validate(Request $request, array $rules, array $messages = [], array $customAttributes = []) {
		if (empty($request)) {
			throw new ValidatorException('Request object not found');
		}
		$requestData = array_merge([], $request->getQueryParams(), $request->post(), $request->getUploadedFiles());

		try {
			/**
			 * @var Factory $validate
			 */
			$result = $this->getContainer()->get(ValidatorFactoryInterface::class)->make($requestData, $rules, $messages, $customAttributes)
				->validate();
		} catch (ValidationException $e) {
			$errorMessage = [];
			$errors = $e->errors();
			foreach ($errors as $message) {
				$errorMessage[] = $message[0];
			}
			throw new ValidatorException(implode('; ', $errorMessage), 403);
		}

		return $result;
	}
}
