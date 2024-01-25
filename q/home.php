<?php
namespace HomeFeed;

class HomeFeed
{

	public $ui;

	public function __construct (\App $a, \User $u) {

		if ($u->isVerified()) $u->gInfo();

		$this->sections = [
			new Welcome
		];
		
		$a->code('window.addEventListener("load", t => new HomeFeed(' . json_encode(['u'=>$u,'s'=>$this->sections]) . '), false);');

		$a->body(div(['class'=>'card home']));

	}


}


$a = new HomeFeed($app, $user);

?>