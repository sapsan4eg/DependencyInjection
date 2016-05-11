<?php

use \Sixx\DependencyInjection\Inject;

class InjectTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        require_once __DIR__ . "/TestClasses/Classes.php";
        Inject::bindByArray([
            "IStart" => ["star" => "Start", "second" => ["name" => "Starter"]],
            "INext" => "Next",
            "SingleInterface" => ["singles" => ["name" => "Single", "single" => true]],
        ]);
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

    public function testCheckInjectParameter()
    {
        $this->assertInstanceOf("SimpleParameter", Inject::instantiation("SimpleParameter", ["c" => 1]));
        $this->assertInstanceOf("SimpleParameter", Inject::instantiation("SimpleParameter", ["c" => 1, "d" => new Next()]));
        $this->assertInstanceOf("SimpleParameter", Inject::instantiation("SimpleParameter", ["c" => 1, "d" => 2]));
    }

    public function testCheckSingle()
    {
        $class = Inject::instantiation("SingleInterface", ["id" => 100]);
        $this->assertEquals(100, $class->getId());
        $class = Inject::instantiation("SingleInterface", ["id" => 200]);
        $this->assertEquals(100, $class->getId());
    }
}
