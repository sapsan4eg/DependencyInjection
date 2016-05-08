<?php

namespace Sixx\DependencyInjection;

use Sixx\DependencyInjection\Exceptions\InjectException;
use Sixx\DependencyInjection\Exceptions\InjectRequiredParameterException;
use Sixx\DependencyInjection\Exceptions\InjectNotInjectedException;

class Inject
{
    protected static $services = [];
    protected static $injectAnnotation = '@var';

    protected function __construct()
    {
    }

    protected function __clone()
    {
    }

    /**
     * @var ServiceContainer
     */
    protected static $serviceContainer;

    /**
     * @param string $className
     * @param array $parameters
     * @throws InjectException
     * @return object
     */
    public static function instantiation($className, array $parameters = null)
    {
        return self::method($className, '__construct', $parameters);
    }

    /**
     * @param \ReflectionClass $class
     * @param $parameters
     * @return object
     */
    protected static function init(\ReflectionClass $class, $parameters)
    {
        if (false == $class->hasMethod("__construct") || false == $class->getMethod('__construct')->isPublic()) {
            $instance = $class->newInstanceWithoutConstructor();
        } else {
            $instance = $class->newInstanceArgs(self::getParameters($class->getMethod('__construct'), $parameters));
        }

        return self::fillProperties($instance, $class->getProperties(\ReflectionProperty::IS_PUBLIC));
    }

    /**
     * @param string $className
     * @param string $methodName
     * @param array|null $parameters
     * @throws InjectException
     * @return mixed
     */
    public static function method($className, $methodName, array $parameters = null)
    {
        if (! class_exists($className)) {
            if (interface_exists($className)) {
                if (self::container()->isInjected($className)) {
                    return self::method(self::container()->getServiceName($className), $methodName, $parameters);
                }
                throw new InjectNotInjectedException("Inject error: interface " . $className . " exist but not injected yet.");
            }
            throw new InjectException("Inject error: class " . $className . " not exist.");
        }

        $classCheck = new \ReflectionClass($className);

        if ('__construct' == $methodName) {
            return self::init($classCheck, $parameters);
        } elseif (false == $classCheck->hasMethod($methodName) || false == $classCheck->getMethod($methodName)->isPublic()) {
            throw new InjectException("Inject error: method " . $methodName . " in " . $className . " not exist or not public.");
        }

        $class = self::init($classCheck, $parameters);
        return $classCheck->getMethod($methodName)->invokeArgs($class, self::getParameters($classCheck->getMethod($methodName), $parameters));
    }

    /**
     * @param \ReflectionMethod $method
     * @param array|null $parameters
     * @throws InjectException
     * @return array
     */
    protected static function getParameters(\ReflectionMethod $method, array $parameters = null)
    {
        $arguments = [];

        try {
            foreach ($method->getParameters() as $parameter) {
                if (self::sameParameter($parameter, $parameters)) {
                    $arguments[$parameter->getName()] = $parameters[$parameter->getName()];
                } elseif (null != $parameter->getClass() && self::container()->isInjected($parameter->getClass()->name, $method->getDocComment())) {
                    $arguments[$parameter->getName()] = self::instantiation(self::container()->getServiceName($parameter->getClass()->name, $method->getDocComment()));
                } elseif (self::container()->isInstantiate($parameter->getClass())) {
                    $arguments[$parameter->getName()] = self::instantiation($parameter->getClass()->name);
                }  elseif (true != $parameter->isOptional()) {
                    throw new InjectRequiredParameterException("Inject error: required parameter [" . $parameter->getName() . "] in " . $method->getDeclaringClass()->name . "::" . $method->getName() . " is not specified.");
                }
            }
        } catch (\ReflectionException $exception) {
            throw new InjectException("Inject error: " . $exception->getMessage());
        }

        return $arguments;
    }

    /**
     * @param \ReflectionParameter $parameter
     * @param null|array $parameters
     * @return bool
     */
    protected static function sameParameter(\ReflectionParameter $parameter, $parameters = null)
    {
        if (! isset($parameters[$parameter->getName()])) {
            return false;
        }

        if (null == $parameter->getClass() && is_object($parameters[$parameter->getName()])) {
            return false;
        }

        if (null != $parameter->getClass() && ! is_object($parameters[$parameter->getName()])) {
            return false;
        }

        if (null != $parameter->getClass() && ! $parameter->getClass()->isInstance($parameters[$parameter->getName()])) {
            return false;
        }

        return true;
    }

    /**
     * @param object $class
     * @param array|null $properties
     * @return object
     */
    protected static function fillProperties($class, array $properties = null)
    {
        foreach ($properties as $property) {
            /**
             * @var \ReflectionProperty $property
             */
            $name = $property->getName();
            $className = self::getVariableTypeName($property->getDocComment(), self::$injectAnnotation);

            if (class_exists($className) || interface_exists($className)) {
                if (self::container()->isInjected($className, $property->getDocComment())) {
                    $class->$name = self::instantiation(self::container()->getServiceName($className, $property->getDocComment()));
                } elseif (self::container()->isInstantiate(new \ReflectionClass($className))) {
                    $class->$name = self::instantiation($className);
                }
            }
        }

        return $class;
    }

    /**
     * @param string $string
     * @param string $injectAnnotation
     * @return bool|string
     */
    protected static function getVariableTypeName($string, $injectAnnotation)
    {
        if (strpos($string, $injectAnnotation) === false) {
            return false;
        }

        $name = substr($string, strpos($string, self::$injectAnnotation) + strlen($injectAnnotation));
        return trim(substr($name, 0, strpos($name, PHP_EOL)));
    }

    /**
     * @return ServiceContainer
     */
    protected static function container()
    {
        if (empty(self::$serviceContainer)) {
            self::$serviceContainer = new ServiceContainer();
        }

        return self::$serviceContainer;
    }

    /**
     * Flush all services from Inject
     */
    public static function flushServices()
    {
        self::container()->flushServices();
    }

    /**
     * @param string $interface
     * @param string|array $class
     * @return bool
     */
    public static function bind($interface, $class)
    {
        return self::container()->bind($interface, $class);
    }

    /**
     * @param array $array
     */
    public static function bindByArray(array $array)
    {
        foreach ($array as $key => $value) {
            if (is_string($key) && (is_string($value) || is_array($value))) {
                self::bind($key, $value);
            }
        }
    }
}
