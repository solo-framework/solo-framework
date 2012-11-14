<?php
/**
 * Интерфейс, описывающий возможности адаптера БД
 *
 * PHP version 5
 *
 * @category Framework
 * @package  DataBase
 * @author   Andrey Filippov <afi.work@gmail.com>
 * @license  %license% name
 * @version  SVN: $Id: Entity.php 9 2007-12-25 11:26:03Z afi $
 * @link     nolink
 */

interface IDBAdapter
{
	/**
	* Создает подключение к БД
	*
	* @return void
	*/
	public function connect();

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
	 * @param string $sql Параметризованный SQL запрос
	 * @param array Значения параметризованного запроса
	 * @param array $driverOptions Специальные настройки драйвера для выполняемого запроса
	 *
	 * @return array
	 */
	public function getRows($sql, array $params, $driverOptions = array());

	 /**
	 * Выполняет SQL запрос
	 * Возвращает количество строк, которые затронуты при выполнении запроса
	 *
	 * @param string $sql Параметризованный SQL запрос
	 * @param array Значения параметризованного запроса
	 * @param array $driverOptions Специальные настройки драйвера для выполняемого запроса
	 *
	 * @see IDBAdapter::executeNonQuery()
	 *
	 * @return int
	 */
	public function executeNonQuery($sql, array $params, $driverOptions = array());

	/**
	 * Возвращает только одну запись из результата запроса
	 *
	 * @param string $sql Параметризованный SQL запрос
	 * @param array Значения параметризованного запроса
	 * @param array $driverOptions Специальные настройки драйвера для выполняемого запроса
	 *
	 * @return array
	 * */
	public function getOneRow($sql, array $params, $driverOptions = array());

	/**
	 * Возвращает первое поле первой строки из
	 * результата запроса
	 *
	 * @param string $sql Параметризованный SQL запрос
	 * @param array Значения параметризованного запроса
	 * @param array $driverOptions Специальные настройки драйвера для выполняемого запроса
	 *
	 * @return mixed
	 */
	public function getOne($sql, array $params, $driverOptions = array());

	/**
	 * Закрывает текущее соединение
	 *
	 * @return void
	 */
	public function close();

	/**
	 * Возвращает значения одного столбца из
	 * полученных строк
	 *
	 * @param string $sql Параметризованный SQL запрос
	 * @param array Значения параметризованного запроса
	 * @param array $driverOptions Специальные настройки драйвера для выполняемого запроса
	 *
	 * @return mixed
	 */
	public function getColumn($sql, $col = 0, array $params, $driverOptions = array());

	/**
	 * Возвращает последний сгенерированный идентификатор записи
	 *
	 * @return int
	 */
	public function getLastInsertId();

	/**
	 * Возвращает экземпляр объекта с атрибутами, соответствующими
	 * значениям записи в БД
	 *
	 * @param string $sql SQL запрос
	 * @param string $className Имя класса объекта
	 * @param array Список значений
	 * @param array $driverOptions Настройки драйвера
	 *
	 * @return mixed
	 */
	public function getOneObject($sql, $className, array $params, $driverOptions = array());

	/**
	 * Возвращает список объектов, имеющих
	 * атрибуты, соответствующие полям таблицы
	 *
	 * @param string $sql SQL запрос
	 * @param string $className Имя класса объекта
	 * @param array Список значений
	 * @param array $driverOptions Настройки драйвера
	 *
	 * @return mixed
	 */
	public function getObjects($sql, $className, array $params, $driverOptions = array());

}
?>