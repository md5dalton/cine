<?php

class UserHandler
{

	public $ui;

	public function __construct (App $a) {

		$a->code('window.addEventListener("load", t => new User(""), false);');

		$a->body(div(['class'=>'card user']));

	}

	
	public function sParam ($g) {

		//$this->title = $g->c ?? $g->t;
		//if (@$)

	}
}


$a = new UserHandler($app);

$a->sParam((object) $_GET);

?>