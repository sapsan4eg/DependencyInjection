<?php

namespace Sixx\DependencyInjection;

use Sixx\DependencyInjection\Exceptions\InjectMustImplementException;

class ServiceContainer
{
    protected $services = [];
    protected $objects = [];

    /**
     * Get class name from container
     * @param string $serviceName
     * @param string $annotation
     * @return null|string
     */
    public function getServiceName($serviceName, $annotation = "")
    {
        return $this->getName($this->getService($serviceName, $annotation));
    }

    /**
     * Return service
     * @param $serviceName
     * @param string $annotation
     * @return null|string|bool
     */
    public function getService($serviceName, $annotation = "")
    {
        if (isset($this->services[$serviceName])) {
            if (is_string($this->services[$serviceName])) {
                return $this->services[$serviceName];
            } elseif (is_array($this->services[$serviceName]) && 0 < count($this->services[$serviceName])) {
                reset($this->services[$serviceName]);

                if (false == is_string($annotation) || empty($annotation)) {
                    return current($this->services[$serviceName]);
                }

                foreach ($this->services[$serviceName] as $name => $service) {
                    if (false !== strpos($annotation, "@" . $name)) {
                        return $service;
                    }
                }

                reset($this->services[$serviceName]);
                return current($this->services[$serviceName]);
            }
        }

        return null;
    }

    /**
     * Get name from service
     * @param string|array $service
     * @return string
     */
    protected function getName($service)
    {
        if (is_string($service)) {
            return $service;
        }

        return $service['name'];
    }

    public function getSingle($service)
    {
        if (is_string($service) || empty($service['single']) || true != $service['single']) {
            return false;
        }

        return true;
    }

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

        if (is_string($class)) {
            $this->services[$interface] = $class;
        } else {
            $classes = [];
            foreach ($class as $name => $value) {

                if (empty($name) || empty($value) || !is_string($name) || (is_array($value) && empty($value['name']))) {
                    continue;
                }

                $classes[$name] = $value;

                if (!empty($value['single']) && true == $value['single']) {
                    $this->objects[$value['name']] = null;
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
        return (new \ReflectionClass($className))->implementsInterface($interfaceName);
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

    public function getObject($className)
    {
        if (!empty($this->objects[$className])) {
            return $this->objects[$className];
        }

        return null;
    }

    public function setObject($className, $object)
    {
        $this->objects[$className] = $object;
    }
}
