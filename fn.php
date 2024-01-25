<?php

function gTables (...$names) {

	$tables = (object) [];

	foreach ($names as $name) {

		$lowercase = strtolower($name);

		$table_name = "\\Tables\\$name";

		$tables->{$lowercase} = $table_name::instance();

	}

	return $tables;

}

function gTable ($name) {

    return new \Tables\AccessControledTable($name);

}
function sUser () {

	if (empty($_COOKIE['user'])) {
		
		$userid = uniqid('u');

		setcookie('user', $userid, ['expires' => time() + (10 * 365 * 24 * 60 * 60)]);

	} else $userid = $_COOKIE['user'];

	$user = new User($userid);

	if (!$user->id) $user = $user->create($userid, '', "Contact_$userid", "", "Guest$userid");

	return $user;

}

function sUser2 () {

	$userid = @$_COOKIE['user'];
	$userid = @$_SESSION['user'] ?? $userid;

	if (!$userid) $userid = uniqid('u');

	if (empty($_COOKIE['user'])) setcookie('user', $userid, ['expires' => time() + (10 * 365 * 24 * 60 * 60)]);
	
	if (empty($_SESSION['user'])) $_SESSION['user'] = $userid;

	$user = new User($userid);

	if (!$user->id) $user = $user->create($userid);

	return $user;

}

function randString (int $length = 8) {

	$str = '';

	$keyspace = implode('', array_merge(range(0,9), range('a','z'), range('A','Z')));

	$max = mb_strlen($keyspace, '8bit') - 1;

	for ($i=0; $i < $length; ++$i) $str .= $keyspace[random_int(0,$max)];
	
	return $str;

}
function bgImg ($path) {
	
	return $path ? 'background-image: url(' . imagepath($path) . ')' : ''; 

}
function sWord (int $num, string $word, bool $includeNumber = true) {
	
	$_num = $includeNumber ? $num : '';

	return "$_num $word" . ($num == 1 ? '' : 's'); 

}
function formattime ($val) {

	$seconds = floor($val);

	$minutes = floor($seconds / 60);   

	$hours = floor($minutes / 60);
	
	$seconds = $seconds - $minutes * 60;
	$minutes = $minutes - $hours * 60;

	$seconds = $seconds < 10 ? '0' . $seconds : $seconds;

	if ($hours) $minutes = $minutes < 10 ? '0' . $minutes : $minutes;

	$time = $minutes . ':' . $seconds;

	if ($hours) $time = $hours . ':' . $time;

	return $time; 

}
function formatdate ($val) {
    
	$now = time();
	$seconds = $now - $val;
	//$seconds = 37260;

	$minutes = floor($seconds/60);
	$hours = floor($seconds/3600);
	$days = floor($seconds/86400); 
	$weeks = floor($seconds/604800);
	$months = floor($seconds/2628000);
	$years = floor($seconds/31536000);

	$t = sWord($years, 'year');
	$t = $years < 1 ? sWord($months, 'month') : $t;
	$t = $months < 1 ? sWord($weeks, 'week') : $t;
	$t = $weeks < 1 ? sWord($days, 'day') : $t;
	$t = $days < 1 ? sWord($hours, 'hour') : $t;
	$t = $hours < 1 ? sWord($minutes, 'minute') : $t;
	$t = $minutes < 1 ? sWord($seconds, 'second') : $t;

	return "$t ago";
	// <br>" . date("Y M j H:m:s", $val) . "<br>sec=$seconds min=$minutes h=$hours D=$days W=$weeks M=$months Y=$years";
	//return "$t ago sec=$seconds min=$minutes h=$hours D=$days W=$weeks M=$months Y=$years ";// . formatdate2($val);
	
}

function formatdate2 ($val) {
    
	//months:days:hours:minutes:seconds

	$now = time();
	$seconds = $now - $val;

	$minutes = round($seconds/60);
	$hours = round($minutes/60);
	$days = round($hours/24); 
	$weeks = round($days/7);

	$t = 
	($minutes < 60) ? $minutes. ' minute' : (
		($hours < 24) ? $hours .' hour' : (
			($days < 7) ? $days .' day' : $weeks .' week'
		)
	);
	
	$t = (explode(' ', $t)[0] == 1 ? $t : $t . 's') . ' ago';

	$months = round($days/30);
	$years = round($days/360);
	
	$t = $months > 1 ? date('M j', $val) : $t;

	$t = $years > 1 ? date('M j Y', $val) : $t;
	
	$t = ($seconds < 60) ? 'now' : $t;

	return $t;
	
}

function formatdateAmerican ($val) {
	
	$unixtime = strtotime($val);

	return date("M j, Y", $unixtime);
	
}
function imagepath ($path) {if ($path) {

	$sha1 = sha1($path);

	$_SESSION[$sha1] = $path;

	return "p/?f=$sha1";

}}
function mediapath ($path) {if ($path) {

	$uniq = uniqid('vid');

	//$sha1 = sha1($path);

	//$_SESSION[$sha1] = $path;
	$_SESSION[$uniq] = $path;

	return "v/?f=$uniq";

}}

function str_clean (string $str) {

	$str = str_replace('_', ' ', $str);
	$str = str_replace('.', '-', $str);
	return $str;
	//return preg_replace('/[^A-Za-a0-9\-]/', '', $val);

}

function randomise (array $array): array {

	shuffle ($array);

	return $array;

}

function gImgColor ($path) {

	if (is_file($path)) {
			
		$img = imagecreatefromstring(file_get_contents($path));
		$thumb = imagecreatetruecolor(1,1);
		imagecopyresampled($thumb,$img,0,0,0,0,1,1,imagesx($img),imagesy($img));
		
		return '#' . dechex(imagecolorat($thumb,0,0));
	}
}
function require_file (string $path) {

    $realpath = realpath($path);

    if ($realpath) require_once $realpath; else print_r("Pathnot found: $path");

}

function gHexToRgb ($hex) {

	list($r,$g,$b) = sscanf($hex, "#%02x%02x%02x");

	return (object) ['r'=>$r,'g'=>$g,'b'=>$b];

}

function gColorPalette ($image, $numColors = 5, $granularity = 5) {

	$granularity = max(1, abs((int)$granularity));

	$colors = [];

	$size = @getimagesize($image);

	if ($size) {

		$img = imagecreatefromstring(file_get_contents($image));

		if ($img) {

			for ($x=0; $x < $size[0]; $x+=$granularity) { 
				for ($y=0; $y < $size[1]; $y+=$granularity) { 
					
					$thisColor = imagecolorat($img, $x, $y);

					$rgb = (object) imagecolorsforindex($img, $thisColor);

					$red = round(round(($rgb->red / 0x33)) * 0x33);
					$green = round(round(($rgb->green / 0x33)) * 0x33);
					$blue = round(round(($rgb->blue / 0x33)) * 0x33);

					$thisRGB = sprintf('%02X%02X%02X', $red, $green, $blue);

					if (array_key_exists($thisColor, $colors)) $colors[$thisRGB]++; else $colors[$thisRGB] = 1; 
				}
			}


		}
	}

	return array_keys($colors);
	//arsort($colors);
	print_r(count($colors));
	return array_slice(array_keys($colors), 0, $numColors);

}
?>