<?php

namespace Sixx\DependencyInjection;

use Sixx\DependencyInjection\Exceptions\InjectException;

class ServiceContainer
{
    protected $services = [];

    /**
     * @param string $serviceName
     * @param string $annotation
     * @return null|string
     */
    public function getServiceName($serviceName, $annotation = "")
    {
        if (isset($this->services[$serviceName])) {
            if (is_string($this->services[$serviceName]))
                return $this->services[$serviceName];
            elseif (is_array($this->services[$serviceName]) && 0 < count($this->services[$serviceName])) {
                reset($this->services[$serviceName]);

                if (false == is_string($annotation) || empty($annotation))
                    return current($this->services[$serviceName]);

                foreach ($this->services[$serviceName] as $name => $service) {
                    if (false !== strpos($annotation, "@" . $name))
                        return $service;

                }

                reset($this->services[$serviceName]);
                return current($this->services[$serviceName]);
            }
        }

        return null;
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
        if (false == (is_string($interface) && ! empty($interface)) || false == (is_string($class) && ! empty($class) || is_array($class)))
            return false;

        if (is_string($class))
            $this->services[$interface] = $class;
        else {
            $classes = [];
            foreach ($class as $name => $value) {
                if (is_string($name) && ! empty($name) && is_string($value) && ! empty($value))
                    $classes[$name] = $value;
            }

            if (0 == count($classes))
                return false;

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
        if (null == $name || null == $this->getServiceName($name))
            return false;

        if (false == $this->isImplement($this->getServiceName($name, $annotation), $name))
            throw new InjectException("Inject error: class " . $this->getServiceName($name, $annotation) . " must implement " . $name);

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
        if (null == $class || $class->isAbstract() || $class->isInterface())
            return false;

        return true;
    }
}
