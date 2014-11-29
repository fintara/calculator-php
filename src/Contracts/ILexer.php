<?php

namespace Fintara\Tools\Calculator\Contracts;

interface ILexer {

    public function getTokens($expression);

    public function setBrackets(array &$brackets);
    public function setOperators(array &$operators);
    public function setFunctions(array &$functions);
    public function setFunctionArgSeparator($separator);

}
