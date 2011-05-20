;<?php exit(); ?>

;**********************************************
;
; Application configuration file
; 
; Этот файл содержит все настройки проекта
; Он должен быть переопределен персональным файлом настроек
; у каждого разработчика или на production-сервере
;
;**********************************************

;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;
;
; Настройки импорта каталогов и классов
;
;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;
[import]
classMapFile = BASE_DIRECTORY "/var/class.map"
import = "@base/app/views/*,@base/app/views/components/*,@base/app/managers/*,@base/app/entity/*,@base/app/actions/*,@framework/lib/Validator/*"

;;;;;;;;;;;;;;;;;;;;;;;;;;;;
; Настройки приложения
;;;;;;;;;;;;;;;;;;;;;;;;;;;;

[application]
; имя сессии
sessionname = "local-dance"

; будем ли отправлять заголовки запрещающие кэширование
nocache = true

; кодировка
encoding = "utf-8"

; режим отладки
debug = true

; путь к каталогу для временных файлов
directory.temp = BASE_DIRECTORY "/var/tmp" ; Каталог для временных файлов

; XML файл, который содержит номер текущей ревизии
file.revision = BASE_DIRECTORY "/version.xml"

; Каталог, где хранятся шаблоны для
; макетов страниц Layouts (относительно каталога приложения)
directory.layouts = BASE_DIRECTORY "/app/templates/layouts"

; Каталог, где хранятся шаблоны для
; контролов (относительно каталога приложения)
directory.templates = BASE_DIRECTORY "/app/templates"
 
;;;;;;;;;;;;;;;;;;;;;;;;;;;;
; Используется репликация
;
; Настройки соединения 
; к базе данных MASTER 
;;;;;;;;;;;;;;;;;;;;;;;;;;;;

[master]
debug = true
user = "root"
password = "password"
host = "localhost"
database = "pythagor"
driver = "MySQL"
encoding = "utf8"

; set persistent connection
persist = false

; you may use socket
;socket = "/var/run/mysqld/mysqld.sock"

; set another value if you need
port = 3306

;;;;;;;;;;;;;;;;;;;;;;;;;;;;
; Используется репликация
;
; Настройки соединения 
; к базе данных SLAVE 
;;;;;;;;;;;;;;;;;;;;;;;;;;;;

[slave]
debug = true
user = "root"
password = "password"
host = "localhost"
database = "pythagor"
driver = "MySQL"
encoding = "utf8"

; set persistent connection
persist = false

; you may use socket
;socket = "/var/run/mysqld/mysqld.sock"

; set another value if you need
port = 3306

;;;;;;;;;;;;;;;;;;;;;;;;;;;;
; template system options
;;;;;;;;;;;;;;;;;;;;;;;;;;;;

[smarty]

; При каждом вызове РНР-приложения Smarty проверяет, изменился или нет текущий шаблон с момента 
; последней компиляции. Если шаблон изменился, он перекомпилируется. В случае, если шаблон еще не 
; был скомпилирован, его компиляция производится с игнорированием значения этого параметра. 
; По умолчанию эта переменная установлена в true. В момент, когда приложение начнет работать в реальных условиях 
;(шаблоны больше не будут изменяться), этап проверки компиляции становится ненужным.
compile.check = true


; Активирует debugging console - окно браузера, содержащее информацию о подключенных шаблонах 
; и загруженных переменных для текущей страницы. 
debugging = false


; Установка уровня ошибок, которые будут отображены. Соответствует уровням ошибок PHP
error.reporting = E_ALL & ~E_NOTICE


; Путь к каталогу для скомпилированных шаблонов
compile.dir = BASE_DIRECTORY "/var/compile"


; Каталог для хранения конфигурационных файлов, используемых в шаблонах. 
; По умолчанию установлено в "./configs", т.е. поиск каталога с конфигурационными файлами 
; будет производиться в том же каталоге, в котором выполняется скрипт
config.dir = ""


; Имя каталога, в котором хранится кэш шаблонов. По умолчанию установлено в "./cache". 
; Это означает, что поиск каталога с кэшем будет производиться в том же каталоге, в котором 
; выполняется скрипт. Вы также можете использовать собственную функцию-обработчик для управления 
; файлами кэша, которая будет игнорировать этот параметр
cache.dir = BASE_DIRECTORY "/var/cache"


; Это директория (или директории), в которых Smarty будет искать необходимые ему плагины. 
; По умолчанию это поддиректория "plugins" директории куда установлен Smarty. Если вы укажете относительный путь, 
; Smarty будет в первую очередь искать относительно SMARTY_DIR, затем оносительно текущей рабочей директории 
; (cwd, current working directory), а затем относительно каждой директории в PHP-директиве include_path. 
; Если $plugins_dir является массивом директорий, Smarty будет искать ваш плагин в каждой директории плагинов 
; в том порядке, в котором они указаны.
user.plugins = BASE_DIRECTORY "/app/smarty.plugins"


; Настройки безопасности Smarty. Рекомендуется значение TRUE
security = true

; Левый разделитель тегов Smarty
leftDelimiter = "{"

; Правый разделитель тегов Smarty
rightDelimiter = "}"

; Это список имён PHP-функций, разрешенных к использованию в условиях IF. 
; Описанные здесь - добавляются к предопределенным. Должны быть разделены запятой
;IF_FUNCS = "strpos,count"


; Это список имён PHP-функций, разрешенных к использованию в качестве модификаторов переменных.
; Описанные здесь - добавляются к предопределенным. Должны быть разделены запятой
;MODIFIER_FUNCS = "strpos,count"

; This is the list of template directories that are considered secure.
; $template_dir is in this list implicitly. Через запятую.
secureDirs = BASE_DIRECTORY "/app/templates"



;;;;;;;;;;;;;;;;;;;;;;;;;;;;
; Logger options
;;;;;;;;;;;;;;;;;;;;;;;;;;;;

[logger]
logger.dir = BASE_DIRECTORY "/var/logs"