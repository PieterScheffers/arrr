<?php

namespace pisc\Arrr;

use DateTime;

class Sort
{
	public static function byDefault($a, $b)
	{
		if( $a < $b ) {
			return -1;
		} else if( $a > $b ) {
			return 1;
		} else {
			return 0;
		}
	}

	public static function byNumber($a, $b)
	{
		return static::byDefault($a, $b);
	}

	public static function byString($a, $b)
	{
		// Returns < 0 if str1 is less than str2; > 0 if str1 is greater than str2, and 0 if they are equal.
		$return = strcmp($a, $b);
		
		if( $return < 0 ) return -1;
		else if( $return > 0) return 1;
		else return 0;
	}

	public static function byStringCase($a, $b)
	{
		// Returns < 0 if str1 is less than str2; > 0 if str1 is greater than str2, and 0 if they are equal.
		// Case insensitive
		return strcasecmp($a, $b);
	}

	function byDate(DateTime $a, DateTime $b)
	{
		$a = DateTime::createFromFormat('Y-m-d H:i:s', $a->format('Y-m-d') . " 00:00:00");
		$b = DateTime::createFromFormat('Y-m-d H:i:s', $b->format('Y-m-d') . " 00:00:00");

		return static::byDefault($a, $b);
	}

	function byDateTime(DateTime $a, DateTime $b)
	{
		return static::byDefault($a, $b);
	}

	function byTime(DateTime $a, DateTime $b)
	{
		$a = DateTime::createFromFormat('Y-m-d H:i:s', "1000-01-01 " . $a->format('H:i:s'));
		$b = DateTime::createFromFormat('Y-m-d H:i:s', "1000-01-01 " . $b->format('H:i:s'));

		return static::byDefault($a, $b);
	}


}