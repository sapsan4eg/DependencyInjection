# Sixx DependencyInjection 
 Simple Dependency Injection for you php project
 
## INSTALLATION WITH COMPOSER
 
 `composer require sixx/dependency-injection`
 
## EXAMPLE

### Constructor Injection
    <?php
        require_once __DIR__ . "/vendor/autoload.php";
        
    class FirstClass
    {
        protected $test;

        public function __construct(TestInterface $test)
        {
            $this->test = $test;
        }

        public function getTest()
        {
            return $this->test;
        }
    }

    interface TestInterface
    {
    }

    class Test implements TestInterface
    {
    }

    use \Sixx\DependencyInjection\Inject;
    // For first you must bind your injection
    Inject::bind("TestInterface", "Test");

    $class = Inject::instantiation("FirstClass");
    var_dump($class->getTest() instanceof Test); // must return true
### Initializer methods
    <?php
    require_once __DIR__ . "/vendor/autoload.php";
    
    class FirstClass
    {
        protected $test;
    
        public function test(TestInterface $test)
        {
            $this->test = $test;
            return $this;
        }
    
        public function getTest()
        {
            return $this->test;
        }
    }
    
    interface TestInterface
    {
    }
    
    class Test implements TestInterface
    {
    }
    
    use \Sixx\DependencyInjection\Inject;
    // For first you must bind your injection
    Inject::bindByArray(["TestInterface" => "Test"]);
    
    $class = Inject::method("FirstClass", "test");
    var_dump($class->getTest() instanceof Test); // must return true
### Initializer properties
`Property must be public and must have attribute @var with needed Class or Interface name`
    
    <?php
    require_once __DIR__ . "/vendor/autoload.php";
    
    class FirstClass
    {
        /**
         * @var TestInterface
         */
        public $test;
    
        public function getTest()
        {
            return $this->test;
        }
    }
    
    interface TestInterface
    {
    }
    
    class Test implements TestInterface
    {
    }
    
    use \Sixx\DependencyInjection\Inject;
    // For first you must bind your injection
    Inject::bindByArray(["TestInterface" => "Test"]);
    
    $class = Inject::instantiation("FirstClass");
    var_dump($class->getTest() instanceof Test); // must return true
### Service Locator
    <?php
    require_once __DIR__ . "/vendor/autoload.php";
    
    class FirstClass implements FirstClassInterface
    {
        /**
         * @var TestInterface
         */
        public $test;
    
        public function getTest()
        {
            return $this->test;
        }
    }
    
    interface FirstClassInterface
    {
    
    }
    
    interface TestInterface
    {
    }
    
    class Test implements TestInterface
    {
    }
    
    use \Sixx\DependencyInjection\Inject;
    // For first you must bind your injection
    Inject::bindByArray(["TestInterface" => "Test", "FirstClassInterface" => "FirstClass"]);
    
    $class = Inject::instantiation("FirstClassInterface");
    var_dump($class instanceof FirstClass); // must return true