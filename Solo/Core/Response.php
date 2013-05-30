<?php
/**
 *
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

namespace Solo\Core;

class Response
{
	/**
	 * Список HTTP-заголовков для отправки
	 *
	 * @var array
	 */
	private static $headers = array();

	/**
	 * Добавляет HTTP-заголовок в коллекцию для послудеющей отправки
	 *
	 * @param string $header Строка заголовка
	 * @param bool $replace определяет, надо ли заменять предыдущий аналогичный заголовок или заголовок того же типа
	 * @param int $httpCode Принудительно задает код ответа HTTP. Следует учитывать, что это будет работать, только если строка string не является пустой.
	 *
	 * @return void
	 */
	public static function addHeader($header, $replace = true, $httpCode = null)
	{
		$obj = new \stdClass();
		$obj->header = $header;
		$obj->replace = $replace;
		$obj->httpCode = $httpCode;

		self::$headers[] = $obj;
	}

	/**
	 * Отправка заголовков браузеру
	 *
	 * @return void
	 */
	public static function sendHeaders()
	{
		foreach (self::$headers as $header)
			header($header->header, $header->replace, $header->httpCode);
	}

	/**
	 * Отправляет один заголовок в браузер
	 *
	 * @param string $header Строка заголовка
	 * @param bool $replace определяет, надо ли заменять предыдущий аналогичный заголовок или заголовок того же типа
	 * @param int $httpCode Принудительно задает код ответа HTTP. Следует учитывать, что это будет работать, только если строка string не является пустой.
	 *
	 * @return void
	 */
	public static function sendHeader($header, $replace = true, $httpCode = null)
	{
		header($header, $replace, $httpCode);
	}


	/**
	 * Отправляет заголовки, приводящие к редиректу на
	 * указанный URL
	 *
	 * @param string $uri URL для редиректа
	 *
	 * @return void
	 */
	public static function redirect($uri)
	{
		if (null == $uri)
			$uri = "/";
		header("Location: " . $uri, true, 302);
		exit();
	}

	/**
	 * Отправляет заголовки, запрещающие кеширование на клиенте
	 *
	 * @return void
	 */
	public static function sendNoCacheHeaders()
	{
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
	}

	/**
	 * Отправляет HTTP заголовок Content-Type
	 *
	 * @param string $encoding Кодировка. Напр. windows-1251, utf-8 etc. By default: utf-8
	 * @param string $type Значение Content-Type. По умолчанию: text/html
	 *
	 * @return void
	 * */
	public static function sendHeaderContentType($encoding = "utf-8", $type = "text/html")
	{
		header("content-type: {$type};charset={$encoding} \r\n");
	}

	public static function sendFile()
	{
		throw new \RuntimeException("Not implemented yet");
	}
}

