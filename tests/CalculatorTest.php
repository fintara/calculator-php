<?php
namespace Fintara\Tools\Calculator\Tests;

require __DIR__ . '/../vendor/autoload.php';

use Fintara\Tools\Calculator\Calculator;
use Fintara\Tools\Calculator\Contracts\ILexer;
use Fintara\Tools\Calculator\DefaultLexer;

class CalculatorTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Calculator
     */
    protected $_calculator;

    /**
     * @var ILexer
     */
    protected $_lexer;

    /**
     * @var array
     */
    protected $_errors;

    public function setUp() {
        $this->_lexer = new DefaultLexer();
        $this->_calculator = new Calculator($this->_lexer);

        $this->_errors = [];
        set_error_handler([$this, 'triggedErrorHandler']);
    }

    public function triggedErrorHandler($errno, $errstr, $errfile, $errline, $errcontext) {
        $this->_errors[] = compact("errno", "errstr", "errfile",
            "errline", "errcontext");
    }

    public function assertError($errstr, $errno) {
        foreach ($this->_errors as $error) {
            if ($error["errstr"] === $errstr
                && $error["errno"] === $errno) {
                return;
            }
        }

        $this->fail('Error with level ' . $errno .
            ' and message ' . $errstr . ' not found in ',
            var_export($this->_errors, true));
    }

    public function assertNoError($errstr, $errno) {
        foreach ($this->_errors as $error) {
            if ($error["errstr"] === $errstr
                && $error["errno"] === $errno) {
                $this->fail('Error with level ' . $errno .
                    ' and message ' . $errstr . ' not found in ',
                    var_export($this->_errors, true));
            }
        }
    }

    public function testDoubleAddingSameNameFunction() {
        $this->_calculator->addFunction('testfunc', function() {}, 0);
        $this->_calculator->addFunction('testfunc', function() {}, 0);

        $this->assertError('Function with name (testfunc) has been already added and will be rewritten',
            E_USER_NOTICE);
    }

    public function testRemoveFunction() {
        $this->_calculator->addFunction('testfunc', function() {}, 0);
        $this->_calculator->removeFunction('testfunc');
        $this->_calculator->addFunction('testfunc', function() {}, 0);

        $this->assertNoError('Function with name (testfunc) has been already added and will be rewritten',
            E_USER_NOTICE);
    }

    /**
     * @expectedException           \InvalidArgumentException
     * @expectedExceptionMessage    There is no function with this name (nosuchfunction)
     */
    public function testRemoveNonexistentFunction() {
        $this->_calculator->removeFunction('nosuchfunction');
    }

    /**
     * @expectedException           \InvalidArgumentException
     * @expectedExceptionMessage    Only letters and underscore are allowed for a name of a function
     */
    public function testInvalidFunctionName() {
        $this->_calculator->addFunction('123', function() {}, 0);
    }

    /**
     * @expectedException           \InvalidArgumentException
     * @expectedExceptionMessage    Cannot rewrite an operator
     */
    public function testInvalidFunctionNameAsOperator() {
        $this->_calculator->addFunction('mod', function() {}, 0);
    }

    /**
     * @expectedException           \InvalidArgumentException
     * @expectedExceptionMessage    Parenthesis are misplaced
     */
    public function testMisplacedParenthesis() {
        $this->_calculator->getReversePolishNotation([')','5','(']);
    }

    /**
     * @expectedException           \InvalidArgumentException
     * @expectedExceptionMessage    Parenthesis are misplaced
     */
    public function testMisplacedParenthesisWithFuncArgSep() {
        $this->_calculator->getReversePolishNotation([',','5','']);
    }

    /**
     * @expectedException           \InvalidArgumentException
     * @expectedExceptionMessage    Division by zero occured
     */
    public function testDivisionByZero() {
        $this->_calculator->setExpression('1/0');
        $this->_calculator->calculate();
    }

    public function testCalculate() {
        $this->executeCalculation('250*14.3', 3575);
        $this->executeCalculation('3^6 / 117', 6.2307692307692);
        $this->executeCalculation('(2.16 - 48.34)^-1', -0.021654395842355994);
        $this->executeCalculation('(59 - 15 + 3*6)/21', 2.952380952381);
        $this->executeCalculation('3-(4-6)', 5);
        $this->executeCalculation('9 mod 4', 1);
        $this->executeCalculation('2^3 * 2 mod 8 + 1', 1);
        $this->executeCalculation('sqrt(4)', 2);
        $this->executeCalculation('5sqrt(4)', 10);
        $this->executeCalculation('log(3,(3*3))', 2);
        $this->executeCalculation('10sqrt(log(3,9)^2)', 20);
    }

    private function executeCalculation($expression, $expect) {
        $this->_calculator->setExpression($expression);
        $this->assertEquals($expect, $this->_calculator->calculate());
    }
}
 