<?php

$host = 'localhost';
$user = 'root';
$pass = '';

$do = @$_GET['do'];
$db = @$_GET['db'];

if (!$do) die('Specify action mk/del');
if (!$db) die('Specify database name');

$mysqli = new mysqli($host, $user, $pass);

if ($mysqli->connect_errno) die('Could not connet: '. $mysqli->connect_errno);


switch ($do) {
    case 'mk':
        $query = "CREATE";
        break;
    case 'del':
        $query = "DELETE";
        break;
}

if (!isset($query)) die('Query not specified');

if ($mysqli->query("$query DATABASE $db")) echo "Action on Database $db successful";

if ($mysqli->errno) die("Action on Database $db FAILED: ". $mysqli->error);

$mysqli->close();

?>