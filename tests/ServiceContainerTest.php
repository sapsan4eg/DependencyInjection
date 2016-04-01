<?php

use \Sixx\DependencyInjection\ServiceContainer;

class ServiceContainerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceContainer
     */
    protected $serviceContainer;

    public function setUp()
    {
        require_once __DIR__ . "/TestClasses/Classes.php";
        $this->serviceContainer = new ServiceContainer();
    }

    public function testBind()
    {
        $this->assertTrue($this->serviceContainer->bind("ITest", "Test"));
        $this->assertFalse($this->serviceContainer->bind("ITest", 1));
        $this->assertFalse($this->serviceContainer->bind(1, "Test"));
        $this->assertTrue($this->serviceContainer->bind("ITest", ["hello" => "Test"]));
        $this->assertFalse($this->serviceContainer->bind("", ["hello" => "Test"]));
        $this->assertFalse($this->serviceContainer->bind("ITest", ["" => "Test"]));
        $this->assertFalse($this->serviceContainer->bind("ITest", ["Test" => ""]));
    }

    public function testGetServiceNameAndFlushAll()
    {
        $this->serviceContainer->bind("ITest", "Test");
        $this->assertEquals("Test", $this->serviceContainer->getServiceName("ITest"));
        $this->serviceContainer->flushServices();
        $this->assertNull($this->serviceContainer->getServiceName("ITest"));
        $this->serviceContainer->bind("ITest", ["first" => "Test", "second" => "TestSecond", "third" => "TestThird"]);
        $this->assertEquals("Test", $this->serviceContainer->getServiceName("ITest"));
        $this->assertEquals("TestSecond", $this->serviceContainer->getServiceName("ITest", "/* @second"));
        $this->assertEquals("TestThird", $this->serviceContainer->getServiceName("ITest", "/* @third"));
        $this->serviceContainer->bind("INext", "Next");
        $this->assertTrue($this->serviceContainer->isInjected("INext"));
        $this->assertFalse($this->serviceContainer->isInjected("ITemp"));
    }
}
