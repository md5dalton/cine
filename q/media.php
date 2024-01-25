<?php

class MediaViewer
{

	public $ui;

	public function __construct (App $a) {

		$this->a = $a;

	}

	public function sParam ($g) {

		$error = '';

		if (isset($g->id)) {

			//$e = new Media($g->id);

			$e = (object) [
				'id'=>$g->id,
				'name'=>'S02E4',
				'channel'=>[
					'id'=>2,
					'name'=>'Warrior Nun'
				],
				'playlist'=>[
					'id'=>3,
					'name'=>'Season 2'
				],
				'overview' =>'This is a long text representing an overview of the media if available.'
			];

			if ($e->id) $this->a->code('window.addEventListener("load", t => new Media(' . json_encode($e) . '), false);'); else {

				$error = div(['class'=>'error'], 'Media not found');

			}

			$this->a->body(div(['class'=>'card media'], $error));

		}

	}

}


$a = new MediaViewer($app);

$a->sParam((object) $_GET);

?>