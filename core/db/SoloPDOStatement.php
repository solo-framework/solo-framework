<?php
/**
 * Класс расширяет возможности PDOStatement
 * Добавлена возможность логгирования запросов
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

class SoloPDOStatement extends PDOStatement
{
	/**
	 * Ссылка на соединение к БД
	 *
	 * @var SoloPDO
	 */
	private $pdo = null;

	/**
	 * Режим отладки
	 *
	 * @var bool
	 */
	private $isDebug = false;

	/**
	 * Конструктор
	 *
	 * @param SoloPDO $pdo Ссылка на соединение к БД
	 *
	 * @return void
	 */
	protected function __construct(SoloPDO $pdo)
	{
		$this->isDebug = $pdo->isDebug;
		$this->pdo = $pdo;
	}


	/**
	 * Выполняет подготовленное выражение
	 *
	 * @param array $params Список значений
	 *
	 * @see PDOStatement::execute()
	 * @return bool Returns true on success or false on failure.
	 */
	public function execute($params = null)
	{
		$return = null;
		if ($this->isDebug)
		{

			$res = preg_split('/(\?)/', $this->queryString);
			$sql = "";
			$count = count($res);
			if ($count > 0)
			{
				for ($i = 0; $i < $count; $i++)
				{
					$tmp = isset($params[$i]) ? $this->pdo->quote($params[$i]) : null;
					$sql .= $res[$i] . $tmp;
				}
			}


			$start = microtime(true);
			$return = parent::execute($params);
			$finish = microtime(true);
			$delta = round($finish - $start, 5);
			$this->pdo->log[] = $sql . " /* time: {$delta} */";
		}
		else
		{
			$return = parent::execute($params);
		}

		return $return;
	}
}
?>