<?php
declare(strict_types=1);

namespace FunctionExecutionTimer;

use InvalidArgumentException;

/**
 * A class used to invoke a callable and return the result from the invocation.
 *
 * @author Rotimi Ade
 */
class ObjectifiedCallable {

    /**
     * A callable that gets executed when an instance of this class's __call() or __invoke() methods are triggered
     * 
     * @var callable
     */
    protected $method;
    
    /**
     * A name (conforming to PHP's method naming convention) you are labeling the callable to be executed by this class via which you can execute the callable using object method call syntax
     * 
     */
    protected string $methodName = '';
    
    /**
     * Returns the callable that gets executed when an instance of this class's __call() or __invoke() methods are triggered
     * 
     */
    public function getMethod(): callable {
        
        return $this->method;
    }

    /**
     * Returns the name (conforming to PHP's method naming convention) you have labeled the callable to be executed by this class via which you can execute the callable using object method call syntax
     * 
     */
    public function getMethodName(): string {
        
        return $this->methodName;
    }

    /**
     * Set the callable that gets executed when an instance of this class's __call() or __invoke() methods are triggered
     * 
     * @param callable $method The callable that gets executed when an instance of this class's __call() or __invoke() methods are triggered
     * 
     */
    public function setMethod(callable $method): self {
        
        $this->method = \Closure::fromCallable( $method );
        
        return $this;
    }

    /**
     * Set the name (conforming to PHP's method naming convention) you are labeling the callable to be executed by this class via which you can execute the callable using object method call syntax
     * 
     * @param string $methodName the name (conforming to PHP's method naming convention) you are labeling the callable to be executed by this class via which you can execute the callable using object method call syntax
     * 
     * 
     * @throws InvalidArgumentException
     * 
     */
    public function setMethodName(string $methodName): self {
        
        if( !$this->isValidMethodName($methodName) ) {

            // A valid php class' method name starts with a letter or underscore, 
            // followed by any number of letters, numbers, or underscores.
            throw new InvalidArgumentException("Error: bad method name `{$methodName}` supplied in " . __METHOD__ . '(...)');
        }
        
        $this->methodName = $methodName;
        
        return $this;
    }

    /**
     * @param string $methodName should match the name of the method assigned (i.e. to $this->methodName) when this object was created
     * @param array<mixed> $args arguments to pass to the function / method to be executed
     * 
     * @return mixed result returned from executing function / method registered on an instance of this class
     * 
     * @throws \Exception if $method !== $this->methodName
     */
    public function __call(string $methodName, array $args): mixed {
        
        if( $this->methodName === $methodName ) {

            return $this->__invoke($args);
            
        } else {
            throw new \InvalidArgumentException(
                "Error: Method `$methodName` not registered in this instance of `" . static::class . '`'
            );
        }
    }
    
    /**
     * Executes function / method registered on an instance of this class
     * 
     * @param array<mixed> $args arguments to pass to the function / method to be executed
     * 
     * @return mixed result returned from executing function / method registered on an instance of this class
     */
    public function __invoke(array $args): mixed {
        
        // need to always keep this method very simple 
        // because we don't want to add any overhead 
        // computations for CallableExecutionTimer::__invoke
        // which calls this method and measures time it takes
        // for the callable in $this->method to be executed.
        return \call_user_func_array($this->method, $args);
    }
    
    /**
     * @param string $methodName a valid name (conforming to PHP's method naming convention) for the callable to be registered to this object
     * @param callable $method a callable that will be executed via this object
     */
    public function __construct (string $methodName, callable $method) {

        if( !$this->isValidMethodName($methodName) ) {

            // A valid php class' method name starts with a letter or underscore, 
            // followed by any number of letters, numbers, or underscores.
            throw new InvalidArgumentException("Error: bad method name `{$methodName}` supplied in " . __METHOD__ . '(...)');
        }
        
        $this->method = \Closure::fromCallable( $method );
        $this->methodName = $methodName;
    }
    
    /**
     * Checks if the supplied string conforms to PHP's method naming convention 
     * 
     * A valid php class' method name starts with a letter or underscore, 
     * followed by any number of letters, numbers, or underscores.
     * 
     * @param string $methodName name to be tested if is valid method name
     * 
     */
    protected function isValidMethodName(string $methodName): bool {
        
        $regexForValidMethodName = '/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/';
        
        return (bool) preg_match( $regexForValidMethodName, preg_quote($methodName, '/') );
    }
}
