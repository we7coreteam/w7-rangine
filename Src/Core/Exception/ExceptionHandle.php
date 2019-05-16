<?php

namespace W7\Core\Exception;

class ExceptionHandle {
  private $exceptionMap = [
    'http' => HttpException::class,
    'tcp' => TcpException::class
  ];
  private $type;
  private $env;

  public function __construct($type) {
    $this->type = $type;
    $this->env = 'release';
    $setting = iconfig()->getUserAppConfig('setting');
    if (!empty($setting['development'])) {
      $$this->env = 'dev';
    }
  }

  public function handle(\Throwable $throwable) {
    $type = 'custom';
    if (!($throwable instanceof ResponseException)) {
      $exception = $this->exceptionMap[$this->type];
      $throwable = new $exception($throwable->getMessage(), $throwable->getCode(), $throwable);
      $type = $this->env;
    }
    return $throwable->setType($type)->render();
  }

  public function registerException($type, $class) {
    $this->exceptionMap[$type] = $class;
  }
}