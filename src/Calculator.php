<?php
namespace Fintara\Tools\Calculator;

class Calculator {

    /**
     * @var string Current arithmetic expression.
     */
    private $_expression;

    /**
     * @var array User-defined functions.
     */
    private $_userFunctions = [];

    /**
     * Constructor.
     * Sets expression if provided.
     * Sets default functions: sqrt(n), ln(n), log(a,b).
     * @param null|string $expression Arithmetic expression (not required).
     */
    public function __construct($expression = null) {
        if($expression) {
            $this->setExpression($expression);
        }

        $this->addFunction('sqrt', function($x) { return sqrt($x); });
        $this->addFunction('ln', function($x) { return log10($x); });
        $this->addFunction('log', function($base, $arg) { return log($arg, $base); });
    }

    /**
     * Sets current arithmetic expression.
     *
     * @param string $expression Arithmetic expression.
     */
    public function setExpression($expression) {
        $this->_expression = str_replace(' ', '', $expression);
    }

    /**
     * @param string   $name Name of the function (as in arithmetic expressions).
     * @param callable $function Interpretation of this function.
     */
    public function addFunction($name, callable $function) {
        if(array_key_exists($name, $this->_userFunctions)) {
            trigger_error("Function with this name ($name) has been already added", E_USER_NOTICE);
        }

        $this->_userFunctions[$name] = $function;
    }

    /**
     * @param  string $name Name of function.
     * @throws \InvalidArgumentException
     */
    public function removeFunction($name) {
        if(array_key_exists($name, $this->_userFunctions)) {
            unset($this->_userFunctions[$name]);
        }
        else {
            throw new \InvalidArgumentException('There is no function with this name');
        }
    }

    /**
     * Finds all tokens (digits, operators, functions, etc.) in the current
     * arithmetic expression.
     *
     * @return array Array of tokens.
     * @throws \Exception If no expression is provided.
     */
    public function getTokens() {
        if(!$this->_expression) {
            throw new \Exception('There is no arithmetic expression provided');
        }

        $brackets  = ['(', ')'];
        $operators = ['+', '-', '*', '/', '^'];

        $tokens = [];
        $number = '';

        for($i = 0; $i < strlen($this->_expression); $i++) {
            if($this->_expression[$i] === '-'
                && ($i === 0 || $this->_expression[$i - 1] === '(' || $this->_expression[$i - 1] === ',')) {
                $number .= $this->_expression[$i];
            }
            else if(ctype_digit($this->_expression[$i]) || $this->_expression[$i] === '.') {
                $number .= $this->_expression[$i];
            }
            else if(!ctype_digit($this->_expression[$i]) && $this->_expression[$i] !== '.' && strlen($number) > 0) {
                if(substr_count($number, '.') > 1) {
                    throw new \InvalidArgumentException('Invalid float number detected (more than 1 float point)');
                }

                $tokens[] = $number;
                $number   = '';
                $i--;
            }
            else if(in_array($this->_expression[$i], $brackets)) {
                $tokens[] = $this->_expression[$i];
            }
            else if(in_array($this->_expression[$i], $operators) && !in_array($this->_expression[$i - 1], $operators)) {
                $tokens[] = $this->_expression[$i];
            }
            else if($this->_expression[$i] === ',') {
                $tokens[] = $this->_expression[$i];
            }
            else if(count($this->_userFunctions) > 0) {
                foreach($this->_userFunctions as $functionName => $function) {
                    if($i + strlen($functionName) < strlen($this->_expression)
                        && substr($this->_expression, $i, strlen($functionName)) === $functionName) {
                        $tokens[] = $functionName;
                    }
                }
            }
        }

        if(strlen($number) > 0) {
            if(substr_count($number, '.') > 1) {
                throw new \InvalidArgumentException('Invalid float number detected (more than 1 float point)');
            }

            $tokens[] = $number;
        }

        return $tokens;
    }


    /**
     * Calculates the current arithmetic expression.
     *
     * @return int|float Result of the calculation.
     * @throws \Exception
     */
    public function calculate() {
        if(!$this->_expression) {
            throw new \Exception('There is no arithmetic expression provided');
        }
    }
}