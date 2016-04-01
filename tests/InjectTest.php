<?php

use \Sixx\DependencyInjection\Inject;

class InjectTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        require_once __DIR__ . "/TestClasses/Classes.php";
        Inject::bindByArray(["IStart" => ["star" => "Start", "second" => "Starter"], "INext" => "Next"]);
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf("Next", Inject::instantiation("INext"));
    }

    public function testMethod()
    {
        $this->assertInstanceOf("Starter", Inject::method("INext", "tryMe"));
    }

    public function testCheckAllInInjected()
    {
        $class = Inject::method("ChildClass", "hello", ["c" => "fff"]);
        $this->assertInstanceOf("Next", $class->getNext());
        $this->assertInstanceOf("Start", $class->getStart());
        $this->assertInstanceOf("Starter", $class->getNext()->tryMe());
        $this->assertInstanceOf("Starter", Inject::method("ChildClass", "getStarter", ["c" => "fff"]));
        $this->assertInstanceOf("Starter", $class->starter);
    }
}
