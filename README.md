# Callable Execution Timer

[![PHP Tests and Code Quality Tools](https://github.com/rotexsoft/function-execution-timer/workflows/Run%20PHP%20Tests%20and%20Code%20Quality%20Tools/badge.svg)](https://github.com/rotexsoft/function-execution-timer/actions?query=workflow%3A%22Run+PHP+Tests+and+Code+Quality+Tools%22) &nbsp;
[![Release](https://img.shields.io/github/release/rotexsoft/function-execution-timer.png?style=flat-square)](https://github.com/rotexsoft/function-execution-timer/releases/latest) &nbsp; 
[![Coverage Status](https://coveralls.io/repos/github/rotexsoft/function-execution-timer/badge.svg?branch=main)](https://coveralls.io/github/rotexsoft/function-execution-timer?branch=main) &nbsp; 
![GitHub repo size](https://img.shields.io/github/repo-size/rotexsoft/function-execution-timer) &nbsp; 
![GitHub top language](https://img.shields.io/github/languages/top/rotexsoft/function-execution-timer) &nbsp; 
[![License](https://img.shields.io/badge/license-BSD-brightgreen.png?style=flat-square)](https://github.com/rotexsoft/function-execution-timer/blob/master/LICENSE) &nbsp; 


A simple PHP library for tracking the total amount of time a 
[callable](https://www.php.net/manual/en/language.types.callable.php) (e.g. 
function / method) takes to execute (it can also return the result of executing 
the callable, if desired).

If you want to do some simple execution time profiling in your application, without using some full blown tool like debugbar or xdebug, then this is the package for you.


## Installation 

**Via composer:** (Requires PHP 7.4+ or PHP 8.0+). 

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

## Executing callables

### Executing built-in php functions

Let's call php's built-in [strtolower](https://www.php.net/manual/en/function.strtolower.php) & [strtoupper](https://www.php.net/manual/en/function.strtoupper.php) functions:

```php
<?php
use \FunctionExecutionTimer\CallableExecutionTimer;

echo CallableExecutionTimer::callFunc('strtolower', 'strtolower', ['BOO']) . PHP_EOL; // outputs 'boo'

echo CallableExecutionTimer::callFunc('strtoupper', 'strtoupper', ['boo']) . PHP_EOL; // outputs 'BOO'
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

echo CallableExecutionTimer::callFunc('foobar', 'foobar', ["one", "two"]) . PHP_EOL ; // outputs 'foobar got one and two'

$bar = 77;
echo CallableExecutionTimer::callFunc('mega', 'mega', [&$bar]) . PHP_EOL ; // outputs 'function mega $a=55'

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
echo CallableExecutionTimer::callFunc(
    'fooBar', [new foo(), "bar"], ["three", "four"]
) . PHP_EOL ; // outputs 'foo::bar got three and four'


// execute a static method
$classname = "myclass";
echo CallableExecutionTimer::callFunc(
    'myclassSay_hello', [$classname, "say_hello"]
) . PHP_EOL; // outputs 'Hello!'

// OR

echo CallableExecutionTimer::callFunc(
    'myclassSay_hello', $classname ."::say_hello"
) . PHP_EOL; // also outputs 'Hello!'

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
echo CallableExecutionTimer::callFunc('B_A_who', [B::class, 'parent::who']) . PHP_EOL; // outputs 'A'

// Parent calling its own method
echo CallableExecutionTimer::callFunc('A_who', [A::class, 'who']) . PHP_EOL;  // outputs 'A'

// Child calling its own method
echo CallableExecutionTimer::callFunc('B_who', [B::class, 'who']) . PHP_EOL; // outputs 'B'
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
    //include_once './vendor/autoload.php'; //include your composer autoloader
    use \FunctionExecutionTimer\CallableExecutionTimer;

    // Syntax 1
    echo CallableExecutionTimer::callFunc(
        'FoobarFooTest', "\\Foobar\\Foo::test", ["Hannes"]
    ) . PHP_EOL; // outputs 'Hello Hannes!'

    // Syntax 2
    echo CallableExecutionTimer::callFunc(
        'FoobarFooTest', ["\\Foobar\\Foo", 'test'], ["Philip"]
    ) . PHP_EOL; // outputs 'Hello Philip!'
}
```

### Executing lambda / anonymous functions

```php
<?php
use \FunctionExecutionTimer\CallableExecutionTimer;

$func = function($arg1, $arg2) {
    return $arg1 * $arg2;
};

echo CallableExecutionTimer::callFunc('func', $func, [2, 4]) . PHP_EOL; // outputs 8

echo CallableExecutionTimer::callFunc(
    'funcInline', 
    function($arg) { return $arg; }, 
    ['in inline lambda function!']
) . PHP_EOL; // outputs 'in inline lambda function!'

// anonymous function that accepts a by-ref argument
$num = 5;
echo CallableExecutionTimer::callFunc(
    'funcInlineByRef', 
    function(int &$arg) { return "\$arg = " . ++$arg; }, 
    [&$num]
) . PHP_EOL; // outputs '$arg = 6' 

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

echo CallableExecutionTimer::callFunc('C__invoke', new C(), ['Jane!']) . PHP_EOL; // outputs 'Hello Jane!'
```

You can also use instances of **\FunctionExecutionTimer\CallableExecutionTimer** to execute callables like below:

```php
<?php
use \FunctionExecutionTimer\CallableExecutionTimer;

$callableObj1 = new CallableExecutionTimer('strtolowerCallback', 'strtolower');

echo $callableObj1->strtolowerCallback('BOO') . PHP_EOL; // triggers __call & outputs 'boo'
                                                         // same as $callableObj1->__call('strtolowerCallback', ['BOO'])

echo $callableObj1(['BOO']) . PHP_EOL;  // triggers __invoke & outputs 'boo'
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
echo CallableExecutionTimer::callFunc(
    'funcWithRefArg', $func, [&$num]
) . PHP_EOL; // outputs '$arg = 0' & $num will have a value of 0 after this call

// Option 2 using the __invoke(array $args) mechanism on the instance of 
// CallableExecutionTimer the callable is bound to
$num = -1;
$callableObj2 = new CallableExecutionTimer('funcWithRefArg', $func);
echo $callableObj2([&$num]) . PHP_EOL;  // triggers the __invoke(array $args) mechanism
                                        // which executes the lambda function and 
                                        // outputs '$arg = 0'.
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
echo $callableObj2->funcWithRefArg($numRef) . PHP_EOL;  // Will throw a PHP Warning.
                                                        // $numRef will not be passed by
                                                        // ref because of the way 
                                                        // __call(string $methodName, array $args) 
                                                        // works, meaning that $num will still 
                                                        // have a value of -1 after the call.

```


## Retrieving execution statistics

There are two ways to retrieve information associated with each execution of callables performed via this library:

1. You can call the **getLatestBenchmark()** method on an instance of **\FunctionExecutionTimer\CallableExecutionTimer** which you just used to execute a callable to get information about the most recent callable execution via that object. This method returns an array with the following keys (in bold, not including the colon):
    * **function** : A string. The name (conforming to PHP's method naming convention) you labeled the callable you executed
    * **args** : An array. Contains the arguments you passed to the callable you executed, if any, otherwise it would be an empty array.
    * **start_time** : A float or an Integer. The timestamp in nanoseconds when the execution of the callable started.
    * **end_time** : A float or an Integer. The timestamp in nanoseconds when the execution of the callable ended.
    * **total_execution_time_in_seconds** : A float or an Integer. The total number of seconds it took to execute the callable.
    * **return_value** : The value returned from the callable that was executed, if any, else NULL.
    * **file_called_from** : A string. The absolute path to the file from which the callable was executed.
    * **line_called_from** : An Integer. The exact line number in the file from which the callable was executed. 

    Below is an example:

    ```php
    <?php
    use \FunctionExecutionTimer\CallableExecutionTimer;

    $funcObj = new CallableExecutionTimer('strtolower', 'strtolower');

    echo $funcObj->strtolower('BOO') . PHP_EOL;
    var_export($funcObj->getLatestBenchmark());
    ```

    The code above will generate output like the one below:

    ```
    array (
    'function' => 'strtolower',
    'args' =>
    array (
        0 => 'BOO',
    ),
    'start_time' => 81023870126000,
    'end_time' => 81023870134000,
    'total_execution_time_in_seconds' => 8.0E-6,
    'return_value' => 'boo',
    'file_called_from' => 'C:\\Code\\function-execution-timer\\tester.php',
    'line_called_from' => 105,
    )
    ```

2. You can call **\FunctionExecutionTimer\CallableExecutionTimer::getBenchmarks()** to get information about the all callable executions performed via
    * all calls to **\FunctionExecutionTimer\CallableExecutionTimer::callFunc(...)** 
    * and all callable executions via various instances of **\FunctionExecutionTimer\CallableExecutionTimer**

    This method returns an array of arrays. Each sub-array has the structure of the array returned by the **getLatestBenchmark()** method described above. Below is some sample code:

    ```php
    <?php
    use \FunctionExecutionTimer\CallableExecutionTimer;

    // First clear previous benchmark info if any
    CallableExecutionTimer::clearBenchmarks(); 

    $funcObj = new CallableExecutionTimer('strtolowerMethod', 'strtolower');
    
    echo $funcObj->strtolowerMethod('BOO') . PHP_EOL;
    echo $funcObj->strtolowerMethod('ABA') . PHP_EOL;

    echo CallableExecutionTimer::callFunc(
        'funcInline', 
        function($arg) { return "Hello $arg !"; }, 
        ['Jane']
    ) . PHP_EOL;

    var_export(CallableExecutionTimer::getBenchmarks());
    ```

    The code above will generate output like the one below:

    ```
    array (
    0 =>
    array (
        'function' => 'strtolowerMethod',
        'args' =>
        array (
        0 => 'BOO',
        ),
        'start_time' => 87248086831300,
        'end_time' => 87248086840600,
        'total_execution_time_in_seconds' => 9.3E-6,
        'return_value' => 'boo',
        'file_called_from' => 'C:\\Code\\function-execution-timer\\tester.php',
        'line_called_from' => 106,
    ),
    1 =>
    array (
        'function' => 'strtolowerMethod',
        'args' =>
        array (
        0 => 'ABA',
        ),
        'start_time' => 87248086997700,
        'end_time' => 87248087001600,
        'total_execution_time_in_seconds' => 3.9E-6,
        'return_value' => 'aba',
        'file_called_from' => 'C:\\Code\\function-execution-timer\\tester.php',
        'line_called_from' => 108,
    ),
    2 =>
    array (
        'function' => 'funcInline',
        'args' =>
        array (
        0 => 'Jane',
        ),
        'start_time' => 87248087019400,
        'end_time' => 87248087024100,
        'total_execution_time_in_seconds' => 4.7E-6,
        'return_value' => 'Hello Jane !',
        'file_called_from' => 'C:\\Code\\function-execution-timer\\tester.php',
        'line_called_from' => 110,
    ),
    )
    ```

IT IS RECOMMENDED THAT YOU CALL **\FunctionExecutionTimer\CallableExecutionTimer::clearBenchmarks()** BEFORE YOU START EXECUTING THE CALLABLES THAT YOU WANT TO GET EXECUTION INFORMATION FOR. THIS WILL CLEAR ALL PREVIOUS EXECUTION INFO FROM PRIOR CALLABLE EXECUTIONS.
