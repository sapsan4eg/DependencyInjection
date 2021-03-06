<?php

use \Sixx\DependencyInjection\Inject;
use \Sixx\DependencyInjection\ServiceContainer;

class InjectExceptionTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        require_once __DIR__ . "/TestClasses/Classes.php";
    }

    /**
     * @expectedException \Sixx\DependencyInjection\Exceptions\InjectException
     * @expectedExceptionMessage Inject error: class SomethingElse not exist.
     */
    public function testExceptionNotExistClass()
    {
        Inject::instantiation("SomethingElse");
    }

    /**
     * @expectedException \Sixx\DependencyInjection\Exceptions\InjectException
     * @expectedExceptionMessage Inject error: method SomeMethod in ChildClass not exist or not public.
     */
    public function testExceptionNotExistMethod()
    {
        Inject::method("ChildClass", "SomeMethod");
    }

    /**
     * @expectedException \Sixx\DependencyInjection\Exceptions\InjectException
     * @expectedExceptionMessage Inject error: method testProtected in ChildClass not exist or not public.
     */
    public function testExceptionNotPublicMethod()
    {
        Inject::method("ChildClass", "testProtected");
    }

    /**
     * @expectedException \Sixx\DependencyInjection\Exceptions\InjectMustImplementException
     * @expectedExceptionMessage Inject error: class Start must implement INext
     */
    public function testExceptionClassMustImplement()
    {
        Inject::bind("INext", "Start");
        Inject::method("ChildClass", "hello");
    }

    /**
     * @expectedException \Sixx\DependencyInjection\Exceptions\InjectRequiredParameterException
     * @expectedExceptionMessage Inject error: required parameter [start] in ChildClass::hello is not specified.
     */
    public function testExceptionRequiredParameter()
    {
        Inject::bind("INext", "Next");
        Inject::method("ChildClass", "hello", ["c" => "wer"]);
    }

    /**
     * @expectedException \Sixx\DependencyInjection\Exceptions\InjectMustImplementException
     * @expectedExceptionMessage Inject error: class Start must implement INext
     */
    public function testExceptionClassMustImplements()
    {
        $class = new ServiceContainer();

        $class->bind("INext", "Start");
        $class->isInjected("INext");
    }

    /**
     * @expectedException \Sixx\DependencyInjection\Exceptions\InjectRequiredParameterException
     * @expectedExceptionMessage Inject error: required parameter [c] in SimpleParameter::__construct is not specified.
     */
    public function testExceptionRequiredParameterDifferent()
    {
        Inject::bind("INext", "Next");
        Inject::instantiation("SimpleParameter", ["c" => new Next()]);
    }

    /**
     * @expectedException \Sixx\DependencyInjection\Exceptions\InjectNotInjectedException
     * @expectedExceptionMessage Inject error: interface INext exist but not injected yet.
     */
    public function testExceptionNotInjected()
    {
        Inject::flushServices();
        Inject::instantiation("INext");
    }
}
