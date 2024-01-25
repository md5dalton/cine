<?php

class MediaViewer
{

	public $id, $playlist, $config;

	private $errormsg;


	public function start ($id, $playlist, $config) {

		$file = new Media($id);

		if (!$file->id) {

			$this->errormsg = 'media not found';

			return;

		}

		//if ($this->playlist) $file->nextMedia($this->playlist);
		
		
		$file->fullSetup();

		
		if ($playlist) $file->nextMedia($playlist);

		//$file->autonext = @$this->config->autonext;
		
		$this->media = $file;
		
	}

	public function getresult () {

		return $this->errormsg ? ['errormsg' => $this->errormsg] : ['media' => $this->media];

	}

}

if (@$input->previous) {

	$p = new Media($input->previous);

	$p->sView($input->progress);

}

$app = new MediaViewer;

$app->start($input->id, @$input->playlist, (object) $input->config);


?>