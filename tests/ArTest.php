<?php

use pisc\Arrr\Arrr;
use pisc\Arrr\Ar;

class ArTest extends PHPUnit_Framework_TestCase 
{

    public function setUp() 
    {
        echo __CLASS__ . "/" . $this->getName() . "\n";
    }

	public function testDetect() 
    {
    	$arrayOfObjects = [
    		(object)[ 'id' => 25, 'place' => 'Amsterdam' ],
    		(object)[ 'id' => 30, 'place' => 'London' ],
    		(object)[ 'id' => 32, 'place' => 'Berlin' ],
    		(object)[ 'id' => 33, 'place' => 'London' ],
    		(object)[ 'id' => 34, 'place' => 'New York' ],
    		(object)[ 'id' => 36, 'place' => 'Amsterdam' ],
    		(object)[ 'id' => 40, 'place' => 'Berlin' ],
    		(object)[ 'id' => 41, 'place' => 'Paris' ],
    		(object)[ 'id' => 43, 'place' => 'Amsterdam' ]
    	];

    	$selectedItem = Ar::detect($arrayOfObjects, function($v, $k) {
    		return $v->place === 'Amsterdam';
    	});

    	$shouldBe = (object)[ 'id' => 25, 'place' => 'Amsterdam' ];

    	$this->assertEquals( $selectedItem, $shouldBe );
	}

    public function testType() 
    {
        $indexed = [ 0 => 'rabbit', 1 => 'cow', 2 => 'horse', 3 => 'cat', 4 => 'dog', 5 => 'frog' ];
        $sparse  = [ 0 => 'rabbit',             2 => 'horse', 3 => 'cat',             5 => 'frog', 7 => 'cow', 10 => 'dog' ];
        $associative = [ 'a' => 'rabbit', 'b' => 'cow', 2 => 'horse', 3 => 'cat', 4 => 'dog', 5 => 'frog' ];

        $this->assertEquals( Ar::type($indexed),     'index' );
        $this->assertEquals( Ar::type($sparse),      'sparse');
        $this->assertEquals( Ar::type($associative), 'assoc' );

    }

    public function testFlatten()
    {
        $flat = Ar::flatten([ 'cow', [ 'bear', ['bunny', 'santa' ], 'rabbit' ]]);
        $expected = [ 'cow', 'bear', 'bunny', 'santa', 'rabbit' ];

        $this->assertEquals( $flat, $expected );
    }

    public function testIndex()
    {
        $arrayOfObjects = [
            (object)[ 'id' => 25, 'place' => 'Amsterdam' ],
            (object)[ 'id' => 25, 'place' => 'London' ],
            (object)[ 'id' => 32, 'place' => 'Berlin' ],
            (object)[ 'id' => 33, 'place' => 'London' ],
            (object)[ 'id' => 34, 'place' => 'New York' ],
            (object)[ 'id' => 36, 'place' => 'Amsterdam' ],
            (object)[ 'id' => 40, 'place' => 'Berlin' ],
            (object)[ 'id' => 41, 'place' => 'Paris' ],
            (object)[ 'id' => 43, 'place' => 'Amsterdam' ]
        ];

        $indexed = Ar::index($arrayOfObjects, 'id');

        $expected = [
            25 => (object)[ 'id' => 25, 'place' => 'London' ],
            32 => (object)[ 'id' => 32, 'place' => 'Berlin' ],
            33 => (object)[ 'id' => 33, 'place' => 'London' ],
            34 => (object)[ 'id' => 34, 'place' => 'New York' ],
            36 => (object)[ 'id' => 36, 'place' => 'Amsterdam' ],
            40 => (object)[ 'id' => 40, 'place' => 'Berlin' ],
            41 => (object)[ 'id' => 41, 'place' => 'Paris' ],
            43 => (object)[ 'id' => 43, 'place' => 'Amsterdam' ]
        ];

        $this->assertEquals( $indexed, $expected );

        // key = place -> lowercased -> replace spaces by underscore
        $indexedCallback = Ar::index($arrayOfObjects, function($item) { return str_replace(' ', '_', strtolower($item->place)); });

        // only 1 occurence of amsterdam left
        $expectedCallback = [
            'london' => (object)[ 'id' => 25, 'place' => 'London' ],
            'berlin' => (object)[ 'id' => 32, 'place' => 'Berlin' ],
            'london' => (object)[ 'id' => 33, 'place' => 'London' ],
            'new_york' => (object)[ 'id' => 34, 'place' => 'New York' ],
            'berlin' => (object)[ 'id' => 40, 'place' => 'Berlin' ],
            'paris' => (object)[ 'id' => 41, 'place' => 'Paris' ],
            'amsterdam' => (object)[ 'id' => 43, 'place' => 'Amsterdam' ]
        ];

        $this->assertEquals( $indexedCallback, $expectedCallback );
    }

    public function testSortBy()
    {
        $array = [
            (object)[ 'id' => 25, 'place' => 'Amsterdam',   'created_at' => '2015-04-03 22:13:00' ],
            (object)[ 'id' => 25, 'place' => 'London',      'created_at' => '2015-04-03 22:13:00' ],
            (object)[ 'id' => 32, 'place' => 'Berlin',      'created_at' => '2022-04-03 22:13:00' ],
            (object)[ 'id' => 33, 'place' => 'London',      'created_at' => '2016-04-03 22:13:00' ],
            (object)[ 'id' => 34, 'place' => 'New York',    'created_at' => '2013-04-03 22:13:00' ],
            (object)[ 'id' => 36, 'place' => 'Amsterdam',   'created_at' => '2012-04-03 22:13:00' ],
            (object)[ 'id' => 40, 'place' => 'Berlin',      'created_at' => '2010-04-03 22:13:00' ],
            (object)[ 'id' => 41, 'place' => 'Paris',       'created_at' => '2035-04-03 22:13:00' ],
            (object)[ 'id' => 43, 'place' => 'Amsterdam',   'created_at' => '2022-04-03 22:13:00' ]
        ];

        $mapped = Ar::map($array, function($item) { $item->created_at = \DateTime::createFromFormat('Y-m-d H:i:s', $item->created_at); return $item; });
        $sorted = Ar::sortBy($mapped, 'place.created_at', [ 'Sort::byStringCase', 'Sort::byDate' ], [ 'desc', 'asc' ]);

        $expected = [
            8 => (object)[ 'id' => 43, 'place' => 'Amsterdam',   'created_at' => '2022-04-03 22:13:00' ],
            0 => (object)[ 'id' => 25, 'place' => 'Amsterdam',   'created_at' => '2015-04-03 22:13:00' ],
            5 => (object)[ 'id' => 36, 'place' => 'Amsterdam',   'created_at' => '2012-04-03 22:13:00' ],
            2 => (object)[ 'id' => 32, 'place' => 'Berlin',      'created_at' => '2022-04-03 22:13:00' ],
            6 => (object)[ 'id' => 40, 'place' => 'Berlin',      'created_at' => '2010-04-03 22:13:00' ],
            3 => (object)[ 'id' => 33, 'place' => 'London',      'created_at' => '2016-04-03 22:13:00' ],
            1 => (object)[ 'id' => 25, 'place' => 'London',      'created_at' => '2015-04-03 22:13:00' ],
            4 => (object)[ 'id' => 34, 'place' => 'New York',    'created_at' => '2013-04-03 22:13:00' ],
            7 => (object)[ 'id' => 41, 'place' => 'Paris',       'created_at' => '2035-04-03 22:13:00' ]
        ];

        $expected = Ar::map($expected, function($item) { $item->created_at = \DateTime::createFromFormat('Y-m-d H:i:s', $item->created_at); return $item; });

        $this->assertEquals( $sorted, $expected );
    }

}