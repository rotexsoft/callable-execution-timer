<?php
declare(strict_types=1);

namespace FunctionExecutionTimer;

/**
 * 
 * A class that can be used to call any function or method while tracking the execution time of each call
 * 
 * @psalm-suppress MixedAssignment
 * 
 * 
 * @author Rotimi Ade
 */
class CallableExecutionTimer extends ObjectifiedCallable {

    public const NANO_SECOND_TO_SECOND_DIVISOR = 1_000_000_000;

    /**
     * Holds execution stats for all function / method calls across all instances of this class
     * 
     * @var array
     */
    protected static $benchmarks = [];

    /**
     * Holds execution stats for all function / method calls across all instances of this class
     * 
     * @var array
     */
    protected $benchmark = [];
    
    /**
     * An array containing data about file, line where static::callFunc(...) was invoked 
     * 
     * Example:
        [
            [file] => C:\function-execution-timer\tester.php
            [line] => 9
            [function] => callFunc
            [class] => FunctionExecutionTimer\CallableExecutionTimer
            [type] => ::
            [args] => 
                [
                    [0] => strtolower
                    [1] => strtolower
                    [2] => FOO BAR
                ]

        ]
     * 
     * @var array
     */
    protected $calleeBacktraceData = [];
    
    /**
     * Set to an array containing backtrace data about file, line where static::callFunc(...) was invoked
     *
     *
     * @param mixed[] $data
     */
    public function setCalleeBacktraceData(array $data): void {
        
        $this->calleeBacktraceData = $data;
    }
    
    /**
     * Return the backtrace data associated with an instance of this class
     * 
     * @return mixed[]
     */
    public function getCalleeBacktraceData(): array {
        
        return $this->calleeBacktraceData;
    }
    
    /**
     * 
     * @param string $method name of method being invoked
     * @param array<mixed> $args
     * @return mixed
     * 
     * @throws \Exception from ObjectifiedCallable::__call
     */
    public function __call(string $method, array $args) {
        
        if($this->calleeBacktraceData === []) {
            
            $backTraceData = \debug_backtrace();

            if(\count($backTraceData) > 0) {

                //Get and set the trace info for the place from where this method was called
               $this->setCalleeBacktraceData(\array_shift($backTraceData));
            }
        }
        
        $result = parent::__call($method, $args);
        
        $this->setCalleeBacktraceData([]);
        
        return $result;
    }
    
    /**
     * Executes function / method registered on an instance of this class
     * 
     * @param array<mixed> $args arguments to pass to the function / method to be executed
     * 
     * @return mixed result returned from executing function / method registered on an instance of this class
     */
    public function __invoke(array $args) {

        $argsCopy = [];
        
        foreach ($args as $k=>$arg) {
            
            // Copy each arg in case there are references we only need the 
            // current value of each reference argument at this point, such 
            // arguments may be modified when the callable associated with 
            // this class is invoked later on below and we don't want to
            // report the modified value(s).
            $argsCopy[$k] = $arg;
        }
        
        $startTime = \hrtime(true); // start timing
        $result = parent::__invoke($args);
        $endTime = \hrtime(true); // stop timing
        
        if($this->calleeBacktraceData === []) {
            
            $backTraceData = \debug_backtrace();

            if(\count($backTraceData) > 0) {

                //Get and set the trace info for the place from where this method was called
               $this->setCalleeBacktraceData(\array_shift($backTraceData));
            }
        }
        
        $this->benchmark = [
            'function' => $this->methodName,
            'args' => $argsCopy,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'total_execution_time_in_seconds' => (($endTime - $startTime) / self::NANO_SECOND_TO_SECOND_DIVISOR),
            'return_value' => $result,
            'file_called_from' => Utils::arrayGet($this->calleeBacktraceData, 'file', 'Unknown'),
            'line_called_from' => Utils::arrayGet($this->calleeBacktraceData, 'line', 'Unknown'),
        ];
        static::$benchmarks[] = $this->benchmark;
        
        $this->setCalleeBacktraceData([]);

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
     * Clear array containing execution stats for all functions / methods called via all instances of this class
     */
    public static function clearBenchmarks(): void {
        
        static::$benchmarks = [];
    }
    
    /**
     * Return an array containing execution stats for the last function / method called via an instance of this class
     * 
     * @return array an array containing execution stats for the last function / method called via an instance of this class
     */
    public function getLatestBenchmark(): array {
        
        return $this->benchmark;
    }
    
    /**
     * Executes a callable 
     *
     * @param string $funcName a name of your choosing (for the callable to be executed) that adheres to PHP method naming rules that will be used to label the call for benchmarking purposes
     * @param callable $funcImplementation the callable to be executed
     * @param array<mixed> $args arguments required by the callable to be executed
     *
     * @return mixed
     */
    public static function callFunc(
        string $funcName, callable $funcImplementation, array $args=[]
    ) {
        $backTraceData = \debug_backtrace();
        $funcObj = (new self($funcName, $funcImplementation));
        
        if(\count($backTraceData) > 0) {
            
            //Get and set the trace info for the place from where this method was called
            $funcObj->setCalleeBacktraceData(\array_shift($backTraceData));
        }
        
        return $funcObj($args);
    }
}
