<?php
/**
 * 控制器的父类
 * @author donknap
 * @date 18-11-12 上午11:38
 */

namespace W7\Core\Controller;

use W7\App;
use Illuminate\Validation\ValidationException;
use W7\Core\Exception\HttpException;
use W7\Http\Message\Server\Request;

abstract class ControllerAbstract {
	/**
	 * 获取一个response对象
	 * @return null|\W7\Http\Message\Server\Response
	 */
	protected function response() {
		$response = App::getApp()->getContext()->getResponse();
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
		$request = App::getApp()->getContext()->getRequest();
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
		return $this->response()->withHeader('Content-Type', 'text/html;charset=utf-8')->withContent($data);
	}

	public function validate(Request $request, array $rules, array $messages = [], array $customAttributes = []) {
		if (empty($request)) {
			throw new HttpException('Request object not found');
		}
		$requestData = array_merge([], $request->getQueryParams(), $request->post());
		try {
			$result = iloader()->get('validator')->make($requestData, $rules, $messages, $customAttributes)
				->validate();
		} catch (ValidationException $e) {
			$errorMessage = [];
			$errors = $e->errors();
			foreach ($errors as $field => $message) {
				$errorMessage[] = $message[0];
			}
			throw new HttpException(implode('; ', $errorMessage));
		}
		return $result;
	}
}