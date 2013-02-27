<?php

namespace Solo\Core;

class Route
{
	private $rules = array();

	public function __construct()
	{

	}

	public function get($pattern)
	{
		if (array_key_exists($pattern, $this->rules))
			return $this->rules[$pattern];
		else
			throw new ClassLoaderException("Can't find class for pattern '{$pattern}'");
	}

	public function add($pattern, $className)
	{
		$this->rules[$pattern] = $className;
	}

	public function debug()
	{
		throw new \Exception("Not implemented yet");
	}
}
