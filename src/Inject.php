<?php

namespace Sixx\DependencyInjection;

use Sixx\DependencyInjection\Exceptions\InjectException;

class Inject
{
    protected static $services = [];
    protected static $injectAnnotation = '@var';

    /**
     * @param string $className
     * @param array $parameters
     * @throws InjectException
     * @return object
     */
    public static function instantiation($className, array $parameters = null)
    {
        if (! class_exists($className)) {
            if (interface_exists($className) && self::injectedParameter(new \ReflectionClass($className)))
                return self::instantiation(self::getService($className), $parameters);

            throw new InjectException("Inject error: class " . $className . " not exist.");
        }

        $class = new \ReflectionClass($className);

        if (false == $class->hasMethod("__construct") || false == (new \ReflectionMethod($className, "__construct"))->isPublic())
            $instance = $class->newInstanceWithoutConstructor();
        else
            $instance = $class->newInstanceArgs(self::getParameters(new \ReflectionMethod($className, "__construct"), $parameters));

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
        if ('__construct' == $methodName)
            return self::instantiation($className, $parameters);

        if (! class_exists($className)) {
            if (interface_exists($className) && self::injectedParameter(new \ReflectionClass($className)))
                return self::method(self::getService($className), $methodName, $parameters);

            throw new InjectException("Inject error: class " . $className . " not exist.");
        }

        $classCheck = new \ReflectionClass($className);

        if (false == $classCheck->hasMethod($methodName) || false == $classCheck->getMethod($methodName)->isPublic())
            throw new InjectException("Inject error: method " . $methodName . " in " . $className . " not exist or not public.");

        $class = self::instantiation($className);
        return $classCheck->getMethod($methodName)->invokeArgs($class, self::getParameters(new \ReflectionMethod($className, $methodName), $parameters));
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
                if (isset($parameters[$parameter->getName()]))
                    $arguments[$parameter->getName()] = $parameters[$parameter->getName()];
                elseif (self::injectedParameter($parameter->getClass()))
                    $arguments[$parameter->getName()] = self::instantiation(self::getService($parameter->getClass()->name));
                elseif (self::instantiatedParameter($parameter->getClass()))
                    $arguments[$parameter->getName()] = self::instantiation($parameter->getClass()->name);
                elseif (true != $parameter->isOptional())
                    throw new InjectException("Required parameter [" . $parameter->getName() . "] in " . $method->getDeclaringClass()->name  . "::" . $method->getName() . " is not specified.");
            }
        } catch (\ReflectionException $exception) {
            throw new InjectException("Inject error: " . $exception->getMessage());
        }

        return $arguments;
    }

    /**
     * @param \ReflectionClass|null $class
     * @return bool
     */
    protected static function injectedParameter(\ReflectionClass $class = null)
    {
        if (null == $class || null == self::getService($class->name))
            return false;

        if (false == (new \ReflectionClass(self::getService($class->name)))->implementsInterface($class->name))
            throw new InjectException("Inject error: class " . self::getService($class->name) . " must implement " . $class->name);

        return true;
    }

    /**
     * @param \ReflectionClass|null $class
     * @return bool
     */
    protected static function instantiatedParameter(\ReflectionClass $class = null)
    {
        if (null == $class || $class->isAbstract() || $class->isInterface())
            return false;

        return true;
    }

    /**
     * Flush all services from Inject
     */
    public static function flushServices()
    {
        self::$services = [];
    }

    /**
     * @param string $interface
     * @param string $class
     */
    public static function bind($interface, $class)
    {
        self::$services[$interface] = $class;
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
            $className = self::getVariableTypeName($property->getDocComment());

            if (class_exists($className) || interface_exists($className)) {
                $propertyClass = new \ReflectionClass($className);
                if (self::injectedParameter($propertyClass))
                    $class->$name = self::instantiation(self::getService($className));
                 elseif (self::instantiatedParameter($propertyClass))
                    $class->$name = self::instantiation($className);
            }
        }

        return $class;
    }

    /**
     * @param $string
     * @return bool|string
     */
    protected static function getVariableTypeName($string)
    {
        if (strpos($string, self::$injectAnnotation) === false)
            return false;

        $name = substr($string, strpos($string, self::$injectAnnotation) + strlen(self::$injectAnnotation));
        return trim(substr($name, 0, strpos($name, "\r\n")));
    }

    /**
     * @param string $service
     * @return string|null
     */
    protected static function getService($service)
    {
        if (isset(self::$services[$service]))
            return self::$services[$service];

        return null;
    }

    /**
     * @param array $array
     */
    public static function bindByArray(array $array)
    {
        foreach ($array as $key => $value) {
            if (is_string($key) && is_string($value))
                self::bind($key, $value);
        }
    }
}
