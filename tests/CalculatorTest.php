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

    public function setUp() {
        $this->_lexer = new DefaultLexer();
        $this->_calculator = new Calculator($this->_lexer);
    }

    public function testGetToken() {
        $this->assertEquals(['1', '+', '2', '-', '3'],
            $this->_lexer->getTokens('1+2-3'));

        $this->assertEquals(['(', '2.16', '-', '48.34', ')', '^', '-1'],
            $this->_lexer->getTokens('(2.16 - 48.34)^-1'));

        $this->assertEquals(['-5', '*', '(', '-5', '+', '1', ')'],
            $this->_lexer->getTokens('-5*(-5+1)'));

        $this->assertEquals(['sqrt', '(', '-5', ')'],
            $this->_lexer->getTokens('sqrt(-5)'));

        $this->assertEquals(['3', '+', '4', '*', '2', '/', '(', '1', '-', '5', ')', '^', '2', '^', '3'],
            $this->_lexer->getTokens('3 + 4 * 2 / ( 1 - 5 ) ^ 2 ^ 3'));
    }

    public function testGetReversedPolishNotation() {
        $this->executeGetReversePolishNotation('1+2', '12+');
        $this->executeGetReversePolishNotation('1^-2', '1-2^');
        $this->executeGetReversePolishNotation('1^(-2)', '1-2^');
        $this->executeGetReversePolishNotation('1+2-3', '12+3-');
        $this->executeGetReversePolishNotation('3 + 4 * 2 / ( 1 - 5 ) ^ 2 ^ 3', '342*15-23^^/+');
        $this->executeGetReversePolishNotation('3 + 4 * 2 / ( 1 - 5 )', '342*15-/+');
        $this->executeGetReversePolishNotation('5 + ((1 + 2) * 4) - 3', '512+4*+3-');
    }

    public function testCalculate() {
        $this->executeCalculation('250*14.3', 3575);
        $this->executeCalculation('3^6 / 117', 6.23077);
        $this->executeCalculation('(2.16 - 48.34)^-1', -0.02165);
        $this->executeCalculation('(59 - 15 + 3*6)/21', 2.95238);
        $this->executeCalculation('3-(4-6)', 5);
        $this->executeCalculation('9 mod 4', 1);
        $this->executeCalculation('2^3 * 2 mod 8 + 1', 1);
    }

    public function testFormatNumber() {
        $limit = 5;

        $this->assertEquals('14.13265', $this->_calculator->formatNumber(14.132646, $limit));
        $this->assertEquals('14.13264', $this->_calculator->formatNumber(14.132644, $limit));
        $this->assertEquals('14.13265', $this->_calculator->formatNumber(14.132645, $limit));
        $this->assertEquals('12345', $this->_calculator->formatNumber(12345, $limit));
        $this->assertEquals('12345', $this->_calculator->formatNumber(12345.0000000, $limit));
        $this->assertEquals('12345', $this->_calculator->formatNumber(12345.0000001, $limit));
        $this->assertEquals('12345.1', $this->_calculator->formatNumber(12345.100001, $limit));
    }

    private function executeCalculation($expression, $expect, $limit = 5) {
        $this->_calculator->setExpression($expression);
        $this->assertEquals($expect, $this->_calculator->calculate($limit));
    }

    private function executeGetReversePolishNotation($expression, $expect) {
        $this->_calculator->setExpression($expression);
        $queue = $this->_calculator->getReversePolishNotation($this->_lexer->getTokens($expression));

        $result = '';
        while($queue->count() > 0) {
            $result .= $queue->dequeue();
        }

        $this->assertEquals($expect, $result);
    }
}
 