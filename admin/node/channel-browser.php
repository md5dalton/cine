<?php
namespace Admin;
//e9e7b217e0bdbe5e6c15e6212c413636

abstract class MediaElement
{
	
	public $id, $name;

	protected $row, $tb;

	
	public function __construct ($e) {

		$this->tb = $this->gTb();

		$this->row = is_string($e) ? $this->tb->gRow($e) : $e;

		$this->sRow();

	}

	protected function gTb () {}
	protected function sRow () {}

	public function gRow () {return $this->row;}
	
	public function gPath () {
		
		$this->path = $this->row->path;
		 
		return $this;
	
	}


}

class Channel extends MediaElement
{

	protected function gTb () {return \Tables\Channels::instance();}

	protected function sRow () {
		
		if ($this->row) {

			$this->id = $this->row->id;
			$this->name = str_clean($this->row->name);

		}

	}
	
	public function gPoster () {

		$this->poster = '';

		$tb = \Tables\Posters::instance();

		$paths = $tb->column('path', ["owner=$this->id"], 'height ASC');

		if ($selected = reset($paths)) {
			
			$this->poster = imagepath($selected);
			
			return $selected;

		} 

	}
	public function gBanner () {

		$this->banner = '';

		$tb = \Tables\Banners::instance();

		$paths = $tb->column('path', ["owner=$this->id"], 'width ASC');

		if ($selected = reset($paths)) {
			
			$this->banner = imagepath($selected);
			
			return $selected;

		} 

	}
	public function gStats () {

		if (!isset($this->stats)) $this->stats = new ChannelStats($this);

	}
	public function gGenre () {
		
		if (!isset($this->genres)) {
				
			$tb = \Tables\Genres::instance();

			$this->genres = $tb->column('name', ["content=$this->id"]);

		}

	}
	public function gOverview () {
		
		if (isset($this->overview)) {

			$tb = \Tables\Overviews::instance();

			$this->overview = $tb->column('text', ["owner=$this->id"]);

		}

	}
	
	
	public function gPlaylists () {

		$this->playlists = [];

		$tb = \Tables\Playlists::instance();

		$rows = $tb->gRows(["owner=$this->id"]);

		foreach ($rows as $row) {

			$e = new Playlist($row);

			$this->playlists[] = $e;

		}

	}

	public function gSummary () {

		$this->gStats();
		$this->gGenre();

		$this->summary = [
			sWord($this->stats->playlists, 'Season')
		];

		if ($this->genres) array_push(implode(' ', $this->genres));

	}

	
}

class ChannelBrowser
{

	private $errormsg, $result = [];

	public function start ($input) {

		$i = $input;

		$page = isset($i->p) ? $i->p : 1;

		$channels = [];
		$pages = [];

		if (isset($i->c)) {

			switch ($i->c) {
				case 'tv':
					$tb = \Tables\Series::instance();
					break;
					case 'cine':
						$tb = \Tables\Movies::instance();
						break;
			}

			if (!isset($_SESSION[$i->c]['channels'])) $_SESSION[$i->c]['channels'] = $tb->column('channel',[],'name ASC'); 
			
			$ids = $_SESSION[$i->c]['channels'];
			$last_sent_key = @$_SESSION[$i->c]['last_sent_key'];

			$length = 15;
			//$start = empty($last_sent_key) ? 0 : $last_sent_key;
			$start = $length*$page - $length;

			$selected = array_slice($ids, $start, $length, true);

			if ($selected) foreach ($selected as $key => $id) {

				$e = new Channel($id);
				
				if ($e->id) {

					$e->{$i->c} = 1;

					$channels[] = $e;

				}
				
				$last_sent_key = $key;

			}

			$_SESSION[$i->c]['last_sent_key'] = $last_sent_key;

			
			$pages_max = 5;

			$number_of_pages = ceil(count($ids) / $length);
			
			$first_page = $page > $pages_max ? ['id'=>1, 'name'=>'First'] : ['id'=>1, 'name'=>1];
			array_unshift($pages, $first_page);

			if ($number_of_pages > 1) for ($i=2; $i <= $number_of_pages; $i++) {
				
				$p = ['id'=>$i, 'name'=>$i];

				$pages[] = $p;

				if ($i+2 > $pages_max) break;
				
			}

			if ($number_of_pages > $pages_max) {
				
				$last_page = ['id'=>$number_of_pages, 'name'=>'Last'];
				array_push($pages, $last_page);

			}


			$this->result['channels'] = $channels;
			$this->result['pages'] = $pages;

		}

	}

	public function getresult () {

		return $this->errormsg ? ['errormsg' => $this->errormsg] : ['browser' => $this->result];

	}


}

$app = new ChannelBrowser;

$app->start($input);




?>