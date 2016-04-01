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
