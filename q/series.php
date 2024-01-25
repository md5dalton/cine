<?php

class SeriesViewer
{

	public $ui;

	public function __construct (App $a, string $id) {

		$this->a = $a;

		$this->e = new \Channel($id);

		if ($this->e->id) $this->setup(); else $a->body(div(['class'=>'not-found'], 'TV Series was not found'));

	}

	private function setUp () {

		$a = $this->a;
		$e = $this->e;

		$a->js('window.addEventListener("load", t => new Channel("' . $e->id . '"), false);');

		$e->gPoster(false);
		
		$maincolor = gImgColor($e->poster);

		$poster = $e->poster;

		$e->gStats();

		$a->body(
			div(['class'=>'card channel'],
				div(['class'=>'info-wrapper'],
					div(['class'=>'bg', 'style'=>bgImg($poster ?? 'medcine.png')],
						div(['class'=>'translucent', 'style'=> $maincolor ? "background-image:linear-gradient(45deg,$maincolor,rgba(0,0,0,0.93))":''])
					),
					div(['class'=>'info'],
						div(['class'=>'picture', 'style'=> bgImg($poster)]),
						div(['class'=>'info-inner'],
							div(['class'=>'profile-info'],
								div(['class'=>'name'], $e->name),
							),
							div(['class'=>'summary'],
								div(sWord($e->stats->playlists, 'Season')),
								div('Drama Fantasy')
							),
							div(['class'=>'bio'], 
								div(['class'=>'title'], 'Overview'),
								div(['class'=>'text'], 'Not available')
							),
							div(['class'=>'preview-buttons'],
								div(['class'=>'button'],
									div(['class'=>'text'], 'Watch Trailer'),
								),
								div(['class'=>'button'],
									div(['class'=>'text'], 'Share'),
								)
							)
						)
					)
				)
			)
		);

	}

	private function error () {


	}
}

if (isset($_GET['id'])) {

	$c = new SeriesViewer($app, $_GET['id']);

}




?>