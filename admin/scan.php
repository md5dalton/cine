<?php
ini_set('max_execution_time', 0);

header('Content-Type: text/event-stream');

header('Cache-Control: co-cache');


function msg ($id, $data = '') {
	
    echo 

        'id: ' . $id . PHP_EOL .

        'data: ' . json_encode($data) . PHP_EOL .

        PHP_EOL;

    ob_flush();

    flush();

}

require_once 'fn.php';

require_file ('../tables/tables.php');
require_file ('../cls/classes.php');

require_once 'cls/classes.php';
require_once 'tables/tables.php';

$db = new Database(['driver' => 'mysql', 'host' => 'localhost', 'dbname' => 'cine', 'username' => 'root', 'password' => '']);

$user = sUser2();

require_once 'finder/Finder.cls';

if ($user) {

	if ($user->gAl() >= 10) {

		msg('USER', 'User has clearance');

		require_once 'scan/scanner.php';

	} else msg('USER-DENIED', 'User does not have clearance');

	
} else msg('USER', 'No User');

msg('CLOSE', 'End of script');


?>