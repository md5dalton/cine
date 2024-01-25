<?php

class ImageUploader
{
	private $errormsg, $result = [];

	public function start ($input) {

        if (!isset($input)) $this->errormsg = 'Image Data not set';

        $api = new TMDB;

        if ($api->saveImage($input)) $this->result = true;

	}

	public function getresult () {

		return $this->errormsg ? ['errormsg' => $this->errormsg] : ['image_upload' => $this->result];

	}


}

$app = new ImageUploader;

$app->start($input);



?>