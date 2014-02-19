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

$calculator = new Calculator('1+2*3/4');
echo $calculator->calculate();
```

###Advanced usage###
Set the calculator once, use it for different expressions
```php
$calculator = new Calculator();
$calculator->setExpression('(7^2)^3');
```

You can add custom functions:
name-of-function (as in expression), implementation, number-of-arguments
```php
$calculator->addFunction('cbrt', function($x) {
    return pow($x, 1/3);
}, 1);
$calculator->setExpression('cbrt(27)');
echo $calculator->calculate(); // 3
```

You can also use different parts of the calculator:
```php
// Separate tokens
$tokens = $calculator->getTokens();

// Rearrange tokens in Postfix notation (returns \SplQueue)
$rpn = $calculator->getReversePolishNotation($tokens);

// Strips all zeros in the decimal part up to the limit
$format = $calculator->formatNumber(4.230000, 3); // 4.23
```