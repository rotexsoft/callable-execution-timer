<?php
declare(strict_types=1);

namespace FunctionExecutionTimer;

/**
 * A class used to invoke a callable
 *
 * @author rotex
 */
class ObjectifiedCallable {

    /**
     * A callable to be executed by this class
     * 
     * @var callable
     */
    protected $method;
    
    /**
     * A name (conforming to PHP's method naming convention) you are labeling the callable to be executed by this class
     * 
     * @var string
     */
    protected $methodName = '';

    /**
     * 
     * @param string $method should match the name of the method assigned (i.e. to $this->methodName) when this object was created
     * @param mixed $args arguments to pass to the function / method to be executed
     * 
     * @return mixed result returned from executing function / method registered on an instance of this class
     * 
     * @throws \Exception if $method !== $this->methodName
     */
    public function __call($method, $args) {
        
        if( $this->methodName === $method ) {
            
            return $this(...$args);
            
        } else {
            
            throw new \InvalidArgumentException("Method `$method` not found.");
        }
    }
    
    /**
     * Executes function / method registered on an instance of this class
     * 
     * @param mixed $args arguments to pass to the function / method to be executed
     * 
     * @return mixed result returned from executing function / method registered on an instance of this class
     */
    public function __invoke(...$args) {

        $meth = $this->method;
        return $meth(...$args);
    }
    
    /**
     * 
     * @param string $methodName a valid name (conforming to PHP's method naming convention) for the callable to be registered to this object
     * @param callable $method a callable that will be executed via this object
     */
    public function __construct (string $methodName, callable $method) {
        
        $cl = \Closure::fromCallable( $method );
        $this->method = $cl;
        $this->methodName = $methodName;
    }
}
