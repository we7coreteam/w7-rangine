<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 18-7-23
 * Time: 下午2:54
 */

namespace W7\Http\Handler;


use http\Exception\InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use W7\Core\Helper\PhpHelper;
use w7\Http\Message\Base\Response;
use w7\Http\Message\Server\Request;

class AdapterHandler
{

    /**
     * execute handler with controller and action
     * @param \w7\Http\Message\Server\Request $request request object
     * @param array $routeInfo handler info
     * @return Response
     * @throws \ReflectionException
     */
    public function doHandler(\w7\Http\Message\Server\Request $request, string $routeInfo, array $matches)
    {

        $handler = explode("-", $routeInfo);

        // execute handler
        $params   = $this->bindParams($request, $handler, $matches);
        $response = PhpHelper::call($handler, $params);


        return $response;
    }

    /**
     * binding params of action method
     *
     * @param \w7\Http\Message\Server\Request $request request object
     * @param mixed $handler handler
     * @param array $matches route params info
     *
     * @return array
     * @throws \ReflectionException
     */
    public function bindParams(\w7\Http\Message\Server\Request $request, $handler, array $matches): array
    {
        if (\is_array($handler)) {
            list($controller, $method) = $handler;
            $reflectMethod = new \ReflectionMethod($controller, $method);
            $reflectParams = $reflectMethod->getParameters();
        } else {
            $reflectMethod = new \ReflectionFunction($handler);
            $reflectParams = $reflectMethod->getParameters();
        }

        $bindParams = [];
        // $matches    = $info['matches'] ?? [];

        // binding params
        foreach ($reflectParams as $key => $reflectParam) {
            $reflectType = $reflectParam->getType();
            $name        = $reflectParam->getName();

            // undefined type of the param
            if ($reflectType === null) {
                if (isset($matches[$name])) {
                    $bindParams[$key] = $matches[$name];
                } else {
                    $bindParams[$key] = null;
                }
                continue;
            }

            /**
             * defined type of the param
             * @notice \ReflectType::getName() is not supported in PHP 7.0, that is why use __toString()
             */
            $type = $reflectType->__toString();
            if ($type === Request::class) {
                $bindParams[$key] = $request;
            }  elseif (isset($matches[$name])) {
                $bindParams[$key] = $this->parserParamType($type, $matches[$name]);
            } else {
                $bindParams[$key] = $this->getDefaultValue($type);
            }
        }

        return $bindParams;
    }

    /**
     * parser the type of binding param
     *
     * @param string $type  the type of param
     * @param mixed  $value the value of param
     *
     * @return bool|float|int|string
     */
    private function parserParamType(string $type, $value)
    {
        switch ($type) {
            case 'int':
                $value = (int)$value;
                break;
            case 'string':
                $value = (string)$value;
                break;
            case 'bool':
                $value = (bool)$value;
                break;
            case 'float':
                $value = (float)$value;
                break;
            case 'double':
                $value = (double)$value;
                break;
        }

        return $value;
    }

    /**
     * the default value of param
     *
     * @param string $type the type of param
     *
     * @return bool|float|int|string
     */
    private function getDefaultValue(string $type)
    {
        $value = null;
        switch ($type) {
            case 'int':
                $value = 0;
                break;
            case 'string':
                $value = '';
                break;
            case 'bool':
                $value = false;
                break;
            case 'float':
                $value = 0;
                break;
            case 'double':
                $value = 0;
                break;
        }

        return $value;
    }
}