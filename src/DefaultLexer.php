<?php

namespace Fintara\Tools\Calculator;

use Fintara\Tools\Calculator\Contracts\ILexer;

class DefaultLexer implements ILexer {

    /**
     * @var array Possible brackets
     */
    private $_brackets;

    /**
     * @var array Possible operators
     */
    private $_operators;

    /**
     * @var array Defined functions.
     */
    private $_functions;

    /**
     * @var string Function separator
     */
    private $_func_arg_separator;

    /**
     * Finds all tokens (digits, operators, functions, etc.) in the current
     * arithmetic expression.
     *
     * @param  string $expression Expression to get tokens for
     * @return array Array of tokens.
     * @throws \Exception If no expression is provided.
     */
    public function getTokens($expression) {
        if(!$expression) {
            throw new \Exception('There is no arithmetic expression provided');
        }

        $tokens = [];
        $number = '';

        for($i = 0; $i < strlen($expression); $i++) {
            if($expression[$i] === '-'
                && ($i === 0 || $expression[$i - 1] === '(' || $expression[$i - 1] === '^'
                    || $expression[$i - 1] === $this->_func_arg_separator)) {
                $number .= $expression[$i];
            }
            else if(ctype_digit($expression[$i]) || $expression[$i] === '.') {
                $number .= $expression[$i];
            }
            else if(!ctype_digit($expression[$i]) && $expression[$i] !== '.' && strlen($number) > 0) {
                if(!is_numeric($number)) {
                    throw new \InvalidArgumentException('Invalid float number detected (more than 1 float point?)');
                }

                $tokens[] = $number;
                $number   = '';
                $i--;
            }
            else if(in_array($expression[$i], $this->_brackets)) {
                if($tokens && $expression[$i] === '(' && is_numeric($tokens[count($tokens) - 1])) {
                    $tokens[] = '*';
                }

                $tokens[] = $expression[$i];
            }
            else if($i + 3 < strlen($expression) && substr($expression, $i, 3) === 'mod') {
                $tokens[] = 'mod';
            }
            else if(in_array($expression[$i], $this->_operators)) {
                if($i + 1 < strlen($expression) && $expression[$i] !== '^'
                    && in_array($expression[$i + 1], $this->_operators)) {
                    throw new \InvalidArgumentException('Invalid expression');
                }
                $tokens[] = $expression[$i];
            }
            else if($expression[$i] === $this->_func_arg_separator) {
                $tokens[] = $expression[$i];
            }
            else if(count($this->_functions) > 0) {
                foreach($this->_functions as $functionName => $function) {
                    if($i + strlen($functionName) < strlen($expression)
                        && substr($expression, $i, strlen($functionName)) === $functionName) {
                        if($tokens && is_numeric($tokens[count($tokens) - 1])) {
                            $tokens[] = '*';
                        }
                        $tokens[] = $functionName;
                        $i = $i + strlen($functionName) - 1;
                    }
                }
            }
            else {
                throw new \InvalidArgumentException("Invalid token occurred ({$expression[$i]})");
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

    public function setBrackets(array &$brackets)
    {
        $this->_brackets = &$brackets;
    }

    public function setOperators(array &$operators)
    {
        $this->_operators = &$operators;
    }

    public function setFunctions(array &$functions)
    {
        $this->_functions = &$functions;
    }

    public function setFunctionArgSeparator($separator)
    {
        $this->_func_arg_separator = $separator;
    }
}