<?php
namespace Fintara\Tools\Calculator;

use Fintara\Tools\Calculator\Contracts\ILexer;

class Calculator {
    const FUNC_ARG_SEPARATOR = ',';

    /**
     * @var array Possible brackets
     */
    private $_brackets  = ['(', ')'];

    /**
     * @var array Possible operators
     */
    private $_operators = ['+', '-', '*', '/', '^', 'mod'];

    /**
     * @var array Defined functions.
     */
    private $_functions = [];

    /**
     * @var string Current arithmetic expression.
     */
    private $_expression;

    /**
     * @var ILexer Lexer who tokenizes the expression.
     */
    private $_lexer;

    /**
     * Constructor.
     * Sets expression if provided.
     * Sets default functions: sqrt(n), ln(n), log(a,b).
     * @param ILexer $lexer
     */
    public function __construct(ILexer $lexer) {
        $this->_lexer = &$lexer;

        $this->_lexer->setOperators($this->_operators);
        $this->_lexer->setBrackets($this->_brackets);
        $this->_lexer->setFunctions($this->_functions);
        $this->_lexer->setFunctionArgSeparator(self::FUNC_ARG_SEPARATOR);

        $this->addFunction('sqrt', function($x) { return sqrt($x); }, 1);
        $this->addFunction('log', function($base, $arg) { return log($arg, $base); }, 2);
    }

    /**
     * Sets current arithmetic expression.
     *
     * @param  string $expression Arithmetic expression.
     * @return Calculator
     */
    public function setExpression($expression) {
        $this->_expression = str_replace(' ', '', $expression);

        return $this;
    }

    /**
     * @param  string   $name Name of the function (as in arithmetic expressions).
     * @param  callable $function Interpretation of this function.
     * @param  int      $paramsCount Number of parameters.
     * @throws \InvalidArgumentException
     */
    public function addFunction($name, callable $function, $paramsCount) {
        $name = strtolower(trim($name));

        if(!ctype_alpha(str_replace('_', '', $name))) {
            throw new \InvalidArgumentException('Only letters and underscore are allowed for a name of a function');
        }
        else if(in_array($name, $this->_operators)) {
            throw new \InvalidArgumentException('Cannot rewrite an operator');
        }



        if(array_key_exists($name, $this->_functions)) {
            trigger_error("Function with name ($name) has been already added and will be rewritten", E_USER_NOTICE);
        }

        $this->_functions[$name] = [
            'func'        => $function,
            'paramsCount' => $paramsCount,
        ];
    }

    /**
     * @param  string $name Name of function.
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function removeFunction($name) {
        if(!array_key_exists($name, $this->_functions)) {
            throw new \InvalidArgumentException('There is no function with this name');
        }

        unset($this->_functions[$name]);
    }

    /**
     * Rearranges tokens according to RPN (Reverse Polish Notation) or
     * also known as Postfix Notation.
     *
     * @param  array $tokens
     * @return \SplQueue
     * @throws \InvalidArgumentException
     */
    public function getReversePolishNotation(array $tokens) {
        $queue = new \SplQueue();
        $stack = new \SplStack();

        $tokensCount = count($tokens);
        for($i = 0; $i < $tokensCount; $i++) {
            if(is_numeric($tokens[$i])) {
                // (string + 0) converts to int or float
                $queue->enqueue($tokens[$i] + 0);
            }
            else if(array_key_exists($tokens[$i], $this->_functions)) {
                $stack->push($tokens[$i]);
            }
            else if($tokens[$i] === self::FUNC_ARG_SEPARATOR) {
                // checking whether stack contains left parenthesis (dirty hack)
                if(substr_count($stack->serialize(), '(') === 0) {
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
                // checking whether stack contains left parenthesis (dirty hack)
                if(substr_count($stack->serialize(), '(') === 0) {
                    throw new \InvalidArgumentException('Parenthesis are misplaced');
                }

                while($stack->top() != '(') {
                    $queue->enqueue($stack->pop());
                }

                $stack->pop();

                if($stack->count() > 0 && array_key_exists($stack->top(), $this->_functions)) {
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
     * Calculates tokens ordered in RPN.
     *
     * @param  \SplQueue $queue
     * @return int|float Result of the calculation.
     * @throws \InvalidArgumentException
     */
    public function calculateFromRPN(\SplQueue $queue) {
        $stack = new \SplStack();

        while($queue->count() > 0) {
            $currentToken = $queue->dequeue();
            if(is_numeric($currentToken)) {
                $stack->push($currentToken);
            }
            else {
                if(in_array($currentToken, $this->_operators)) {
                    if($stack->count() < 2) {
                        throw new \InvalidArgumentException('Invalid expression');
                    }
                    $stack->push($this->executeOperator($currentToken, $stack->pop(), $stack->pop()));
                }
                else if(array_key_exists($currentToken, $this->_functions)) {
                    if($stack->count() < $this->_functions[$currentToken]['paramsCount']) {
                        throw new \InvalidArgumentException('Invalid expression');
                    }

                    $params = [];
                    for($i = 0; $i < $this->_functions[$currentToken]['paramsCount']; $i++) {
                        $params[] = $stack->pop();
                    }

                    $stack->push($this->executeFunction($currentToken, $params));
                }
            }
        }

        if($stack->count() === 1) {
            return $stack->pop();
        }

        throw new \InvalidArgumentException('Invalid expression');
    }

    /**
     * Calculates the current arithmetic expression.
     *
     * @return int|float Result of the calculation.
     */
    public function calculate() {
        $tokens = $this->_lexer->getTokens($this->_expression);
        $rpn    = $this->getReversePolishNotation($tokens);

        $result = $this->calculateFromRPN($rpn);

        return $result;
    }

    /**
     * @param  string $operator A valid operator.
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function isOperatorLeftAssociative($operator) {
        if(!in_array($operator, $this->_operators)) {
            throw new \InvalidArgumentException("Cannot check association of $operator operator");
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
            throw new \InvalidArgumentException("Cannot check precedence of $operator operator");
        }

        if($operator === '^') {
            return 6;
        }
        else if($operator === '*' || $operator === '/') {
            return 4;
        }
        else if($operator === 'mod') {
            return 2;
        }
        return 1;
    }

    /**
     * @param  string    $operator A valid operator.
     * @param  int|float $a First value.
     * @param  int|float $b Second value.
     * @return int|float Result.
     * @throws \InvalidArgumentException
     */
    private function executeOperator($operator, $a, $b) {
        if($operator === '+') {
            return $a + $b;
        }
        else if($operator === '-') {
            return $b - $a;
        }
        else if($operator === 'mod') {
            return $b % $a;
        }
        else if($operator === '*') {
            return $a * $b;
        }
        else if($operator === '/') {
            if($a === 0) {
                throw new \InvalidArgumentException('Division by zero occured');
            }
            return $b / $a;
        }
        else if($operator === '^') {
            return pow($b, $a);
        }

        throw new \InvalidArgumentException('Unknown operator provided');
    }

    /**
     * @param  string $functionName
     * @param  array  $params
     * @return int|float Result.
     */
    private function executeFunction($functionName, $params) {
        return call_user_func_array($this->_functions[$functionName]['func'], array_reverse($params));
    }
}
