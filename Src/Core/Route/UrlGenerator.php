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

namespace W7\Core\Route;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use W7\Contract\Router\UrlGeneratorInterface;

class UrlGenerator implements UrlGeneratorInterface {
	/**
	 * The route collection.
	 *
	 * @var RouteCollector
	 */
	protected $routeCollector;

	/**
	 * @var Closure
	 */
	protected $requestResolver;

	/**
	 * The asset root URL.
	 *
	 * @var string
	 */
	protected $assetRoot;

	/**
	 * The forced URL root.
	 *
	 * @var string
	 */
	protected $forcedRoot;

	/**
	 * The forced scheme for URLs.
	 *
	 * @var string
	 */
	protected $forceScheme;

	/**
	 * A cached copy of the URL root for the current request.
	 *
	 * @var string|null
	 */
	protected $cachedRoot;

	/**
	 * A cached copy of the URL scheme for the current request.
	 *
	 * @var string|null
	 */
	protected $cachedScheme;

	/**
	 * The callback to use to format hosts.
	 *
	 * @var \Closure
	 */
	protected $formatHostUsing;

	/**
	 * The callback to use to format paths.
	 *
	 * @var \Closure
	 */
	protected $formatPathUsing;

	/**
	 * The route URL generator instance.
	 *
	 * @var RouteUrlGenerator|null
	 */
	protected $routeGenerator;

	public function __construct(RouteCollector $routeCollector, Closure $requestResolver, $assetRoot = null) {
		$this->routeCollector = $routeCollector;
		$this->assetRoot = $assetRoot;
		$this->requestResolver = $requestResolver;
	}

	/**
	 * Get the current URL for the request.
	 *
	 * @return string
	 */
	public function current() {
		return $this->to($this->getRequest()->getUri()->getPath());
	}

	/**
	 * Generate an absolute URL to the given path.
	 *
	 * @param  string  $path
	 * @param  mixed  $extra
	 * @param  bool|null  $secure
	 * @return string
	 */
	public function to($path, $extra = [], $secure = null) {
		// First we will check if the URL is already a valid URL. If it is we will not
		// try to generate a new one but will simply return the URL as is, which is
		// convenient since developers do not always have to check if it's valid.
		if ($this->isValidUrl($path)) {
			return $path;
		}

		$tail = implode(
			'/',
			array_map(
				'rawurlencode',
				(array) $this->formatParameters($extra)
			)
		);

		// Once we have the scheme we will compile the "tail" by collapsing the values
		// into a single string delimited by slashes. This just makes it convenient
		// for passing the array of parameters to this URL as a list of segments.
		$root = $this->formatRoot($this->formatScheme($secure));

		[$path, $query] = $this->extractQueryString($path);

		return $this->format(
			$root,
			'/'.trim($path.'/'.$tail, '/')
		).$query;
	}

	/**
	 * Generate a secure, absolute URL to the given path.
	 *
	 * @param  string  $path
	 * @param  array  $parameters
	 * @return string
	 */
	public function secure($path, $parameters = []) {
		return $this->to($path, $parameters, true);
	}

	/**
	 * Generate the URL to an application asset.
	 *
	 * @param  string  $path
	 * @param  bool|null  $secure
	 * @return string
	 */
	public function asset($path, $secure = null) {
		if ($this->isValidUrl($path)) {
			return $path;
		}

		// Once we get the root URL, we will check to see if it contains an index.php
		// file in the paths. If it does, we will remove it since it is not needed
		// for asset paths, but only for routes to endpoints in the application.
		$root = $this->assetRoot ?: $this->formatRoot($this->formatScheme($secure));

		return $this->removeIndex($root).'/'.trim($path, '/');
	}

	/**
	 * Generate the URL to a secure asset.
	 *
	 * @param  string  $path
	 * @return string
	 */
	public function secureAsset($path) {
		return $this->asset($path, true);
	}

	/**
	 * Remove the index.php file from a path.
	 *
	 * @param  string  $root
	 * @return string
	 */
	protected function removeIndex($root) {
		$i = 'index.php';

		return Str::contains($root, $i) ? str_replace('/'.$i, '', $root) : $root;
	}

	/**
	 * Get the default scheme for a raw URL.
	 *
	 * @param  bool|null  $secure
	 * @return string
	 */
	public function formatScheme($secure = null) {
		if (! is_null($secure)) {
			return $secure ? 'https://' : 'http://';
		}

		if (is_null($this->cachedScheme)) {
			$this->cachedScheme = $this->forceScheme ?: $this->getRequest()->getUri()->getScheme().'://';
		}

		return $this->cachedScheme;
	}

	public function route($name, $parameters = [], $absolute = true) {
		$routeData = $this->routeCollector->getRouteByName($name);
		if (!is_null($routeData)) {
			$route = new Route(
				$routeData['name'],
				$routeData['uri'],
				$routeData['module'],
				$routeData['handler'],
				[],
				$routeData['middleware']['before'] ?? [],
				$routeData['defaults'] ?? []
			);
			return $this->toRoute($route, $parameters, $absolute);
		}

		throw new RuntimeException("Route [{$name}] not defined.");
	}

	public function toRoute($route, $parameters, $absolute) {
		return $this->routeUrl()->to(
			$route,
			$this->formatParameters($parameters),
			$absolute
		);
	}

	/**
	 * Format the array of URL parameters.
	 *
	 * @param  mixed|array  $parameters
	 * @return array
	 */
	public function formatParameters($parameters) {
		return Arr::wrap($parameters);
	}

	/**
	 * Extract the query string from the given path.
	 *
	 * @param  string  $path
	 * @return array
	 */
	protected function extractQueryString($path) {
		if (($queryPosition = strpos($path, '?')) !== false) {
			return [
				substr($path, 0, $queryPosition),
				substr($path, $queryPosition),
			];
		}

		return [$path, ''];
	}

	/**
	 * Get the base URL for the request.
	 *
	 * @param  string  $scheme
	 * @param  string|null  $root
	 * @return string
	 */
	public function formatRoot($scheme, $root = null) {
		if (is_null($root)) {
			if (is_null($this->cachedRoot)) {
				$this->cachedRoot = $this->forcedRoot ?:
					($this->getRequest()->getUri()->getScheme() . '://' . $this->getRequest()->getUri()->getHost() . ($this->getRequest()->getServerParams()['REQUEST_BASE_URL'] ?? ''));
			}

			$root = $this->cachedRoot;
		}

		$start = Str::startsWith($root, 'http://') ? 'http://' : 'https://';

		return preg_replace('~'.$start.'~', $scheme, $root, 1);
	}

	/**
	 * Format the given URL segments into a single URL.
	 *
	 * @param  string  $root
	 * @param  string  $path
	 * @param  Route|null  $route
	 * @return string
	 */
	public function format($root, $path, $route = null) {
		$path = '/'.trim($path, '/');

		if ($this->formatHostUsing) {
			$root = call_user_func($this->formatHostUsing, $root, $route);
		}

		if ($this->formatPathUsing) {
			$path = call_user_func($this->formatPathUsing, $path, $route);
		}

		return rtrim($root.$path, '/');
	}

	/**
	 * Determine if the given path is a valid URL.
	 *
	 * @param  string  $path
	 * @return bool
	 */
	public function isValidUrl($path) {
		if (! preg_match('~^(#|//|https?://|(mailto|tel|sms):)~', $path)) {
			return filter_var($path, FILTER_VALIDATE_URL) !== false;
		}

		return true;
	}

	protected function routeUrl() {
		if (! $this->routeGenerator) {
			$this->routeGenerator = new RouteUrlGenerator($this, $this->getRequest());
		}

		return $this->routeGenerator;
	}

	/**
	 * Set the default named parameters used by the URL generator.
	 *
	 * @param  array  $defaults
	 * @return void
	 */
	public function defaults(array $defaults) {
		$this->routeUrl()->defaults($defaults);
	}

	/**
	 * @return ServerRequestInterface
	 */
	public function getRequest() {
		$requestResolver = $this->requestResolver;
		return $requestResolver();
	}

	public function getRouteCollector() {
		return $this->routeCollector;
	}

	public function setRouteCollector(RouteCollector $routeCollector) {
		$this->routeCollector = $routeCollector;
		return $this;
	}
}
