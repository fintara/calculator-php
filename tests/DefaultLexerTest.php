<?php
namespace Fintara\Tools\Calculator\Tests;

require __DIR__ . '/../vendor/autoload.php';

use Fintara\Tools\Calculator\Calculator;
use Fintara\Tools\Calculator\Contracts\ILexer;
use Fintara\Tools\Calculator\DefaultLexer;

class DefaultLexerTest extends \PHPUnit_Framework_TestCase {

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

    /**
     * @expectedException           \Exception
     * @expectedExceptionMessage    There is no arithmetic expression provided
     */
    public function testGetTokenEmptyExpression() {
        $this->_lexer->getTokens(null);
    }

    /**
     * @expectedException           \InvalidArgumentException
     * @expectedExceptionMessage    Invalid expression
     */
    public function testGetTokenDoubleOperators() {
        $this->_lexer->getTokens('1++2');
    }

    /**
     * @expectedException           \InvalidArgumentException
     * @expectedExceptionMessage    Invalid float number detected (more than 1 float point?)
     */
    public function testGetTokenNonNumericValueInside() {
        $this->_lexer->getTokens('1.2.3 + 5');
    }

    /**
     * @expectedException           \InvalidArgumentException
     * @expectedExceptionMessage    Invalid float number detected (more than 1 float point?)
     */
    public function testGetTokenNonNumericValueEnd() {
        $this->_lexer->getTokens('1.2.3');
    }

    public function testGetToken() {
        $this->assertEquals(['1', '+', '2', '-', '3'],
            $this->_lexer->getTokens('1+2-3'));

        $this->assertEquals(['(', '2.16', '-', '48.34', ')', '^', '-1'],
            $this->_lexer->getTokens('(2.16 - 48.34)^-1'));

        $this->assertEquals(['-5', '*', '(', '-5', '+', '1', ')'],
            $this->_lexer->getTokens('-5*(-5+1)'));

        $this->assertEquals(['5', '*', 'sqrt', '(', '-5', ')'],
            $this->_lexer->getTokens('5 sqrt(-5)'));

        $this->assertEquals(['log', '(', '3', ',', '9',')'],
            $this->_lexer->getTokens('log(3,9)'));

        $this->assertEquals(['3', '+', '4', '*', '2', '/', '(', '1', '-', '5', ')', '^', '2', '^', '3'],
            $this->_lexer->getTokens('3 + 4 * 2 / ( 1 - 5 ) ^ 2 ^ 3'));

        $this->assertEquals(['5', '*', '(', '6', ')'],
            $this->_lexer->getTokens('5(6)'));

        $this->assertEquals(['(','5',')','*','(','6',')'],
            $this->_lexer->getTokens('(5)(6)'));
    }

}
