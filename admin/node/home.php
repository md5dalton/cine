<?php

class BatchHandler
{

	private $errormsg, $result = [];

	public function __construct ($i) {
		//print_r('$this->data');

		$this->result = (object) [];

		$this->i = $i;

		if (isset($i->q)) switch ($i->q) {
			case 'image-upload':
				$this->saveImages();
				break;
			case 'submitdata':
				$this->sData();
				break;
			case 'submitlocaldata':
				$this->sLocalData();
				break;
		}


	}

	private function saveImages () {
		
		if (!isset($this->i->images)) return $this->errormsg = 'Images not set yet';

		$api = new TMDB;

		foreach ($this->i->images as $i) $api->saveImage($i->size, $i->path, $i->blob);

		$this->result->status = true;

	}

	private function sLocalData () {
		
		if (!isset($this->i->data)) return $this->errormsg = 'Data not set yet';

		$db = new DatabaseUpdater;
		$api = new TMDB;

		$tb = gTables('Channels');

		$languages = ['en', 'es'];

		foreach ($this->i->data as $id => $channels) {

			$channelId = reset($channels);

			$channelRow = $tb->channels->row(["id=$channelId"]);

			$contentType = $channelRow->content_type;

			$details = (object) [];

			foreach ($languages as $lang) {
				
				$data = $api->getJSON($contentType, $id, $lang);
				
				if ($data) $details->{$lang} = $data;
				
			}
	
			foreach ($channels as $channel) $db->updateChannel($channel, $id);
			
			$db->sDetails($details, false);

		}

		$this->result->status = true;

	}

	private function sData () {
		
		if (!isset($this->i->data)) return $this->errormsg = 'Data not set yet';

		$db = new DatabaseUpdater;
		$api = new TMDB;

		$languages = ['en', 'es'];

		foreach ($this->i->data as $id => $d) {

			$details = $d->details;
			$channels = $d->channels;
			$contentType = $d->content_type;

			foreach ($languages as $lang) {
				
				$data = $details->{$lang};

				if ($data) $api->saveJSON ($contentType, $data, $lang);
				
			}
	
			foreach ($channels as $channel) $db->updateChannel($channel, $id);
			
			$db->sDetails($details, false);

		}

		$this->result->status = true;

	}

	public function getresult () {

		return $this->errormsg ? ['errormsg' => $this->errormsg] : $this->result;

	}


}

$app = new BatchHandler($input);

?>