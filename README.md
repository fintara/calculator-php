##PHP Calculator for arithmetic expressions##

PHP calculator which evaluates different arithmetic expressions:
```
2+5.9       = 7.9
3.5^(2-5)   = 0.02332361516035
5-(2*(1+2)) = -1
```

The included functions by default are sqrt(x) and log(base, arg), but
there is also an option to add custom functions with any number of arguments.

###Basic usage###
```php
use \Fintara\Tools\Calculator\Calculator;
use \Fintara\Tools\Calculator\DefaultLexer;

$calculator = new Calculator(new DefaultLexer());
$calculator->setExpression('1+2*3/4');

echo $calculator->calculate(); // 2.5
```

###Advanced usage###
You can add custom functions in the following format:
`name-of-function` (as in expression), `implementation`, `number-of-arguments`
```php
$calculator->addFunction('cbrt', function($x) {
    return pow($x, 1/3);
}, 1);

$calculator->setExpression('cbrt(27)');

echo $calculator->calculate(); // 3
```

You can also use different parts of the calculator (or lexer):
```php
$lexer = new DefaultLexer();

// Separate tokens
$tokens = $lexer->getTokens('1+2*3'); // [1, '+', 2, '*', 3]

// Rearrange tokens in Postfix notation (returns \SplQueue)
$rpn = $calculator->getReversePolishNotation($tokens);
```
