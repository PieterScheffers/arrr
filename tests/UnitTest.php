<?php
namespace pisc\Arrr;

use PHPUnit_Framework_TestCase;

class UnitTest extends PHPUnit_Framework_TestCase 
{
    public function __construct() 
    {
        call_user_func_array("parent::__construct", func_get_args());
        require_once(__DIR__ . '/../vendor/autoload.php');
    }

    public function setUp() 
    {
        echo __CLASS__ . "/" . $this->getName() . "\n";
    }
}