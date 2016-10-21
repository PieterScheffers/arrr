<?php

if( !function_exists('ar') )
{
	function ar($attributes)
	{
		return new pisc\Arrr\Arrr($attributes);
	}
}
