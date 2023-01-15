<?php

$t = 'blah123456@example.com';
$b = microtime(true);
for ($i=0; $i < 2806; $i++) $t = crypt($t, 'apIpUIgaIsuu5y3kqiAXVBdiTGclx2');
$e = microtime(true);
echo($t . "\n");
echo(sprintf('%0.3f', $e - $b) . "\n");
