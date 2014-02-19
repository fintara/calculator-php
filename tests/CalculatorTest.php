<?php
namespace Fintara\Tools\Calculator\Tests;

require __DIR__ . '/../vendor/autoload.php';

use Fintara\Tools\Calculator\Calculator;

class CalculatorTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Calculator
     */
    protected $_calculator;

    public function setUp() {
        $this->_calculator = new Calculator();
    }

    public function testGetToken() {
        $this->_calculator->setExpression('1+2-3');
        $this->assertEquals(['1', '+', '2', '-', '3'], $this->_calculator->getTokens());

        $this->_calculator->setExpression('(2.16 - 48.34)^-1');
        $this->assertEquals(['(', '2.16', '-', '48.34', ')', '^', '-1'], $this->_calculator->getTokens());

        $this->_calculator->setExpression('-5*(-5+1)');
        $this->assertEquals(['-5', '*', '(', '-5', '+', '1', ')'], $this->_calculator->getTokens());

        $this->_calculator->setExpression('sqrt(-5)');
        $this->assertEquals(['sqrt', '(', '-5', ')'], $this->_calculator->getTokens());

        $this->_calculator->setExpression('3 + 4 * 2 / ( 1 - 5 ) ^ 2 ^ 3');
        $this->assertEquals(['3', '+', '4', '*', '2', '/', '(', '1', '-', '5', ')', '^', '2', '^', '3'],
            $this->_calculator->getTokens());
    }

    public function testGetReversedPolishNotation() {
        $this->_calculator->setExpression('1+2');
        $queue = $this->_calculator->getReversedPolishNotation($this->_calculator->getTokens());
        $this->assertEquals('12+', $this->queueToString($queue));

        $this->_calculator->setExpression('1^-2');
        $queue = $this->_calculator->getReversedPolishNotation($this->_calculator->getTokens());
        $this->assertEquals('1-2^', $this->queueToString($queue));

        $this->_calculator->setExpression('1^(-2)');
        $queue = $this->_calculator->getReversedPolishNotation($this->_calculator->getTokens());
        $this->assertEquals('1-2^', $this->queueToString($queue));

        $this->_calculator->setExpression('1+2-3');
        $queue = $this->_calculator->getReversedPolishNotation($this->_calculator->getTokens());
        $this->assertEquals('12+3-', $this->queueToString($queue));

        $this->_calculator->setExpression('3 + 4 * 2 / ( 1 - 5 ) ^ 2 ^ 3');
        $queue = $this->_calculator->getReversedPolishNotation($this->_calculator->getTokens());
        $this->assertEquals('342*15-23^^/+', $this->queueToString($queue));

        $this->_calculator->setExpression('3 + 4 * 2 / ( 1 - 5 )');
        $queue = $this->_calculator->getReversedPolishNotation($this->_calculator->getTokens());
        $this->assertEquals('342*15-/+', $this->queueToString($queue));

        $this->_calculator->setExpression('5 + ((1 + 2) * 4) - 3');
        $queue = $this->_calculator->getReversedPolishNotation($this->_calculator->getTokens());
        $this->assertEquals('512+4*+3-', $this->queueToString($queue));
    }

    private function queueToString(\SplQueue $queue) {
        $result = '';
        while($queue->count() > 0)
            $result .= $queue->dequeue();

        return $result;
    }
}
 