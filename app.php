<?php
//2021.06.26

require_once "fn.php";
    
require_once "tables/tables.php";
require_once "cls/classes.php";
require_once "main.php";

$app = new App("MedCine");

$db = new Database(["driver" => "mysql", "host" => "localhost", "dbname" => "cine", "username" => "root", "password" => ""]);

$user = sUser2();

// $user->gConfig("language", true);

// if (isset($_GET["language"])) $user->sConfig("language", $_GET["language"], true);

switch (@$_GET["q"]) {
    case "user":
        require_once("q/user.php");
        break;

    case "media":
        require_once("q/media.php");
        break;

    case "channel":
        require_once("q/channel.php");
        break;
    
    case "search":
        require_once("q/search.php");
        break;
    
    default:
        require_once("q/home.php");
        break;
}

$app->sTheme("#152028");
//$app->head->sManifest("manifest.json");

$app->echo();



?>