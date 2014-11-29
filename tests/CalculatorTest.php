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

    public function testCalculate() {
        $this->executeCalculation('250*14.3', 3575);
        $this->executeCalculation('3^6 / 117', 6.2307692307692);
        $this->executeCalculation('(2.16 - 48.34)^-1', -0.021654395842355994);
        $this->executeCalculation('(59 - 15 + 3*6)/21', 2.952380952381);
        $this->executeCalculation('3-(4-6)', 5);
        $this->executeCalculation('9 mod 4', 1);
        $this->executeCalculation('2^3 * 2 mod 8 + 1', 1);
    }

    private function executeCalculation($expression, $expect, $limit = 5) {
        $this->_calculator->setExpression($expression);
        $this->assertEquals($expect, $this->_calculator->calculate($limit));
    }
}
 