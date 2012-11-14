<?php
/**
 *
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi.work@gmail.com>
 */

class MySQLCondition implements ISQLCondition
{

	/**
	 * SQL запрос
	 *
	 * @var string
	 */
	private $sql = " ";

	/**
	 * Значения параметризованного запроса
	 *
	 * @var array
	 */
	public $params = array();

	private function __construct()
	{

	}

	/**
	 * Создает экземпляр MySQLCondition
	 *
	 * @return MySQLCondition
	 */
	public static function create()
	{
		return new self();
	}

	/**
	 * Генерирует SQL код запроса WHERE
	 *
	 * @param string $sql
	 */
	public function where($sql)
	{
		$this->sql .= "WHERE {$sql} ";
		return $this;
	}

	/**
	 * Генерирует SQL код запроса ORDER BY
	 *
	 * @param string $sql
	 *
	 * @return MySQLCondition
	 */
	public function orderBy($orderBy)
	{
		$this->sql .= "ORDER BY {$orderBy} ";
		return $this;
	}

	/**
	 * Генерирует SQL код запроса GROUP BY
	 *
	 * @param string $sql
	 *
	 * @return MySQLCondition
	 */
	public function groupBy($sql)
	{
		$this->sql .= "GROUP BY {$sql} ";
		return $this;
	}

	/**
	 * Генерирует SQL код запроса LIMIT
	 *
	 * @param int $offset Сдвиг
	 * @param int $rowCount Количество строк
	 *
	 * @return MySQLCondition
	 */
	public function limit($offset, $rowCount)
	{
		$this->sql .= "LIMIT {$offset}, {$rowCount} ";
		return $this;
	}

	/**
	 * Генерирует SQL код запроса IN
	 *
	 * @param array $list Список значений
	 *
	 * @return MySQLCondition
	 */
	public function in($list)
	{
		$this->sql .= "IN (" . implode(", ", $list) . ") ";
		return $this;
	}

	/**
	 * Устанавливает значения параметризованного запроса
	 *
	 * @return MySQLCondition
	 */
	public function setParams()
	{
		$this->params = func_get_args();
		return $this;
	}

	/**
	 * Возвращает собранный SQL запрос
	 *
	 * @see ISQLCondition::buildSQL()
	 * @return string
	 */
	public function buildSQL()
	{
		return $this->sql;
	}

	/**
	 * Возвращает список параметров запроса
	 *
	 * @see ISQLCondition::getParams()
	 *
	 * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}
}
?>