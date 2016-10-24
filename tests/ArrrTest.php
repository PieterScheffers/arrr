<?php
namespace pisc\Arrr;

use pisc\Arrr\UnitTest;
use pisc\Arrr\Arrr;
use pisc\Arrr\Ar;

class ArrrTest extends UnitTest 
{
    public function setUp() 
    {
        echo __CLASS__ . "/" . $this->getName() . "\n";
    }

    public function testFunctionCall()
    {
        $array = ar(['someKey' => "someValue"]);
        $this->assertEquals( $array["someKey"], "someValue" );
    }

	public function testDetect() 
    {
    	$arrayOfObjects = new Arrr([
    		(object)[ 'id' => 25, 'place' => 'Amsterdam' ],
    		(object)[ 'id' => 30, 'place' => 'London' ],
    		(object)[ 'id' => 32, 'place' => 'Berlin' ],
    		(object)[ 'id' => 33, 'place' => 'London' ],
    		(object)[ 'id' => 34, 'place' => 'New York' ],
    		(object)[ 'id' => 36, 'place' => 'Amsterdam' ],
    		(object)[ 'id' => 40, 'place' => 'Berlin' ],
    		(object)[ 'id' => 41, 'place' => 'Paris' ],
    		(object)[ 'id' => 43, 'place' => 'Amsterdam' ]
    	]);

    	$selectedItem = $arrayOfObjects->detect(function($v, $k) {
    		return $v->place === 'Amsterdam';
    	});

    	$shouldBe = (object)[ 'id' => 25, 'place' => 'Amsterdam' ];

    	$this->assertEquals( $selectedItem, $shouldBe );
	}

    public function testType() 
    {
        $indexed = new Arrr([ 0 => 'rabbit', 1 => 'cow', 2 => 'horse', 3 => 'cat', 4 => 'dog', 5 => 'frog' ]);
        $sparse  = new Arrr([ 0 => 'rabbit',             2 => 'horse', 3 => 'cat',             5 => 'frog', 7 => 'cow', 10 => 'dog' ]);
        $associative = new Arrr([ 'a' => 'rabbit', 'b' => 'cow', 2 => 'horse', 3 => 'cat', 4 => 'dog', 5 => 'frog' ]);

        $this->assertEquals( $indexed->type(),     'index' );
        $this->assertEquals( $sparse->type(),      'sparse' );
        $this->assertEquals( $associative->type(), 'assoc' );

    }

    public function testJoin()
    {
        $arrr = new Arrr([ "scheep", "cow", "chicken", "pig" ]);
        $string = $arrr->join("|");

        $expected = "scheep|cow|chicken|pig";

        $this->assertEquals( $string, $expected );
    }

}