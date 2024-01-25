<?php

require_once 'fn.php';


require_file ('../tables/tables.php');
require_file ('tables/tables.php');
require_file ('../cls/classes.php');
require_file ('cls/classes.php');

require_file ('../app.cls');

$db = new Database(['driver' => 'mysql', 'host' => 'localhost', 'dbname' => 'cine', 'username' => 'root', 'password' => '']);

$user = sUser2();

$app = new App('MedCine Admin Console 2021.07.27');

if ($user->gAl() >= 10) {
        
    switch (@$_GET['q']) {
    
        case 'search':
            require_once('q/search.php');
            break;
    
        case 'channel':
            require_once('q/channel.php');
            break;
    
        default:
            require_once('q/home.php');
            break;
        
    }


    $app->echo();

}

?>