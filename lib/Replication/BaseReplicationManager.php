<?php
/**
 * Базовый класс для менеджеров проекта
 * Разработан с учетом репликации
 * 
 * При старте транзакции чтение и запись происходит
 * на MASTER сервере
 * 
 * Если транзакции нет или она завершена, то чтение происходит со SLAVE,
 * а запись на MASTER
 * 
 * PHP version 5
 * 
 * @category Framework
 * @package  Core
 * @author   Andrey Filippov <afi.work@gmail.com>
 * @license  %license% name
 * @version  SVN: $Id: BaseEntityManager.php 2982 2011-02-08 14:10:06Z afi $
 * @link     nolink
 */

class BaseReplicationManager extends EntityManager
{
	/**
	 * Признак того, что транзакция уже стартовала
	 * 
	 * @var boolean
	 */
	public static $isTransactionStarted = false;
	
	/**
	 * Конструктор
	 * 
	 * @return void
	 */
	function __construct()
	{		
		if (self::$isTransactionStarted)
			$this->removeReplication();
		else
			$this->setReplication();
	}


	/**
	* Сохраняет сущность в БД
	* 
	* @param Entity &$object Экземпляр сущности
	* 
	* @return Entity
	**/
	public function save(Entity &$object)
	{
		if (self::$isTransactionStarted)
			$this->removeReplication();
			
		return parent::save($object);
	}
	
	
	/**
	 * Удаляет сущность из базы по заданному идентификатору
	 * 
	 * @param int $id Идентификатор сущности
	 * 
	 * @return void
	 */
	public function remove($id)
	{
		if (self::$isTransactionStarted)
			$this->removeReplication();
			
		return parent::remove($id);
	}
	
	/**
	 * Удаляет записи, определенные в SqlCondition
	 * 
	 * @param SQLCondition $condition object of SQLCondition class
	 * 
	 * @return void
	 */
	public function removeByCondition(SQLCondition $condition)
	{
		if (self::$isTransactionStarted)
			$this->removeReplication();
			
		return parent::removeByCondition($condition);
	}
	
	/**
	* Возвращает сущность по идентификатору
	* 
	* @param int $id идентификатор сущности
	* 
	* @return Entity
	*/
	public function getById($id)
	{
		if (self::$isTransactionStarted)
			$this->removeReplication();
			
		return parent::getById($id);
	}
	
	/**
	* Возвращает список сущностей по условию
	* 
	* @param SQLCondition $condition Объект класса SQLCondition
	* 
	* @return null or array
	*/
	public function get(SQLCondition $condition = null)
	{
		if (self::$isTransactionStarted)
			$this->removeReplication();
			
		return parent::get($condition);		
	}
	
	/**
	* Возвращает список сущностей текущего типа по сложному
	* SQL запросу. Важно, чтобы запрос возвращал данные только(!) из соответствующей
	* таблицы. Если запрос возвращает поля не определенные в сущности, они игнорируются. 
	* 
	* @param string $sql текст SQL запроса
	* 
	* @return mixed
	*/
	public function getBySQL($sql)
	{
		if (self::$isTransactionStarted)
			$this->removeReplication();

		return parent::getBySQL($sql);
	}
	
	/**
	 * Удаляет все записи данной сущности в БД
	 * 
	 * @return void
	 * */
	public function removeAll()
	{
		if (self::$isTransactionStarted)
			$this->removeReplication();

		return parent::removeAll();
	}
	
	/**
	* Возвращает только одну запись из результирующего набора
	* 
	* @param SQLCondition $condition Объект класса SQLCondition 
	* 
	* @return entity or null
	*/
	public function getOne(SQLCondition $condition)
	{
		if (self::$isTransactionStarted)
			$this->removeReplication();

		return parent::getOne($condition);		
	}	
	
	/**
	 * Выполняет произвольный запрос и возвращает список массивов
	 * 
	 * @param string $sql SQL запрос
	 * 
	 * @return array
	 * */
	public function getByAnySQL($sql)
	{
		if (self::$isTransactionStarted)
			$this->removeReplication();

		return parent::getByAnySQL($sql);		
	}
	
	/**
	 * Выполняет произвольный запрос и возвращает 
	 * только одну запись из результирующего набора
	 * 
	 * @param string $sql Текст SQL запроса
	 * 
	 * @return mixed
	 */
	public function getOneByAnySQL($sql)
	{
		if (self::$isTransactionStarted)
			$this->removeReplication();

		return parent::getOneByAnySQL($sql);		
	}
	
	/**
	* Выполняет SQL запрос не требующий возврата
	* каких-либо данных
	* 
	* @param string $sql SQL запрос
	* 
	* @return void
	*/
	public function executeNonQuery($sql)
	{
		if (self::$isTransactionStarted)
			$this->removeReplication();

		parent::executeNonQuery($sql);		
	}
	
	/**
	 * Возвращает столбец значений 
	 * 
	 * @param string $sqlQuery Любой SQL запрос
	 * @param int    $colNum   Номер столбца в результирующем наборе данных, который будет возвращен
	 * 
	 * @return array
	 */
	public function getColumn($sqlQuery, $colNum = 0)
	{
		if (self::$isTransactionStarted)
			$this->removeReplication();

		return parent::getColumn($sqlQuery, $colNum);
	}
	
	/**
	 * Экранирует специальные символы в строках 
	 * для использования в выражениях SQL, 
	 * принимая во внимание кодировку соединения
	 * 
	 * @param string $string Входная строка
	 * 
	 * @return string
	 */
	public function escape($string)
	{
		if (self::$isTransactionStarted)
			$this->removeReplication();

		return parent::escape($string);
	}
	
	/**
	 * Выставляет режим репликации, 
	 * когда на чтение и запись разные соединения
	 * 
	 * @return void
	 */
	private function setReplication()
	{
		$this->setWriteConnection(Application::getConnection("master"));
		$this->setReadConnection(Application::getConnection("slave"));
	}
	
	/**
	 * Убирает режим репликации: на чтение и запись - одно соединение MASTER
	 * 
	 * @return void
	 */
	private function removeReplication()
	{
		$this->setCommonConnection(Application::getConnection("master"));
	}
	
	/**
	* Старт транзакции
	* 
	* @return void
	*/
	public function startTransaction()
	{
		if (self::$isTransactionStarted)
			throw new Exception("Вложенные транзакции не поддерживаются");
		
		// когда стартуем транзакцию, то принудительно ставим
		// на чтение и запись Master. Это необходимо при
		// использовании репликации, чтобы можно было прочитать
		// только что записанные данные. 
		$this->removeReplication();
		parent::startTransaction();
		self::$isTransactionStarted = true;
	}
	
	/**
	* Завершение транзакции
	* 
	* @return void
	*/
	public function commitTransaction()
	{		
		parent::commitTransaction();
		self::$isTransactionStarted = false;
		
		// после завершения транзакции возвращаем соединения на разные серверы
		$this->setReplication();
	}
	
	/**
	* Откат транзакции
	* 
	* @return void
	*/
	public function rollbackTransaction()
	{
		parent::rollbackTransaction();
		self::$isTransactionStarted = false;
		
		// после отката транзакции возвращаем соединения на разные серверы
		$this->setReplication();
	}
}
?>