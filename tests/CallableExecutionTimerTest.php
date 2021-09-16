<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
declare(strict_types=1);

use FunctionExecutionTimer\CallableExecutionTimer;

/**
 * Description of CallableExecutionTimerTest
 *
 * @author Rotimi
 */
class CallableExecutionTimerTest extends \PHPUnit\Framework\TestCase {

    protected function setUp(): void { 
        
        parent::setUp();
    }

    public function testConstructorWorksAsExpected() {

        //valid callable
        $callableObj1 = new CallableExecutionTimer('strtolower', 'strtolower');
        $this->assertEquals('boo', $callableObj1->strtolower('BOO')); // __call
        $this->assertEquals('boo', $callableObj1(['BOO'])); // __invoke
        
        $this->expectException(\InvalidArgumentException::class);
        $msg = "Error: bad method name `-strtolower` supplied in"
             . " FunctionExecutionTimer\ObjectifiedCallable::__construct(...)";
        $this->expectExceptionMessage($msg);
        
        // use bad method name
        $callableObj2 = new CallableExecutionTimer('-strtolower', 'strtolower');
    }
    
    public function test__CallWorksAsExpected() {

        //valid callable
        $callableObj1 = new CallableExecutionTimer('strtolower', 'strtolower');
        $this->assertEquals('boo', $callableObj1->__call('strtolower', ['BOO']));
        
        $this->expectException(\InvalidArgumentException::class);
        $msg = "Error: Method `strtolower2` not registered in this instance of "
             . "`FunctionExecutionTimer\CallableExecutionTimer`";
        $this->expectExceptionMessage($msg);
        
        // use non registered method name
        $callableObj1->__call('strtolower2', ['BOO']);
    }
    
    public function test__InvokeWorksAsExpected() {

        //valid callable
        $callableObj1 = new CallableExecutionTimer('strtolower', 'strtolower');
        $this->assertEquals('boo', $callableObj1->__invoke(['BOO']));
    }
    
    public function testSetCalleeBacktraceDataWorksAsExpected() {
        
        $array = [ 'a' => 'Apple', 'b' => 'Ball', 'c' => 'Cat', 'Dog', 'Egg'];
        
        $callableObj1 = new CallableExecutionTimer('strtolower', 'strtolower');
        $callableObj1->setCalleeBacktraceData($array);
        
        $this->assertEquals($array, $callableObj1->getCalleeBacktraceData());
    }
    
    public function testGetLatestBenchmarkWorksAsExpected() {
        
        CallableExecutionTimer::clearBenchmarks();
        
        $callableObj1 = new CallableExecutionTimer('strtolower', 'strtolower');
        $this->assertEquals([], $callableObj1->getLatestBenchmark()); //1st call
        
        $this->assertEquals('boo', $callableObj1(['BOO']));
        $benchmark = $callableObj1->getLatestBenchmark();
        $this->assertArrayHasKey('function', $benchmark);
        $this->assertArrayHasKey('args', $benchmark);
        $this->assertArrayHasKey('start_time', $benchmark);
        $this->assertArrayHasKey('end_time', $benchmark);
        $this->assertArrayHasKey('total_execution_time_in_seconds', $benchmark);
        $this->assertArrayHasKey('return_value', $benchmark);
        $this->assertArrayHasKey('file_called_from', $benchmark);
        $this->assertArrayHasKey('line_called_from', $benchmark);

        $this->assertEquals('boo', $callableObj1->strtolower('BOO'));
        $newBenchmark = $callableObj1->getLatestBenchmark();
        $this->assertArrayHasKey('function', $newBenchmark);
        $this->assertArrayHasKey('args', $newBenchmark);
        $this->assertArrayHasKey('start_time', $newBenchmark);
        $this->assertArrayHasKey('end_time', $newBenchmark);
        $this->assertArrayHasKey('total_execution_time_in_seconds', $newBenchmark);
        $this->assertArrayHasKey('return_value', $newBenchmark);
        $this->assertArrayHasKey('file_called_from', $newBenchmark);
        $this->assertArrayHasKey('line_called_from', $newBenchmark);

        $this->assertNotEquals($benchmark, $newBenchmark);
        
        // Test that the arg value(s) reported for a callable that accepts one or more
        // args by reference is the value of each arg as passed by the caller and not
        // a potentially modified value that may have been as a result of the callable
        // modifying the arg(s) passed by reference.
        $num = -1;
        $callableObj2 = new CallableExecutionTimer(
                            'funcInlineByRef', 
                            function(int &$arg) { return "\$arg = " . ++$arg; }
                        );
        $this->assertEquals('$arg = 0', $callableObj2([&$num]));
        $this->assertEquals(0, $num);  // $num has been modified because of the call above
        $newBenchmark2 = $callableObj2->getLatestBenchmark();
        $this->assertEquals([-1], $newBenchmark2['args']); // the initial -1 value of $num should be reported
        
        CallableExecutionTimer::clearBenchmarks();
    }
    
    public function testGetBenchmarksWorksAsExpected() {
        
        CallableExecutionTimer::clearBenchmarks();
        
        $callableObj1 = new CallableExecutionTimer('strtolower', 'strtolower');
        
        $this->assertEquals('boo', $callableObj1(['BOO']));
        $this->assertEquals('boo', $callableObj1->strtolower('BOO'));
        $this->assertEquals('boo', CallableExecutionTimer::callFunc('strtolower', 'strtolower', ['BOO']));
        
        $callableObj2 = new CallableExecutionTimer('strtoupper', 'strtoupper');
        
        $this->assertEquals('BOO', $callableObj2(['boo']));
        $this->assertEquals('BOO', $callableObj2->strtoupper('boo'));
        $this->assertEquals('BOO', CallableExecutionTimer::callFunc('strtoupper', 'strtoupper', ['boo']));
        
        $allBenchmarks = CallableExecutionTimer::getBenchmarks();
        
        foreach($allBenchmarks as $benchmark) {
            
            $this->assertIsArray($benchmark);
            $this->assertArrayHasKey('function', $benchmark);
            $this->assertArrayHasKey('args', $benchmark);
            $this->assertArrayHasKey('start_time', $benchmark);
            $this->assertArrayHasKey('end_time', $benchmark);
            $this->assertArrayHasKey('total_execution_time_in_seconds', $benchmark);
            $this->assertArrayHasKey('return_value', $benchmark);
            $this->assertArrayHasKey('file_called_from', $benchmark);
            $this->assertArrayHasKey('line_called_from', $benchmark);
        }

        CallableExecutionTimer::clearBenchmarks();
        
        // Test that the arg value(s) reported for a callable that accepts one or more
        // args by reference is the value of each arg as passed by the caller and not
        // a potentially modified value that may have been as a result of the callable
        // modifying the arg(s) passed by reference.
        $num = -1;
        $this->assertEquals(
            '$arg = 0', 
            CallableExecutionTimer::callFunc(
                'funcInlineByRef', 
                function(int &$arg) { return "\$arg = " . ++$arg; },
                [&$num]
            )
        );
        $this->assertEquals(0, $num); // $num has been modified because of the call above
        $allBenchmarks2 = CallableExecutionTimer::getBenchmarks();
        $lastBenchmark = array_pop($allBenchmarks2);
        $this->assertEquals([-1], $lastBenchmark['args']); // the initial -1 value of $num should be reported
        
        CallableExecutionTimer::clearBenchmarks();
    }
    
    public function testClearBenchmarksWorksAsExpected() {
        
        $callableObj1 = new CallableExecutionTimer('strtolower', 'strtolower');
        
        $this->assertEquals('boo', $callableObj1(['BOO']));
        $this->assertEquals('boo', $callableObj1->strtolower('BOO'));
        $this->assertEquals('boo', CallableExecutionTimer::callFunc('strtolower', 'strtolower', ['BOO']));
        
        $this->assertNotEmpty(CallableExecutionTimer::getBenchmarks());
        
        CallableExecutionTimer::clearBenchmarks();
        
        $this->assertEmpty(CallableExecutionTimer::getBenchmarks());
    }
    
    public function testGetCalleeBacktraceDataWorksAsExpected() {
        
        $array = [ 'a' => 'Apple', 'b' => 'Ball', 'c' => 'Cat', 'Dog', 'Egg'];
        
        $callableObj1 = new CallableExecutionTimer('strtolower', 'strtolower');
        
        $this->assertEquals([], $callableObj1->getCalleeBacktraceData());
        
        $callableObj1->setCalleeBacktraceData($array);
        $this->assertEquals($array, $callableObj1->getCalleeBacktraceData());
    }
    
    public function testCallFuncWorksAsExpected() {
        
        // built-in php function
        $this->assertEquals('boo', CallableExecutionTimer::callFunc('strtolower', 'strtolower', ['BOO']));
        $this->assertEquals('BOO', CallableExecutionTimer::callFunc('strtoupper', 'strtoupper', ['boo']));
        
        // User defined function
        $this->assertEquals(
            "foobar got one and two", 
            CallableExecutionTimer::callFunc('foobar', 'foobar', ["one", "two"])
        );
        
        // Passing values by reference
        $bar = 77;        
        $this->assertEquals(
            "function mega \$a=55", 
            CallableExecutionTimer::callFunc('mega', 'mega', [&$bar])
        );
        $this->assertEquals(55, $bar); // verify that variable passed by ref was modified
        
        // Class instance method
        $this->assertEquals(
            "foo::bar got three and four", 
            CallableExecutionTimer::callFunc('fooBar', [new foo(), "bar"], ["three", "four"])
        );
        
        // Class static method
        $classname = "myclass";
        $this->assertEquals(
            "Hello!", 
            CallableExecutionTimer::callFunc('myclassSay_hello', [$classname, "say_hello"])
        );
        $this->assertEquals(
            "Hello!", 
            CallableExecutionTimer::callFunc('myclassSay_hello', $classname ."::say_hello")
        );
        
        // Relative static class method call
        $this->assertEquals(
            "A", 
            CallableExecutionTimer::callFunc('B_A_who', [B::class, 'parent::who'])
        );
        $this->assertEquals(
            "A", 
            CallableExecutionTimer::callFunc('A_who', [A::class, 'who'])
        );
        $this->assertEquals(
            "B", 
            CallableExecutionTimer::callFunc('B_who', [B::class, 'who'])
        );
        
        // Using namespace name, invoke static method in a namespaced class
        $this->assertEquals(
            "Hello Hannes!", 
            CallableExecutionTimer::callFunc('FoobarFooTest', "\\Foobar\\Foo::test", ["Hannes"])
        );
        $this->assertEquals(
            "Hello Philip!", 
            CallableExecutionTimer::callFunc('FoobarFooTest', ["\\Foobar\\Foo", 'test'], ["Philip"])
        );
        
        // Using lambda function
        $func = function($arg1, $arg2) {
            return $arg1 * $arg2;
        };
        $this->assertEquals(8, CallableExecutionTimer::callFunc('func', $func, [2, 4]));
        
        // inline lambda
        $this->assertEquals(
            "in inline lambda function!", 
            CallableExecutionTimer::callFunc('funcInline', function($arg) { return $arg; }, ['in inline lambda function!'])
        );
            
        // inline lambda by ref arg
        $num = 5;
        $this->assertEquals(
            "\$arg = 6", 
            CallableExecutionTimer::callFunc('funcInlineByRef', function(int &$arg) { return "\$arg = " . ++$arg; }, [&$num])
        );
        $this->assertEquals(6, $num);
        
        // Object implementing __invoke can be used as callables
        $this->assertEquals(
            "Hello Jane!", 
            CallableExecutionTimer::callFunc('C__invoke', new C(), ['Jane!'])
        );
    }
}
