<?php
/**
 * Базовый класс для работы с БД
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

class MySQLAdapter implements IDBAdapter
{
	/**
	 * История выполенных SQL запросов
	 *
	 * @var array
	 */
	protected $history = null;

	/**
	 * Ссылка на соединение с БД
	 *
	 * @var resource $db
	 */
	protected $db = null;

	/**
	 * Режим отладки.
	 * При включенном режиме ведется запись
	 * SQL запросов в историю
	 *
	 * @var boolean
	 */
	public $debug = false;

	/**
	 * Имя пользователя
	 *
	 * @var string
	 */
	public $user = null;

	/**
	 * Пароль
	 *
	 * @var string
	 */
	public $password = null;

	/**
	 * Имя сервера (URL, IP)
	 *
	 * @var string
	 */
	public $host = null;

	/**
	 * Имя базы данных
	 *
	 * @var string
	 */
	public $database = null;

	/**
	 * Устанавливать постоянное соединение с сервером
	 *
	 * @var string
	 */
	public $persist = false;

	/**
	 * Порт
	 *
	 * @var int
	 */
	public $port = 3306;

	/**
	 * Путь к сокету
	 *
	 * @var string
	 */
	public $socket = null;

	/**
	 * Параметр new_link может заставить функцию mysql_connect() открыть ещё одно соединение,
	 * даже если соединение с аналогичными параметрами уже открыто
	 *
	 * @var bool
	 */
	public $newLink = false;

	/**
	 * Параметр должен быть комбинацией из следующих констант:
	 * MYSQL_CLIENT_COMPRESS, MYSQL_CLIENT_IGNORE_SPACE, MYSQL_CLIENT_INTERACTIVE
	 *
	 * @link http://ru.php.net/manual/en/mysql.constants.php#mysql.client-flags
	 *
	 * @var int
	 */
	public $clientFlags = 0;

	/**
	 * Список команд, выполняемых сразу после подключения
	 * к серверу
	 *
	 * @var array
	 */
	public $initialCommands = array();

	/**
	 * Конструктор
	 *
	 * @return void
	 * */
	public function __construct()
	{

	}

	/**
	* Экранирует специальные символы в SQL запросе
	*
	* @param string $string SQL запрос
	*
	* @return string
	*/
	public function escape($string)
	{
		if ($this->db === null)
			$this->connect();
		return mysql_real_escape_string($string, $this->db);
	}

	/**
	 * Возвращает значение параметра конфигурации по его имени.
	 * Устанавливает значение по умолчанию, если необходимо
	 *
	 * @param array $params Hashtable containing settings
	 * @param string $key Key
	 * @param mixed $default value
	 *
	 * @return mixed
	 */
	private function getConfigParam($params, $key, $default)
	{
		if (!isset($params[$key]) )
			return $default;
		else
			return $params[$key];
	}

	/**
	* Создает подключение к БД
	*
	*
	* @return void
	*/
	public function connect()
	{
		if (isset($this->socket))
			$this->host = $this->host .":". $this->socket;

		if (isset($this->port) && $this->port != 3306)
			$this->host = $this->host .":". $this->port;

		if (isset($this->persist) && $this->persist == true)
			$this->db = mysql_pconnect($this->host, $this->user, $this->password, $this->clientFlags);
		else
			$this->db = mysql_connect($this->host, $this->user, $this->password, $this->newLink, $this->clientFlags);

		$this->checkConnect();

		mysql_select_db($this->database, $this->db);
		$this->checkError($this->db);

		if (count($this->initialCommands) > 0)
		{
			foreach ($this->initialCommands as $command)
				$this->executeNonQuery($command);
		}
	}

	/**
	 * Закрывает текущее соединение
	 *
	 * @return void
	 */
	public function close()
	{
		if ($this->db !== null)
			mysql_close($this->db);
	}

	/**
	* Проверка успешности соединения к БД
	*
	* @throws Exception
	* @return void
	*/
	private function checkConnect()
	{
		$errNum = mysql_errno();
		$errorDesc = mysql_error();
		if (0 != $errNum)
			throw new RuntimeException("DataBase error: [". $errNum ."] " . $errorDesc, $errNum);
	}

	/**
	* Проверка результата запроса к БД на ошибку
	*
	* @param resource $res Ссылка на соединение к БД
	*
	* @throws Exception
	* @return void
	*/
	private function checkError($res)
	{
		$errNum = mysql_errno($res);
		$errorDesc = mysql_error($res);
		if (0 != $errNum)
			throw new RuntimeException("DataBase error: [". $errNum ."] " . $errorDesc . print_r($this->getQueryHistory(), true), $errNum);
	}

	/**
	 * Выполняет SQL запрос к БД
	 *
	 * @param string $sql Текст SQL Запроса
	 *
	 * @return MySQLResult
	 */
	protected function query($sql)
	{
		if ($this->db === null)
			$this->connect();

		if ($this->debug)
			$s = $this->getmicrotime();
		// выполняем запрос
		$res = mysql_query($sql, $this->db);

		if ($this->debug)
		{
			$e = $this->getmicrotime();
			$this->history[count($this->history) + 1] = $sql . " /*time : " . round($e - $s , 3) . "*/";
		}
		$this->checkError($this->db);

		$genId = 0;
		if (substr(strtolower($sql), 0, 6) === "insert")
			$genId = mysql_insert_id($this->db);

		return new MySQLResult($res, $sql, $genId);
	}

	/**
	 * Возвращает текущее время.
	 * Для измерений скорости запроса
	 *
	 * @return float
	 */
	private function getmicrotime()
	{
		list($usec, $sec) = explode(" ",microtime());
		return ((float)$usec + (float)$sec);
	}

	/**
	* Старт транзакции
	*
	* @return void
	*/
	public function startTransaction()
	{
		$sql = "SET AUTOCOMMIT = 0";
		$this->query($sql);
		$sql = "START TRANSACTION";
		$this->query($sql);
	}

	/**
	* Завершение транзакции
	*
	* @return void
	*/
	public function commitTransaction()
	{
		$sql = "COMMIT";
		$this->query($sql);
		$sql = "SET AUTOCOMMIT = 1";
		$this->query($sql);
	}

	/**
	* Откат транзакции
	*
	* @return void
	*/
	public function rollbackTransaction()
	{
		$sql = "ROLLBACK";
		$this->query($sql);
		$sql = "SET AUTOCOMMIT = 1";
		$this->query($sql);
	}


	/**
	 * Возвращает список строк, полученных из БД
	 *
	 * @param string $sql Текст SQL Запроса
	 *
	 * @return array
	 */
	public function getRows($sql)
	{
		$result = $this->query($sql);
		return $result->getRows();
	}

	/**
	 * Возвращает значения одного столбца из
	 * полученных строк
	 *
	 * @param string $sql Текст SQL Запроса
	 * @param int $col Номер столбца
	 *
	 * @return array
	 */
	public function getColumn($sql, $col = 0)
	{
		$result = $this->query($sql);
		return $result->getColumn($col);
	}


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
	public function executeNonQuery($sql)
	{
		$res = $this->query($sql);
		return $res->getInsertId();
	}

	/**
	 * Возвращает только одну запись из результата запроса
	 *
	 * @param string $sql Текст SQL Запроса
	 *
	 * @return array
	 */
	public function getOneRow($sql)
	{
		$res = $this->getRows($sql);
		if ($res)
			return $res[0];
		else
			return null;
	}

	/**
	 * Возвращает первое поле первой строки из
	 * результата запроса
	 *
	 * @param string $sql Текст SQL Запроса
	 *
	 * @return mixed
	 */
	public function getOne($sql)
	{
		$res = $this->getOneRow($sql);
		if ($res == null)
			return null;
		else
			return reset($res);
	}

	/**
	 * Возвращает список выполненных SQL запросов
	 *
	 * @return array
	 */
	public function getQueryHistory()
	{
		return $this->history;
	}
}
?>