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

        $this->_calculator->setExpression('-5*(-5+1)');
        $this->assertEquals(['-5', '*', '(', '-5', '+', '1', ')'], $this->_calculator->getTokens());

        $this->_calculator->setExpression('sqrt(-5)');
        $this->assertEquals(['sqrt', '(', '-5', ')'], $this->_calculator->getTokens());
    }

    public function testGetReversedPolishNotation() {
        // todo
    }
}
 