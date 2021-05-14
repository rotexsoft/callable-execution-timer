<?php
declare(strict_types=1);

namespace FunctionExecutionTimer;

/**
 * 
 * A class that can be used to call any function or method while tracking the execution time of each call
 *
 * @author rotex
 */
class FunctionExecutionTimer extends ObjectifiedCallable {

    /**
     * Holds execution stats for all function / method calls across all instances of this class
     * 
     * @var array
     */
    protected static $benchmarks = [];

    /**
     * Executes function / method registered on an instance of this class
     * 
     * @param mixed $args arguments to pass to the function / method to be executed
     * 
     * @return mixed result returned from executing function / method registered on an instance of this class
     */
    public function __invoke(...$args) {

        $startTime = \microtime(true);

        $result = parent::__invoke(...$args);
        
        $endTime = \microtime(true);
        
        static::$benchmarks[] = [
            'function' => $this->methodName,
            'args' => $args,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'total_execution_time_in_seconds' => ($endTime - $startTime),
            'return_value' => $result,
        ];
        
        return $result;
    }
    
    /**
     * Return an array containing execution stats for all functions / methods called via all instances of this class
     * 
     * @return array an array containing execution stats for all functions / methods called via all instances of this class
     */
    public static function getBenchmarks(): array {
        
        return static::$benchmarks;
    }
    
    /**
     * Executes a callable 
     * 
     * @param string $funcName a name of your choosing (for the callable to be executed) that adheres to PHP method naming rules
     * @param callable $funcImplementation the callable to be executed
     * @param mixed $args arguments required by the callable to be executed
     * 
     * @return mixed
     */
    public static function callFunc(
        string $funcName, callable $funcImplementation,  ...$args
    ) {
        $funcObj = (new self($funcName, $funcImplementation));
        
        return $funcObj(...$args);
    }
}
