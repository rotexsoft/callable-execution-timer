# Callable Execution Timer

[![PHP Tests and Code Quality Tools](https://github.com/rotexsoft/function-execution-timer/workflows/Run%20PHP%20Tests%20and%20Code%20Quality%20Tools/badge.svg)](https://github.com/rotexsoft/function-execution-timer/actions?query=workflow%3A%22Run+PHP+Tests+and+Code+Quality+Tools%22) &nbsp;
[![Release](https://img.shields.io/github/release/rotexsoft/function-execution-timer.png?style=flat-square)](https://github.com/rotexsoft/function-execution-timer/releases/latest) &nbsp; 
[![License](https://img.shields.io/badge/license-BSD-brightgreen.png?style=flat-square)](https://github.com/rotexsoft/function-execution-timer/blob/master/LICENSE) &nbsp; 


A simple PHP library for tracking the total amount of time a 
[callable](https://www.php.net/manual/en/language.types.callable.php) (e.g. 
function / method) takes to execute (it can also return the result of executing 
the callable, if desired).


## Installation 

**Via composer:** (Requires PHP 7.3+ or PHP 8.0+). 

    composer require rotexsoft/callable-execution-timer

## Introduction

A simple PHP library for tracking the total amount of time a callable (e.g. 
function / method) takes to execute and return result(s) (if any).

> For the rest of this documentation the term **callable** will mostly be referring to functions / methods

This library also provides information associated with each execution / invocation of the callable such as:

* the arguments passed to the callable (array)
* the total time it took to execute the callable in seconds (int / float)
* the value returned from calling the callable (mixed)
* the absolute path to the file in which the callable was called (string)
* the exact line number in the file in which the callable was called (integer)

## Basic usage: Executing callables

### Executing built-in php functions

Let's call php's built-in [strtolower](https://www.php.net/manual/en/function.strtolower.php) & [strtoupper](https://www.php.net/manual/en/function.strtoupper.php) functions:

```php
<?php
use \FunctionExecutionTimer\CallableExecutionTimer;

CallableExecutionTimer::callFunc('strtolower', 'strtolower', ['BOO']); // returns 'boo'

CallableExecutionTimer::callFunc('strtoupper', 'strtoupper', ['boo']) // returns 'BOO'
```

> NOTE: The first argument passed to **CallableExecutionTimer::callFunc(...)** is a name (conforming to PHP's method naming convention) you want to name the callable you are about to execute. This name will be used to label the information related to the execution of the callable as will be shown later on in this documentation.

> NOTE: The second argument passed to **CallableExecutionTimer::callFunc(...)** is the callable you are trying to execute.

> NOTE: The third argument passed to **CallableExecutionTimer::callFunc(...)** is an array containing all the arguments to be passed to the callable you are trying to execute. You can omit this argument if the callable to be executed does not accept any arguments.


### Executing user defined functions

```php
<?php
use \FunctionExecutionTimer\CallableExecutionTimer;

function foobar($arg, $arg2) {
    return __FUNCTION__ . " got $arg and $arg2";
}

// a function that has a by-ref argument
function mega(&$a){
    $a = 55;
    return "function mega \$a=$a";
}

CallableExecutionTimer::callFunc('foobar', 'foobar', ["one", "two"]); // returns 'foobar got one and two'

$bar = 77;
CallableExecutionTimer::callFunc('mega', 'mega', [&$bar]); // returns 'function mega $a=55'

// $bar now has a value of 55 after the execution of the function above
```

### Executing class methods

```php
<?php
use \FunctionExecutionTimer\CallableExecutionTimer;

class foo {

    function bar($arg, $arg2) {
        return __METHOD__ . " got $arg and $arg2";
    }
}

class myclass {

    static function say_hello() {
        return "Hello!";
    }
}

// execute an instance method
CallableExecutionTimer::callFunc(
    'fooBar', [new foo(), "bar"], ["three", "four"]
); // returns 'foo::bar got three and four'


// execute a static method
$classname = "myclass";
CallableExecutionTimer::callFunc(
    'myclassSay_hello', [$classname, "say_hello"]
); // returns 'Hello!'

// OR

CallableExecutionTimer::callFunc(
    'myclassSay_hello', $classname ."::say_hello"
); // also returns 'Hello!'

```

### Executing parent and child class methods

```php
<?php
use \FunctionExecutionTimer\CallableExecutionTimer;

class A {

    public static function who() {
        return "A";
    }
}

class B extends A {

    public static function who() {
        return "B";
    }
}

// Child calling parent's implementation of method defined in both parent & child
CallableExecutionTimer::callFunc('B_A_who', [B::class, 'parent::who']); // returns 'A'

// Parent calling its own method
CallableExecutionTimer::callFunc('A_who', [A::class, 'who']);  // returns 'A'

// Child calling its own method
CallableExecutionTimer::callFunc('B_who', [B::class, 'who']); // returns 'B'
```

### Executing namespaced static class methods

```php
<?php
namespace Foobar {

    class Foo {

        static public function test($name) {
            return "Hello {$name}!";
        }
    }
}

namespace {
    use \FunctionExecutionTimer\CallableExecutionTimer;

    // Syntax 1
    CallableExecutionTimer::callFunc(
        'FoobarFooTest', "\\Foobar\\Foo::test", ["Hannes"]
    ); // returns 'Hello Hannes!'

    // Syntax 2
    CallableExecutionTimer::callFunc(
        'FoobarFooTest', ["\\Foobar\\Foo", 'test'], ["Philip"]
    ); // returns 'Hello Philip!'
}
```

### Executing lambda / anonymous functions

```php
<?php
use \FunctionExecutionTimer\CallableExecutionTimer;

$func = function($arg1, $arg2) {
    return $arg1 * $arg2;
};

CallableExecutionTimer::callFunc('func', $func, [2, 4]); // returns 8

CallableExecutionTimer::callFunc(
    'funcInline', 
    function($arg) { return $arg; }, 
    ['in inline lambda function!']
); // returns 'in inline lambda function!'

// anonymous function that accepts a by-ref argument
$num = 5;
CallableExecutionTimer::callFunc(
    'funcInlineByRef', 
    function(int &$arg) { return "\$arg = " . ++$arg; }, 
    [&$num]
); // returns '$arg = 6' 

// $num now has a value of 6 at this point
```

### Executing an object that is an instance of a class that has an __invoke method

```php
<?php
use \FunctionExecutionTimer\CallableExecutionTimer;

class C {

    public function __invoke($name) {
        return "Hello {$name}";
    }
}

CallableExecutionTimer::callFunc('C__invoke', new C(), ['Jane!']); // returns 'Hello Jane!'
```

You can also use instances of **\FunctionExecutionTimer\CallableExecutionTimer** to execute callables like below:

```php
<?php
use \FunctionExecutionTimer\CallableExecutionTimer;

$callableObj1 = new CallableExecutionTimer('strtolowerCallback', 'strtolower');

$callableObj1->strtolowerCallback('BOO'); // triggers __call & returns 'boo'
                                          // same as $callableObj1->__call('strtolowerCallback', ['BOO'])

$callableObj1(['BOO']); // triggers __invoke & returns 'boo'
                        // same as $callableObj1->__invoke(['BOO'])

```

> **WARNING:** Executing a callable that has one or more parameters that should be passed by reference should be done using **\FunctionExecutionTimer\CallableExecutionTimer::callFunc(...)** or executing the function by using the **__invoke(array $args)** mechanism on the instance of **\FunctionExecutionTimer\CallableExecutionTimer** the callable is bound to.

> It won't work by trying to invoke the callable on an instance of  **\FunctionExecutionTimer\CallableExecutionTimer**
using the method call syntax that triggers **__call()** under the hood.

For example, you can execute the lambda function below that accepts an argument by reference via the following two ways:

```php
<?php
use \FunctionExecutionTimer\CallableExecutionTimer;

$func = function(int &$arg) { 
    return "\$arg = " . ++$arg; 
};

// Option 1 use CallableExecutionTimer::callFunc(...)
$num = -1;
CallableExecutionTimer::callFunc(
    'funcWithRefArg', $func, [&$num]
); // returns '$arg = 0' & $num will have a value of 0 after this call

// Option 2 using the __invoke(array $args) mechanism on the instance of 
// CallableExecutionTimer the callable is bound to
$num = -1;
$callableObj2 = new CallableExecutionTimer('funcWithRefArg', $func);
$callableObj2([&$num]); // triggers the __invoke(array $args) mechanism
                        // which executes the lambda function and 
                        // returns '$arg = 0'.
                        // $num will have a value of 0 after this call


///////////////////////////////////////////////////////////////////////////
// NOTE: trying to invoke the function on an instance of 
// **\FunctionExecutionTimer\CallableExecutionTimer** using the method call
// syntax that triggers **__call()** under the hood will not work, $num
// will not be passed by reference as expected and you will get a PHP
// warning to that effect.
// DON'T DO THIS
///////////////////////////////////////////////////////////////////////////
$num = -1;
$callableObj2 = new CallableExecutionTimer('funcWithRefArg', $func);
$numRef = &$num;
$callableObj2->funcWithRefArg($numRef); // Will throw a PHP Warning.
                                        // $numRef will not be passed by
                                        // ref because of the way 
                                        // __call(string $methodName, array $args) 
                                        // works, meaning that $num will still 
                                        // have a value of -1 after the call.

```