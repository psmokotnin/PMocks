<?php
/** 
 * Loader class for mocking loading objects.
 *
 * There are two modes: normal and 'mock'.
 * In normal mode, all the classes are loads normally from original place.
 * In 'mock' mode, it'll create a mock class for each loaded class.
 * Original class will be copied to a tempory dir with a temporary name.
 * Mock class will extend original.
 * After you've load class in 'mock' mode you can redefine any 
 * method or property of it.
 */
namespace PMocks;

require_once 'Rewriter.php';
require_once 'Exception.php';

class Loader
{
    const MODE_NORMAL = 0;
    const MODE_MOCK   = 1;
    public static $mockPath = 'tests/include';
    protected static $shutdownRegistered = false;
    
    protected static $mode = self::MODE_NORMAL;
    
    /**
     * Enable or disable mock mode.
     * 
     * @access public
     * @static
     * @return void
     */
    public static function mockMode($enable = false, $autoload = false)
    {
        self::$mode = ($enable ? self::MODE_MOCK : self::MODE_NORMAL);
        
        $includePaths = explode(PATH_SEPARATOR, get_include_path());
        if (self::isMockMode()) {
            if (!is_writable(self::$mockPath))
                throw new Exception('mock path does not exists or not writeable');

            //add mock folder as first in include path
            array_unshift($includePaths, self::$mockPath);
            
            //register script shudown function
            if (!self::$shutdownRegistered) {
                register_shutdown_function(array(__CLASS__, 'shutdown'));
                self::$shutdownRegistered = true;
            }
            
            if ($autoload) {
                //register autoload
                spl_autoload_register(array(__CLASS__, 'autoload'));
            }

        } else {
            // remove mock folder from incude path 
            foreach ($includePaths as $i => $p) {
                if ($p == self::$mockPath)
                    unset($includePaths[$i]);
            }
            self::clearMockFolder();
            
            //unregister autoload
            spl_autoload_unregister(array(__CLASS__, 'autoload'));
        }
        set_include_path(implode(PATH_SEPARATOR, $includePaths));
    }
    
    public static function isMockMode()
    {
        return (self::$mode == self::MODE_MOCK);
    }
    
    /**
     * Alias of mockClass
     * 
     * @access public
     * @static
     * @param string $class
     * @param mixed $dirs (default: null)
     * @param @rules Array of rules to apply.
     * @throws Loader\Exception
     * @return bool
     */
    public static function loadClass($class, $dirs = null, $rules = array())
    {
        return self::mockClass($class, $rules, $dirs);
    }
    
    
    /**
     * Creates file in a folder with m
     * 
     * @access public
     * @static
     * @param string $className
     * @param @rules Array of rules to apply.
     * @return void
     */
    public static function mockClass($className, $rules = array(), $dirs = null)
    {
        if (class_exists($className, false) || interface_exists($className, false)) {
            require_once 'Loader/Exception.php';
            throw new Loader\Exception('Can\'t mock already loaded class');
        }
        
        self::checkName($className);
        $file = self::getFileNameFromClassName($className);

        if (self::isMockMode() AND strpos($className, __NAMESPACE__) !== 0) {
            $targetFile = self::seekFileForInclude($file, $dirs);
            $rewriter = new Rewriter(file_get_contents($targetFile));
            $rewriter->addRule(new Rewriter\Rule\Replace(T_DIR, "'" . dirname($file) . "'"));
            $rewriter->addRule(new Rewriter\Rule\Replace(T_FILE, "'" . $file . "'"));
            foreach ($rules as $rule) {
                if (!$rule->getClass()) {
                    $rule->setClass($className);
                }
                $rewriter->addRule($rule);
            }
            
            $filePath = self::$mockPath . '/' . $file;
            $dir      = dirname($filePath);
            if (!is_dir($dir))
                mkdir($dir, 0755, true);
            file_put_contents($filePath, $rewriter->getCode());
        } else {
            $filePath = $file;
        }
        
        include_once ($filePath);
        if (!class_exists($className, false) AND !interface_exists($className, false)) {
            require_once 'Loader/Exception.php';
            throw new Loader\Exception("createMock \"$className\" failed");
        }
        return true;
    }
    
    
    /**
     * Find a file, is being included.
     * 
     * @access protected
     * @static
     * @param string $file
     * @throws Loader\Exception
     * @return string The file path.
     */
    protected static function seekFileForInclude($file, $dirs = null)
    {
        $includePaths = explode(PATH_SEPARATOR, get_include_path());
        
        if (!empty($dirs)) {
            if (is_string($dirs)) {
                $dirs = explode(PATH_SEPARATOR, $dirs);
            }
            foreach ($dirs as $dir) {
                array_unshift($includePaths, $dir);
            }
        }

        foreach ($includePaths as $path) {
            if ($path == self::$mockPath)
                continue;
            if (is_file($path . '/' . $file)) {
                return $path . '/' . $file;
            }
        }
        
        require_once 'Loader/Exception.php';
        throw new Loader\Exception("File \"$file\" does not exist");
    }
    
    
    /**
     * delete all the files in a temporary include folder
     * 
     * @access public
     * @static
     * @return void
     */
    public static function clearMockFolder()
    {
        $cleaner = function($path) use (&$cleaner) {
            foreach (new \DirectoryIterator($path) as $fileInfo) {
                if ($fileInfo->isDot())
                    continue;
                elseif ($fileInfo->isDir()) {
                    $cleaner($fileInfo->getPathName());
                    rmdir($fileInfo->getPathName());
                }
                elseif ($fileInfo->isFile())
                    unlink($fileInfo->getPathName());
            }
        };
        if (is_writable(self::$mockPath))
            $cleaner(self::$mockPath);
    }
    
    /**
     * Convert class name to file name.
     * 
     * @access protected
     * @static
     * @param string $className
     * @return string
     */
    protected static function getFileNameFromClassName($className)
    {
        $className = ltrim($className, '\\');
        return str_replace(array('\\', '_'), '/', $className) . '.php';
    }
    
    /**
     * Check class name for PHP naming rules.
     * 
     * @access protected
     * @static
     * @return void
     */
    protected static function checkName($className)
    {
        if (!preg_match('/^([a-zA-Z_\\\x7f-\xff]+)([a-zA-Z0-9_\x7f-\xff]*)$/', $className)) {
            throw new Exception('Class name contain wrong characters');
        }
        return true;
    }
    
    
    /**
     * Script shutdown function. Here we'll delete temps files.
     * 
     * @access public
     * @static
     * @return void
     */
    public static function shutdown()
    {
        self::clearMockFolder();
    }
    
    
    /**
     * SPL autoload function.
     * 
     * @access public
     * @static
     * @param mixed $className
     * @return void
     */
    public static function autoload($className)
    {
        try {
            return self::loadClass($className);
        } catch (Exception $e) {}
        
        return false;
    }
}