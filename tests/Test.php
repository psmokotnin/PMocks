<?php
use PMocks\Log;
use PMocks\Loader;
use PMocks\Rewriter\Rule;

class Test extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->assertLessThan(0, Zend_Version::compareVersion('1.12'), 'Zend version must be at least 1.12');
        Loader::mockMode(true);
    }
    
    public function tearDown()
    {
        Loader::mockMode(false);
    }
    
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_loadClass()
    {
        Loader::loadClass('\ExampleApplication\Model');

        $example = new \ExampleApplication\Model();
        
        //__DIR__ replace
        $this->assertEquals('ExampleApplication', $example->getDir());
        
        //__FILE__ replace
        $this->assertEquals('ExampleApplication/Model.php', $example->getFile());
    }
    
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */    
    public function test_loadWithDirs()
    {
        Loader::loadClass('Foo', 'ExampleApplication/External');
        $this->assertTrue(class_exists('Foo'));
    }
    
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */    
    public function test_loadWithDirsArray()
    {
        Loader::loadClass('Foo', array('ExampleApplication/External'));
        $this->assertTrue(class_exists('Foo'));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testConstant()
    {
        $rule = new Rule\Object\Constant('C_PROPERTY', 'Goodbye');
        Loader::mockClass('\ExampleApplication\Model', array($rule));
        
        $this->assertEquals('Goodbye', \ExampleApplication\Model::C_PROPERTY);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testConstantGlobalNS()
    {
        $rule = new Rule\Object\Constant('C_PROPERTY', 5);
        Loader::mockClass('ExampleApplication_Model_GlobalNS', array($rule));
        
        $this->assertEquals(5, ExampleApplication_Model_GlobalNS::C_PROPERTY);
    }
    
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testMethod()
    {
        $rules = array();
        $rules[] = new Rule\Object\Method('foo', 'return 123;');
        Loader::mockClass('\ExampleApplication\Model', $rules);
        
        $example = new \ExampleApplication\Model();
        
        $this->assertEquals('S', \ExampleApplication\Model::bar());
        $this->assertEquals(123, $example->foo());
    }
    
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testStaticMethod()
    {
        $rules = array();
        $rules[] = new Rule\Object\Method('bar', "return 'qwerty';");
        Loader::mockClass('\ExampleApplication\Model', $rules);
        
        $example = new \ExampleApplication\Model();
        
        $this->assertEquals('qwerty', \ExampleApplication\Model::bar());
        $this->assertEquals('D', $example->foo());
    }
    
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLineNumber()
    {
        $rules = array();
        $code = "for (\$k = 0; \$k < 0; \$k ++) {
            \$k ++;
        }
        return 123;";
        $rule = new Rule\Object\Method('foo', 'return 123;');
        $rule->makeCodeSingleLine();
        Loader::mockClass('\ExampleApplication\Model', $rules);
        
        $example = new \ExampleApplication\Model();
        
        $this->assertTrue($example->checkLine());
    }
    
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLogCalled()
    {
        $rules = array();
        $rules[] = new Rule\Object\Method('somemethod', '', true);
        Loader::mockClass('\ExampleApplication\Model', $rules);
        $example = new \ExampleApplication\Model();
        $example->foo();
        $count = Log::callCount('ExampleApplication\Model', 'somemethod');
        
        $this->assertEquals(4, $count);
    }
    
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLogLastCall()
    {
        $rules = array();
        $rules[] = new Rule\Object\Method('somemethod', '', true);
        Loader::mockClass('\ExampleApplication\Model', $rules);
        $example = new \ExampleApplication\Model();
        $example->foo();
        $last = Log::getLastCall('ExampleApplication\Model', 'somemethod');
        
        $this->assertEquals(array(7), $last['args']);
    }
    
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLogLastCallArgs()
    {
        $rules = array();
        $rules[] = new Rule\Object\Method('somemethod', '', true);
        Loader::mockClass('\ExampleApplication\Model', $rules);
        $example = new \ExampleApplication\Model();
        $example->foo();
        $args = Log::getLastCallArgs('ExampleApplication\Model', 'somemethod');
        
        $this->assertEquals(7, $args[0]);
    }
    
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @expectedException \Pmocks\Loader\Exception
     */
    public function testNotFound()
    {
        Loader::mockClass('\Unexists');
    }
}