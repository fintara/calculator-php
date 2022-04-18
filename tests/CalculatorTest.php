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

    public function testCustomVarargFunctionNested() {
        $this->calculator->addFunction('max', function (float ...$a) : float {
            return max(...$a);
        });

        $result = $this->calculator->calculate('1 + max(5, max(2, sqrt(81), max(4, 6, 10))) + 10');
        $this->assertEquals(21, $result);
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

    public function testCalculate001() {
        $this->executeCalculation('250*14.3', 3575);
    }

    public function testCalculate002() {
        $this->executeCalculation('3^6 / 117', 6.2307692307692);
    }

    public function testCalculate003() {
        $this->executeCalculation('(2.16 - 48.34)^-1', -0.021654395842355994);
    }

    public function testCalculate004() {
        $this->executeCalculation('(59 - 15 + 3*6)/21', 2.952380952381);
    }

    public function testCalculate005() {
        $this->executeCalculation('3-(4-6)', 5);
    }

    public function testCalculate006() {
        $this->executeCalculation('9 % 4', 1);
    }

    public function testCalculate007() {
        $this->executeCalculation('2^3 * 2 % 8 + 1', 1);
    }

    public function testCalculate008() {
        $this->executeCalculation('sqrt(4)', 2);
    }

    public function testCalculate009() {
        $this->executeCalculation('sqrt(sqrt(16))', 2);
    }

    public function testCalculate010() {
        $this->executeCalculation('5sqrt(4)', 10);
    }

    public function testCalculate011() {
        $this->executeCalculation('log(3,(3*3))', 2);
    }

    public function testCalculate012() {
        $this->executeCalculation('10sqrt(log(3,9)^2)', 20);
    }

    public function testCalculate013() {
        $this->executeCalculation('sqrt(1) + sqrt(log(3,9)) + sqrt(log(sqrt(9),sqrt(81)))', 3.82842712474619);
    }

    private function executeCalculation($expression, $expect) {
        $result = $this->calculator->calculate($expression);
        $this->assertEquals($expect, $result, $expression);
    }
}
