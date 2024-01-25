<?php

header('Content-Type: text/css');

require_once 'lessc.inc.php';

$less = new lessc;

echo $less->compilefile('app.less');


?>