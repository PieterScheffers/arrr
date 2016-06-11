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

}