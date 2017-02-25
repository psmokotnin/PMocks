<?php
/** 
 * Log class allow you to know that method was called, how many times and what args was.
 *
 * \Pmocks\Log::add(debug_backtrace()); - add data to log;
 * ::clear() - clear all data
 * ::callCount($class, $method) - return count of calls
 */
namespace PMocks;

class Log
{
    protected static $data = array();
    
    /**
     * Add a data to log.
     * 
     * @access public
     * @static
     * @param mixed $data Log data
     * @return void
     */
    public static function add($data)
    {
        $data = reset($data);
        $data['time'] = microtime(true);
        self::$data[] = $data;
    }
    
    /**
     * Clear all the loged data.
     * 
     * @access public
     * @static
     * @return void
     */
    public static function clear()
    {
        self::$data = array();
    }
    
    /**
     * Return how many times the given class::method was called.
     * 
     * @access public
     * @static
     * @param string $class
     * @param string $method
     * @return int
     */
    public static function callCount($class, $method)
    {
        return count(self::getCalls($class, $method));
    }
    
    /**
     * Return all calls data of the given class::method.
     * 
     * @access public
     * @static
     * @param string $class
     * @param string $method
     * @return array
     */
    public static function getCalls($class, $method)
    {
        return array_filter(self::$data, function ($row) use ($class, $method) {
            return ($row['class'] == $class AND $row['function'] == $method);
        });
    }
    
    /**
     * Return last call data of the given class::method.
     * 
     * @access public
     * @static
     * @param string $class
     * @param string $method
     * @return array
     */
    public static function getLastCall($class, $method)
    {
        return end(self::getCalls($class, $method));
    }
    
    /**
     * Return last call arguments data of the given class::method.
     * 
     * @access public
     * @static
     * @param string $class
     * @param string $method
     * @return array
     */
    public static function getLastCallArgs($class, $method)
    {
        $lastCall = end(self::getCalls($class, $method));
        return $lastCall['args'];
    }
}