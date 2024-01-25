<?php
require_file ('./finder/Finder.cls');

class ChannelSearchHandler
{

	private $errormsg, $result = [];

	private $json = [];

	private $pool = [];

	public function __construct () {

		$this->result = (object) [];
		
	}

	
	public function start ($i) {

		$this->i = $i;

		if (!isset($i->q)) return $this->errormsg = 'q not set';

		switch ($i->q) {
			case 'json-local-details':
				$this->gDetails();
				break;
			
			case 'submitdata':
				$this->sDetails();
				break;
			
			case 'image-upload':
				$this->saveImage();
				break;
			
			
			case 'set-default':
				$this->sDefault();
				break;
			
		}
		
	}

	private function sDefault () {
	
		if (!isset($this->i->path->en)) return $this->errormsg = 'path not set';
		if (!isset($this->i->channel)) return $this->errormsg = 'channel not specified';

		$api = new \TMDB;
		$db = new \DatabaseUpdater;

		$en = $api->gJSON($this->i->path->en);

		$es = isset($this->i->path->es) ? $api->gJSON($this->i->path->es) : false;

		if ($en === false) return $this->errormsg = 'data not found';

		$data = (object) ['en'=>$en];

		if ($es) $data->es = $es;

		$db->sDetails($data);
		$db->updateChannel($this->i->channel, $en->id);
		
		//Assume there were no fatal errors
		//if ($result !== true) return $this->errormsg = 'setting default failed';

		$this->result->default = 'setting default was successful';
	
	}

	private function saveImage () {
	
		if (!isset($this->i->d)) return $this->errormsg = 'data not set';
		if (!isset($this->i->d->size)) return $this->errormsg = 'size not specified';
		if (!isset($this->i->d->path)) return $this->errormsg = 'path not specified';
		if (!isset($this->i->d->blob)) return $this->errormsg = 'no image data';

		$api = new \TMDB;

		$result = $api->saveImage ($this->i->d->size, $this->i->d->path, $this->i->d->blob);
		
		if ($result !== true) return $this->errormsg = 'image save failed';

		$this->result->upload = 'image saved successfully';
	
	}

	private function sDetails () {
	
		if (!isset($this->i->c)) return $this->errormsg = 'content not set';
		if (!isset($this->i->d)) return $this->errormsg = 'data not set';
		if (!isset($this->i->l)) return $this->errormsg = 'language not set';

		$api = new \TMDB;

		$result = $api->saveJSON ($this->i->c, $this->i->d, $this->i->l);
		
		if ($result !== true) return $this->errormsg = 'data save failed';

		$this->result->submitdata = 'data submitted successfully';
	
	}

	private function sFiles () {

		//if (!isset($_SESSION['channels_details'])) {

			$api = new TMDB;
			$fm = new \Finder($api->root, true);

			
			$keys_to_select = [
				'backdrop_path',
				'id',
				'release_date',
				'last_air_date',
				'poster_path'
			];

			foreach ($fm->gFiles(['json']) as $f) {

				
				$dirname = dirname($f);
				$filename = pathinfo($f, PATHINFO_FILENAME);

				list ($id, $language) = explode('-', $filename);

				if ($language == 'en') {

					$json = json_decode(file_get_contents($f));

					$selected = array_intersect_key(
						(array) $json, 
						array_flip($keys_to_select)
					);

					$selected['path'] = ['en'=>$f, 'es'=>"$dirname/$id-es.json"];
					$selected['filename'] = basename($f);
					$selected['name'] = @$json->name ?: @$json->title;

					$this->json[] = (object) $selected; 

				}

			}

			$_SESSION['channels_details'] = $this->json;
		
		//} else $this->json = $_SESSION['channels_details'];


	}

	private function gDetails () {

		$i = $this->i;

		if (!isset($i->t)) return $this->errormsg = 'no term specified';

		$this->sFiles();

		$term = $i->t;

		$matches = [];

		foreach ($this->json as $d) {

	        $strpos = stripos($d->name, $term);

	        if ($strpos !== false) $matches[] = ['pos' => $strpos, 'str' => $d->name, 'd' => $d];

		}

		//$this->result->matches = $matches;
		
		array_multisort(
			array_column($matches, 'pos'), SORT_DESC, 
			array_column($matches, 'str'), SORT_REGULAR, 
		$matches);

		//$this->result->matchesSorted = $matches;

		$this->gSuggestions(array_slice($matches, 0, 9, true));

	}

	private function gSuggestions (array $suggested) {

		$suggestions = [];

		foreach ($suggested as $key => $arr) $suggestions[] = $arr['d'];

		$this->result->details = $suggestions;

	}

	public function getresult () {

		return $this->errormsg ? ['errormsg' => $this->errormsg] : $this->result;

	}

}

$app = new ChannelSearchHandler;

$app->start($input);


?>