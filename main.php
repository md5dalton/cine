<?php


function attributes (array $attributes) {

	$selected = [];

	foreach ($attributes as $attr => $value) {
		
		if ($value === true) $selected[] = $attr; elseif ($value) { 
			
			$selected[] = $attr . '="' . $value . '"';

		}
	}

	return implode(' ', $selected);

}
function container (string $name, ...$elms) {

	$attributes = [];

	$content = '';
	
	foreach ($elms as $e) if (is_array($e)) $attributes  = $e; elseif (is_string($e)) $content .= $e;

	return "<$name " . attributes($attributes) . " >$content</$name>";

}
function SelfContainer (string $name, ...$elms) {

	$attributes = [];

	foreach ($elms as $e) if (is_array($e)) $attributes  = $e;// elseif (is_string($e)) $content .= $e;

	return "<$name " . attributes($attributes) . " />";

}


function meta (array $attributes) {

	return selfContainer('meta', $attributes);

}
function _link (array $attributes) {

	return selfContainer('link', $attributes);

}

function title (string $val) {

	return container('title', $val);

}
function script (string $src, string $content = '') {

	return container('script', $content, $src ? ['src' => $src] : []);

}
function style (string $href, string $content = '', string $type = '') {

	return $href ? selfContainer('link', ['rel'=>"stylesheet" . ($type ? "/$type" :''), 'href'=>$href]) : container('style', $content);

}

function body (...$e) {

	return container('body', ...$e);
	
}
function div (...$e) {

	return container('div', ...$e);
	
}
function a (...$e) {

	return container('a', ...$e);
	
}
function img (...$e) {

	return container('img', ...$e);
	
}
function video (...$e) {

	return container('video', ...$e);
	
}
function label (...$e) {

	return container('label', ...$e);
	
}


function input (array $attributes) {

	return selfContainer('input', $attributes);

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

class Head 
{

	public $ui;

	public $favicon, $title;

	public $links = [], $scripts = [], $styles = [], $metadata = [];

	public function __construct ($title) {

		$this->title = title($title);

	}

	public function theme ($color = '') {

		if ($color) {

			$this->meta(
				['name'=>'theme-color', 'content'=>$color],
				['name'=>'apple-mobile-web-app-capable', 'content'=>'yes'],
				['name'=>'apple-mobile-web-app-status-bar-style', 'content'=>$color]
			);

		}
		
	}
	
	public function icon ($href) {

		$this->favicon = _link(['rel'=>'shortcut icon', 'href'=>$href, 'type'=>'image/png']);
		
	}

	public function sMeta ($name, $content) {

		$this->meta(['name'=>$name, 'content'=>$content]);

	}

	public function meta (...$items) {

		
		foreach ($items as $i) {

			$this->metadata[] = meta($i);

		}

	}


	public function css (...$src) {

		foreach ($src as $s) $this->styles[] = style($s);

	}
	public function less (...$src) {

		foreach ($src as $s) $this->styles[] = style($s, '', 'less');

	}

	public function js (...$src) {

		foreach ($src as $s) $this->scripts[] = script($s);

	}
	
	public function jsCode ($str) {

		$this->scripts[] = script('', $str);

	}

	public function sManifest ($href) {

		$this->links[] = _link(['rel'=>'manifest', 'href'=>$href]);

	}

	public function gUi () {

		$this->ui =
		'<!doctype html><html translate="no">'. 
		$this->title.
		$this->favicon.
		implode('', $this->links).
		implode('', $this->metadata).
		implode('', $this->styles).
		implode('', $this->scripts);

	}



}

class App
{

	public $ui;
	public $theme = 'light';

	public $themeColor;

	public $head, $content;

	public function __construct (string $title) {

		$this->head = new Head($title);

		$this->head->icon('/res/favicon.png');
		$this->head->sMeta('viewport','width=device-width, initial-scale=1.0');

		$this->head->css(
			'/res/fonts/linearicons_coders/css/linearicons.css',
			//'app.less.php',
			//'app.less'
		);
		$this->head->less('app.less');


		$this->head->js(
			'/res/scripts/less.min.js',
			'/res/scripts/fn.js',
			'app.js?'.time()
		);

		if (!empty($_COOKIE['theme']) && $_COOKIE['theme'] == 'dark') $this->theme = 'dark';

	}

	public function body (string $content = '', bool $topbar = true) {
		
		$this->content = body(['class' => $this->theme], $content);

	}

	public function sTheme ($color = '') {
		
		$this->head->theme($color);

	}
	
	public function code ($str) {
		
		$this->head->jsCode($str);

	}
	public function js ($str) {
		
		$this->head->jsCode($str);

	}


	public function echo () {

		$this->head->gUi();

		echo $this->head->ui . $this->content;

	}

}

?>