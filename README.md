# PMocks

## Introduction

PMocks - it's a library for mock classes in projects based on Zend Framework.
Base idea is that we are creating a modified copy of mocked class and insert our code only where it's needed.
All other code of file is saved in original format, includes line numbers. So, if you have an error in mocked class it's easy to find it.
New implementation of a class putts in a temporary folder and includes.


Of course, you can easy transform the library to an any other framework.

## Depends
- **PHP** 5.3 or greater
- **Zend Framework** 1.12 or greater
    

## Examples
Explore [Example Test](tests/Test.php) to find out how to use it.

### Replace token


### Constatnt

### Method

### Call count

### Call args


