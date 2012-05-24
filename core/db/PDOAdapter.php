<?php
/**
 * Адаптер соединения к БД для PDO
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

class PDOAdapter implements IDBAdapter, IApplicationComponent
{

	/**
	 * Строка подключения в формате DSN
	 * http://www.php.net/manual/en/pdo.drivers.php
	 *
	 * @var string
	 */
	public $dsn = null;

	/**
	 * Имя пользователя для подключения
	 *
	 * @var string
	 */
	public $username = null;

	/**
	 * Список атрибутов драйвера
	 *
	 * http://www.php.net/manual/en/pdo.setattribute.php
	 * http://www.php.net/manual/en/ref.pdo-mysql.php
	 *
	 * @var array
	 */
	public $driverOptions = array();

	/**
	 * Список команд, выполняемых при подключении
	 *
	 * @var array
	 */
	public $initialCommands = array();

	/**
	 * Пароль для подключения
	 *
	 * @var string
	 */
	public $password = null;

	/**
	 * Режим отладки
	 *
	 * @var bool
	 */
	public $isDebug = false;

	/**
	 * Ссылка на соединение с БД
	 *
	 * @var SoloPDO
	 */
	protected $pdo = null;

	/**
	 * Настройки драйвера по-умолчанию
	 *
	 * @var array
	 */
	private $defaultDriverOptions = array(

			// Коммитим сразу после вставки
			PDO::ATTR_AUTOCOMMIT => true,

			// ошибки  преобразуем в исключения
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

			// получаем ассоциативный список при запросе
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
		);

	/**
	 * Конструктор
	 *
	 * @return void
	 * */
	public function __construct()
	{

	}

	/**
	 * Инициализация компонента
	 *
	 * @see IApplicationComponent::initComponent()
	 *
	 * @return void
	 **/
	public function initComponent()
	{
		return true;
	}

	/* (non-PHPdoc)
	 * @see IDBAdapter::connect()
	 */
	public function connect()
	{
		if ($this->pdo == null)
		{
			// объединение настроек драйвера
			if (count($this->driverOptions) > 0)
			{
				foreach ($this->driverOptions as $k => $v)
					$this->defaultDriverOptions[$k] = $v;
			}

			$this->pdo = new SoloPDO($this->dsn, $this->username, $this->password, $this->defaultDriverOptions, $this->isDebug);

			if (count($this->initialCommands) > 0)
			{
				foreach ($this->initialCommands as $command)
					$this->executeNonQuery($command, array());
			}
		}

	}

	/**
	 * Возвращает последний сгенерированный идентификатор записи
	 *
	 * @see IDBAdapter::getLastInsertId()
	 *
	 * @return int
	 */
	public function getLastInsertId()
	{
		return (int)$this->pdo->lastInsertId();
	}

	/**
	 * Проверка доступности соединения
	 *
	 * @return void
	 */
	protected function checkConnection()
	{
		if ($this->pdo == null)
			$this->connect();
	}

	/**
	 * Выполняет запрос на получение данных
	 *
	 * @param string $sql SQL запрос
	 * @param array Список значений
	 * @param array $driverOptions Настройки драйвера
	 *
	 * @return PDOStatement
	 */
	protected function query($sql, array $params, $driverOptions = array())
	{
		$this->checkConnection();
		$stmt = $this->pdo->prepare($sql, $driverOptions);
		$stmt->execute($params);
		return $stmt;
	}

	/**
	* Старт транзакции
	*
	* @return void
	*/
	public function startTransaction()
	{
		$this->checkConnection();
		$this->pdo->beginTransaction();
	}

	/**
	* Завершение транзакции
	*
	* @return void
	*/
	public function commitTransaction()
	{
		$this->pdo->commit();
	}

	/**
	* Откат транзакции
	*
	* @return void
	*/
	public function rollbackTransaction()
	{
		$this->pdo->rollBack();
	}

	/**
	 * Возвращает список строк, полученных из БД
	 *
	 * @param string $sql Текст SQL Запроса
	 * @param array Значения параметризованного запроса
	 * @param array $driverOptions Специальные настройки драйвера для выполняемого запроса
	 *
	 * @return array
	 */
	public function getRows($sql, array $params, $driverOptions = array())
	{
		$stmt = $this->query($sql, $params, $driverOptions);
		return $stmt->fetchAll();
	}

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
	public function getObjects($sql, $className, array $params, $driverOptions = array())
	{
		$stmt = $this->query($sql, $params, $driverOptions);
		$objects = array();
		while ($obj = $stmt->fetchObject($className))
			$objects[] = $obj;

		return $objects;
	}

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
	public function getOneObject($sql, $className, array $params, $driverOptions = array())
	{
		$res = $this->query($sql, $params, $driverOptions);
		return $res->fetchObject($className);
	}

	 /**
	 * Выполняет SQL запрос
	 * Возвращает количество строк, которые затронуты при выполнении запроса
	 *
	 * @param string $sql SQL запрос
	 * @param array Список значений
	 * @param array $driverOptions Настройки драйвера
	 *
	 * @see IDBAdapter::executeNonQuery()
	 *
	 * @return int
	 */
	public function executeNonQuery($sql, array $params, $driverOptions = array())
	{
		$this->checkConnection();
		$stmt = $this->pdo->prepare($sql, $driverOptions);
		$stmt->execute($params);
		return $stmt->rowCount();
	}

	/**
	 * Возвращает только одну запись из результата запроса
	 *
	 * @param string $sql Текст SQL Запроса
	 * @param array Значения параметризованного запроса
	 * @param array $driverOptions Специальные настройки драйвера для выполняемого запроса
	 *
	 * @see IDBAdapter::getOneRow()
	 *
	 * @return mixed
	 */
	public function getOneRow($sql, array $params, $driverOptions = array())
	{
		$stmt = $this->query($sql, $params, $driverOptions = array());
		return $stmt->fetch();
	}

	/**
	 * Возвращает первое поле первой строки из
	 * результата запроса
	 *
	 * @param string $sql Текст SQL Запроса
	 * @param array Значения параметризованного запроса
	 * @param array $driverOptions Специальные настройки драйвера для выполняемого запроса
	 *
	 * @return mixed
	 */
	public function getOne($sql, array $params, $driverOptions = array())
	{
		$row = $this->getOneRow($sql, $params, $driverOptions);
		return reset($row);
	}

	/**
	 * Закрывает соединение с БД
	 *
	 * @see IDBAdapter::close()
	 *
	 * @return void
	 */
	public function close()
	{
		$this->pdo = null;
	}

	/**
	 * Возвращает значения одного столбца из
	 * полученных строк
	 *
	 * @param string $sql Текст SQL Запроса
	 * @param array Значения параметризованного запроса
	 * @param array $driverOptions Специальные настройки драйвера для выполняемого запроса
	 *
	 * @see IDBAdapter::getColumn()
	 * @return mixed
	 */
	public function getColumn($sql, $col = 0, array $params, $driverOptions = array())
	{
		$stmt = $this->query($sql, $params, $driverOptions);
		$res = array();
		while ($data = $stmt->fetchColumn($col))
			$res[] = $data;

		return $res;
	}

	/**
	 * Возвращает список выполненных запросов
	 *
	 * @return array
	 */
	public function getLog()
	{
		return $this->pdo->log;
	}

}

?>