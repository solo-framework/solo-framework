<?php
/**
 * Структура, представляющая собой результат
 * SQL запроса, выполненного с помощью SQL адаптера
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

class MySQLResult 
{
	/**
	 * Ресурс, представляющий собой набор данных,
	 * возвращаемый SELECT, SHOW, DESCRIBE, EXPLAIN и другими SQL выражениями
	 * 
	 * @var resource
	 */
	public $result = null;
	
	/**
	 * Текст SQL запроса
	 * 
	 * @var string
	 */
	public $sql = null;
	
	/**
	 * Идентификатор строки, сгенерированный
	 * последним выражением INSERT
	 * 
	 * @var integer
	 */
	protected $insertId = 0;
	
	/**
	 * Конструктор
	 * 
	 * @param resource &$result Ресурс, представляющий собой набор данных
	 * @param string $sql Текст SQL запроса
	 * @param integer $insertId Идентификатор строки, сгенерированный последним выражением INSERT
	 * 
	 * @return void
	 */
	public function MySQLResult(&$result, $sql, $insertId) 
	{
		$this->result = $result;
		$this->sql = $sql;
		$this->insertId = $insertId;
	}
	
	/**
	* Идентификатор строки, сгенерированный последним выражением INSERT
	* 
	* @return integer
	*/
	public function getInsertId()
	{
		return $this->insertId;
	}
	
	/**
	* Возвращает список строк, полученных из БД
	* 
	* @return array
	*/
	public function getRows()
	{
		$rows = null;
		while ($item = mysql_fetch_array($this->result, MYSQL_ASSOC)) 
		{
			$rows[]  = $item;
		}
		return $rows;		
	}
	
	/**
	* Возвращает значения одного столбца из
	* полученных строк
	* 
	* @param int $col Номер столбца
	* 
	* @return array
	*/
	public function getColumn($col = 0)
	{
		$rows = array();
		while ($item = mysql_fetch_array($this->result, MYSQL_NUM)) 
		{
			$rows[] = $item[$col];
		}
		return $rows;	
	}
}
?>