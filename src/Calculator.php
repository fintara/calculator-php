<?php
namespace Fintara\Tools\Calculator;

class Calculator {
    const FUNC_ARG_SEPARATOR = ',';

    /**
     * @var array Possible brackets
     */
    private $_brackets  = ['(', ')'];

    /**
     * @var array Possible operators
     */
    private $_operators = ['+', '-', '*', '/', '^'];

    /**
     * @var array Defined functions.
     */
    private $_functions = [];

    /**
     * @var string Current arithmetic expression.
     */
    private $_expression;

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
     * @return Calculator
     */
    public function setExpression($expression) {
        $this->_expression = str_replace(' ', '', $expression);

        return $this;
    }

    /**
     * @param string   $name Name of the function (as in arithmetic expressions).
     * @param callable $function Interpretation of this function.
     */
    public function addFunction($name, callable $function) {
        if(array_key_exists($name, $this->_functions)) {
            trigger_error("Function with this name ($name) has been already added", E_USER_NOTICE);
        }

        $this->_functions[$name] = $function;
    }

    /**
     * @param  string $name Name of function.
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function removeFunction($name) {
        if(array_key_exists($name, $this->_functions)) {
            unset($this->_functions[$name]);
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

        $tokens = [];
        $number = '';

        for($i = 0; $i < strlen($this->_expression); $i++) {
            if($this->_expression[$i] === '-'
                && ($i === 0 || $this->_expression[$i - 1] === '(' || $this->_expression[$i - 1] === self::FUNC_ARG_SEPARATOR)) {
                $number .= $this->_expression[$i];
            }
            else if(ctype_digit($this->_expression[$i]) || $this->_expression[$i] === '.') {
                $number .= $this->_expression[$i];
            }
            else if(!ctype_digit($this->_expression[$i]) && $this->_expression[$i] !== '.' && strlen($number) > 0) {
                if(!is_numeric($number)) {
                    throw new \InvalidArgumentException('Invalid float number detected (more than 1 float point?)');
                }

                $tokens[] = $number;
                $number   = '';
                $i--;
            }
            else if(in_array($this->_expression[$i], $this->_brackets)) {
                $tokens[] = $this->_expression[$i];
            }
            else if(in_array($this->_expression[$i], $this->_operators)
                && !in_array($this->_expression[$i - 1], $this->_operators)) {
                $tokens[] = $this->_expression[$i];
            }
            else if($this->_expression[$i] === self::FUNC_ARG_SEPARATOR) {
                $tokens[] = $this->_expression[$i];
            }
            else if(count($this->_functions) > 0) {
                foreach($this->_functions as $functionName => $function) {
                    if($i + strlen($functionName) < strlen($this->_expression)
                        && substr($this->_expression, $i, strlen($functionName)) === $functionName) {
                        $tokens[] = $functionName;
                    }
                }
            }
        }

        if(strlen($number) > 0) {
            if(!is_numeric($number)) {
                throw new \InvalidArgumentException('Invalid float number detected (more than 1 float point?)');
            }

            $tokens[] = $number;
        }

        return $tokens;
    }

    /**
     * Rearranges tokens according to RPN (Reverse Polish Notation) or
     * also known as Postfix Notation.
     *
     * @param  array $tokens
     * @return \SplQueue
     * @throws \InvalidArgumentException
     */
    public function getReversedPolishNotation(array $tokens) {
        $queue = new \SplQueue();
        $stack = new \SplStack();

        $tokensCount = count($tokens);
        for($i = 0; $i < $tokensCount; $i++) {
            if(is_numeric($tokens[$i])) {
                $queue->enqueue($tokens[$i] + 0);
            }
            else if(array_key_exists($tokens[$i], $this->_functions)) {
                $stack->push($tokens[$i]);
            }
            else if($tokens[$i] === self::FUNC_ARG_SEPARATOR) {
                if(!$stack->offsetExists('(')) {
                    throw new \InvalidArgumentException('Parenthesis are misplaced');
                }

                while($stack->top() != '(') {
                    $queue->enqueue($stack->pop());
                }
            }
            else if(in_array($tokens[$i], $this->_operators)) {
                while($stack->count() > 0 && in_array($stack->top(), $this->_operators)
                    && (($this->isOperatorLeftAssociative($tokens[$i])
                        && $this->getOperatorPrecedence($tokens[$i]) === $this->getOperatorPrecedence($stack->top()))
                    || ($this->getOperatorPrecedence($tokens[$i]) < $this->getOperatorPrecedence($stack->top())))) {
                    $queue->enqueue($stack->pop());
                }

                $stack->push($tokens[$i]);
            }
            else if($tokens[$i] === '(') {
                $stack->push('(');
            }
            else if($tokens[$i] === ')') {
                if(!$stack->offsetExists('(')) {
                    throw new \InvalidArgumentException('Parenthesis are misplaced');
                }

                while($stack->top() != '(') {
                    $queue->enqueue($stack->pop());
                }

                $stack->pop();

                if(array_key_exists($stack->top(), $this->_functions)) {
                    $queue->enqueue($stack->pop());
                }
            }
        }

        while($stack->count() > 0) {
            $queue->enqueue($stack->pop());
        }

        return $queue;
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

        $tokens = $this->getTokens();
        $rpn    = $this->getReversedPolishNotation($tokens);
    }

    /**
     * @param  string $operator A valid operator.
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function isOperatorLeftAssociative($operator) {
        if(!in_array($operator, $this->_operators)) {
            throw new \InvalidArgumentException('Cannot check association of ' . $operator . ' operator');
        }

        if($operator === '^')
            return false;

        return true;
    }

    /**
     * @param  string $operator A valid operator.
     * @return int
     * @throws \InvalidArgumentException
     */
    private function getOperatorPrecedence($operator) {
        if(!in_array($operator, $this->_operators)) {
            throw new \InvalidArgumentException('Cannot check association of ' . $operator . ' operator');
        }

        if($operator === '^') {
            return 3;
        }
        else if($operator === '*' || $operator === '/') {
            return 2;
        }
        return 1;
    }
}

