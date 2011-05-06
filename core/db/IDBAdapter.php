<?php 
/**
 * Интерфейс, описывающий возможности адаптера БД
 * 
 * PHP version 5
 * 
 * @category Framework
 * @package  DataBase
 * @author   Andrey Filippov <afi@i-loto.ru>
 * @license  %license% name
 * @version  SVN: $Id: Entity.php 9 2007-12-25 11:26:03Z afi $
 * @link     nolink
 */

interface IDBAdapter
{
	/**
	* Создает подключение к БД
	* 
	* @param array $params Параметры подключения в виде массива ключ => значение
	* 
	* @return void
	*/
	public function connect($params);
	
	/**
	* Старт транзакции
	* 
	* @return void
	*/	
	public function startTransaction();
	
	/**
	* Завершение транзакции
	* 
	* @return void
	*/	
	public function commitTransaction();
	
	/**
	* Откат транзакции
	* 
	* @return void
	*/	
	public function rollbackTransaction();
	
	/**
	 * Возвращает список строк, полученных из БД
	 * 
	 * @param string $sql Текст SQL Запроса
	 * 
	 * @return array
	 */	
	public function getRows($sql);
	
	/**
	*	Выполняет SQL запрос и проверяет результат на ошибку.
	*	Возвращает последний сгенерированный 
	*   идентификатор автоинкрементного поля
	* 
	*	@param string $sql SQL запрос
	*
	*	@throws Exception
	*	@return int Last insert id
	*/		
	public function executeNonQuery($sql);
	
	/**
	 * Возвращает только одну запись из результата запроса
	 * 
	 * @param string $sql Текст SQL Запроса
	 * 
	 * @return array
	 * */
	public function getOneRow($sql);
	
	/**
	 * Возвращает первое поле первой строки из
	 * результата запроса
	 * 
	 * @param string $sql Текст SQL Запроса
	 * 
	 * @return mixed
	 */
	public function getOne($sql);
	
	/**
	 * Закрывает текущее соединение
	 * 
	 * @return void
	 */
	public function close();
	
}
?>