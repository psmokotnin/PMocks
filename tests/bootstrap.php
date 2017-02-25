<?
chdir(dirname(__FILE__) . '/..');

set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__),
    get_include_path(),
)));

require_once 'Zend/Loader.php';
require_once 'Zend/Loader/Autoloader.php';
require_once 'PMocks/Loader.php';

$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->registerNamespace('PMocks');
