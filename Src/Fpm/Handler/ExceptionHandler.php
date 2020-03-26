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

namespace W7\Fpm\Handler;

use W7\Http\Handler\ExceptionHandler as HandlerAbstract;
use W7\Http\Message\Outputer\FpmResponseOutputer;
use W7\Http\Message\Server\Response;
use W7\Http\Message\Server\Response as Psr7Response;

class ExceptionHandler extends HandlerAbstract {
	public function handle(\Throwable $e): Response {
		$response = parent::handle($e);

		if (!icontext()->getResponse()) {
			//表示在未进dispatcher流程就出错
			$fpmResponse = new Psr7Response();
			$fpmResponse->setOutputer(new FpmResponseOutputer());
			$fpmResponse = $fpmResponse->withStatus($response->getStatusCode())->withContent($response->getBody()->getContents());
			$fpmResponse->send();
		}

		return $response;
	}
}
