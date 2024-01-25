<?php

require_once 'fn.php';

require_file ('../tables/tables.php');
require_file ('tables/tables.php');
require_file ('../cls/classes.php');
require_file ('cls/classes.php');

$db = new Database(['driver' => 'mysql', 'host' => 'localhost', 'dbname' => 'cine', 'username' => 'root', 'password' => '']);

$user = sUser2();

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
	
	case 'channel-search':
	case 'home':
	case 'media':
	case 'search':
	case 'channel':
	case 'image-upload':
	case 'channel-browser':
	case 'suggestions':
	case 'ContentTypeManager':

		$output = load($input);
		
		break;
	
	default:
		# code...
		break;
}

echo json_encode($output);

?>