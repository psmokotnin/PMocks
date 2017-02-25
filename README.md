# PMocks

## Introduction

PMocks is a light library for mock classes in projects for testing.
Instead of other ways, you do not need to install any extensions. It's only PHP and written in.
The base idea is that we are creating a modified copy of mocked class in a temporary folder for include and insert our code only where it's really needed.
All other code of file is saved with an original formatting, includes comments and line numbers (see method rule).
So, if you have an error or an exception in a mocked class it's easy to find it.
New implementation of a class puts in a temporary folder and includes.

It was written for project based on Zend Framework, but, of course, take it easy to modify this library to your project.

## Limitations
You can't mock already loaded and base PHP classes.

## Depends
- **PHP** 5.3 or greater
- **PHPUnit** 
    

## Run tests
```
$ ./runtest
```

## How it works:

Explore [Example Test](tests/Test.php) to find out how to use the library.

### Important notice

Don't forget to include this docblock before each testMethod that use PMocks. To be sure that your mocks does not affect to other tests.
```
/**
 * @runInSeparateProcess
 * @preserveGlobalState disabled
 */
```

### mockMode

Before mock anything you should enable:
PMocks\Loader::mockMode($enable = false, $autoload = false)

* mockMode(true) - add temp folder into include path as first item and check it, register shutdown function.
* mockMode(true, true) - enable PMocks autoloader too
* mockMode(false) - clean temporary folder, restore include path, unregister shutdown function and autoloader

### Replace rule

Replace any token in your code to an another.
Makes all private methods and properties public:
```
$rule = PMocks\Rewriter\Rule\Replace(T_PRIVATE, 'public');
```

Each mock action adds the Replace rules for T_DIR and T_FILE automatically.

### Constant

Constant rule let you change any constants in class
```
$rule = \PMocks\Rewriter\Rule\Object\Constant('API_BASE_URI', 'http://example.com/api/', 'Zend_Service_Twitter');
```

### Method

Changes the behavior of the method.
```
new Rule\Object\Method('foo', 'return 123;');
$this->assertEquals(123, $example->foo());
```

Method rule allow to log each call.
```
$rules[] = new Rule\Object\Method('foo', '', true);
Loader::mockClass('Bar', $rules);

$example = new Bar();
$example->foo();

$this->assertEquals(1, \Pmocks\Log::callCount('Bar', 'foo'));
```

For keep line numbers safe use function makeCodeSingleLine
```
$code = "for ($i = 0; $i < 10; $i ++) {
    someFunc($i);
}";
$rule = new Rewriter\Rule\Object\Method('foo', $code);
$rule->makeCodeSingleLine();
```

### Method call log

* PMocks\Log::clear() - Clear all the loged data.
* PMocks\Log::callCount($class, $method) - Return how many times the given class::method was called.
* PMocks\Log::getCalls($class, $method) - Return all calls data of the given class::method.
* PMocks\Log::getLastCall($class, $method) - Return last call data of the given class::method.
* PMocks\Log::getLastCallArgs($class, $method) - Return last call arguments data of the given class::method.

### Own rules

For create custom rules implement **PMocks\RuleInterface**
