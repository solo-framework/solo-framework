<?php
/**
 * Класс, расширяющий функциональность PDO возможностью логгирования
 * SQL запросов
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi.work@gmail.com>
 */

namespace Solo\Core\DB;

class SoloPDO extends \PDO
{
	/**
	 * Список выполненных SQL запросов
	 *
	 * @var array
	 */
	public $log = array();

	/**
	 * Режим отладки
	 *
	 * @var bool
	 */
	public $isDebug = false;

	/**
	 * Имя класса, наследующего PDOStatement
	 *
	 * @var string
	 */
	public $statementClass = "Solo\Core\DB\SoloPDOStatement";

	/**
	 * Конструктор
	 *
	 * @param string $dsn Строка подключения в формате DSN
	 * @param string $username Имя пользователя БД
	 * @param string $password Пароль для доступа к БД
	 * @param array $driverOptions Список настроек драйвера
	 * @param bool $isDebug Режим отладки
	 *
	 * @return \Solo\Core\DB\SoloPDO
	 */
	public function __construct($dsn, $username = null, $password = null, $driverOptions = array(), $isDebug = false)
	{
		parent::__construct($dsn, $username, $password, $driverOptions);
		$this->isDebug = $isDebug;

		// подготовленные выражения обслуживает наш класс
		$this->setAttribute(\PDO::ATTR_STATEMENT_CLASS, array($this->statementClass, array($this)));

		if ($this->getAttribute(\PDO::ATTR_DRIVER_NAME) == "mysql")
			$this->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

	}

	/**
	 * Выполняет подготовку запроса
	 *
	 * @param string $sql Текст SQL Запроса
	 * @param array $driverOptions Специальные настройки драйвера для выполняемого запроса
	 *
	 * @see PDO::prepare()
	 * @return \PDOStatement
	 */
	public function prepare($sql, $driverOptions = array())
	{
		return parent::prepare($sql, $driverOptions);
	}
}
?>
