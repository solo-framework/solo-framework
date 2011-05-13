<?php
/**
 * Класс для рабзора строки запроса и 
 * выяаления запрашиваемых Представлений или Действий
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
 * @category Framework
 * @package  Core
 * @author   Andrey Filippov <afi@i-loto.ru>
 * @license  %license% name
 * @version  SVN: $Id: Router.php 9 2007-12-25 11:26:03Z afi $
 * @link     nolink
 */
class Router
{
	/**
	 * Текущий тип запроса: Действие или Представление
	 * 
	 * @var string
	 */
	public $requestType = self::TYPE_VIEW;
	
	/**
	 * Тип запроса: Действие
	 * 
	 * @var string
	 */
	const TYPE_ACTION = "TYPE_ACTION";
	
	/**
	 * Тип запроса: Представление
	 * 
	 * @var string
	 */	
	const TYPE_VIEW = "TYPE_VIEW";
	
	/**
	 * Список правил разбора строки запроса
	 * 
	 * @var mixed
	 */
	private $rules = null;

	/**
	 * метод HTTP запроса
	 *
	 * @var string
	 */
	public $requestMethod = null;


	/**
	 * Имя представления по умолчанию
	 *
	 * @var string
	 */
	public $defailtView = "Index";
	
	/**
	 * Имя модуля по умолчанию
	 *
	 * @var string
	 */
	public $defailtModule = "Main";
	
	private $currentView = null;
	private $currentModule = null;
	private $currentAction = null;
	
	public function __construct()
	{
		
	}
	
	/**
	 * Добавление правила разбора REQUEST_URI
	 *
	 * @param string $type Тип TYPE_VIEW или TYPE_ACTION
	 * @param string $pattern Регулярное выражение для поиска подстроки
	 * @param string $transform Регулярное выражение для замены подстроки
	 * 
	 * @return void
	 */
	public function addRule($type, $pattern, $transform)
	{
		$this->rules[] = array(
			"type" => $type,
			"pattern" => $pattern,
			"transform" => $transform
		);
	}
	
	/**
	 * Добваление правил маршрутизатора из списка
	 * 
	 * @param array $data Список с правилами маршрутизатора
	 * 
	 * @return void
	 */
	public function addRulesFromConfig($data)
	{
		if ($data == null)
			throw new RuntimeException("Router rules is null");
		
		// количество записей для правил должно быть кратно 3-м, иначе правила
		// составлены неправильно:
		// почему 3?
		// 1-й элемент: Тип правила (TYPE_VIEW или TYPE_ACTION)
		// 2-й элемент: что заменяем (regex)
		// 3-й элемент: на что заменяем (regex)
		if (count($data) % 3)
			throw new RuntimeException("Incorrect count of Router rules");
			
		$data = array_chunk($data, 3);
		foreach ($data as $rule)
		{
			$this->addRule($rule[0], $rule[1], $rule[2]);
		}
	}
	
	/**
	 * Делает преобразование строки запроса в соответствии с 
	 * правилами
	 * 
	 * @param string $queryString Строка запроса ($_SERVER["REQUEST_URI"])
	 * 
	 * @return void
	 */
	public function parse($queryString = null)
	{
		if (count($this->rules) == 0)
			throw new RuntimeException("Please, define Router rules");
		
		if ($queryString == null)
			$queryString = $_SERVER["REQUEST_URI"];
			
		$queryString = "/" . ltrim($queryString, "/");
			
		$result = "";
		foreach ($this->rules as $item)
		{
			$rule = $item["pattern"];
			$target = $item["transform"];
			$type = $item["type"];

			$match = null;
			if (preg_match($rule, $queryString, $match) > 0)
			{
				$this->requestType = $type;
				$result = preg_replace($rule, $target, $queryString);
				break;// срабатывает только первое совпадение
			}
		}

		// Поместим переменные в $_REQUEST
		$this->parsePathInfo($result);

		// Установим имя Модуля
		$this->currentModule = ucfirst(Request::get("module", $this->defailtModule));
		
		// Имя текущего Представления
		if ($this->requestType == self::TYPE_VIEW)
			$this->currentView = ucfirst(Request::get("view", $this->defailtView));
		
		// Имя текущего Действия
		if ($this->requestType == self::TYPE_ACTION)
		{
			$action = Request::get("action", null);
			if ($action == null)
				throw new SystemException("Page not found", "Undefined action name", SystemException::HTTP_404);
			else 
				$this->currentAction = ucfirst($action);
		}

		$this->requestMethod = $_SERVER["REQUEST_METHOD"];
	}
	
	/**
	 * Возвращает имя текущего Представления
	 *
	 * @return string
	 * 
	 * @return void
	 */
	public function getViewName()
	{
		return $this->currentView;
	}
	
	/**
	 * Возвращает имя текущего Модуля
	 *
	 * @return string
	 * 
	 * @return void
	 */
	public function getModuleName()
	{
		return $this->currentModule;
	}
	
	/**
	 * Возвращает имя текущего Действия
	 *
	 * @return string
	 * 
	 * @return void
	 */
	public function getActionName()
	{
		return $this->currentAction;
	}
	
	
	
	/**
	 * Делает разбор строки запроса и 
	 * помещает результаты в $_REQUEST
	 * 
	 * @param string $pathInfo Строка запроса типа param1/value1/param2/value2/
	 * 
	 * @return void
	 */
	function parsePathInfo($pathInfo)
	{
		if ($pathInfo === '')
			return false;
	
		$list = null;
		if (Request::isGet())
			$list = &$_GET;
		if (Request::isPost())
			$list = &$_POST;
			
		$pathInfo = trim($pathInfo, "/");
		$segs = explode('/', $pathInfo . '/');
		$n = count($segs);
		for ($i = 0; $i < $n - 1; $i += 2)
		{
			$key = urldecode($segs[$i]);
			if ($key === '')
				continue;
			$value = urldecode($segs[$i + 1]);
			if (($pos = strpos($key, '[]')) !== false)
				$list[substr($key, 0, $pos)][] = $value;
			else
				$list[$key] = $value;
		}
	}
}
?>