<?php

$arr = array();
$nu = null;

assert(null == false);
assert($arr == $nu);
assert($arr == false);
assert($arr == null);
// assert($arr == null);
assert(array() == null);
assert(array() == false);

$arr = array();
assert($arr == null);
assert($arr !== null);
assert($arr === null);

?>