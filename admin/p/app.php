<?php
@ini_set('error_reporting', E_STRICT);


if ($f = $_GET['f']) {

    header('Pragma: public');
    header('Cache-Control: max-age=86400');
    header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));

    header('Content-type: image/jpg');

    echo file_get_contents($_SESSION[$f]);

}

//print_r($_SESSION);
//echo $_SESSION[$_GET['f']];
/*if ($f = @$_SESSION[$_GET['f']]) {
    
    $finfo = new finfo(FILEINFO_MIME);

    $mime = $finfo->file($f);

    header('Content-type: ' . $mime);

    echo file_get_contents($f);

}
*/

?>