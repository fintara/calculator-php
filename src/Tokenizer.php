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
     * @param array $customTokens Function names, variables, etc.
     * @return array Tokens of $expression
     * @throws \Exception
     */
    public function tokenize(string $expression, array $customTokens = []): array
    {
        $expression = preg_replace('/\s*/', '', $expression);
        $exprLength = strlen($expression);

        $tokens = [];
        $numberBuffer = '';
        $varBuffer = '';

        for($i = 0; $i < $exprLength; $i++) {
            $char = $expression[$i];

            if($char === Tokens::MINUS
                && ($i === 0 || $expression[$i - 1] === Tokens::PAREN_LEFT || $expression[$i - 1] === Tokens::POW
                    || $expression[$i - 1] === Tokens::ARG_SEPARATOR)) {
                $numberBuffer .= $char;
            }
            else if(ctype_digit($char) || $char === Tokens::FLOAT_POINT) {
                $numberBuffer .= $char;
            }
            else if(!ctype_digit($char) && $char !== Tokens::FLOAT_POINT && strlen($numberBuffer) > 0) {
                if(!is_numeric($numberBuffer)) {
                    throw new \InvalidArgumentException('Invalid float number detected (more than 1 float point?)');
                }

                $tokens[] = $this->parseNumber($numberBuffer);
                $numberBuffer   = '';
                $i--;
            }
            else if(in_array($char, Tokens::PARENTHESES)) {
                if($tokens && $char === Tokens::PAREN_LEFT &&
                    (is_numeric($tokens[count($tokens) - 1]) || in_array($tokens[count($tokens) - 1], Tokens::PARENTHESES))) {
                    $tokens[] = Tokens::MULT;
                }

                $tokens[] = $char;
            }
            else if(in_array($char, Tokens::OPERATORS)) {
                if($i + 1 < $exprLength && $char !== Tokens::POW
                    && in_array($expression[$i + 1], Tokens::OPERATORS)) {
                    throw new \InvalidArgumentException('Invalid expression');
                }
                $tokens[] = $char;
            }
            else if($char === Tokens::ARG_SEPARATOR) {
                $tokens[] = $char;
            }
            else {
                $found = false;
                foreach($customTokens as $name) {
                    $nameLength = strlen($name);
                    if($i + $nameLength <= $exprLength
                        && substr($expression, $i, $nameLength) === $name) {
                        if($tokens && is_numeric($tokens[count($tokens) - 1])) {
                            $tokens[] = Tokens::MULT;
                        }
                        $tokens[] = $name;
                        $i = $i + $nameLength - 1;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    throw new \InvalidArgumentException("Invalid token occurred '{$char}'");
                }
            }
        }

        if(strlen($numberBuffer) > 0) {
            if(!is_numeric($numberBuffer)) {
                throw new \InvalidArgumentException('Invalid float number detected (more than 1 float point?)');
            }

            $tokens[] = $this->parseNumber($numberBuffer);
        }

        return $tokens;
    }

    /**
     * @param string $buffer
     * @return int | float
     */
    private function parseNumber(string $buffer) {
        if (strstr($buffer, Tokens::FLOAT_POINT) !== false) {
            return floatval($buffer);
        }

        return intval($buffer);
    }
}
