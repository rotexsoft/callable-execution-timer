<?php
declare(strict_types=1);

namespace FunctionExecutionTimer;

/**
 * Description of Utils
 *
 * @author Rotimi Ade
 */
class Utils {

    /**
     * Get a value associated with the specified key in the specified array or return
     * the specified default value if specified key doesn't exist in the specified array
     * 
     * @param array $potentialArray array in which value is to be retrieved from
     * @param string $key key in the array whose value is to be retrieved if key exists in array
     * @param mixed $defaultVal value to return if key not in array
     */
    public static function arrayGet(array $potentialArray, string $key, mixed $defaultVal=''): mixed {
        
        return (\array_key_exists($key, $potentialArray))
                ? $potentialArray[$key]
                : $defaultVal;
    }
}
