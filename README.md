## PHP Calculator for arithmetic expressions

PHP calculator which evaluates different arithmetic expressions:
```
2+5.9       = 7.9
3.5^(2-5)   = 0.02332361516035
5-(2*(1+2)) = -1
```

The included functions by default are `sqrt(x)` and `log(base, arg)`, but
there is also an option to add custom functions with any number of arguments.

### Basic usage
```php
use \Fintara\Tools\Calculator\Calculator;

$calculator = Calculator::create(); // use default tokenizer
echo $calculator->calculate('1+2*3/4'); // 2.5
```

### Advanced usage

#### Custom functions
You can add custom functions in the following format:
`name-of-function` (as in expression), `implementation`, `number-of-arguments`
```php
$calculator->addFunction('cbrt', function($x) {
    return pow($x, 1/3);
}, 1);

echo $calculator->calculate('cbrt(27)'); // 3
```

#### Tokenizer 
You can also use the tokenizer (or supply calculator with your own):
```php
$tokenizer = new Tokenizer();
$tokens = $tokenizer->tokenize('1+2*3.5'); // [1, '+', 2, '*', 3.5]
```

In case the expression contains functions (e.g. `sqrt(x)`), 
the tokenizer needs to know all functions' names as second parameter.
```php
$tokens = $tokenizer->tokenize('1+sqrt(4)', ['sqrt']); // [1, '+', 'sqrt', '(', 4, ')']
```

Note: The default tokenizer automatically adds `*` (multiplication) 
sign between a number and following function or a number and following 
parenthesis (if the sign is not found). 

### Changelog
2.0.0
* Breaking: operator `mod` is renamed to `%`.
* Breaking: adding function with existing name throws. 
You can use the helper `replaceFunction`.
* Breaking: `getReversePolishNotation` is private.
* Breaking: `setExpression` is removed. Use directly `calculate($expression)`.
* Breaking: `ILexer` and `DefaultLexer` are replaced with 
`TokenizerInterface` and `Tokenizer` respectively.
