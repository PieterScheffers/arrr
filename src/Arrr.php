<?php

/**
 * Convention!!
 * Functions that handle directly on the collection
 * are ending with 'It'
 */

namespace pisc\Arrr;

use ArrayIterator;
use IteratorAggregate;
use Countable;
use ArrayAccess;
use JsonSerializable;
use Serializable;
use Closure;

class Arrr implements IteratorAggregate, Countable, ArrayAccess, Serializable, JsonSerializable
{
	protected $attributes;

	public function __construct($attributes)
	{
		if( !is_array($attributes) && get_class($attributes) === 'Illuminate\Support\Collection' )
		{
			$this->attributes = $attributes->all();
		}

		$this->attributes = (array)$attributes;
	}

	public static function instance($attributes)
	{
		return new static($attributes);
	}

	public static function ar($attributes)
	{
		return new static($attributes);
	}

	/////////////// functional ///////////////
	
	public function each(Closure $callback) 
	{
		foreach( $this->attributes as $key => $value ) 
		{
			if( $callback($value, $key) === false ) {
				break;
			}
		}

		return $this;
	}

	public function reduce(Closure $callback, $initial = 0)
	{
		return new static( Ar::reduce($this->attributes, $callback, $initial) );
	}

	public function map(Closure $callback)
	{
		return new static( Ar::map($this->attributes, $callback) );
	}

	public function mapIt(Closure $callback)
	{
		foreach( $this->attributes as $key => $value ) 
		{
			$this->attributes[$key] = $callback($value, $key);
		}

		return $this;
	}

	public function filter(Closure $callback)
	{
		return new static( Ar::filter($this->attributes, $callback) );
	}

	public function filterIt(Closure $callback)
	{
		foreach( $this->attributes as $key => $value ) 
		{
			if( !$callback($value, $key) )
			{
				unset($this->attributes[$key]);
			}
		}

		return $this;
	}

	public function select(Closure $callback)
	{
		return $this->filter($callback);
	}

	public function selectIt(Closure $callback)
	{
		return $this->filterIt($callback);
	}

	public function detect(Closure $callback)
	{
		return Ar::detect($this->attributes, $callback);
	}

	public function find(Closure $callback)
	{
		return $this->detect($callback);
	}

	/////////////// Custom ///////////////

	/**
	 * Flattens the array
	 * @return array      flat array
	 */
	public function flatten() 
	{
		return new static( Ar::flatten($this->attributes) );
	}

	public function flattenIt()
	{
		$this->attributes = Ar::flatten($this->attributes);

		return $this;
	}

	public function groupBy($groupBy = "id")
	{
		return new static( Ar::groupBy($this->attributes, $groupBy) );
	}

	public function groupByIt()
	{
		$this->attributes = Ar::groupBy($this->attributes, $groupBy);

		return $this;
	}

	/**
	 * Indexes the array, make the keys like a prop of the objects
	 * Warning: When an attribute doesn't exist, key is a empty string,
	 * so it overwrites others that have an empty key
	 * 
	 * @param  string $prop Property key of object/array
	 * @return array        Array with the property as keys
	 */
	public function index($property = "id") {
		return Ar::index($this->attributes, $property);
	}

	public function indexIt($property = "id")
	{
		$this->attributes = Ar::index($this->attributes, $property);

		return $this;
	}

	/**
	 * Find whether the array is indexed or an associative array
	 * If it is indexed, find if its sparse or not
	 * 
	 * @return string       type of array (index, assoc, sparse)
	 */
	public function type()
	{
		return Ar::type($this->attributes);
	}

	/////////////// SORT ///////////////

	public function sort()
	{

	}

	public function sortBy($sortByKeys, $methods)
	{
		return new static( Ar::sortBy($this->attributes, $sortByKeys, $methods) );
	}

	public function sortByIt($sortByKeys, $methods)
	{
		$this->attributes = Ar::sortBy($this->attributes, $sortByKeys, $methods);

		return $this;
	}

	/////////////// return Boolean ///////////////
	
	public function containsOr()
	{
		$args = (new static(func_get_args()))->flattenIt();

		$found = $args->detect(function($value) {
			return in_array($value, $this->attributes);
		});

		return !is_null($found);
	}

	public function containsAnd()
	{
		$args = (new static(func_get_args()))->flattenIt();

		foreach( $this->attributes as $key => $value) 
		{
			$found = $args->detect(function($arg) use ($value) {
				return $arg == $value;
			});

			if( $found ) $args->removeAllIt($found);

			if( $args->count() < 1 ) return true;
		}

		return false;
	}

	/////////////// return Boolean ///////////////

	public function removeFirst($remove)
	{
		$newArr = [];
		$found = false;

		foreach( $this->attributes as $key => $value)
		{
			if( $found === false && $value != $remove )
			{
				$found = true;
				$newArr[$key] = $value;
			}
		}

		return new static($newArr);
	}

	public function removeFirstIt($remove)
	{
		foreach( $this->attributes as $key => $value)
		{
			if( $value == $remove )
			{
				unset($this->attributes[$key]);
				return $this;
			}
		}

		return $this;
	}

	public function removeAll($remove)
	{
		$newArr = [];

		foreach( $this->attributes as $key => $value)
		{
			if( $value != $remove )
			{
				$newArr[$key] = $value;
			}
		}

		return new static($newArr);
	}

	public function removeAllIt($remove)
	{
		foreach( $this->attributes as $key => $value)
		{
			if( $value == $remove )
			{
				unset($this->attributes[$key]);
			}
		}

		return $this;
	}

	/////////////// Push / Pop / Shift / Unshift ///////////////
	
	public function append($items)
	{
		foreach( (array) $items as $key => $item ) 
		{
			array_push($this->attributes, $item);
		}

		return $this;
	}

	public function push($items)
	{
		return $this->append($items);
	}

	public function pop($count = 1)
	{
		$poppedItems = [];
		$i = 0;

		while(( $item = array_pop($this->attributes) ) && $i < $count )
		{
			$poppedItems[] = $item;
			$i += 1;
		}

		return count($poppedItems) === 1 ? $poppedItems[0] : $poppedItems;
	}

	public function last()
	{
		$values = $this->values()->toArray();
		$numberOfvalues = count($values);

		return ( $numberOfvalues > 0 ) ? $values[$numberOfvalues - 1] : null;
	}

	public function prepend($items)
	{
		foreach( (array) $items as $key => $item ) 
		{
			array_unshift($this->attributes, $item);
		}

		return $array;
	}

	public function unshift($items)
	{
		return $this->prepend($items);
	}

	public function shift($count = 1)
	{
		$shiftedItems = [];
		$i = 0;

		while(( $item = array_shift($this->attributes) ) && $i < $count )
		{
			$shiftedItems[] = $item;
			$i += 1;
		}

		return count($shiftedItems) === 1 ? $shiftedItems[0] : $shiftedItems;
	}

	public function first()
	{
		$values = $this->values()->toArray();
		return ( count($values) > 0 ) ? $values[0] : null;
	}

	public function slice($offset, $length = null)
	{
		return new static( Ar::slice($this->attributes, $offset, $length) );
	}

	public function sliceIt($offset, $length = null)
	{
		$this->attributes = Ar::slice($this->attributes, $offset, $length);

		return $this;
	}

	public function reverse($preserveKeys = false)
	{
		return new static(array_reverse($this->attributes, $preserveKeys));
	}

	public function reverseIt($preserveKeys = false)
	{
		$this->attributes = array_reverse($this->attributes, $preserveKeys);

		return $this;
	}

	/////////////// misc ///////////////
	
	public function values()
	{
		return new static(array_values($this->attributes));
	}

	public function valuesIt()
	{
		$this->attributes = array_values($this->attributes);

		return $this;
	}

	public function keys()
	{
		return new static(array_keys($this->attributes));
	}

	public function flip()
	{
		return new static(array_flip($this->attributes));
	}

	/////////////// Serializable ///////////////

    public function serialize() 
    {
        return serialize($this->attributes);
    }

    public function unserialize($attributes) 
    {
        $this->attributes = unserialize($attributes);
    }

  
    /////////////// ArrayAccess ///////////////

    public function offsetExists($offset)
    {
    	return isset($this->attributes[$offset]);
    }

    public function offsetGet($offset)
    {
    	return isset($this->attributes[$offset]) ? $this->attributes[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        if( is_null($offset) ) 
        {
            $this->attributes[] = $value;
        } 
        else 
        {
            $this->attributes[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
    	unset($this->attributes[$offset]);
    }
    
    /////////////// IteratorAggregate ///////////////

    public function getIterator() 
    {
        return new ArrayIterator($this->attributes);
    }

    /////////////// Countable ///////////////

    public function count()
    {
    	return count($this->attributes);
    }

    public function length()
    {
    	return $this->count();
    }

    /////////////// Return array ///////////////

	public function all()
	{
		return $this->attributes;
	}

	public function toCollection()
	{
		if( !class_exists(\Illuminate\Support\Collection, true) )
		{
			throw new Exception("Laravel Collection not available!");
		}

		return new \Illuminate\Support\Collection($this->attributes);
	}

	public function toArray()
	{
		return $this->attributes;
	}

	/////////////// jsonSerializable ///////////////

    public function jsonSerialize() 
    {
        return $this->attributes;
    }

	public function toJson($options = 0, $depth = 512)
	{
		$json = json_encode($this, $options, $depth);
		return empty($json) ? "{}" : $json;
	}

	public function __toString()
	{
		return $this->toJson();
	}

	/**
	 * All static method calls are redirected to Ar class
	 * @param  string $name      Name of the static function
	 * @param  array  $arguments Array of arguments to the function
	 * @return mixed             Returns the returnvalue of the function
	 */
	public static function __callStatic($name, $arguments)
	{
		return call_user_func_array(["Ar", $name], $arguments);
	}

}