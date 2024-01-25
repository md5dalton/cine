<?php
namespace HomeFeed;
require_file ('./finder/Finder.cls');

class BatchHandler
{
	public $channels = [], $images = [], $local_details = [];

	private $api, $tb;

	public function __construct () {

		$this->api = new \TMDB;

		$this->tb = gTables('Channels', 'ChannelDetails');

		$this->sImages();
		$this->sLocal();
		$this->sChannels();

	}

	
	private function sLocal () {
		
		$fm = new \Finder($this->api->root, true);

		foreach ($fm->gFiles(['json']) as $f) {
			
			$filename = pathinfo($f, PATHINFO_FILENAME);

			list ($id, $language) = explode('-', $filename);

			if ($language == 'en') {

				$json = json_decode(file_get_contents($f));

				$this->local_details[$json->id] = 1; 

			}

		}

	}
	
	private function sChannels () {
		
		$rows = $this->tb->channels->rows([], '*', 'timestamp DESC');

		foreach (array_slice($rows, 0, 10000) as $row) {

			if (!$row->details) {
					
				$e = new \Channel($row);

				$e->gType();

				$this->channels[] = $e;
			
			}
		}

	}
	private function sImages () {
		
		$details = $this->tb->channeldetails->rows();

		$imageTypes = ['poster'=> 'w500', 'backdrop'=>'w780'];

		foreach ($details as $d) foreach ($imageTypes as $name => $size) {

			$name .= '_path';

			if (isset($d->{$name})) {
			
				$path = $this->api->gImagefullpath($size, $d->{$name});

				if (!file_exists($path)) $this->images[] = ['size'=>$size, 'path'=>$d->{$name}];

			}

		}

	}

}

class HomeFeed
{

	public $ui;

	public function __construct (\App $a, \User $u) {

		$this->result = [
			'batch'=> new BatchHandler
		];
		
		$a->code('window.addEventListener("load", t => new HomeFeed(' . json_encode($this->result) . '), false);');

		$a->body(div(['class'=>'card home']));

	}


}


$a = new HomeFeed($app, $user);

?>