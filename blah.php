<?php

$a = implode(0x00,array('a','b','c'));
echo urlencode($a).PHP_EOL;
print_r(explode(0x00,$a));
