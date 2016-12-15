[![Latest Stable Version](https://img.shields.io/packagist/v/pisc/arrr.svg?style=flat-square)](https://packagist.org/packages/pisc/arrr)
[![Build Status](https://travis-ci.org/PieterScheffers/arrr.svg?branch=master)](https://travis-ci.org/PieterScheffers/arrr)

# arrr ✅
Array class for PHP

## Installation

```sh
$ composer require pisc/arrr
```

## Instantiation:

```php
// shorthand function (global function)
$array = ar([ 'someKey' => 'someValue' ]);

// constructor
$array = new Arrr([ 'someKey' => 'someValue' ]);

$array = new Ar([ 'someKey' => 'someValue' ]); // returns Ar instance
$array->mapIt(function($item) {
	return $item . "_suffix";
}); // returns Arrr instance

// factory
$array = Arrr::ar([ 'someKey' => 'someValue' ]);
$array = Arrr::instance([ 'someKey' => 'someValue' ]);
$array = Ar::ar([ 'someKey' => 'someValue' ]);
$array = Ar::instance([ 'someKey' => 'someValue' ]);

```

## How to use:

```php
use pisc\arrr\arrr;

$array = ar([ 'someKey' => 'someValue' ]);

```

```php

$flat = Ar::flatten([ 'cow', [ 'bear', ['bunny', 'santa' ], 'rabbit' ]]);

```

Most methods are callable as:
- Static method with the first parameter the array     - Ar::map($array, function($item) { return $item; })
- Instance method which returns a new instance of Arrr - $Arrr->map(function($item) { return $item; })
- Instance method which works on the current instance  - $Arrr->mapIt(function($item) { return $item; })

## Convention

All methods ending on 'It' modify the array itself and return itself, so you can do something like:

```php
$places = ar([
	'Amsterdam',
	'Berlin',
	'Paris',
	'Vienna',
	'Rome',
	'Madrid'
]);

$places->filterIt(function($place) {
	return in_array($place[0], [ 'A', 'V', 'R' ]);
});

$places->mapIt(function($place) {
	return "To destination {$place}";
})

echo $places->toJson(); 

// returns
'[ "To destination Amsterdam", "To destination Vienna", "To destination Rome" ]'
```
## Run tests

```sh
# When installed globally
$ phpunit 

# When installed locally
$ ./vendor/bin/phpunit
```

## Methods

### sortBy (sortByIt)
Sort an array by multiple keys or result of a closure
When a compare function result in 0, it will test the next key or closure

accepts: 
- $sortByKeys ((array) string / closure ) 
- $methods    ((array) string / callable )

```php
$array = ar([
	(object)[ 'id' => 1, 'name' => 'Jack', 'length' => 180 ],
	(object)[ 'id' => 2, 'name' => 'Jack', 'length' => 150 ],
	(object)[ 'id' => 3, 'name' => 'Ben', 'length' => 180 ],
	(object)[ 'id' => 4, 'name' => 'Ben', 'length' => 150 ],
	(object)[ 'id' => 5, 'name' => 'Vince', 'length' => 180 ],
	(object)[ 'id' => 6, 'name' => 'Vince', 'length' => 150 ],
]);

$sortedArray = $array->sortBy([ 'length', 'name' ], [ 'Sort::byDefault', 'strcmp' ]);

// result
[
	(object)[ 'id' => 4, 'name' => 'Ben', 'length' => 150 ],
	(object)[ 'id' => 2, 'name' => 'Jack', 'length' => 150 ],
	(object)[ 'id' => 6, 'name' => 'Vince', 'length' => 150 ],
	(object)[ 'id' => 3, 'name' => 'Ben', 'length' => 180 ],
	(object)[ 'id' => 1, 'name' => 'Jack', 'length' => 180 ],
	(object)[ 'id' => 5, 'name' => 'Vince', 'length' => 180 ],
	
]


```

