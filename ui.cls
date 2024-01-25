<?php

function attributes ($attributes) {

	foreach ($attributes as $attr => &$value) $value = $attr . '="' . $value . '"';

	return implode(' ', $attributes);

}
function container (string $name, ...$elms) {

	$attributes = [];

	$content = '';
	
	foreach ($elms as $e) if (is_array($e)) $attributes  = $e; elseif (is_string($e)) $content .= $e;

	return "<$name " . attributes($attributes) . " >$content</$name>";

}

function div (...$e) {

	return container('div', ...$e);
	
}


require_once 'app/app.cls';

class UI
{

	public function __construct () {

		$this->app = new App('MedCine Dev 2021.05.02');

		$this->app->favicon('/res/favicon.png');
		
		$this->app->style('/res/fonts/linearicons_coders/css/linearicons.css');
		$this->app->style('app.less.php');
		//$app->script('app.js?'.time());
		

		if (empty($_COOKIE['user'])) setcookie('user', uniqid('u'), ['httponly' => true]);

		//if (!empty($_COOKIE['theme']) && $_COOKIE['theme'] == 'dark') {
			
			$this->app->meta(['name'=>'theme-color', 'content'=>'#152028']);
			$this->app->meta(['name'=>'apple-mobile-web-app-capable', 'content'=>'yes']);
			$this->app->meta(['name'=>'apple-mobile-web-app-status-bar-style', 'content'=>'#152028']);
			
			$this->app->body(['class' => 'dark']);
/*
		} else {

			$this->app->meta(['name'=>'theme-color', 'content'=>'white']);

		}*/
		
	}

}


class Header
{

	public function __construct (string $title) {

		$this->ui = 
			div(['class'=>'top-bar'], 
				div(['class'=>'icon menu-icon linearicons-menu']),
				div(['class'=>'logo'], $title),
				div(['class'=>'icon user-icon linearicons-user'])
			);

	}

}


class Footer
{

	public function __construct () {

		$this->ui = 
			div(['class'=>'bottom-bar'], 
				$this->icon('Me', 'user'),
				$this->icon('Home', 'home2'),
				$this->icon('Search', 'magnifier')
			);

	}

	private function icon ($text, $li) {

		return div(
			div(['class'=> "icon linearicons-$li"]),
			div(['class'=> 'text'], $text)
		);

	}

}

$ui = new UI;
$header = new Header('MedCine');
$footer = new Footer;

echo $header->ui;
echo $footer->ui;


?>