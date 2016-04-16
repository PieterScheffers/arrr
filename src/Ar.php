<?php

namespace pisc\Arrr;

use Closure;

class Ar
{
	protected $attributes;

	public function __construct($attributes)
	{
		$this->attributes = $attributes;
	}

	public function instance($attributes)
	{
		return new Arrr($attributes);
	}

	public static function reduce($array, Closure $callback, $initial = 0)
	{
		foreach( $array as $key => $value ) 
		{
			$initial = $callback($initial, $value, $key);
		}

		return $initial;
	}

	public static function map($array, Closure $callback)
	{
		$newAttributes = [];

		foreach( $array as $key => $value )
		{
			$newAttributes[$key] = $callback($value, $key);
		}

		return $newAttributes;
	}

	public static function filter($array, Closure $callback)
	{
		$newAttributes = [];

		foreach( $array as $key => $value ) 
		{
			if( $callback($value, $key) )
			{
				$newAttributes[$key] = $value;
			}
		}

		return $newAttributes;
	}

	public static function detect($array, Closure $callback)
	{
		foreach( $array as $key => $value ) 
		{
			if( $callback($value, $key) )
			{
				return $value;
			}
		}

		return null;
	}

	public static function flatten($array) {

	    $array = array_reduce($array, function($a, $item) {
	        if( is_array($item) ) $item = arrayFlatten($item);

	        return array_merge($a, (array)$item);
	    }, []);

	    return $array;
	}

	/**
	 * Indexes the array, make the keys like a prop of the objects
	 * Warning: When an attribute doesn't exist, key is a empty string,
	 * so it overwrites others that have an empty key
	 *
	 * @param  array  $array Array of values or objects
	 * @param  string $prop  Property key of object/array
	 * @return array         Array with the property as keys
	 */
	public static function index($array, $property = "id") {
		$newArray = [];

		foreach ($array as $key => $value) 
		{
			if( is_callable($property) )
			{
				$key = $property($value, $key);
			}
			else
			{
				$key = def($value, $property, '');
			}
			
			$newArray[$key] = $value;
		}

		return $newArray;
	}

	/**
	 * Sort by multiple keys
	 * 
	 * @param  array           $array    Array of values
	 * @param  string/array    $sortBy   String with dots seperating keys or array of keys/functions
	 * @param  array/callable  $methods  Array of compare methods or one method
	 * @return array                     Sorted array
	 */
	function sortBy($array, $sortByKeys, $methods)
	{
		if( is_string($sortByKeys) )
		{
			$sortByKeys = explode('.', $sortByKeys);
		}

		$sortByKeys = array_values((array)$sortByKeys);
		$methods = array_values((array)$methods);

		uasort($array, function($a, $b) use ($sortByKeys, $methods) {

			$return = 0;

			foreach( $sortByKeys as $key => $sortBy ) 
			{
				if( is_callable($sortBy) )
				{
					$aSort = $sortBy($a);
					$bSort = $sortBy($b);
				}
				else 
				{
					$aSort = def($a, $sortBy);
					$bSort = def($b, $sortBy);
				}

				$method = isset($methods[$key]) ? $methods[$key] : $methods[ ( count($methods) - 1 ) ];

				$return = $method($aSort, $bSort);

				if( $return !== 0 )
				{
					return $return;
				}
			}

			return 0;
		});

		return $array;
	}

	/**
	 * Group an array of objects by an attribute of an object or the result of a closure
	 * 
	 * @param  array           $array    Array of objects
	 * @param  string/closure  $groupBy  Attribute of object or a closure. Closure gets object as parameter and should return a value that can be a key of the array
	 * @return array                     Array of arrays
	 */
	function groupBy($array, $groupBy = "id")
	{
		return array_reduce($array, function($arr, $item) use ($groupBy) {

			// get value of attribute
			if( is_closure($groupBy) )
			{
				// execute closure
				$value = $groupBy($item);
			} 
			else
			{
				// get attribute of object
				$value = u\def($item, $groupBy);
			}

			// build new array with value as keys
			if( isset($arr[$value]) )
			{
				$arr[$value][] = $item;
			}
			else
			{
				$arr[$value] = [ $item ];
			}

			return $arr;

		}, []);
	}

	/**
	 * Find whether the array is indexed or an associative array
	 * If it is indexed, find if its sparse or not
	 * 
	 * @return string       type of array (index, assoc, sparse)
	 */
	public static function type($array)
	{
	    $last_key = -1;
	    $type = 'index';

	    foreach( $array as $key => $val )
	    {
	        if( !is_int( $key ) || $key < 0 )
	        {
	            return 'assoc';
	        }

	        if( $type !== 'sparse' ) 
	        {
		        if( $key !== $last_key + 1 ){
		            $type = 'sparse';
		        }
		        $last_key = $key;
		    }
	    }

	    return $type;
	}

	public function __call($name, $arguments)
	{
		$instance = new Arrr($this->attributes);
		return call_user_func_array([$instance, $name], $arguments);
	}
}