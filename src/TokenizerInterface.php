<?php
/**
 * Created by PhpStorm.
 * User: Tsvetan Ovedenski
 * Date: 06/09/2017
 * Time: 11:46
 */

namespace Fintara\Tools\Calculator;


interface TokenizerInterface
{
    /**
     * @param string $expression
     * @param array $functionNames
     * @return array Tokens of $expression
     */
    public function tokenize(string $expression, array $functionNames = []): array;
}
