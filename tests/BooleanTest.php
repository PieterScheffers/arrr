<?php
namespace pisc\Arrr;

use pisc\Arrr\Arrr;
use pisc\Arrr\Ar;

class BooleanTest extends UnitTest 
{
    public function testContainsOr()
    {
    	$arrr = new Arrr([ "bunny", "rabbit", "cow", "bull", "pig", "swine", "hog" ]);

    	$real = $arrr->containsOr([ "car", "rabbit", "bus" ], "bull");
    	$expected = true;
    	$this->assertEquals( $real, $expected );

    	$real = $arrr->containsOr([ "car", "motor", "bus" ], "cycle");
    	$expected = false;
    	$this->assertEquals( $real, $expected );
    }

    public function testContainsAnd()
    {
    	$arrr = new Arrr([ "bunny", "rabbit", "cow", "bull", "pig", "swine", "hog" ]);

    	$real = $arrr->containsAnd([ "cow", "swine", "bull" ], "bull");
    	$expected = true;
    	$this->assertEquals( $real, $expected );

    	$real = $arrr->containsAnd([ "car", "cow", "swine" ], "bull");
    	$expected = false;
    	$this->assertEquals( $real, $expected );
    }
}