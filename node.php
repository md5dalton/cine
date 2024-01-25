<?php


require_once 'fn.php';
require_once 'cls/Database.php';
require_once 'tables/tables.php';
require_once 'cls/classes.php';
require_once 'main.php';

$db = new Database(['driver' => 'mysql', 'host' => 'localhost', 'dbname' => 'cine', 'username' => 'root', 'password' => '']);

sUser2();

function load ($input) {
	$app = null;

	require_once "node/$input->query.php";

	
	return ['result' => $app !== null ? $app->getresult() : ''];

}

$input = json_decode(file_get_contents('php://input'));

$output = (object) [];

if ($input) switch ($input->query) {
	case 'login':
		$output = load($input);
		break;
	
	case 'register':
		$output = load($input);
		break;
	
	case 'home':
	case 'user':
	case 'media':
	case 'search':
	case 'channel':
	case 'playlist':
	case 'interact':
	case 'suggestions':

		$output = load($input);
		
		break;
	
	default:
		# code...
		break;
}

echo json_encode($output);

?>