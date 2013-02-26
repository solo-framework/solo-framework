<?php
/**
 * Класс для работы с расширением gettext
 *
 * 	//
 *	// Настройки i18n
 *	//
 *	"i18n" => array
 *	(
 *		"localePath" => BASE_DIRECTORY . "/app/locale",
 *		"defaultLocaleName" => "en_US",
 *		"domain" => "messages",
 *		"encoding" => "UTF-8"
 *	),
 *
 *
 * PHP version 5
 *
 * @example Translate::init("./tools/locale");
 * @package I18N
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

namespace Solo\Lib\I18n;

class Translate
{
	/**
	 * Нельзя создавать экземпляры
	 */
	private function __construct()
	{}

	/**
	 * Флаг инициализации
	 *
	 * @var boolean
	 */
	private static $instance = null;


	/**
	 * Инициализация словаря
	 *
	 * @param string $domain Имя домена - файла, в котором находится перевод(без расширения)
	 * @param string $localeName Имя локали: ru_RU, en_US и т.д.
	 * @param string $localePath Путь к каталогу, где хранятся переводы
	 * @param string $encoding Кодировка перевода
	 *
	 * @return void
	 */
	public static function init($localePath, $localeName = "en_US", $domain = "messages", $encoding = "UTF-8")
	{
		// уже инициализирован?
		if (self::$instance)
			return true;

		if (!file_exists($localePath))
			throw new \RuntimeException("Не найден каталог с переводами {$localePath}");

		// Задаем нужный язык с помощью магии
		putenv("LC_ALL={$localeName}");
		putenv("LANG={$localeName}");
		putenv("LANGUAGE={$localeName}");

		// для линукса задаем любую
		// валидную локаль
		// Для Windows вообще не будем устанавливать
		if (PHP_OS !== "WINNT")
		{
			// установим en_US.utf8, т.к. она есть везде
			$set = setlocale(LC_ALL, "{$localeName}.utf8");
			if ($set === false)
				throw new \RuntimeException("Указана неправильная локаль для словаря {$localeName}.utf8");
		}

		// Задаем каталог домена, где содержатся переводы
		$bind = bindtextdomain($domain, $localePath);
		if (!$bind)
			throw new \RuntimeException("Ошибка установки пути для домена");

		// Выбираем домен для работы
		$td = textdomain($domain);
		if (!$td)
			throw new \RuntimeException("Ошибка установки домена по умолчанию");

		// Если необходимо, принудительно указываем кодировку
		bind_textdomain_codeset($domain, $encoding);

		self::$instance = true;
	}
}
?>
