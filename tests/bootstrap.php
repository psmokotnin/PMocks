<?php
error_reporting(E_ALL);
chdir(dirname(__FILE__) . '/..');

set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__),
    get_include_path(),
)));

require_once 'PMocks/Loader.php';

if (!is_dir(\PMocks\Loader::$mockPath))
    mkdir(is_dir(\PMocks\Loader::$mockPath), 0755, true);
    
if (version_compare(PHP_VERSION, '7.0') >= 0) {
    require_once 'tests/TestCase.7.php';
} else {
    require_once 'tests/TestCase.5.php';
}