<?php
return array
(
	//
	// Настройки импорта каталогов и классов
	//
	"import" => array
	(
		// в этот файл будет записана информация об импортированных каталогах и файлах
		"classMapFile" => BASE_DIRECTORY . "/var/class.map",

		//
		// Установка псевдонимов путей. Псевдонимы используются для упрощения
		// указания путей. Использование: ClassLoader::import("@framework/SomeClass.php");
		// В этом случае директива @framework заменяется на полный путь к соответствующему каталогу
		//
		"alias" => array
		(
			// базовый каталог
			"base" => BASE_DIRECTORY,

			// путь к фреймворку
			"framework" => BASE_DIRECTORY . "/framework",

			// путь к каталогу, где находятся файлы бизнес-логики (views, actions, etc.)
			"app" => BASE_DIRECTORY . "/app",

			// Каталог, доступный для Apache HTTP Server (Document root)
			"public" => BASE_DIRECTORY . "/public",
		),

		// Эти каталоги будут импортированы в приложение
		"import" => array
		(
			"@framework/core/*",
			"@framework/core/db/*",
			"@framework/lib/Validator/*",
			"@base/app/*",
			"@base/app/views/*",
			"@base/app/views/components/*",
			"@base/app/managers/*",
			"@base/app/entity/*",
			"@base/app/actions/*"
		)
	),

	//
	// Настройки приложения
	//

	"application" => array
	(
		// имя сессии
		"sessionname" => "application_name",

		// будем ли отправлять заголовки запрещающие кэширование
		"nocache" => true,

		// кодировка
		"encoding" => "utf-8",

		// режим отладки
		"debug" => true,

		// путь к каталогу для временных файлов
		"directory.temp" => BASE_DIRECTORY."/var/tmp",

		// XML файл, который содержит номер текущей ревизии
		"file.revision" => BASE_DIRECTORY ."/version.xml",

		// Каталог, где хранятся шаблоны для
		// макетов страниц Layouts (относительно каталога приложения)
		"directory.layouts" => BASE_DIRECTORY ."/app/templates/layouts",

		// Каталог, где хранятся шаблоны для
		// контролов (относительно каталога приложения)
		"directory.templates" => BASE_DIRECTORY ."/app/templates",
	),


	//
	// Настройки соединения MASTER
	//
	"master" => array
	(
		"debug" => true,
		"user" => "root",
		"password" => "password",
		"host" => "localhost",
		"database" => "classnet",
		"driver" => "MySQL",
		"encoding" => "utf8",

		// set persistent connection
		"persist" => false,

		// Можно использовать сокеты
		"socket" => "/var/run/mysqld/mysqld.sock",

		// порт
		"port" => 3306,
	),


	//
	// Настройки шаблонизатора Smarty
	//
	"smarty" => array
	(
		// При каждом вызове РНР-приложения Smarty проверяет, изменился или нет текущий шаблон с момента
		// последней компиляции. Если шаблон изменился, он перекомпилируется. В случае, если шаблон еще не
		// был скомпилирован, его компиляция производится с игнорированием значения этого параметра.
		// По умолчанию эта переменная установлена в true. В момент, когда приложение начнет работать в реальных условиях
		// (шаблоны больше не будут изменяться), этап проверки компиляции становится ненужным.
		"compile.check" => true,


		// Активирует debugging console - окно браузера, содержащее информацию о подключенных шаблонах
		// и загруженных переменных для текущей страницы.
		"debugging" => false,


		//; Установка уровня ошибок, которые будут отображены. Соответствует уровням ошибок PHP
		"error.reporting" => E_ALL & ~E_NOTICE,


		//; Путь к каталогу для скомпилированных шаблонов
		"compile.dir" => BASE_DIRECTORY . "/var/compile",


		// Каталог для хранения конфигурационных файлов, используемых в шаблонах.
		// По умолчанию установлено в "./configs", т.е. поиск каталога с конфигурационными файлами
		// будет производиться в том же каталоге, в котором выполняется скрипт
		"config.dir" => "",


		// Имя каталога, в котором хранится кэш шаблонов. По умолчанию установлено в "./cache".
		// Это означает, что поиск каталога с кэшем будет производиться в том же каталоге, в котором
		// выполняется скрипт. Вы также можете использовать собственную функцию-обработчик для управления
		// файлами кэша, которая будет игнорировать этот параметр
		"cache.dir" => BASE_DIRECTORY . "/var/cache",


		// Это директория (или директории), в которых Smarty будет искать необходимые ему плагины.
		// По умолчанию это поддиректория "plugins" директории куда установлен Smarty. Если вы укажете относительный путь,
		// Smarty будет в первую очередь искать относительно SMARTY_DIR, затем оносительно текущей рабочей директории
		// (cwd, current working directory), а затем относительно каждой директории в PHP-директиве include_path.
		// Если $plugins_dir является массивом директорий, Smarty будет искать ваш плагин в каждой директории плагинов
		// в том порядке, в котором они указаны.
		"user.plugins" => BASE_DIRECTORY . "/app/smarty.plugins",


		//; Настройки безопасности Smarty. Рекомендуется значение TRUE
		"security" => true,

		//; Левый разделитель тегов Smarty
		"leftDelimiter" => "{",

		//; Правый разделитель тегов Smarty
		"rightDelimiter" => "}",

		//; Это список имён PHP-функций, разрешенных к использованию в условиях IF.
		//; Описанные здесь - добавляются к предопределенным. Должны быть разделены запятой
		//;IF_FUNCS => "strpos,count"


		//; Это список имён PHP-функций, разрешенных к использованию в качестве модификаторов переменных.
		//; Описанные здесь - добавляются к предопределенным. Должны быть разделены запятой
		//;MODIFIER_FUNCS => "strpos,count"

		//; This is the list of template directories that are considered secure.
		//; $template_dir is in this list implicitly. Через запятую.
		"secureDirs" => BASE_DIRECTORY . "/app/templates"
	),

	//
	// Настройки логирования
	//
	"logger" => array
	(
		"logger.dir" => BASE_DIRECTORY . "/var/logs"
	)
);
?>