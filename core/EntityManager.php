<?php 
/**
 * Базовый класс для менеджеров сущностей
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

abstract class EntityManager
{
	/**
	 * имя класса сущности, которой управляет менеджер
	 * 
	 * @var string
	 */
	public $class = null;
	
	/**
	 * Соединение к Slave серверу
	 * 
	 * @var resource
	 */
	private $readConnection = null;
	
	/**
	 * Соединение к Master серверу
	 * 
	 * @var resource
	 */
	private $writeConnection = null;
	
	/**
	 * Формат даты и времени, принятый в базе данных
	 * сответствующий параметрам, принимаемым функцией date()
	 * 
	 * @var string
	 */
	public $dateTimeInFormat = "Y-m-d H:i:s";
	
	/**
	 * Формат даты и времени, принятый в приложении
	 * сответствующий параметрам, принимаемым функцией date()
	 * 
	 * @var string
	 */
	public $dateTimeOutFormat = "c";
	
	/**
	 * Создает новый экземпляр сущности
	 * 
	 * @param string $className Имя класса сущности
	 * 
	 * @return Entity object
	 **/
	public static function newEntity($className)
	{
		$name = ucfirst(strtolower($className));
		// from autoload
		$entity = new $name();
		return $entity;
	}
	
	/**
	 * Возвращает имя класса сущности, с которой работает менеджер
	 * 
	 * @return string
	 */
	protected function defineClass()
	{
		if ($this->class == null)
			$this->class = str_replace("Manager", "", get_class($this));
		return $this->class;
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
		if ($object->getId() == null)
			$sql = $this->buildInsert($object);
		else 
			$sql = $this->buildUpdate($object);
		
		$genId = $this->getWriteConnection()->executeNonQuery($sql);
		if ($genId != 0)
			$object->setId($genId);

		return $object;
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
		$ent = self::newEntity($this->defineClass());
		$sql = "DELETE FROM `{$ent->entityTable}` WHERE {$ent->primaryKey} = {$id}";
		$res = $this->getWriteConnection()->executeNonQuery($sql);

		return $res;
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
		$table = strtolower($this->defineClass());
		$sql = "DELETE FROM `{$table}` " . $condition->buildSQL();
		$res = $this->getWriteConnection()->executeNonQuery($sql);
		return $res;
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
		$ent = self::newEntity($this->defineClass());
		$sql = "SELECT * FROM `{$ent->entityTable}` WHERE {$ent->primaryKey} = {$id}";
		$result = $this->getReadConnection()->getOneRow($sql);
		if (null == $result)
			return null;
		$ent = $this->fillEntity($ent, $result);
		return $ent;
	}
	
	/**
	 * Заполняет поля сущности значениями
	 * 
	 * @param object $object Сущность
	 * @param array  $values Ассоциативный массив имен полей и их значений
	 * 
	 * @return object
	 * */
	protected function fillEntity($object, $values)
	{
		foreach ($values as $fld => $value)
		{
			$type = $object->getFieldType($fld);
			if ($type === null)
				continue;
			if ($type === Entity::ENTITY_FIELD_DATETIME || $type === Entity::ENTITY_FIELD_TIMESTAMP)
			{
				$object->$fld = $this->formatDateTimeOut($value);
			}
			else 
			{
				$object->$fld = stripslashes($value);
			}
		}
		return $object;
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
		$object = self::newEntity($this->defineClass());
		$sql = $this->buildSelect($object, $condition);
		$result = $this->getReadConnection()->getRows($sql);
		if (null == $result)
			return null;
		$objects = null;
		foreach ($result as $row) 
		{
			$object = self::newEntity($this->defineClass());
			$objects[] = $this->fillEntity($object, $row);			
		}
		return $objects;
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
		$result = $this->getReadConnection()->getRows($sql);
		if (null == $result)
			return null;
			
		$objects = array();
		foreach ($result as $row) 
		{
			$object = self::newEntity($this->defineClass());
			$objects[] = $this->fillEntity($object, $row);			
		}
		return $objects;
	}
	
	/**
	 * Удаляет все записи данной сущности в БД
	 * 
	 * @return void
	 * */
	public function removeAll()
	{
		$object = self::newEntity($this->defineClass());
		$sql = "DELETE FROM `{$object->entityTable}`";
		return $this->getWriteConnection()->executeNonQuery($sql);
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
		$res = $this->get($condition);
		if ($res != null)
		{
			if (count($res) > 1)
				throw new Exception("More then one record returned");
			return $res[0];
		}
		else 
		{
			return null;
		}
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
		return $this->getReadConnection()->getRows($sql);
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
		$res = $this->getByAnySQL($sql);
		if ($res != null)
		{
			if (count($res) > 1)
				throw new Exception("More then one record returned");
			return $res[0];
		}
		else 
		{
			return null;
		}	
	}
	
	/**
	 * Генерирует SQL запрос по заданному SQLCondition
	 * 
	 * @param Entity       $object    Экземпляр сущности
	 * @param SQLCondition $condition Объект условия
	 * 
	 * @return string текст SQL запроса
	 */
	protected function buildSelect(Entity $object, SQLCondition $condition = null)
	{
		$sql = "SELECT * FROM `{$object->entityTable}`";
		if ($condition != null)
			$sql = $sql . $condition->buildSQL();
		return $sql;
	}
	
	/**
	 * Генерирует SQL запрос на вставку данных в БД
	 * 
	 * @param Entity $object Экземпляр сущности
	 * 
	 * @return string текст SQL запроса
	 */
	protected function buildInsert(Entity $object)
	{
		$object->selfTest();
		$types = $object->getFields();
		$result = array();
		foreach ($types as $name => $type)
		{
			// если поле заполняется в базе автоматически, то его исключаем из
			// генерации SQL
			if ($type == Entity::ENTITY_FIELD_CURRENT_TIMESTAMP)
			{
				continue;
			}	
					
			$val = $object->$name;
			if (null === $val)
				continue;
			if ($type == Entity::ENTITY_FIELD_STRING)
			{
				$val = $this->escape($val);
				$val = "'{$val}'";
			}
			if ($type == Entity::ENTITY_FIELD_DATETIME || $type == Entity::ENTITY_FIELD_TIMESTAMP)
			{
				$val = $this->formatDateTimeIn($val);
				$val = "'{$val}'";
			}
			if ($type == Entity::ENTITY_FIELD_TIME)
			{
				$val = XDateTime::formatTime($val);
				$val = "'{$val}'";
			}
			$result['`' . $name . '`'] = $val;
		}
		
		$fields = implode(", ", array_keys($result));
		$values = implode(", ", array_values($result));
		$table = $object->entityTable;

		return "INSERT INTO `{$table}` ({$fields}) VALUES({$values})";
	}
	
	/**
	 * Строит SQL запрос для UPDATE сущности
	 * 
	 * @param Entity $object Сущность
	 * 
	 * @return string SQL текст SQL запроса
	 */
	protected function buildUpdate(Entity $object)
	{
		$object->selfTest();
		$types = $object->getFields();
		$result = array();
		$existsFields = get_object_vars($object);
		
		foreach ($types as $name => $type)
		{
			// нет такого поля - если убрали вручную, чтобы не обновлять
			//if (!property_exists($object, $name))
			if (!array_key_exists($name, $existsFields))
				continue;
			
			$val = $object->$name;
			// если поле заполняется в базе автоматически, то его исключаем из
			// генерации SQL
			if ($type == Entity::ENTITY_FIELD_CURRENT_TIMESTAMP)
			{
				continue;
			}
			if (null == $val)			
			{
				if ($val === 0)
					$val = 0;
				else 
					$val = 'null';
			}
			else 
			{
				if ($type == Entity::ENTITY_FIELD_STRING)
				{
					$val = $this->escape($val);
					$val = "'{$val}'";
					
				}
				if ($type == Entity::ENTITY_FIELD_DATETIME || $type == Entity::ENTITY_FIELD_TIMESTAMP)
				{
					$val = $this->formatDateTimeIn($val);				
					$val = "'{$val}'";
				}
				if ($type == Entity::ENTITY_FIELD_TIME)
				{
					$val = XDateTime::formatTime($val);
					$val = "'{$val}'";
				}				
			}
			$result[] = '`' .$name . '`'." = ".$val;
		}
		
		$values = implode(", ", $result);
		$table = $object->entityTable;

		return "UPDATE `{$table}` SET {$values} WHERE {$object->primaryKey} = {$object->getId()}";	
	}
	
	/**
	* Стартуем транзакцию
	* 
	* @return void
	*/
	public function startTransaction()
	{
		$this->getWriteConnection()->startTransaction();
	}
	
	/**
	* Завершение транзакции
	* 
	* @return void
	*/
	public function commitTransaction()
	{
		$this->getWriteConnection()->commitTransaction();
	}
	
	/**
	* откат транзакции
	* 
	* @return void
	*/
	public function rollbackTransaction()
	{
		$this->getWriteConnection()->rollbackTransaction();
	}
	
	/**
	* Иногда репликация не используется, поэтому Read и Write 
	* соединения можно объединить в одно
	* 
	* @param IDBAdapter $object Объект IDBAdapter
	* 
	* @return void
	*/
	public function setCommonConnection(IDBAdapter $object)
	{	
		$this->readConnection = $this->writeConnection = $object;
	}
	
	/**
	* Назначает соединение к Master серверу
	* 
	* @param IDBAdapter $object Объект IDBAdapter
	* 
	* @return void
	*/
	public function setWriteConnection(IDBAdapter $object)
	{	
		$this->writeConnection = $object;
	}
	
	/**
	* Назначает соединение к Slave серверу
	* 
	* @param IDBAdapter $object Объект IDBAdapter
	* 
	* @return void
	*/
	public function setReadConnection(IDBAdapter $object)
	{	
		$this->readConnection = $object;
	}
	
	/**
	* Возвращает соединение к Master серверу
	* 
	* @return IDBAdapter object
	* @throws Exception
	*/
	public function getWriteConnection()
	{	
		if ($this->writeConnection != null)
			return $this->writeConnection;
		else 
			throw new Exception("Write connection is NULL");
	}
	
	/**
	* Возвращает соединение к Slave серверу
	* 
	* @return IDBAdapter object
	* @throws Exception
	*/
	public function getReadConnection()
	{	
		if ($this->readConnection != null)
			return $this->readConnection;
		else 
			throw new Exception("Read connection is NULL");
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
		$this->getWriteConnection()->executeNonQuery($sql);
	}
	
	/**
	* Форматирует список сущностей в хэш так, чтобы
	* ключом был ID сущности,а значением - сама сущность
	* 
	* @param array $list Массив сущностей
	* 
	* @return array
	*/
	public static function formatEntityList($list)
	{
		if ($list == null)
			return null;
		$res = null;
		foreach ($list as $object)
		{
			$res[$object->getId()] = $object;			
		}
		return $res;
	}
	
	/**
	 * Форматирование даты и времени в Формат базы данных
	 *
	 * @param string $date Дата в формате strtotime() 
	 * 
	 * @return string
	 */
	public function formatDateTimeIn($date)
	{
		if ($date === null)
			return null;

		$dt = new XDateTime($date);
	
		return $dt->format($this->dateTimeInFormat);
	}
	
	/**
	 * Форматирование даты и времени в Формат представления
	 * 
	 * @param string $date Дата в формате базы данных
	 * 
	 * @return string
	 */
	public function formatDateTimeOut($date)
	{
		if ($date === null)
			return null;

		$dt = new XDateTime($date);
		return $dt->format($this->dateTimeOutFormat);
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
		return $this->readConnection->getColumn($sqlQuery, $colNum);
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
		return $this->writeConnection->escape($string);
	}	
}
?>