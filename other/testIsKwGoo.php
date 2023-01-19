<?php

header('Content-Type: text/plain');

require_once('/opt/kwynn/isKwGoo.php');

echo(isKwGoo() ? 'Y' : 'N');
