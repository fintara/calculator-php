<?php declare(strict_types=1);
namespace Fintara\Tools\Calculator\Tests;

use \PHPUnit\Framework\TestCase;
use Fintara\Tools\Calculator\Calculator;

class CalculatorTest extends TestCase {

    /** @var Calculator */
    protected $calculator;


    protected function setUp(): void {
        $this->calculator = Calculator::create();
    }

    public function testDoubleAddingSameNameFunction() {
        $this->expectException(\Exception::class);

        $this->calculator->addFunction('testfunc', function() {});
        $this->calculator->addFunction('testfunc', function() {});
    }

    public function testRemoveFunction() {
        $this->calculator->addFunction('testfunc', function() {});
        $this->calculator->removeFunction('testfunc');
        $this->calculator->addFunction('testfunc', function() {});

        $this->assertTrue(true);
    }

    public function testInvalidFunctionName() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Only letters and underscore are allowed for a name of a function");

        $this->calculator->addFunction('123', function() {});
    }

    public function testMisplacedParenthesis() {
        $this->expectException(\InvalidArgumentException::class);

        $this->calculator->calculate(')5(');
    }

    public function testMisplacedParenthesisWithFuncArgSep() {
        $this->expectException(\InvalidArgumentException::class);

        $this->calculator->calculate(',5');
    }

    public function testDivisionByZero() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Division by zero occured");

        $this->calculator->calculate('1/0');
    }

    public function testCustomFunction() {
        $this->calculator->addFunction('plus_one', function(float $num) {
            return $num + 1;
        });

        $result = $this->calculator->calculate('1 + plus_one(1)');
        $this->assertEquals(3, $result);
    }

    public function testCustomVarargFunction() {
        $this->calculator->addFunction('max', function (float ...$a) : float {
            return max(...$a);
        });

        $result = $this->calculator->calculate('1 + max(2, 4, 3) + 10');
        $this->assertEquals(15, $result);
    }

    public function testReplaceFunction() {
        $this->calculator->addFunction('plus_one', function($num) {
            return $num + 10;
        });

        $this->calculator->replaceFunction('plus_one', function($num) {
            return $num + 1;
        });

        $result = $this->calculator->calculate('1 + plus_one(1)');
        $this->assertEquals(3, $result);
    }

    public function testCalculate() {
        $this->executeCalculation('250*14.3', 3575);
        $this->executeCalculation('3^6 / 117', 6.2307692307692);
        $this->executeCalculation('(2.16 - 48.34)^-1', -0.021654395842355994);
        $this->executeCalculation('(59 - 15 + 3*6)/21', 2.952380952381);
        $this->executeCalculation('3-(4-6)', 5);
        $this->executeCalculation('9 % 4', 1);
        $this->executeCalculation('2^3 * 2 % 8 + 1', 1);
        $this->executeCalculation('sqrt(4)', 2);
        $this->executeCalculation('sqrt(sqrt(16))', 2);
        $this->executeCalculation('5sqrt(4)', 10);
        $this->executeCalculation('log(3,(3*3))', 2);
        $this->executeCalculation('10sqrt(log(3,9)^2)', 20);
    }

    private function executeCalculation($expression, $expect) {
        $result = $this->calculator->calculate($expression);
        $this->assertEquals($expect, $result, $expression);
    }
}
