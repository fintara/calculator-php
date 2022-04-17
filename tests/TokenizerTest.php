<?php declare(strict_types=1);
namespace Fintara\Tools\Calculator\Tests;

use PHPUnit\Framework\TestCase;
use Fintara\Tools\Calculator\Tokenizer;
use Fintara\Tools\Calculator\TokenizerInterface;

class TokenizerTest extends TestCase {

    /**
     * @var TokenizerInterface
     */
    private $tokenizer;

    protected function setUp(): void {
        $this->tokenizer = new Tokenizer();
    }

    public function testGetTokenDoubleOperators() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid expression");

        $this->tokenizer->tokenize('1++2');
    }

    public function testGetTokenNonNumericValueInside() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid float number detected (more than 1 float point?");

        $this->tokenizer->tokenize('1.2.3 + 5');
    }

    public function testGetTokenNonNumericValueEnd() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid float number detected (more than 1 float point?");

        $this->tokenizer->tokenize('1.2.3');
    }

    public function testInvalidToken() {
        $this->expectException(\InvalidArgumentException::class);

        $this->tokenizer->tokenize('1 & 2');
    }

    public function testGetToken() {
        $functionNames = ['sqrt', 'log'];

        $cases = [
            [['1', '+', '2', '-', '3'], '1+2-3'],
            [['(', '2.16', '-', '48.34', ')', '^', '-1'], '(2.16 - 48.34)^-1'],
            [['-5', '*', '(', '-5', '+', '1', ')'], '-5*(-5+1)'],
            [['5', '*', 'sqrt', '(', '-5', ')'], '5 sqrt(-5)'],
            [['log', '(', '3', ',', '9',')'], 'log(3,9)'],
            [['3', '+', '4', '*', '2', '/', '(', '1', '-', '5', ')', '^', '2', '^', '3'], '3 + 4 * 2 / ( 1 - 5 ) ^ 2 ^ 3'],
            [['5', '*', '(', '6', ')'], '5(6)'],
            [['(','5',')','*','(','6',')'], '(5)(6)'],
        ];

        foreach ($cases as $case) {
            $this->assertEquals($case[0], $this->tokenizer->tokenize($case[1], $functionNames));
        }
    }

}
