<?php

return array
(
	"URLManager" => array
	(
		"filters" => array
		(
			array("search" => '/index\.php/', "replace" => ""),
		),

		"rules" => array
		(
			// Запрос представления типа http://local.ru/ViewName/param1/value1/param2/value2/
			// заменяется на вид: http://local.ru/view/ViewName/param1/value1/param2/value2/
			// игнорируется "action", меняется только REQUEST_URI
			//
			// Это самое общее правило и оно должно быть последним в списке, уточняющие нужно вставлять выше
			array("pattern" => '~^(?!^[/]?action)[/]?([\w]+)[/]?~i', "replace" => '/view/$1/'),

			array("pattern" => '', "replace" => '')

		)
	)
);

?>