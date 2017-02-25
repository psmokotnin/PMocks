<?php
namespace ExampleApplication;

/**
 * It's an example class, that we'll mock.
 * Find out tests/Test.php for mock examples.
 *
 * Important! If you modify this file, don't forget to set right line number in method checkLine().
 */
class Model
{
    const SOMECONST  = 2;
    const C_PROPERTY = 'Hello';
    protected static $PS_PROPERTY = 'world';
    
    public function __construct()
    {
    }
    
    public function foo()
    {
        for ($i = 0; $i < 20; $i++) {
            $i = $this->somemethod($i);
            if ($i >= 7) {
                break;
            }
        }
        return 'D';
    }
    
    public static function bar()
    {
        return 'S';
    }
    
    public function getFile()
    {
        return __FILE__;
    }
    
    public function getDir()
    {
        return __DIR__;
    }
    
    protected function somemethod($number)
    {
        return $number * 2;
    }
    
    
    /**
     * This method must return true;
     * Mock must not change original line numbers.
     * 
     * @access public
     * @return bool
     */
    public function checkLine()
    {
        return (__LINE__ == 61);
    }
}