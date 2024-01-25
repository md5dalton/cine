<?php

class TMDBJson
{
	public function __construct (string $api_root, $data) {

		$this->root = $api_root;

		$this->data = $data;

		$this->details = $this->data->details;

		$this->dir = $this->root . $this->data->type . "/" . $this->details->id;
		
		if (!is_dir($this->dir)) mkdir($this->dir, 0700, true);

	}

	public function save () {

		$jsonfile = "$this->dir/details.json";
		$es_overview_file = "$this->dir/es-overview.txt";

		$encoded = json_encode($this->details);
		
		file_put_contents($jsonfile, $encoded);
		file_put_contents($es_overview_file, $this->data->es_overview);

	}
}
class TMDB
{

	public $root = '/tmdb/';
	public $images_base_path = 'images/';

	public $base_image;

	public function __construct () {

		$this->base_image = $this->root . 'images/';

	}

	public function gImagefullpath($size, $path) {

		return $this->base_image . $size . $path;

	}


	public function gImagesBasePath () {

		return $this->root . $this->images_base_path;

	}
	public function saveImageOld ($e) {

		if (!isset($e->blob)) return;

		$dir = $this->gImagesBasePath() . "$e->size";
		
		if (!is_dir($dir)) mkdir($dir, 0700, true);

		$file = $dir . $e->path;

		$data = substr($e->blob, strpos($e->blob, ',') + 1);

		$decoded = base64_decode($data);
		
		return file_put_contents($file, $decoded) ? $file : false;

	}	

	public function sData ($d) {

		if ($d) foreach ($d as $data) {

			$e = new TMDBJson($this->root, $data);

			$e->save();

		}

	}

	public function saveJSON ($contentType, $data, $language) {

		if (!isset($data->id)) return 'data not valid';
		
		$dir = $this->root . $contentType;

		if (!is_dir($dir)) mkdir($dir, 0700, true);

		$jsonfile = "$dir/$data->id-$language.json";

		$encoded = json_encode($data);
		
		if (file_put_contents($jsonfile, $encoded)) return true;

	}
	public function getJSON ($contentType, $id, $language) {

		$path = $this->root . "$contentType/$id-$language.json";
		
		$file = file_get_contents($path);

		if (!$file) return false;

		return json_decode($file);

	}
	public function gJSON ($path) {

		$file = file_get_contents($path);

		if (!$file) return false;

		return json_decode($file);

	}

	public function saveImage ($size, $path, $blob) {

		$dir = $this->base_image . "$size";
		
		if (!is_dir($dir)) mkdir($dir, 0700, true);

		$file = $dir . $path;

		$data = substr($blob, strpos($blob, ',') + 1);

		$decoded = base64_decode($data);
		
		if (file_put_contents($file, $decoded)) return true;

	}
}
?>