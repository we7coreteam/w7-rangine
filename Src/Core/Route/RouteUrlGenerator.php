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

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Psr\Http\Message\RequestInterface;
use W7\Core\Route\Exception\UrlGenerationException;

class RouteUrlGenerator {
	/**
	 * The URL generator instance.
	 */
	protected UrlGenerator $url;

	/**
	 * The request instance.
	 */
	protected RequestInterface $request;

	/**
	 * The named parameter defaults.
	 */
	public array $defaultParameters = [];

	/**
	 * Characters that should not be URL encoded.
	 */
	public array $dontEncode = [
		'%2F' => '/',
		'%40' => '@',
		'%3A' => ':',
		'%3B' => ';',
		'%2C' => ',',
		'%3D' => '=',
		'%2B' => '+',
		'%21' => '!',
		'%2A' => '*',
		'%7C' => '|',
		'%3F' => '?',
		'%26' => '&',
		'%23' => '#',
		'%25' => '%',
	];

	/**
	 * Create a new Route URL generator.
	 *
	 * @param UrlGenerator $url
	 * @param RequestInterface $request
	 * @return void
	 */
	public function __construct(UrlGenerator $url, RequestInterface $request) {
		$this->url = $url;
		$this->request = $request;
	}

	/**
	 * @param Route $route
	 * @param array $parameters
	 * @param false $absolute
	 * @return string
	 * @throws UrlGenerationException
	 */
	public function to(Route $route, array $parameters = [], bool $absolute = false): string {
		$domain = $this->getRouteDomain($route, $parameters);

		// First we will construct the entire URI including the root and query string. Once it
		// has been constructed, we'll make sure we don't have any missing parameters or we
		// will need to throw the exception to let the developers know one was not given.
		$uri = $this->addQueryString($this->url->format(
			$root = $this->replaceRootParameters($route, $domain, $parameters),
			$this->replaceRouteParameters($route->getUri(), $parameters),
			$route
		), $parameters);

		if (preg_match_all('/{(.*?)}/', $uri, $matchedMissingParameters)) {
			throw UrlGenerationException::forMissingParameters($route, $matchedMissingParameters[1]);
		}

		// Once we have ensured that there are no missing parameters in the URI we will encode
		// the URI and prepare it for returning to the developer. If the URI is supposed to
		// be absolute, we will return it as-is. Otherwise we will remove the URL's root.
		$uri = strtr(rawurlencode($uri), $this->dontEncode);

		if (! $absolute) {
			$uri = preg_replace('#^(//|[^/?])+#', '', $uri);

			if ($base = $this->request->getServerParams()['REQUEST_BASE_URL'] ?? '') {
				$uri = preg_replace('#^'.$base.'#i', '', $uri);
			}

			return '/'.ltrim($uri, '/');
		}

		return $uri;
	}

	/**
	 * Get the formatted domain for a given route.
	 *
	 * @param Route $route
	 * @param array $parameters
	 * @return string
	 */
	protected function getRouteDomain(Route $route, array &$parameters): string {
		return $this->formatDomain($route, $parameters);
	}

	/**
	 * Format the domain and port for the route and request.
	 *
	 * @param Route $route
	 * @param array $parameters
	 * @return string
	 */
	protected function formatDomain(Route $route, array &$parameters): string {
		return $this->addPortToDomain(
			$this->getRouteRoot($route, $this->getRouteScheme($route))
		);
	}

	/**
	 * Get the scheme for the given route.
	 *
	 * @param Route $route
	 * @return string
	 */
	protected function getRouteRoot(Route $route, $scheme): string {
		return $this->url->formatRoot($scheme);
	}

	/**
	 * Get the scheme for the given route.
	 *
	 * @param Route $route
	 * @return string
	 */
	protected function getRouteScheme(Route $route): string {
		return $this->url->formatScheme();
	}

	/**
	 * Add the port to the domain if necessary.
	 *
	 * @param string $domain
	 * @return string
	 */
	protected function addPortToDomain(string $domain): string {
		$port = (int) $this->request->getUri()->getPort();

		return ($port === 443) || ($port === 80)
					? $domain : $domain.':'.$port;
	}

	/**
	 * Replace the parameters on the root path.
	 *
	 * @param Route $route
	 * @param string $domain
	 * @param array $parameters
	 * @return string
	 */
	protected function replaceRootParameters(Route $route, string $domain, array &$parameters): string {
		$scheme = $this->getRouteScheme($route);

		return $this->replaceRouteParameters(
			$this->url->formatRoot($scheme, $domain),
			$parameters
		);
	}

	/**
	 * Replace all of the wildcard parameters for a route path.
	 *
	 * @param string $path
	 * @param  array  $parameters
	 * @return string
	 */
	protected function replaceRouteParameters(string $path, array &$parameters): string {
		$path = $this->replaceNamedParameters($path, $parameters);

		$path = preg_replace_callback('/\{.*?\}/', function ($match) use (&$parameters) {
			// Reset only the numeric keys...
			$parameters = array_merge($parameters);

			return (! isset($parameters[0]) && ! Str::endsWith($match[0], '?}'))
						? $match[0]
						: Arr::pull($parameters, 0);
		}, $path);

		return trim(preg_replace('/\{.*?\?\}/', '', $path), '/');
	}

	/**
	 * Replace all of the named parameters in the path.
	 *
	 * @param string $path
	 * @param array $parameters
	 * @return string
	 */
	protected function replaceNamedParameters(string $path, array &$parameters): string {
		$path = preg_replace_callback('/\[\/\{(.*?)(:.*)?\}\]/', function ($m) use (&$parameters) {
			if (isset($parameters[$m[1]]) && $parameters[$m[1]] !== '') {
				return '/' . Arr::pull($parameters, $m[1]);
			}

			return '';
		}, $path);

		$path = preg_replace_callback('/\{(.*?)(\?)?\}/', function ($m) use (&$parameters) {
			if (isset($parameters[$m[1]]) && $parameters[$m[1]] !== '') {
				return Arr::pull($parameters, $m[1]);
			}

			if (isset($this->defaultParameters[$m[1]])) {
				return $this->defaultParameters[$m[1]];
			}

			if (isset($parameters[$m[1]])) {
				Arr::pull($parameters, $m[1]);
			}

			return $m[0];
		}, $path);

		return $path;
	}

	/**
	 * Add a query string to the URI.
	 *
	 * @param string $uri
	 * @param array $parameters
	 * @return string
	 */
	protected function addQueryString(string $uri, array $parameters): string {
		// If the URI has a fragment we will move it to the end of this URI since it will
		// need to come after any query string that may be added to the URL else it is
		// not going to be available. We will remove it then append it back on here.
		if (! is_null($fragment = parse_url($uri, PHP_URL_FRAGMENT))) {
			$uri = preg_replace('/#.*/', '', $uri);
		}

		$uri .= $this->getRouteQueryString($parameters);

		return (is_null($fragment) || $fragment === false) ? $uri : $uri."#{$fragment}";
	}

	/**
	 * Get the query string for a given route.
	 *
	 * @param  array  $parameters
	 * @return string
	 */
	protected function getRouteQueryString(array $parameters): string {
		// First we will get all of the string parameters that are remaining after we
		// have replaced the route wildcards. We'll then build a query string from
		// these string parameters then use it as a starting point for the rest.
		if (count($parameters) === 0) {
			return '';
		}

		$query = Arr::query(
			$keyed = $this->getStringParameters($parameters)
		);

		// Lastly, if there are still parameters remaining, we will fetch the numeric
		// parameters that are in the array and add them to the query string or we
		// will make the initial query string if it wasn't started with strings.
		if (count($keyed) < count($parameters)) {
			$query .= '&'.implode(
				'&',
				$this->getNumericParameters($parameters)
			);
		}

		$query = trim($query, '&');

		return $query === '' ? '' : "?{$query}";
	}

	/**
	 * Get the string parameters from a given list.
	 *
	 * @param  array  $parameters
	 * @return array
	 */
	protected function getStringParameters(array $parameters): array {
		return array_filter($parameters, 'is_string', ARRAY_FILTER_USE_KEY);
	}

	/**
	 * Get the numeric parameters from a given list.
	 *
	 * @param  array  $parameters
	 * @return array
	 */
	protected function getNumericParameters(array $parameters): array {
		return array_filter($parameters, 'is_numeric', ARRAY_FILTER_USE_KEY);
	}

	/**
	 * Set the default named parameters used by the URL generator.
	 *
	 * @param  array  $defaults
	 * @return void
	 */
	public function defaults(array $defaults): void {
		$this->defaultParameters = array_merge(
			$this->defaultParameters,
			$defaults
		);
	}
}
