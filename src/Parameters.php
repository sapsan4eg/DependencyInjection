<?php

namespace Sixx\DependencyInjection;

use Sixx\DependencyInjection\Exceptions\InjectException;
use Sixx\DependencyInjection\Exceptions\InjectRequiredParameterException;

class Parameters
{
    /**
     * @param \ReflectionMethod $method
     * @param array|null $parameters
     * @throws InjectException
     * @return array
     */
    public static function getParameters(\ReflectionMethod $method, array $parameters = null)
    {
        $arguments = [];

        try {
            foreach ($method->getParameters() as $parameter) {
                if (self::canBeInjectedParameter($parameter, $parameters)) {
                    $arguments[$parameter->getName()] = self::instantiation($parameters[$parameter->getName()]);
                } elseif (self::sameParameter($parameter, $parameters)) {
                    $arguments[$parameter->getName()] = $parameters[$parameter->getName()];
                } elseif (null != $parameter->getClass() && self::container()->isInjected($parameter->getClass()->name, $method->getDocComment())) {
                    $arguments[$parameter->getName()] = self::instantiation(self::container()->getServiceName($parameter->getClass()->name, $method->getDocComment(), $parameter->getName()));
                } elseif (self::container()->isInstantiate($parameter->getClass())) {
                    $arguments[$parameter->getName()] = self::instantiation($parameter->getClass()->name);
                } elseif (true != $parameter->isOptional()) {
                    throw new InjectRequiredParameterException("Inject error: required parameter [" . $parameter->getName() . "] in " . $method->getDeclaringClass()->name . "::" . $method->getName() . " is not specified.");
                }
            }
        } catch (\ReflectionException $exception) {
            throw new InjectException("Inject error: " . $exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
        }

        return $arguments;
    }

    /**
     * @param \ReflectionParameter $parameter
     * @param $parameters
     * @return bool
     */
    public static function canBeInjectedParameter(\ReflectionParameter $parameter, $parameters)
    {
        if (null != $parameter->getClass()) {
            return false;
        }

        if (!isset($parameters[$parameter->getName()])) {
            return false;
        }

        if (!is_string($parameters[$parameter->getName()])) {
            return false;
        }

        if (class_exists($parameters[$parameter->getName()]) || interface_exists($parameters[$parameter->getName()])) {
            return true;
        }

        return false;
    }

    /**
     * @param \ReflectionParameter $parameter
     * @param null|array $parameters
     * @return bool
     */
    public static function sameParameter(\ReflectionParameter $parameter, $parameters = null)
    {
        if (!isset($parameters[$parameter->getName()])) {
            return false;
        }

        if (null == $parameter->getClass() && is_object($parameters[$parameter->getName()])) {
            return false;
        }

        if (null != $parameter->getClass() && !is_object($parameters[$parameter->getName()])) {
            return false;
        }

        if (null != $parameter->getClass() && !$parameter->getClass()->isInstance($parameters[$parameter->getName()])) {
            return false;
        }

        return true;
    }
}
