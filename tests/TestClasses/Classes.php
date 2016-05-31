<?php

class ChildClass extends ParentClass
{
    /**
     * @var IStart
     */
    protected $start;

    /**
     * @second
     * @var IStart
     */
    public $starter;

    /**
     * @star
     * @param IStart $start
     * @return $this
     */
    public function hello(IStart $start)
    {
        $this->start = $start;
        return $this;
    }

    public function getNext()
    {
        return $this->next;
    }

    public function getStart()
    {
        return $this->start;
    }

    protected function testProtected()
    {

    }
}

class ParentClass
{
    /**
     * @var INext
     */
    protected $next;

    /**
     * @var string
     */
    protected $c;

    public function __construct(INext $next, $c)
    {
        $this->next = $next;
        $this->c = $c;
    }

    /**
     * @second 
     * @param IStart $start
     * @return IStart
     */
    public function getStarter(IStart $start)
    {
        return $start;
    }
}

class Next implements INext
{
    /**
     * @var IStart
     * @second
     */
    public $start;

    /**
     * @var Start
     */
    protected $protectedVar;

    public function tryMe()
    {
        return $this->start;
    }
}

interface INext
{
    function tryMe();
}

class Start implements IStart
{

}

class Starter implements IStart
{

}

interface IStart
{

}

class SimpleParameter
{
    public $d;

    public function __construct($c, \INext $d)
    {
        $this->d = $d;
    }
}

interface SingleInterface
{
    public function getId();
    public function setId($id);
}

class Single implements SingleInterface
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }
}
class SecondSingle implements SingleInterface
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }
}

class TestForSingle
{
    /**
     * @var Single
     */
    protected $single;
    protected $second;

    /**
     * TestForSingle constructor.
     * @param SingleInterface $secondSingle
     * @param SingleInterface $single
     *
     * @singles $single
     * @someTest $secondSingle
     */
    public function __construct(SingleInterface $secondSingle, SingleInterface $single)
    {
        $this->second = $secondSingle;
        $this->single = $single;
    }

    public function setSecondId($id)
    {
        $this->second->setId($id);
    }

    public function getSingle()
    {
        return $this->single;
    }

    public function getSecond()
    {
        return $this->second;
    }
}

interface DecoratorInterface
{
    public function helloWorld();
}

class TestDecorator implements DecoratorInterface
{
    protected $decorator;

    /**
     * TestDecorator constructor.
     * @param DecoratorInterface $decorator
     * @emptyDecoratorPlease
     */
    public function __construct(DecoratorInterface $decorator)
    {
        $this->decorator = $decorator;
    }

    public function helloWorld()
    {
        $this->decorator->helloWorld();
    }
}
