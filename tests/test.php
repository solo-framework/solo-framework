<?php

//$uri = "/view/user/id/10";
//$pattern = "/view/user/";
//
//$match = array();
//$res = preg_match("~^{$pattern}~", $uri, $match);
//if ($res > 0)
//{
//	print_r($match);
//}
//
//var_dump($res);

//$str = 'foobar: 2008';
//preg_match('/(?<name>\w+): (?<digit>\d+)/', $str, $matches);
//
//$res = array();
//foreach ($matches as $k => $v)
//{
//	if (is_numeric($k))
//		continue;
//	else
//		$res[$k] = $v;
//}
//
//
//print_r($res);

//$rx = '%/(?P<param>[\w]+)/(?P<value>[\w]+)/(?P<id>[\w]+)/(?P<param2>[\d]+)%';

//$rx = '%/(?P<param>[\d]+)%';
//$uri = "/10";
//
//preg_match($rx, $uri, $m);
//
//print_r($m);
//
//exit();

//$rule = "/:param1:{any}/:par:{num}";
$rule = "/id/:id:{num}/:name:{any}";
$uri = "/id/10/myname";

$wildcards = array(
	'{any}' => '[a-zA-Z0-9\.\-_%=]+',
	'{num}' => '[0-9]+'
);

// сначала заменить placeholders
//$rule = preg_replace('~:([\w]+):~', '(?<$1>$2', $rule);
$rule = preg_replace('%:([\w]+):(\{[\w]+\})%', '(?P<$1>$2)', $rule);
$rule = "~" . trim($rule, '/') . "~";

// затем шаблоны
$rule = str_replace(array_keys($wildcards), array_values($wildcards), $rule);
print_r($rule);

$isMatch = preg_match($rule, $uri, $matches);

$res = array();
if ($isMatch)
{
	foreach ($matches as $k => $v)
	{
		if (is_numeric($k))
			continue;
		else
			$res[$k] = $v;
	}
}

print_r($res);