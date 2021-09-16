<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
declare(strict_types=1);

use FunctionExecutionTimer\ObjectifiedCallable;

/**
 * Description of ObjectifiedCallableTest
 *
 * @author Rotimi
 */
class ObjectifiedCallableTest extends \PHPUnit\Framework\TestCase {

    protected function setUp(): void { 
        
        parent::setUp();
    }
    
    public function testConstructorWorksAsExpected() {

        //valid callable
        $callableObj1 = new ObjectifiedCallable('strtolower', 'strtolower');
        $this->assertEquals('boo', $callableObj1->strtolower('BOO')); // __call
        $this->assertEquals('boo', $callableObj1(['BOO'])); // __invoke
        
        $this->expectException(\InvalidArgumentException::class);
        $msg = "Error: bad method name `-strtolower` supplied in"
             . " FunctionExecutionTimer\ObjectifiedCallable::__construct(...)";
        $this->expectExceptionMessage($msg);
        
        // use bad method name
        $callableObj2 = new ObjectifiedCallable('-strtolower', 'strtolower');
    }
    
    public function test__CallWorksAsExpected() {

        //valid callable
        $callableObj1 = new ObjectifiedCallable('strtolower', 'strtolower');
        $this->assertEquals('boo', $callableObj1->__call('strtolower', ['BOO']));
        
        $this->expectException(\InvalidArgumentException::class);
        $msg = "Error: Method `strtolower2` not registered in this instance of "
             . "`FunctionExecutionTimer\ObjectifiedCallable`";
        $this->expectExceptionMessage($msg);
        
        // use non registered method name
        $callableObj1->__call('strtolower2', ['BOO']);
    }
    
    public function test__InvokeWorksAsExpected() {

        //valid callable
        $callableObj1 = new ObjectifiedCallable('strtolower', 'strtolower');
        $this->assertEquals('boo', $callableObj1->__invoke(['BOO']));
    }
    
    public function testIsValidMethodNameWorksAsExpected() {

        //valid callable
        $callableObj1 = new class('strtolower', 'strtolower') extends ObjectifiedCallable {
                            public function isValidMethodNamePublic(string $methodName): bool {
                               
                                return $this->isValidMethodName($methodName);
                            }
                        };
        $this->assertTrue($callableObj1->isValidMethodNamePublic('someMethod'));
        $this->assertTrue($callableObj1->isValidMethodNamePublic('some2_Method'));
        $this->assertTrue($callableObj1->isValidMethodNamePublic('_some2_Method'));
        $this->assertFalse($callableObj1->isValidMethodNamePublic('1someMethod'));
        $this->assertFalse($callableObj1->isValidMethodNamePublic('-someMethod'));
        $this->assertFalse($callableObj1->isValidMethodNamePublic('some-Method'));
    }
    
    public function testGetMethodWorksAsExpected() {

        $callableObj1 = new ObjectifiedCallable('stringtolower', 'strtolower');
        $this->assertTrue(\is_callable($callableObj1->getMethod()));
        $this->assertEquals('boo', $callableObj1(['BOO']));
        
        $func = function($arg1, $arg2) {
            return $arg1 * $arg2;
        };
        $callableObj2 = new ObjectifiedCallable('callback', $func);
        $this->assertTrue(\is_callable($callableObj2->getMethod()));
        $this->assertEquals(8, $callableObj2([4,2]));
    }
    
    public function testGetMethodNameWorksAsExpected() {

        //valid callable
        $callableObj1 = new ObjectifiedCallable('stringtolower', 'strtolower');
        $this->assertEquals('stringtolower', $callableObj1->getMethodName());
        
        $func = function($arg1, $arg2) {
            return $arg1 * $arg2;
        };
        $callableObj2 = new ObjectifiedCallable('callback', $func);
        $this->assertEquals('callback', $callableObj2->getMethodName());
    }
    
    public function testSetMethodWorksAsExpected() {

        $callableObj1 = new ObjectifiedCallable('stringtolower', 'strtolower');
        $this->assertEquals('boo', $callableObj1(['BOO']));
        
        $callableObj1->setMethod('strtoupper');
        $this->assertEquals('BOO', $callableObj1(['boo']));
        
        $func = function($arg1, $arg2) {
            return $arg1 * $arg2;
        };
        $callableObj2 = new ObjectifiedCallable('callback', $func);
        $this->assertEquals(8, $callableObj2([4,2]));
        
        $func2 = function($arg1, $arg2) {
            return $arg1 / $arg2;
        };
        $callableObj2->setMethod($func2);
        $this->assertEquals(2, $callableObj2([4,2]));
    }
    
    public function testsetMethodNameWorksAsExpected() {

        $callableObj1 = new ObjectifiedCallable('stringtolower', 'strtolower');
        $this->assertEquals('stringtolower', $callableObj1->getMethodName());
        $callableObj1->setMethodName('stringtoupper');
        $this->assertEquals('stringtoupper', $callableObj1->getMethodName());
        
        $this->expectException(\InvalidArgumentException::class);
        $msg = "Error: bad method name `-strtolower` supplied in"
             . " FunctionExecutionTimer\ObjectifiedCallable::setMethodName(...)";
        $this->expectExceptionMessage($msg);
        
        // use bad method name
        $callableObj1->setMethodName('-strtolower');
    }
}
