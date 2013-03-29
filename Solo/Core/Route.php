<?php

namespace Solo\Core;

class Route
{
	/**
	 * Список правил для распознавания маршрутов
	 *
	 * @var array
	 */
	private $rules = array();

	/**
	 * Список подстановок (wildcards)
	 *
	 * @var array
	 */
	private $wildcards = array(
		'{any}' => '[a-zA-Z0-9\.\-_%=]+',
		'{num}' => '[0-9]+'
	);

	/**
	 * Список подстрок в начале URI,
	 * которые будут игнорироваться при поиске
	 *
	 * @var array
	 */
	private $prefixList = array();


	/**
	 * Конструктор
	 *
	 * @return Route
	 */
	public function __construct()
	{

	}

	/**
	 *
	 *
	 * @param string $uri содержимое $SERVER['QUERY_STRING']
	 *
	 * @return string|null
	 */
	public function getClass($uri)
	{
		foreach ($this->prefixList as $prefix)
		{
			$res = strpos($uri, $prefix);
			if ($res !== false)
			{
				$uri = substr($uri, strlen($prefix) , 0);
				if (!$uri)
					$uri = "/";
				break;
			}
		}

		if ("/" == $uri)
		{
			if (array_key_exists("/", $this->rules))
				return $this->rules["/"];
			else
				return null;
		}

		$uri = "/" . trim($uri, "/") . "/";
		//var_dump($uri);
		$className = null;

		// самый простой поиск
		foreach ($this->rules as $rule => $class)
		{
			if ("/" == $rule)
				continue;
			// более точно определенные правила ищем в первую очередь?
			$res = strpos($uri, $rule);
			if ($res !== false)
			{
				$this->parsePathInfo(str_replace($rule, "", $uri));
				$className = $class;
				break; // нашли правило - остальные не проверяем
			}

			// проверка регулярных выражений и placeholder'ов
			$isMatch = $this->parseWildcards($rule, $uri);
			if ($isMatch)
			{
				$className = $class;
				break;
			}
		}

		return $className;
	}

	/**
	 * Поиск, замена wildcards и проверка на соответствие
	 *
	 * @param string $rule Маршрут
	 * @param string $uri Строка запроса
	 *
	 * @return bool
	 */
	public function parseWildcards($rule, $uri)
	{
		// маршруты могут описываться строками типа /:id:{num}/:name:{any}
		// ,где :id: - имя_переменной, {num} - имя wildcard
		// Например, маршрут "/user/:username:{any}" будет соответствовать
		// шаблону '/user/[a-zA-Z0-9\.\-_%=]+' (напр. /user/some_username),
		// и при совпадении правила в $_REQUEST
		// появится переменная 'username' со значением 'some_username'

		$rule = preg_replace('%:([\w]+):(\{[\w]+\})%', '(?P<$1>$2)', $rule);

		$rule = "~" . trim($rule, '/') . "/~";

		// заменить wildcards на регулярные выражения
		$rule = str_replace(array_keys($this->wildcards), array_values($this->wildcards), $rule);

		// проверяем соответствие uri маршруту
		$isMatch = preg_match($rule, $uri, $matches);

		$res = array();
		if (Request::isGet())
			$res = &$_GET;
		if (Request::isPost())
			$res = &$_POST;

		if ($isMatch)
		{
			foreach ($matches as $k => $v)
			{
				if (is_numeric($k))
					continue;
				else
					$res[$k] = $v;
			}

			// убираем из строки запроса совпавшую часть маршрута.
			// оставшуюся часть преобразуем в переменные и их значения
			$additional = "/" . trim(str_replace($matches[0], "", $uri), '/');
			$this->parsePathInfo($additional);
		}

		return (bool)$isMatch;
	}


	/**
	 * Добавляет правило для распознавания маршрута
	 *
	 * @param string $pattern Описание маршрута
	 * @param string $className Имя класса, соответсвующего маршруту
	 *
	 * @return void
	 */
	public function add($pattern, $className)
	{
		if ("/" !== $pattern)
			$pattern = "/" . trim($pattern, "/") . "/";

		$this->rules[$pattern] = $className;
	}

	/**
	 * Добавляет подстроку, которая будет игнорироваться при поиске
	 * маршрута (в начале URI)
	 *
	 * @param $prefix
	 *
	 * @return void
	 */
	public function addPrefix($prefix)
	{
		$this->prefixList[] = $prefix;
	}

	/**
	 * Очистка правил маршрутизации
	 *
	 * @return void
	 */
	public function clear()
	{
		$this->rules = array();
	}

	/**
	 * Adds new wildcard
	 *
	 * @param string $name Имя подстановки
	 * @param string $pattern Регулярное выражение, на которое заменяется wildcard
	 *
	 * @throws \RuntimeException
	 */
	public function addWildCard($name, $pattern)
	{
		$name = trim($name, "{}");
		$name = "{{$name}}";

		if (array_key_exists($name, $this->wildcards))
			throw new \RuntimeException("The wildcard '{$name}' already exists");

		$this->wildcards[$name] = $pattern;
	}

	/**
	 *
	 *
	 * @param $uri
	 *
	 * @return null|string
	 */
	public function debug($uri)
	{
		$res = $this->getClass($uri);
		if (!$res)
			return "There is no rule for this URI: {$uri}";
		else
			return $res;
	}

	/**
	 * Делает разбор строки запроса и
	 * помещает результаты в $_GET или $_POST
	 *
	 * @param string $pathInfo Строка запроса типа param1/value1/param2/value2/
	 *
	 * @return void
	 */
	public function parsePathInfo($pathInfo)
	{
		if ($pathInfo === '')
			return;

		$pathInfo = trim($pathInfo, "/");
		$segs = explode('/', $pathInfo . '/');

		$list = null;
		if (Request::isGet())
			$list = &$_GET;
		if (Request::isPost())
			$list = &$_POST;

		$n = count($segs);
		for ($i = 0; $i < $n - 1; $i += 2)
		{
			$key = $segs[$i];
			if ($key === '')
				continue;
			$value = $segs[$i + 1];
			if (($pos = strpos($key, '[')) !== false && ($m = preg_match_all('/\[(.*?)\]/', $key, $matches)) > 0)
			{
				$name = substr($key, 0, $pos);
				for ($j = $m - 1; $j >= 0; --$j)
				{
					if ($matches[1][$j] === '')
						$value = array($value);
					else
						$value = array($matches[1][$j] => $value);
				}
				if (isset($list[$name]) && is_array($list[$name]))
					$value = array_merge_recursive($list[$name], $value);
				$list[$name] = $value;
			}
			else
				$list[$key] = $value;
		}
	}
}
