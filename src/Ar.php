<?php

namespace pisc\Arrr;

use Closure;
use pisc\upperscore as u;
use pisc\Arrr\Sort;
use pisc\Arrr\Arrr;

class Ar
{
	protected $attributes;

	public function __construct($attributes)
	{
		$this->attributes = $attributes;
	}

	public static function instance($attributes)
	{
		return new Arrr($attributes);
	}

	public static function ar($attributes)
	{
		return static::instance($attributes);
	}

	public static function toArray($attributes)
	{
		if( is_array($attributes) ) return $attributes;
		if( is_object($attributes) )
		{
			if( method_exists($attributes, 'toArray') )
			{
				return $attributes->toArray();
			}

			return (array)$attributes;
		}

		return [ $attributes ];
	}

	public static function toArrr($attributes)
	{
		if( $attributes instanceof Arrr ) return $attributes;
		return new Arrr(static::toArray($attributes));
	}

	public static function each($array, Closure $callback)
	{
		foreach( $array as $key => $value ) 
		{
			if( !$callback($value, $key) )
			{
				break;
			}
		}
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

	public static function select($array, Closure $callback)
	{
		return static::filter($array, $callback);
	}

	public static function detect($array, Closure $callback)
	{
		foreach( $array as $key => $value ) 
		{
			if( call_user_func($callback, $value, $key) )
			{
				return $value;
			}
		}

		return null;
	}

	public static function find($array, Closure $callback)
	{
		return static::detect($array, $callback);
	}

	public static function flatten($array) {

	    $array = array_reduce($array, function($a, $item) {
	        if( is_array($item) ) $item = static::flatten($item);

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
				$key = call_user_func($property, $value, $key);
			}
			else
			{
				$key = u\def($value, $property, '');
			}
			
			$newArray[$key] = $value;
		}

		return $newArray;
	}

	public static function contains($array, $values)
	{
		return static::containsAll($array, $values);
	}

	public static function containsSome($array, $values)
	{
		$value = static::detect( (array) $values, function($value) use ($array) {
			return in_array($value, $array);
		});

		return !is_null($value);
	}

	public static function containsAll($array, $values)
	{
		$value = static::detect( (array) $values, function($value) use ($array) {
			return !in_array($value, $array);
		});

		return is_null($value);
	}

	/**
	 * Sort by multiple keys
	 *
	 * Usages:
	 *
	 * String of attributes
	 * Ar::sortBy($array, 'created_at.firstName', [ 'Sort::byDateTime', 'Sort::byString' ], [ 'asc', 'desc' ]);
	 *
	 * Array
	 * Ar::sortBy($array, [ 'created_at', function($i) { return strtolower($i->firstName); } ], [ 'Sort::byDateTime', 'Sort::byString' ], [ 'asc', 'desc' ])
	 * 
	 * @param  array           $array    Array of values
	 * @param  string/array    $sortBy   String with dots seperating keys or array of keys/functions
	 * @param  array/callable  $sorters  Array of compare sorters or one method
	 * @param  array           $orders   Array of order (desc/asc)
	 * @return array                     Sorted array
	 */
	public static function sortBy($array, $sortByKeys, $sorters, $orders = [])
	{
		if( is_string($sortByKeys) )
		{
			$sortByKeys = explode('.', $sortByKeys);
		}

		$sortByKeys = array_values((array)$sortByKeys);
		$sorters = array_values((array)$sorters);

		uasort($array, function($a, $b) use ($sortByKeys, $sorters, $orders) {

			$return = 0;

			foreach( $sortByKeys as $key => $sortBy ) 
			{
				// call callback or get attribute
				if( is_callable($sortBy) )
				{
					$aSort = call_user_func($sortBy, $a);
					$bSort = call_user_func($sortBy, $b);
				}
				else 
				{
					$aSort = u\def($a, $sortBy);
					$bSort = u\def($b, $sortBy);
				}

				$method = isset($sorters[$key]) ? $sorters[$key] : $sorters[ ( count($sorters) - 1 ) ];

				// make sure Sort class from pisc\arrr namespace is used
				if( is_string($method) && substr($method, 0, 4) === 'Sort' ) $method = __NAMESPACE__ .'\\' . $method;

				$order = u\def($orders, $key, 'desc');

				// call compare function
				$return = call_user_func($method, $aSort, $bSort);

				// reverse return value
				if( $order !== 'desc' )
				{
					$return *= -1;
				}

				// return if the items compared are not the same
				if( $return !== 0 )
				{
					return $return;
				}
			}

			return 0;
		});

		return $array;
	}

	public static function merge()
	{
		$args = array_map(function($arg) { return static::toArray($arg); }, func_get_args());
		
		array_unshift($args, $this->attributes);

		return call_user_func_array('array_merge', $args);
	}

	public static function mergeValues()
	{
		$args = array_map(function($arg) { return array_values(static::toArray($arg)); }, func_get_args());
		
		array_unshift($args, array_values($this->attributes));

		return call_user_func_array('array_merge', $args);
	}

	public static function append($array, $items)
	{
		foreach( (array) $items as $key => $item ) 
		{
			array_push($array, $item);
		}

		return $array;
	}

	public static function push($array, $items)
	{
		return static::append($array, $items);
	}

	public static function pop($array, $count = 1)
	{
		$poppedItems = [];
		$i = 0;

		while(( $item = array_pop($array) ) && $i < $count )
		{
			$poppedItems[] = $item;
			$i += 1;
		}

		return count($poppedItems) === 1 ? $poppedItems[0] : $poppedItems;
	}

	public static function last($array, $count = 1)
	{
		$items = static::slice($array, -$count);
		return (count($items) === 1) ? $items[0] : $items;
	}

	public static function prepend($array, $items)
	{
		foreach( (array) $items as $key => $item ) 
		{
			array_unshift($array, $item);
		}

		return $array;
	}

	public static function unshift($array, $items)
	{
		return static::prepend($array, $items);
	}

	public static function shift($array, $count = 1)
	{
		$shiftedItems = [];
		$i = 0;

		while(( $item = array_shift($array) ) && $i < $count )
		{
			$shiftedItems[] = $item;
			$i += 1;
		}

		return count($shiftedItems) === 1 ? $shiftedItems[0] : $shiftedItems;
	}

	public static function first($array, $count = 1)
	{
		return static::slice($array, 0, $count);
	}

	public static function slice($array, $offset, $length = null)
	{
		return array_slice($array, $offset, $length, true);
	}

	public static function values($array)
	{
		return array_values($array);
	}

	/**
	 * Group an array of objects by an attribute of an object or the result of a closure
	 * 
	 * @param  array           $array    Array of objects
	 * @param  string/closure  $groupBy  Attribute of object or a closure. Closure gets object as parameter and should return a value that can be a key of the array
	 * @return array                     Array of arrays
	 */
	public static function groupBy($array, $groupBy = "id")
	{
		return static::reduce($array, function($arr, $item) use($groupBy) {

			// get value of attribute
			if( is_closure($groupBy) )
			{
				// execute closure
				$value = $groupBy($item);
			} 
			else
			{
				// get attribute of object
				$value = pisc\upperscore\def($item, $groupBy);
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
		// create new Arrr instance
		$instance = new Arrr($this->attributes);

		// if the method modifies the instance itself, 
		// set the attributes array of this instance to an empty array
		if( substr($name, -2, 2) === 'It' )
		{
			$this->attributes = [];
		}

		// call the function on the Arrr instance
		return call_user_func_array([$instance, $name], $arguments);
	}
}