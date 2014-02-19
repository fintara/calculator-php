##PHP Calculator for arithmetic expressions##

####Basic usage####
```php
use \Fintara\Tools\Calculator\Calculator;

$calculator = new Calculator();
$calculator->setExpression('1+2*3/4');
echo $calculator->calculate();
```

####Advanced usage####
```php
$calculator = new Calculator('(7^2)^3'); // Set an expression directly in constructor

// You can add custom functions:
// name-of-function (as in expression), implementation, number-of-arguments
$calculator->addFunction('cbrt', function($x) {
    return pow($x, 1/3);
}, 1);

// You can also use different parts of the calculator:
$tokens = $calculator->getTokens(); // Separate tokens
$rpn = $calculator->getReversePolishNotation($tokens); // Rearrange tokens in Postfix notation (returns \SplQueue)
$format = $calculator->formatNumber(4.230000, 3); // (4.23) Strips all zeros in the decimal part up to the limit
```