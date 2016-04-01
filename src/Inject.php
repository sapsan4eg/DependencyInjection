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

            if (interface_exists($className)) {
                if (self::injectedParameter(new \ReflectionClass($className)))
                    return self::instantiation(self::getServiceName($className), $parameters);

                throw new InjectException("Inject error: interface " . $className . " exist but not injected yet.");
            }

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

            if (interface_exists($className)) {
                if (self::injectedParameter(new \ReflectionClass($className)))
                    return self::method(self::getServiceName($className), $methodName, $parameters);

                throw new InjectException("Inject error: interface " . $className . " exist but not injected yet.");
            }

            throw new InjectException("Inject error: class " . $className . " not exist.");
        }

        $classCheck = new \ReflectionClass($className);

        if (false == $classCheck->hasMethod($methodName) || false == $classCheck->getMethod($methodName)->isPublic())
            throw new InjectException("Inject error: method " . $methodName . " in " . $className . " not exist or not public.");

        $class = self::instantiation($className, $parameters);
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
                    $arguments[$parameter->getName()] = self::instantiation(self::getServiceName($parameter->getClass()->name));
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
        if (null == $class || null == self::getServiceName($class->name))
            return false;

        if (false == (new \ReflectionClass(self::getServiceName($class->name)))->implementsInterface($class->name))
            throw new InjectException("Inject error: class " . self::getServiceName($class->name) . " must implement " . $class->name);

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
                $propertyClass = new \ReflectionClass($className);
                if (self::injectedParameter($propertyClass))
                    $class->$name = self::instantiation(self::getServiceName($className));
                elseif (self::instantiatedParameter($propertyClass))
                    $class->$name = self::instantiation($className);
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
        if (strpos($string, $injectAnnotation) === false)
            return false;

        $name = substr($string, strpos($string, self::$injectAnnotation) + strlen($injectAnnotation));
        return trim(substr($name, 0, strpos($name, PHP_EOL)));
    }

    /**
     * @param string $serviceName
     * @param string $annotation
     * @return null|string
     */
    protected static function getServiceName($serviceName, $annotation = "")
    {
        if (isset(self::$services[$serviceName])) {
            if (is_string(self::$services[$serviceName]))
                return self::$services[$serviceName];
            elseif (is_array(self::$services[$serviceName])) {
                foreach (self::$services[$serviceName] as $name => $service) {
                    if (false !== strpos($annotation, "@" . $name))
                        return $service;
                }
            }
        }

        return null;
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
     * @param string|array $class
     * @return bool
     */
    public static function bind($interface, $class)
    {
        if (false == is_string($interface) || false == (is_string($class) || is_array($class)))
            return false;

        if (is_string($class))
            self::$services[$interface] = $class;
        else {
            $classes = [];
            foreach ($class as $name => $value) {
                if (is_string($name) && is_string($value))
                    $classes[$name] = $value;
            }

            if (0 == count($classes))
                return false;

            self::$services[$interface] = $classes;
        }

        return true;
    }

    /**
     * @param array $array
     */
    public static function bindByArray(array $array)
    {
        foreach ($array as $key => $value) {
            if (is_string($key) && (is_string($value) || is_array($value)))
                self::bind($key, $value);
        }
    }
}
