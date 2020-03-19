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

use Symfony\Component\HttpFoundation\File\UploadedFile;
use W7\App;
use W7\Core\Exception\ValidatorException;
use W7\Core\View\View;
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
		return $this->response()->withContent($data);
	}

	protected function responseJson($data) {
		return $this->response()->withHeader('Content-Type', 'application/json')->withContent($data);
	}

	protected function responseHtml($data) {
		return $this->response()->withHeader('Content-Type', 'text/html')->withContent($data);
	}

	protected function render($name, $context = []) {
		return $this->responseHtml(iloader()->get(View::class)->render($name, $context));
	}

	public function validate(Request $request, array $rules, array $messages = [], array $customAttributes = []) {
		if (empty($request)) {
			throw new ValidatorException('Request object not found');
		}
		$requestData = array_merge([], $request->getQueryParams(), $request->post());

		//如果有上传文件，需要转化为laravel上传对象附加到requestData中
		$uploadFile = $request->getUploadedFiles();
		if (!empty($uploadFile)) {
			/**
			 * @var \W7\Http\Message\Upload\UploadedFile $file
			 */
			foreach ($uploadFile as $name => $file) {
				$requestData[$name] = new UploadedFile($file->getTmpFile(), $file->getClientFilename(), $file->getClientMediaType(), $file->getError());
			}
		}
		return ivalidate($requestData, $rules, $messages, $customAttributes);
	}
}
