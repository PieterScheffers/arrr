# arrr
Array class for PHP

## Installation

```sh
$ composer require pisc/arrr
```

## How to use:

```php
use pisc\arrr\arrr;

$array = new Arrr([ 'someKey' => 'someValue' ]);

```

```php

$flat = Ar::flatten([ 'cow', [ 'bear', ['bunny', 'santa' ], 'rabbit' ]]);

```

## Convention

All methods ending on 'It' modify the array itself and return itself, so you can do something like:

```php
$places = new Arrr([
	'Amsterdam',
	'Berlin',
	'Paris',
	'Vienna',
	'Rome',
	'Madrid'
]);

$places->filterIt(function($place) {
	return in_array($place[0], [ 'A', 'V', 'R' ]);
})->mapIt(function($place) {
	return "To destination {$place}";
})->toJson();

// returns
'[ "To destination Amsterdam", "To destination Vienna", "To destination Rome" ]'
```
## Run tests