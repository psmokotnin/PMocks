<?php
/** 
 * Loader class extends Zend_Loader for mocking loading objects.
 *
 * There are two modes: normal and 'mock'.
 * In normal mode, all the classes are loads normally through Zend_Loader.
 * In 'mock' mode, it'll create a mock class for each loaded class.
 * Original class will be copied to a tempory dir with a temporary name.
 * Mock class will extend original.
 * After you've load class in 'mock' mode you can redefine any 
 * method or property of it.
 * 
 * @extends Zend_Loader
 * @version 1.12
 */
namespace PMocks;

class Loader extends \Zend_Loader
{
    const MODE_NORMAL = 0;
    const MODE_MOCK   = 1;
    public static $mockPath = 'tests/include';
    
    protected static $mode = self::MODE_NORMAL;
    
    /**
     * Enable or disable mock mode.
     * 
     * @access public
     * @static
     * @return void
     */
    public static function mockMode($enable = false)
    {
        self::$mode = ($enable ? self::MODE_MOCK : self::MODE_NORMAL);
        
        $includePaths = explode(PATH_SEPARATOR, get_include_path());
        if (self::isMockMode()) {
            if (!is_writable(self::$mockPath))
                throw new Exception('mock path does not exists or not writeable');

            array_unshift($includePaths, self::$mockPath);
            register_shutdown_function(function() {
                \PMocks\Loader::clearMockFolder();
            });
        } else {
            // remove mock folder from incude path 
            foreach ($includePaths as $i => $p) {
                if ($p == self::$mockPath)
                    unset($includePaths[$i]);
            }
            self::clearMockFolder();
        }
        set_include_path(implode(PATH_SEPARATOR, $includePaths));
    }
    
    public static function isMockMode()
    {
        return (self::$mode == self::MODE_MOCK);
    }
    
    /**
     * Load class through Zend_Loader,
     * but in 'mock' mode create mock files first
     * 
     * @access public
     * @static
     * @param string $class
     * @param mixed $dirs (default: null)
     * @param @rules Array of rules to apply.
     * @throws Loader\Exception
     * @return void
     */
    public static function loadClass($class, $dirs = null, $rules = array())
    {
        if (self::$mode == self::MODE_MOCK) {
            self::mockClass($class, $rules, $dirs);
        }
        return parent::loadClass($class, $dirs);
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
        
        $file = self::standardiseFile($className);
        self::_securityCheck($file);

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
        
        include($filePath);
        if (!class_exists($className, false)) {
            require_once 'Loader/Exception.php';
            throw new Loader\Exception("createMock \"$className\" failed");
        }

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
}
