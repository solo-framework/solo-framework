<?php
/**
 *
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi.work@gmail.com>
 */

namespace Solo\Core;

use \Solo\Core\DB\IDBAdapter;
use RuntimeException;
use DateTime;
use Solo\Core\DB\ISQLCondition;


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
	 * @var IDBAdapter
	 */
	private $readConnection = null;

	/**
	 * Соединение к Master серверу
	 *
	 * @var IDBAdapter
	 */
	private $writeConnection = null;

	/**
	 * Формат даты и времени, принятый в базе данных
	 * Синтаксис соответствует PHP функции date()
	 *
	 * @var string
	 */
	public $dateTimeFormat = "Y-m-d H:i:s";

	/**
	 * Формат времени, принятый в базе данных
	 * Синтаксис соответствует PHP функции date()
	 *
	 * @var string
	 */
	public $timeFormat = "H:i:s";

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
	public function save(Entity $object)
	{
		if ($object->getId() == null)
		{
			$this->insert($object);
			$object->setId($this->getWriteConnection()->getLastInsertId());
		}
		else
		{
			$this->update($object);
		}

		return $object;
	}

	/**
	 * Генерирует SQL запрос на вставку данных в БД
	 * и выполняет его
	 *
	 * @param Entity $object Экземпляр сущности
	 *
	 * @return void
	 */
	protected function insert(Entity $object)
	{
		$object->selfTest();
		$types = $object->getFields();
		$values = array();
		$fields = array();
		foreach ($types as $name => $type)
		{
			// если поле заполняется в базе автоматически, то его исключаем из
			// генерации SQL
			if ($type == Entity::ENTITY_FIELD_CURRENT_TIMESTAMP)
				continue;

			$val = $object->$name;
			if (null === $val)
				continue;

			if ($type == Entity::ENTITY_FIELD_DATETIME || $type == Entity::ENTITY_FIELD_TIMESTAMP)
				$val = $this->formatDateTimeIn($val);

			if ($type == Entity::ENTITY_FIELD_TIME)
				$val = $this->formatTime($val);

			$values[] = $val;
			$fields[] = "`{$name}`";
		}

		$table = $object->entityTable;
		$count = count($values);
		$sql = "";

		if (0 == $count)
		{
			$sql = "INSERT INTO `{$table}` () VALUES()";
		}
		else
		{
			$fields = implode(", ", $fields);
			$holders = implode(", ", array_fill(0, $count, "?"));
			$sql = "INSERT INTO `{$table}` ({$fields}) VALUES({$holders})";
		}
		$this->getWriteConnection()->executeNonQuery($sql, $values);
	}

	/**
	 * Генерирует SQL запрос на обновление сущности в БД
	 * и выполняет его
	 *
	 * @param Entity $object Экземпляр сущности
	 *
	 * @return void
	 */
	protected function update(Entity $object)
	{
		$object->selfTest();
		$types = $object->getFields();
		$values = array();
		$fields = array();
		$existsFields = get_object_vars($object);

		foreach ($types as $name => $type)
		{
			// нет такого поля - видимо убрали вручную, чтобы не обновлять
			// значение в базе
			if (!array_key_exists($name, $existsFields))
				continue;

			$val = $object->$name;
			// если поле заполняется в базе автоматически, то его исключаем из
			// генерации SQL
			if ($type == Entity::ENTITY_FIELD_CURRENT_TIMESTAMP)
				continue;

			if ($type == Entity::ENTITY_FIELD_DATETIME || $type == Entity::ENTITY_FIELD_TIMESTAMP)
				$val = $this->formatDateTimeIn($val);

			if ($type == Entity::ENTITY_FIELD_TIME)
				$val = $this->formatTime($val);

			$values[] = $val;
			$fields[] = "`{$name}` = ?";
		}

		$values[] = $object->getId();
		$fields = implode(", ", $fields);
		$table = $object->entityTable;

		$sql = "UPDATE `{$table}` SET {$fields} WHERE {$object->primaryKey} = ?";
		$this->getWriteConnection()->executeNonQuery($sql, $values);
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
	* @throws \RuntimeException
	*/
	public function getWriteConnection()
	{
		if ($this->writeConnection != null)
			return $this->writeConnection;
		else
			throw new \RuntimeException("Write connection is NULL");
	}

	/**
	* Возвращает соединение к Slave серверу
	*
	* @return IDBAdapter object
	* @throws RuntimeException
	*/
	public function getReadConnection()
	{
		if ($this->readConnection != null)
			return $this->readConnection;
		else
			throw new RuntimeException("Read connection is NULL");
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
		$dt = new DateTime($date);
		return $dt->format($this->dateTimeFormat);
	}

	/**
	 * Форматирование времени в Формат базы данных
	 *
	 * @param string $time Время в формате strtotime()
	 *
	 * @return string
	 */
	public function formatTime($time)
	{
		if ($time == null)
			return null;
		$dt = new DateTime($time);
		return $dt->format($this->timeFormat);
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
		$sql = "SELECT * FROM `{$ent->entityTable}` WHERE {$ent->primaryKey} = ?";
		return $this->getReadConnection()->getOneObject($sql, $this->defineClass(), array($id));
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
		return $this->getWriteConnection()->executeNonQuery($sql, array());
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
		$sql = "DELETE FROM `{$ent->entityTable}` WHERE {$ent->primaryKey} = ?";
		$res = $this->getWriteConnection()->executeNonQuery($sql, array($id));
	}

	/**
	 * Выполняет произвольный запрос и возвращает список строк из БД
	 *
	 * @param string $sql Параметризованный SQL запрос
	 * @param array Значения параметризованного запроса
	 * @param array $driverOptions Специальные настройки драйвера для выполняемого запроса
	 *
	 * @return array
	 * */
	public function getByAnySQL($sql, array $params, $driverOptions = array())
	{
		return $this->getReadConnection()->getRows($sql, $params, $driverOptions);
	}

	/**
	 * Выполняет произвольный запрос и возвращает
	 * только одну запись из результирующего набора
	 *
	 * @param string $sql Текст SQL запроса
	 *
	 * @return mixed
	 */
	public function getOneByAnySQL($sql, array $params, $driverOptions = array())
	{
		return $this->getReadConnection()->getOneRow($sql, $params, $driverOptions);
	}

	/**
	* Возвращает список сущностей по условию
	*
	* @param SQLCondition $condition Объект класса SQLCondition
	*
	* @return null or array
	*/
	public function get(ISQLCondition $condition = null)
	{
		$object = self::newEntity($this->defineClass());
		$sql = "SELECT * FROM `{$object->entityTable}`";
		$params = array();
		if ($condition != null)
		{
			$sql .= $condition->buildSQL();
			$params = $condition->getParams();
		}

		return $this->getReadConnection()->getObjects($sql, $this->defineClass(), $params);
	}

	/**
	* Возвращает список сущностей текущего типа по сложному
	* SQL запросу. Важно, чтобы запрос возвращал данные только(!) из соответствующей
	* таблицы. Если запрос возвращает поля не определенные в сущности, они игнорируются.
	*
	* @param string $sql Параметризованный SQL запрос
	* @param array Значения параметризованного запроса
	* @param array $driverOptions Специальные настройки драйвера для выполняемого запроса
	*
	* @return mixed
	*/
	public function getBySQL($sql, array $params, $driverOptions = array())
	{
		return $this->getReadConnection()->getObjects($sql, $this->defineClass(), $params, $driverOptions);
	}

	/**
	 * Удаляет записи, определенные в SqlCondition
	 *
	 * @param SQLCondition $condition object of SQLCondition class
	 *
	 * @return int Количество удаленных строк
	 */
	public function removeByCondition(ISQLCondition $condition)
	{
		$table = strtolower($this->defineClass());
		$sql = "DELETE FROM `{$table}` " . $condition->buildSQL();
		$res = $this->getWriteConnection()->executeNonQuery($sql, $condition->getParams());
		return $res;
	}

	/**
	* Возвращает только одну запись из результирующего набора
	*
	* @param SQLCondition $condition Объект класса SQLCondition
	*
	* @return entity or null
	*/
	public function getOne(ISQLCondition $condition)
	{
		$res = $this->get($condition);
		if ($res != null)
		{
			if (count($res) > 1)
				throw new RuntimeException("More then one record returned");
			return $res[0];
		}
		else
		{
			return null;
		}
	}

	/**
	* Выполняет SQL запрос не требующий возврата
	* каких-либо данных
	*
	* @param string $sql Параметризованный SQL запрос
	* @param array Значения параметризованного запроса
	* @param array $driverOptions Специальные настройки драйвера для выполняемого запроса
	*
	* @return void
	*/
	public function executeNonQuery($sql, array $params, $driverOptions = array())
	{
		return $this->getWriteConnection()->executeNonQuery($sql, $params, $driverOptions);
	}

	/**
	 * Возвращает столбец значений
	 *
	 * @param string $sqlQuery Параметризованный SQL запрос
	 * @param int    $colNum   Номер столбца в результирующем наборе данных, который будет возвращен
	 * @param array Значения параметризованного запроса
	 * @param array $driverOptions Специальные настройки драйвера для выполняемого запроса
	 *
	 * @return array
	 */
	public function getColumn($sql, array $params, $colNum = 0, $driverOptions = array())
	{
		return $this->getReadConnection()->getColumn($sql, $colNum, $params, $driverOptions);
	}

	/**
	 * Выполняет вызов хранимой процедуры и возвращает список строк из БД
	 *
	 * @param string $sql Параметризованный SQL запрос
	 * @param array Значения параметризованного запроса
	 * @param array $driverOptions Специальные настройки драйвера для выполняемого запроса
	 *
	 * @return array
	 */
	public function callStoredProcedure($sql, array $params, $driverOptions = array())
	{
		return $this->getReadConnection()->getRows($sql, $params, $driverOptions);
	}


	/**
	* Возвращает список сущностей с использованием хранимой процедуры
	*
	* @param string $sql Параметризованный SQL запрос
	* @param array Значения параметризованного запроса
	* @param array $driverOptions Специальные настройки драйвера для выполняемого запроса
	*
	* @return array
	*/
	public function getByStoredProcedure($sql, array $params, $driverOptions = array())
	{
		$class = $this->defineClass();
		$list = $this->getReadConnection()->getRows($sql, $params, $driverOptions);
		$result = null;

		if (count($list) > 0)
		{
			foreach ($list as $field => $value)
			{
				$obj = new $class();
				$obj->$field = $value;
				$result[] = $obj;
			}
		}
		return $result;
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
}
?>
