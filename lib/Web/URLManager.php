<?php
/**
 * Класс для разбора строки запроса и
 * определения запрашиваемых Представлений или Действий
 *
 * в .htaccess добавить:
 * <IfModule mod_rewrite.c>
 *   RewriteEngine on
 *
 *	# если папка или файл реально существуют, используем их
 *	RewriteCond %{REQUEST_FILENAME} !-f
 *	RewriteCond %{REQUEST_FILENAME} !-d
 *
 *	# если нет — отдаём всё index.php
 *	RewriteRule . index.php
 *
 * </IfModule>
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi.work@gmail.com>
 */

class URLManager
{
	/**
	 * Включить\выключить
	 *
	 * @var bool
	 */
	public $isEnabled = false;

	/**
	 * Правила фильтрации
	 *
	 * Фильтр имеет вид:
	 * array
	 * (
	 * 	  "search" => "regex for search",
	 *    "replace" => "replace_value"
	 * )
	 *
	 * @var mixed
	 */
	public $filters = null;

	/**
	 * Список правил трансформации URL
	 *
	 * Правило имеет вид:
	 * array(
	 * 	"pattern" => '~^/index\.php~i',
	 * 	"replace" => ""
	 * )
	 *
	 * @var mixed
	 */
	public $rules = null;

	/**
	 * Формат формируемого URL
	 * path - Типа /view/viewname/par1/val1/par2/val2
	 * get - типа view=viewname&par1=val1&par2=val2
	 *
	 * @var string
	 */
	public $format = "path";

	/**
	 * Конструктор
	 *
	 * @return void
	 */
	public function __construct()
	{

	}

	public function parse($queryString = null)
	{
		if (!$this->isEnabled)
			return;

		if (count($this->rules) == 0)
			throw new RuntimeException("Please define URLManager rules");

		if ($queryString == null)
			$queryString = $_SERVER["REQUEST_URI"];

		if ($this->filters)
		{
			foreach ($this->filters as $filter)
				$queryString = preg_replace($filter["search"], $filter["replace"], $queryString);
		}

		$queryString = "/" . ltrim($queryString, "/");

		$result = $queryString;
		foreach ($this->rules as $item)
		{
			$rule = $item["pattern"];
			$target = $item["replace"];

			$match = null;
			if (preg_match($rule, $queryString, $match) > 0)
			{
				$result = preg_replace($rule, $target, $queryString);
				break;// срабатывает только первое совпадение
			}
		}

		// Поместим результат в переменные сервера
		$this->parsePathInfo($result);
	}


	public function createRequestUri($params)
	{

	}

	public function initComponent()
	{

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
?>