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
	 * Массив с параметрами подключения к БД
	 * 
	 * @var array
	 * @see Configurator
	 */
	protected $config = null;
	
	/**
	 * Режим отладки.
	 * При включенном режиме ведется запись
	 * SQL запросов в историю
	 * 
	 * @var boolean
	 */
	protected $debug = false;
	
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
			$this->connect($this->config);
		return mysql_real_escape_string($string, $this->db);
	}
	
	/**
	* Устанавливает конфигурацию подключения
	*
	* @param array $config Параметры подключения в виде массива ключ=>значение
	* 
	* @see Configurator
	* @return void
	*/
	public function setConfig($config)
	{
		$this->config = $config;
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
	* @param array $params Параметры подключения в виде массива ключ => значение
	* 
	* @return void
	*/
	public function connect($params)
	{
		if ($params == null)
			throw new Exception("DataBase error: invalid configuration");
		
		$this->debug = (bool)$this->getConfigParam($params, "debug", false);
		$user = $this->getConfigParam($params, "user", "");
		$password = $this->getConfigParam($params, "password", "");
		$host = $this->getConfigParam($params, "host", "localhost");
		$database = $this->getConfigParam($params, "database", "");
		$driver = $this->getConfigParam($params, "driver", "MySQL");
		$encoding = $this->getConfigParam($params, "encoding", "utf8");
		$persist = (bool)$this->getConfigParam($params, "persist", false);
		$port = $this->getConfigParam($params, "port", 3306);
		$socket = $this->getConfigParam($params, "socket", null);
		
		if (isset($socket))
			$host = $host .":". $socket;
		
		if (isset($port) && $port != 3306)
			$host = $host .":". $port;

		if (isset($persist) && $persist == true)
			$this->db = @mysql_pconnect($host, $user, $password);
		else 
			$this->db = @mysql_connect($host, $user, $password);
				
		$this->checkConnect();
				
		@mysql_select_db($database, $this->db);
		$this->checkError($this->db);
		
		// set encoding
		if (isset($encoding))
			$this->executeNonQuery("SET NAMES " . $encoding);
	}
	
	/**
	 * Закрывает текущее соединение
	 * 
	 * @return void
	 */
	public function close()
	{
		@mysql_close($this->db);
	}
	
	/**
	* Проверка успешности соединения к БД
	* 
	* @throws Exception
	* @return void
	*/
	private function checkConnect()
	{
		$errNum = @mysql_errno();
		$errorDesc = @mysql_error();
		if (0 != $errNum)
			throw new Exception("DataBase error: [". $errNum ."] " . $errorDesc);
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
		$errNum = @mysql_errno($res);
		$errorDesc = @mysql_error($res);
		if (0 != $errNum)
			throw new Exception("DataBase error: [". $errNum ."] " . $errorDesc . print_r($this->getQueryHistory(), true));
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
			$this->connect($this->config);

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
	 * */
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