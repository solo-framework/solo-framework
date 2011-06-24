<?php
/**
 * Исключение, генерируемое классах бизнес-логики.
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

class ApplicationException extends Exception
{
	/**
	 * Конструктор
	 *
	 * @param string $message
	 * @param Exception $previous
	 */
	public function __construct($message, Exception $previous = null)
	{
		parent::__construct($message, 0, $previous);
	}
}
?>