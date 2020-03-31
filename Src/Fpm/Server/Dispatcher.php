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

namespace W7\Fpm\Server;

use Psr\Http\Message\ServerRequestInterface;
use W7\Core\Dispatcher\RequestDispatcher;
use W7\Core\Exception\RouteNotAllowException;
use W7\Core\Exception\RouteNotFoundException;
use FastRoute\Dispatcher as RouteDispatcher;

class Dispatcher extends RequestDispatcher {
	protected function getBaseUri(ServerRequestInterface $request) {
		$serverParams = $request->getServerParams();
		$filename = basename($serverParams['SCRIPT_FILENAME']);

		if (isset($serverParams['SCRIPT_NAME']) && basename($serverParams['SCRIPT_NAME']) === $filename) {
			$baseUrl = $serverParams['SCRIPT_NAME'];
		} elseif (isset($serverParams['PHP_SELF']) && basename($serverParams['PHP_SELF']) === $filename) {
			$baseUrl = $serverParams['PHP_SELF'];
		} elseif (isset($serverParams['ORIG_SCRIPT_NAME']) && basename($serverParams['ORIG_SCRIPT_NAME']) === $filename) {
			$baseUrl = $serverParams['ORIG_SCRIPT_NAME']; // 1and1 shared hosting compatibility
		} else {
			// Backtrack up the script_filename to find the portion matching
			// php_self
			$path = $serverParams['PHP_SELF'] ?? '';
			$file = $serverParams['SCRIPT_FILENAME'] ?? '';
			$segs = explode('/', trim($file, '/'));
			$segs = array_reverse($segs);
			$index = 0;
			$last = \count($segs);
			$baseUrl = '';
			do {
				$seg = $segs[$index];
				$baseUrl = '/'.$seg.$baseUrl;
				++$index;
			} while ($last > $index && (false !== $pos = strpos($path, $baseUrl)) && 0 != $pos);
		}

		// Does the baseUrl have anything in common with the request_uri?
		$requestUri = $this->getRequestUri($request);
		if ('' !== $requestUri && '/' !== $requestUri[0]) {
			$requestUri = '/'.$requestUri;
		}

		if ($baseUrl && null !== $prefix = $this->getUrlencodedPrefix($requestUri, $baseUrl)) {
			// full $baseUrl matches
			return $prefix;
		}

		if ($baseUrl && null !== $prefix = $this->getUrlencodedPrefix($requestUri, rtrim(\dirname($baseUrl), '/'.\DIRECTORY_SEPARATOR).'/')) {
			// directory portion of $baseUrl matches
			return rtrim($prefix, '/'.\DIRECTORY_SEPARATOR);
		}

		$truncatedRequestUri = $requestUri;
		if (false !== $pos = strpos($requestUri, '?')) {
			$truncatedRequestUri = substr($requestUri, 0, $pos);
		}

		$basename = basename($baseUrl);
		if (empty($basename) || !strpos(rawurldecode($truncatedRequestUri), $basename)) {
			// no match whatsoever; set it blank
			return '';
		}

		// If using mod_rewrite or ISAPI_Rewrite strip the script filename
		// out of baseUrl. $pos !== 0 makes sure it is not matching a value
		// from PATH_INFO or QUERY_STRING
		if (\strlen($requestUri) >= \strlen($baseUrl) && (false !== $pos = strpos($requestUri, $baseUrl)) && 0 !== $pos) {
			$baseUrl = substr($requestUri, 0, $pos + \strlen($baseUrl));
		}

		return rtrim($baseUrl, '/'.\DIRECTORY_SEPARATOR);
	}

	protected function getRequestUri(ServerRequestInterface $request) {
		$requestUri = '';

		$serverParams = $request->getServerParams();
		if (isset($serverParams['IIS_WasUrlRewritten']) && '1' == $serverParams['IIS_WasUrlRewritten'] && '' != $serverParams['UNENCODED_URL']) {
			// IIS7 with URL Rewrite: make sure we get the unencoded URL (double slash problem)
			$requestUri = $serverParams['UNENCODED_URL'];
		} elseif (!empty($serverParams['REQUEST_URI'])) {
			$requestUri = $serverParams['REQUEST_URI'];

			if ('' !== $requestUri && '/' === $requestUri[0]) {
				// To only use path and query remove the fragment.
				if (false !== $pos = strpos($requestUri, '#')) {
					$requestUri = substr($requestUri, 0, $pos);
				}
			} else {
				// HTTP proxy reqs setup request URI with scheme and host [and port] + the URL path,
				// only use URL path.
				$uriComponents = parse_url($requestUri);

				if (isset($uriComponents['path'])) {
					$requestUri = $uriComponents['path'];
				}

				if (isset($uriComponents['query'])) {
					$requestUri .= '?'.$uriComponents['query'];
				}
			}
		} elseif (!empty($serverParams['ORIG_PATH_INFO'])) {
			// IIS 5.0, PHP as CGI
			$requestUri = $serverParams['ORIG_PATH_INFO'];
			if ('' != $serverParams['QUERY_STRING']) {
				$requestUri .= '?'.$serverParams['QUERY_STRING'];
			}
		}

		return $requestUri;
	}

	/**
	 * Returns the prefix as encoded in the string when the string starts with
	 * the given prefix, null otherwise.
	 */
	private function getUrlencodedPrefix(string $string, string $prefix): ?string {
		if (0 !== strpos(rawurldecode($string), $prefix)) {
			return null;
		}

		$len = \strlen($prefix);

		if (preg_match(sprintf('#^(%%[[:xdigit:]]{2}|.){%d}#', $len), $string, $match)) {
			return $match[0];
		}

		return null;
	}

	protected function getPathInfo(ServerRequestInterface $request) {
		if (null === ($requestUri = $this->getRequestUri($request))) {
			return '/';
		}

		// Remove the query string from REQUEST_URI
		if (false !== $pos = strpos($requestUri, '?')) {
			$requestUri = substr($requestUri, 0, $pos);
		}
		if ('' !== $requestUri && '/' !== $requestUri[0]) {
			$requestUri = '/'.$requestUri;
		}

		if (null === ($baseUrl = $this->getBaseUri($request))) {
			return $requestUri;
		}

		$pathInfo = substr($requestUri, \strlen($baseUrl));
		if (false === $pathInfo || '' === $pathInfo) {
			// If substr() returns false then PATH_INFO is set to an empty string
			return '/';
		}

		return (string) $pathInfo;
	}

	protected function getRoute(ServerRequestInterface $request) {
		$httpMethod = $request->getMethod();
		//该方法最后在http-message中做兼容
		$pathInfo = $this->getPathInfo($request);
		if ($pathInfo == '/' && !empty($request->getQueryParams()['r'])) {
			$url = $request->getQueryParams()['r'];
		} else {
			$url = $pathInfo;
		}

		$route = $this->router->dispatch($httpMethod, $url);

		$controller = $method = '';
		switch ($route[0]) {
			case RouteDispatcher::NOT_FOUND:
				throw new RouteNotFoundException('Route not found, ' . $url, 404);
				break;
			case RouteDispatcher::METHOD_NOT_ALLOWED:
				throw new RouteNotAllowException('Route not allowed, ' . $url, 405);
				break;
			case RouteDispatcher::FOUND:
				if ($route[1]['handler'] instanceof \Closure) {
					$controller = $route[1]['handler'];
					$method = '';
				} else {
					list($controller, $method) = $route[1]['handler'];
				}
				break;
		}

		return [
			'name' => $route[1]['name'],
			'module' => $route[1]['module'],
			'method' => $method,
			'controller' => $controller,
			'args' => $route[2],
			'middleware' => $route[1]['middleware']['before'],
		];
	}
}
