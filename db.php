<?php

$host = 'localhost';
$user = 'root';
$pass = '';

$q = @$_GET['q'];

if (!$q) die('Specify query');

$query = $q;

$mysqli = new mysqli($host, $user, $pass);

if ($mysqli->connect_errno) die('Could not connet: '. $mysqli->connect_errno);

if ($mysqli->query($query)) echo "$query successful";

if ($mysqli->errno) die("$query FAILED: ". $mysqli->error);

$mysqli->close();

?>