<?php
namespace Admin;

class ChannelViewer
{

	private $error ='', $id, $channel = [];
	
	public $ui;

	public function __construct (\App $a) {

		$this->a = $a;

	}

	private function handleError ($err) {

		$this->a->body(div(['class'=>'card channel'], div(['class'=>'error'], $err)));

	}

	public function sParam ($g) {

		if (!isset($g->id)) return $this->handleError('Not Defined');
		if (!isset($g->c)) return $this->handleError('Unknown Content');
			
		$e = new Channel($g->id);

		if (!isset($e)) return $this->handleError('Unknown Content type');
		if (!isset($e->id)) return $this->handleError('Content Not Found');
		
		$e->setup();

		$this->a->code('window.addEventListener("load", t => new Channel(' . json_encode($e) . '), false);');
		$this->a->body(div(['class'=>'card channel']));
		
	}

}


$a = new ChannelViewer($app);

$a->sParam((object) $_GET);


function hexToRgb ($hex) {

	list($r,$g,$b) = sscanf($hex, "#%02x%02x%02x");

	return (object) ['r'=>$r,'g'=>$g,'b'=>$b];

}

function colorPalette ($image, $numColors = 5, $granularity = 5) {

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