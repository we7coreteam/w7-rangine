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

namespace W7\Core\Exception\Handler;

use Throwable;
use W7\Core\Exception\Formatter\ExceptionFormatterInterface;
use W7\Core\Exception\ResponseExceptionAbstract;
use W7\Core\Helper\Traiter\AppCommonTrait;
use W7\Http\Message\Server\Response;

class ExceptionHandler {
	use AppCommonTrait;

	protected $serverType;
	protected $response;
	/**
	 * @var ExceptionFormatterInterface
	 */
	protected $exceptionFormatter;

	public function setServerType($serverType): void {
		$this->serverType = $serverType;
	}

	protected function getServerType() {
		return $this->serverType;
	}

	public function setResponse(Response $response): void {
		$this->response = $response;
	}

	public function setExceptionFormatter(ExceptionFormatterInterface $exceptionFormatter) {
		$this->exceptionFormatter = $exceptionFormatter;
	}

	/**
	 * @return Response
	 */
	public function getResponse() {
		return $this->response;
	}

	public function isReport(Throwable $throwable) {
		return !property_exists($throwable, 'isReport') || $throwable->isReport;
	}

	public function report(\Throwable $throwable) {
		if (!$this->isReport($throwable)) {
			return true;
		}

		$errorMessage = sprintf(
			'Uncaught Exception %s: "%s" at %s line %s',
			get_class($throwable),
			$throwable->getMessage(),
			$throwable->getFile(),
			$throwable->getLine()
		);

		$context = [];
		if ((ENV & BACKTRACE) === BACKTRACE) {
			$context = array('exception' => $throwable);
		}

		$this->getLogger()->debug($errorMessage, $context);
	}

	public function handle(\Throwable $e) : Response {
		// ResponseExceptionAbstract as a special exception, the exception whenever will feedback to the client
		if ($e instanceof ResponseExceptionAbstract) {
			return $this->getResponse()->withStatus(empty($e->getCode()) ? 500 : $e->getCode())->withContent($e->getMessage());
		}

		if ((ENV & DEBUG) === DEBUG) {
			return $this->handleDevelopment($e);
		}

		return $this->handleRelease($e);
	}

	protected function handleRelease(\Throwable $e) : Response {
		return $this->getResponse()->withStatus(500)->withContent($this->exceptionFormatter->formatReleaseExceptionToString($e));
	}

	protected function handleDevelopment(\Throwable $e) : Response {
		return $this->getResponse()->withStatus(500)->withContent($this->exceptionFormatter->formatDevelopmentExceptionToString($e));
	}
}
