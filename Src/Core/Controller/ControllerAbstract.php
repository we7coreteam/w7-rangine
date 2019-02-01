<?php
/**
 * 控制器的父类
 * @author donknap
 * @date 18-11-12 上午11:38
 */

namespace W7\Core\Controller;


use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use W7\App;

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
	 * @return null|\Psr\Http\Message\ServerRequestInterface
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

	public function validate(array $rules, array $messages = [], array $customAttributes = []) {
		$request = App::getApp()->getContext()->getRequest();
		if (empty($request)) {
			return true;
		}
		$requestData = array_merge([], $request->getQueryParams(), $request->post());
		$result = $this->getValidater()->make($requestData, $rules, $messages, $customAttributes)
			->validate();

		print_r($result);exit;
	}

	/**
	 * @return Factory;
	 */
	private function getValidater() {
		$translator = iloader()->withClass(Translator::class)->withSingle()->withParams([
			'loader' => new ArrayLoader(),
			'locale' => 'zh-CN',
		])->get();

		$validate = iloader()->withClass(Factory::class)->withSingle()->withParams([
			'translator' => $translator,
		])->get();

		return $validate;
	}
}