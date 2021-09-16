<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
declare(strict_types=1);

use FunctionExecutionTimer\Utils;

/**
 * Description of ObjectifiedCallableTest
 *
 * @author Rotimi
 */
class UtilsTest extends \PHPUnit\Framework\TestCase {

    protected function setUp(): void { 
        
        parent::setUp();
    }
    
    public function testArrayGetWorksAsExpected() {

        $array = [
            'a' => 'Apple',
            'b' => 'Ball',
            'c' => 'Cat',
            'Dog', 'Egg',
        ];

        // existent key returns corresponding value
        $this->assertEquals('Apple', Utils::arrayGet($array, 'a', 'a Not Found'));
        $this->assertEquals('Ball', Utils::arrayGet($array, 'b', 'b Not Found'));
        $this->assertEquals('Cat', Utils::arrayGet($array, 'c', 'c Not Found'));
        $this->assertEquals('Dog', Utils::arrayGet($array, '0', '0 Not Found'));
        $this->assertEquals('Egg', Utils::arrayGet($array, '1', '1 Not Found'));

        // non-existent key returns default value
        $this->assertEquals('d Not Found', Utils::arrayGet($array, 'd', 'd Not Found'));
        $this->assertEquals('2 Not Found', Utils::arrayGet($array, '2', '2 Not Found'));
    }
}
