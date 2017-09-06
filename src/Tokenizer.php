<?php
/**
 * Created by PhpStorm.
 * User: Tsvetan Ovedenski
 * Date: 06/09/2017
 * Time: 11:52
 */

namespace Fintara\Tools\Calculator;

class Tokenizer implements TokenizerInterface
{
    /**
     * @param string $expression
     * @param array $functionNames
     * @return array Tokens of $expression
     * @throws \Exception
     */
    public function tokenize(string $expression, array $functionNames = []): array
    {
        $exprLength = strlen($expression);

        $tokens = [];
        $numberBuffer = '';

        for($i = 0; $i < $exprLength; $i++) {
            if($expression[$i] === Tokens::MINUS
                && ($i === 0 || $expression[$i - 1] === Tokens::PAREN_LEFT || $expression[$i - 1] === Tokens::POW
                    || $expression[$i - 1] === Tokens::ARG_SEPARATOR)) {
                $numberBuffer .= $expression[$i];
            }
            else if(ctype_digit($expression[$i]) || $expression[$i] === Tokens::FLOAT_POINT) {
                $numberBuffer .= $expression[$i];
            }
            else if(!ctype_digit($expression[$i]) && $expression[$i] !== Tokens::FLOAT_POINT && strlen($numberBuffer) > 0) {
                if(!is_numeric($numberBuffer)) {
                    throw new \InvalidArgumentException('Invalid float number detected (more than 1 float point?)');
                }

                $tokens[] = $numberBuffer;
                $numberBuffer   = '';
                $i--;
            }
            else if(in_array($expression[$i], Tokens::PARENTHESES)) {
                if($tokens && $expression[$i] === Tokens::PAREN_LEFT &&
                    (is_numeric($tokens[count($tokens) - 1]) || in_array($tokens[count($tokens) - 1], Tokens::PARENTHESES))) {
                    $tokens[] = Tokens::MULT;
                }

                $tokens[] = $expression[$i];
            }
            else if(in_array($expression[$i], Tokens::OPERATORS)) {
                if($i + 1 < $exprLength && $expression[$i] !== Tokens::POW
                    && in_array($expression[$i + 1], Tokens::OPERATORS)) {
                    throw new \InvalidArgumentException('Invalid expression');
                }
                $tokens[] = $expression[$i];
            }
            else if($expression[$i] === Tokens::ARG_SEPARATOR) {
                $tokens[] = $expression[$i];
            }
            else if(count($functionNames) > 0) {
                foreach($functionNames as $functionName) {
                    $nameLength = strlen($functionName);
                    if($i + $nameLength < $exprLength
                        && substr($expression, $i, $nameLength) === $functionName) {
                        if($tokens && is_numeric($tokens[count($tokens) - 1])) {
                            $tokens[] = Tokens::MULT;
                        }
                        $tokens[] = $functionName;
                        $i = $i + $nameLength - 1;
                    }
                }
            }
            else {
                throw new \InvalidArgumentException("Invalid token occurred ({$expression[$i]})");
            }
        }

        if(strlen($numberBuffer) > 0) {
            if(!is_numeric($numberBuffer)) {
                throw new \InvalidArgumentException('Invalid float number detected (more than 1 float point?)');
            }

            $tokens[] = $numberBuffer;
        }

        return $tokens;
    }
}
