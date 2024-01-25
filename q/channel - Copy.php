<?php

class ChannelPlaylists
{
	public $ui;

	public $items = [];

	public function __construct (array $elms) {

		foreach ($elms as $elm) $this->li($elm);

		$this->ui = div(['class'=>'tablinks-playlists'], implode('', $this->items));

	}

	private function li ($e) {

		$f = 
		div(['class'=>'tab-link'],
			input(['type'=>'radio', 'checked' => @$e->checked]),
			label($e->name)
		);

		$this->items[] = $f;

	}

}

class PlaylistMedia
{
	public $ui;

	public $items = [];

	public function __construct (playlist $e) {

		foreach ($e->gMedia() as $elm) $this->li($elm);

		$e->gStats();

		$this->ui = 
		div(['class'=>'playlist-media'], 
			div(['class'=>'stats'],
				div(sWord($e->stats->media, 'Episodes'))
			),
			div(['class'=>'list'], implode('', $this->items))
		);

	}

	private function li ($e) {

		$f = 
		div(['class'=>'file'],
			div(['class'=>'thumb-btn linearicons-play-circle']),
			div(['class'=>'details'],
				div(['class'=>'name'], $e->name),
				div(['class'=>'sub-details'], 
					div($e->length)
				)
			),
			div(['class'=>'context-menu-handle linearicons-ellipsis'])
		);

		$this->items[] = $f;

	}

}
class ChannelViewer
{

	public $ui;

	public function __construct (string $id) {

		$ch = json_decode(json_encode([
			'id'=>$id,
			'picture'=>'d:/wall.jpg', 
			'name'=>'Suits',
			'bio_eng'=> 'English This representative of a long text that describes a plot of a movie or series',
			'bio_esp'=> 'Spanish This representative of a long text that describes a plot of a movie or series',
			'stats'=> [
				'playlists' => 8,
				'media' => 165
			],
			'playlists' => [
				['name'=>'Season 1', 'checked'=>true],
				['name'=>'Season 2'],
				['name'=>'Season 3'],
				['name'=>'Season 4'],
				['name'=>'Season 5'],
				['name'=>'Season 6'],
				['name'=>'Season 7'],
				['name'=>'Season 8'],
				['name'=>'Season 9'],
				['name'=>'Season 10'],
				['name'=>'Season 11']
			],
			'media' => [
				['name'=>'S01E01', 'length'=>'34 min'],
				['name'=>'S01E02', 'length'=>'60 min'],
				['name'=>'S01E03', 'length'=>'51 min'],
				['name'=>'S01E03', 'length'=>'51 min'],
				['name'=>'S01E03', 'length'=>'51 min'],
				['name'=>'S01E03', 'length'=>'51 min'],
				['name'=>'S01E03', 'length'=>'51 min'],
				['name'=>'S01E03', 'length'=>'51 min'],
				['name'=>'S01E03', 'length'=>'51 min'],
				['name'=>'S01E03', 'length'=>'51 min'],
				['name'=>'S01E03', 'length'=>'51 min'],
				['name'=>'S01E03', 'length'=>'51 min'],
				['name'=>'S01E03', 'length'=>'51 min'],
				['name'=>'S01E03', 'length'=>'51 min'],
				['name'=>'S01E03', 'length'=>'51 min'],
				['name'=>'S01E03', 'length'=>'51 min'],
				['name'=>'S01E03', 'length'=>'51 min'],
				['name'=>'S01E03', 'length'=>'51 min'],
				['name'=>'S01E03', 'length'=>'51 min'],
				['name'=>'S01E03', 'length'=>'51 min'],
				['name'=>'S01E03', 'length'=>'51 min'],
				['name'=>'S01E03', 'length'=>'51 min']
			]
		])); 

		//$e = new Channel($ch);
		//$e = new Channel($id);

		$this->setUp($ch);
		//if ($e->id) $this->setUp($e); else $this->error();

	}

	private function setUp (/*channel */$e) {
	
		//$e->gPlaylists();
		//$e->gStats();

		$pl = [
			['name'=>'Season 1', 'checked'=>true],
			['name'=>'Season 2'],
			['name'=>'Season 3'],
			['name'=>'Season 4'],
			['name'=>'Season 5'],
			['name'=>'Season 6'],
			['name'=>'Season 7'],
			['name'=>'Season 8'],
			['name'=>'Season 9'],
			['name'=>'Season 10'],
			['name'=>'Season 11']
		];

		$med = [
			['name'=>'S01E01', 'length'=>'34 min'],
			['name'=>'S01E02', 'length'=>'60 min'],
			['name'=>'S01E03', 'length'=>'51 min'],
			['name'=>'S01E03', 'length'=>'51 min'],
			['name'=>'S01E03', 'length'=>'51 min'],
			['name'=>'S01E03', 'length'=>'51 min'],
			['name'=>'S01E03', 'length'=>'51 min'],
			['name'=>'S01E03', 'length'=>'51 min'],
			['name'=>'S01E03', 'length'=>'51 min'],
			['name'=>'S01E03', 'length'=>'51 min'],
			['name'=>'S01E03', 'length'=>'51 min'],
			['name'=>'S01E03', 'length'=>'51 min'],
			['name'=>'S01E03', 'length'=>'51 min'],
			['name'=>'S01E03', 'length'=>'51 min'],
			['name'=>'S01E03', 'length'=>'51 min'],
			['name'=>'S01E03', 'length'=>'51 min'],
			['name'=>'S01E03', 'length'=>'51 min'],
			['name'=>'S01E03', 'length'=>'51 min'],
			['name'=>'S01E03', 'length'=>'51 min'],
			['name'=>'S01E03', 'length'=>'51 min'],
			['name'=>'S01E03', 'length'=>'51 min'],
			['name'=>'S01E03', 'length'=>'51 min']
		];
		
		//$playlists = new ChannelPlaylists($pl);
		//$playlists = new ChannelPlaylists($e->playlists);
		//$media = new PlaylistMedia($media);
		//$media = new PlaylistMedia(reset($e->playlists));

		$img = imagecreatefromjpeg($e->picture);
		$thumb = imagecreatetruecolor(1,1);
		imagecopyresampled($thumb,$img,0,0,0,0,1,1,imagesx($img),imagesy($img));
		$maincolor = strtoupper(dechex(imagecolorat($thumb,0,0)));

		$this->ui = 
			div(['class'=>'card channel'],
				div(['class'=>'info-wrapper', 'style'=>"background-color:#$maincolor"],
					div(['class'=>'info'],
						div(['class'=>'picture', 'style'=> bgImg($e->picture)]),
						div(['class'=>'info-inner'],
							div(['class'=>'profile-info'],
								div(['class'=>'name'], $e->name),
							),
							div(['class'=>'summary'],
								div(sWord($e->stats->playlists, 'Season')),
								div(sWord($e->stats->media, 'Episode')),
							),
							div(['class'=>'preview-buttons'],
								div('Watch Trailer'),
								div('Preview Pilot')
							)
						)
					),
					div(['class'=>'bio'], 
						//div($e->gBio())
					)
				),
				//div(
					//div(['class'=>'playlists'])
					//div(['class'=>'tablinks'], $playlists->ui),
					//div(['class'=>'tabcontent'], $media->ui)
				//),
				script('', 'window.addEventListener("load", t => new Channel("majara"), false);')
			);

	}

	private function error () {

		$this->ui = div(['class'=>'not-found'], 'Channel was not found');

	}
}

if (isset($_GET['id'])) {

	$c = new ChannelViewer($_GET['id']);

	echo $c->ui;

}




?>