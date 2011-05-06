<?php 
/**
 * Класс для составления SQL выражения.
 * Служит для упрощения составления относительно простых запросов.
 * Не учитывает все возможные комбинации SELECT.
 * 
 * PHP version 5
 * 
 * @category Framework
 * @package  Core
 * @author   Andrey Filippov <afi@i-loto.ru>
 * @license  %license% name
 * @version  SVN: $Id: Entity.php 9 2007-12-25 11:26:03Z afi $
 * @link     nolink
 */

class SQLCondition 
{
	/**
	 * Значение отступа в SQL конструкции LIMIT
	 * 
	 * @var integer
	 */
	public $offset = null;
	
	/**
	 * Количество строк в SQL конструкции LIMIT
	 * 
	 * @var integer
	 */
	public $rows = null;
	
	/**
	 * Текст условия WHERE в SQL выражении
	 * 
	 * @var string
	 */
	public $where = null;
	
	/**
	 * Текст условия ORDER BY в SQL выражении
	 * 
	 * @var string
	 */
	public $orderBy = null;
	
	/**
	 * Текст условия GROUP BY в SQL выражении
	 * 
	 * @var string
	 */
	public $groupBy = null;	
	
	/**
	 * Конструктор
	 * 
	 * @param string $where Выражение WHERE
	 * @param string $groupBy Выражение GROUP BY
	 * @param string $orderBy Выражение ORDER BY
	 * @param string $rows Сколько записей возвращать
	 * @param string $offset С какой записи возвращать
	 * 
	 * @return void
	 * */
	public function SQLCondition($where = null, $groupBy = null, $orderBy = null, $rows = null, $offset = null) 
	{
		$this->rows = $rows;
		$this->offset = $offset;
		$this->where = $where;
		$this->orderBy = $orderBy;
		$this->groupBy = $groupBy;
	}
	
	/**
	* Формирует SQL запрос
	* 
	* @return string SQL запрос
	*/
	public function buildSQL()
	{
		$sql = "";
		if ($this->where)
			$sql .= " WHERE " . $this->where;
		if ($this->groupBy)
			$sql .= " GROUP BY " . $this->groupBy;
		if ($this->orderBy)
			$sql .= " ORDER BY " . $this->orderBy;
		if ($this->rows != null && $this->offset !== null)
			$sql .= " LIMIT " . $this->offset . " , " . $this->rows;
		return $sql;
	}
	
	/**
	 * Подготовка параметризованного SQL запроса.
	 * 
	 * @param string $sqlTemplate Шаблон SQL запроса
	 * @param array $data данные для шаблона. Это может быть одиночное значение или массив.
	 * 
	 * @return string SQL запрос
	 * 
	 * @example 
	 * $sqlt = "$sql = "SELECT userid FROM projectpeople WHERE projectid = ?";
	 * $data = 1; // OR $data = array('name', 1, 'bla bla');
	 * $sql = SQLCondition::prepare($sqlt, $data);
	 * */
	public static function prepare($sqlTemplate, $data)
	{
		$args[] = str_replace("?", "%s", $sqlTemplate);
		if (!is_array($data))
			$data = array($data);
		
		foreach ($data as $value) 
		{
			//$type = gettype($value);
			if ($value === null)
				throw new Exception("Value for SQL prepare is NULL");//$args[] = "NULL";
			else 
				$args[] = $value;
		}

		if (count($args) == 1)
			throw new Exception("Data for prepare is empty");
			
		$sql = call_user_func_array("sprintf", $args);
		return $sql;
	}
}
?>