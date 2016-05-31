<?php

namespace Sixx\DependencyInjection;

use Sixx\DependencyInjection\Exceptions\InjectMustImplementException;

class ServiceContainer
{
    protected $services = [];
    protected $objects = [];
    protected $parameters = [];

    /**
     * Get class name from container
     * @param string $serviceName
     * @param string|null $annotation
     * @param string|null $parameterName
     * @return null|string
     */
    public function getServiceName($serviceName, $annotation = null, $parameterName = null)
    {
        $serviceName = $this->removeFirstSlash($serviceName);

        if (isset($this->services[$serviceName])) {

            if (is_string($this->services[$serviceName])) {
                return $this->services[$serviceName];
            } elseif (is_array($this->services[$serviceName]) && 0 < count($this->services[$serviceName])) {
                reset($this->services[$serviceName]);

                if (false == is_string($annotation) || empty($annotation)) {
                    return current($this->services[$serviceName]);
                }

                $possible = [];

                foreach ($this->services[$serviceName] as $name => $service) {
                    if (false !== strpos($annotation, "@" . $name)) {
                        if (null == $parameterName) {
                            return $service;
                        }

                        $possible[$name] = $service;
                    }
                }

                if (0 < count($possible)) {
                    foreach ($possible as $name => $service) {
                        $temp = substr($annotation, strpos($annotation, "@" . $name) + strlen("@" . $name));
                        $temp = trim(substr($temp, 0, strpos($temp, PHP_EOL)));

                        if (!empty($temp) && false !== strpos($temp, $parameterName)) {
                            return $service;
                        }
                    }
                    reset($possible);
                    return current($possible);
                }

                reset($this->services[$serviceName]);
                return current($this->services[$serviceName]);
            }
        }

        return null;
    }

    /**
     * Check this class must be is single in project
     * @param $serviceName
     * @return bool
     */
    public function isSingle($serviceName)
    {
        return isset($this->objects[$serviceName]) ? true : false;
    }

    /**
     * Flush all services from ServiceContainer
     */
    public function flushServices()
    {
        $this->services = [];
    }

    /**
     * @param string $interface
     * @param string|array $class
     * @return bool
     */
    public function bind($interface, $class)
    {
        if (false == (is_string($interface) && !empty($interface)) || false == (is_string($class) && !empty($class) || is_array($class))) {
            return false;
        }

        $interface = $this->removeFirstSlash($interface);

        if (is_string($class)) {
            $this->services[$interface] = $class;
        } else {
            $classes = [];
            foreach ($class as $name => $value) {

                if (empty($name) || empty($value) || !is_string($name) || (is_array($value) && empty($value['name']))) {
                    continue;
                }

                $classes[$name] = $this->removeFirstSlash((is_array($value) ? $value['name'] : $value));

                if (!empty($value['single']) && true == $value['single']) {
                    $this->objects[$value['name']] = 0;
                }

                if (!empty($value['parameters']) && is_array($value['parameters'])) {
                    $this->parameters[$value['name']] = $value['parameters'];
                }
            }

            if (0 == count($classes)) {
                return false;
            }

            $this->services[$interface] = $classes;
        }

        return true;
    }

    /**
     * @param null|string $name
     * @param null|string $annotation
     * @return bool
     */
    public function isInjected($name = null, $annotation = null)
    {
        if (null == $name || null == $this->getServiceName($name)) {
            return false;
        }

        if (false == $this->isImplement($this->getServiceName($name, $annotation), $name)) {
            throw new InjectMustImplementException("Inject error: class " . $this->getServiceName($name, $annotation) . " must implement " . $name);
        }

        return true;
    }

    /**
     * @param string $className
     * @param string $interfaceName
     * @return bool
     */
    protected function isImplement($className, $interfaceName)
    {
        if ($interfaceName == $className) {
            return true;
        }

        if (interface_exists($interfaceName)) {
            return (new \ReflectionClass($className))->implementsInterface($interfaceName);
        }

        if (class_exists($interfaceName) && $className) {
            return (new \ReflectionClass($className))->isSubclassOf($interfaceName);
        }

        return false;
    }

    /**
     * @param \ReflectionClass|null $class
     * @return bool
     */
    public function isInstantiate(\ReflectionClass $class = null)
    {
        if (null == $class || $class->isAbstract() || $class->isInterface()) {
            return false;
        }

        return true;
    }

    /**
     * Return instantiated single object
     * @param $className
     * @return object|null
     */
    public function getObject($className)
    {
        if (!empty($this->objects[$className]) && is_object($this->objects[$className])) {
            return $this->objects[$className];
        }

        return null;
    }

    /**
     * Set object to single list
     * @param $className
     * @param $object
     */
    public function setObject($className, $object)
    {
        $this->objects[$className] = $object;
    }

    /**
     * @param $className
     * @return array|null
     */
    public function getParameters($className)
    {
        if (!empty($this->parameters[$className]) && is_array($this->parameters[$className])) {
            return $this->parameters[$className];
        }

        return null;
    }

    /**
     * @param $string
     * @return string
     */
    protected function removeFirstSlash($string)
    {
        if (0 === strpos($string, '\\')) {
            $string = substr($string, 1);
        }
        return $string;
    }
}
