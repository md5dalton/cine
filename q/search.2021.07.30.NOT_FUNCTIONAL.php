<?php



class SortOption
{
	public $id, $name;

	private $column;

	public function __construct ($column, $label, $value) {

		$this->column = $column;
		$this->name = $label;
		$this->id = $value;

	}

	public function gColumn () {

		return $this->column;

	}
}
class SortOrder
{

	public $id, $name;

	private $sort;

	public function __construct ($id, $name, $sort) {

		$this->id = $id;
		$this->name = $name;
		$this->sort = $sort;

	}

	public function gSort () {

		return $this->sort;

	}
	
}

class SortOptions
{
	public $options = [];
	public $order = [];

	public function __construct () {

		$options = [
			['name', 'TV Show name'],
			['title', 'Movie Title'],
			['release_date', 'Date released'],
			['first_air_date', 'First date aired'],
			['last_air_date', 'Last date aired'],
			['popularity', 'Popularity']
		];

		foreach ($options as $key => $o) {

			list($column, $label) = $o;

			$this->sOption($column, $label, $key);

		}

		$this->order = [
			new SortOrder(0, 'Asc', SORT_ASC),
			new SortOrder(1, 'Desc', SORT_DESC)
		];

	}	

	private function sOption ($column, $label, $id) {

		$e = new SortOption($column, $label, $id);

		$this->options[] = $e;

	}

	
	public function check ($id) {

		foreach ($this->options as &$o) if ($o->id == $id) {
			
			$o->checked = true;
		
			return $o;

		}

	}
	
	public function checkOrder ($id) {

		foreach ($this->order as &$o) if ($o->id == $id) {
			
			$o->checked = true;
			
			return $o;

		}

	}
}

class Filtering
{
	public $filters = [];

	private $start_id = 1;

	public function __construct () {
		
		$options = [
			['Content Type', 'c', 'rad', $this->gContentTypeOptions()],
			['Genre', 'g', 'rad', $this->gGenreOptions()],
			['Original Language', 'l', 'rad', $this->gLanguageOptions()],
			//['In Production', 'p', 'check', [['name'=> 'Yes', 'id'=>1]]],
			//['Running Time', 't', 'range', ['default'=>[30, 180]]]
		];

		foreach ($options as $o) {

			list($optionTitle, $queryId, $optionType, $values) = $o;

			$this->sOption($optionTitle, $queryId, $optionType, $values);
			
		}
		

	}

	private function gGenreOptions () {

		$genreTb = \Tables\Genres::instance();
		$tb = \Tables\MediaGenres::instance();

		$rows = $tb->rows();

		$count = array_count_values(array_column($rows, 'genre'));

		arsort($count);

		$options = [];

		$max_genres = array_slice($count, 0, 20, true);

		foreach ($max_genres as $genre_id => $num) {

			$row = $genreTb->row(["id=$genre_id"]);
			
			if ($row) $options[] = ['id'=>$row->id, 'name'=>$row->name];

		}

		return $options;

	}

	private function gContentTypeOptions () {

		$tb = new \Tables\ContentTypes;

		$rows = $tb->rows();

		$options = [];

		foreach ($rows as $row) {

			$options[] = ['id'=>$row->id, 'name'=>$row->name];

		}

		return $options;

	}

	private function gLanguageOptions () {

		$options = [
			['id'=>'en', 'name'=>'English'],
			['id'=>'es', 'name'=>'Español'],
			['id'=>'fr', 'name'=>'Français']
		];

		return $options;

	}

	private function sOption ($optionTitle, $queryId, $optionType, $values) {

		$this->options[] = (object) ['title'=>$optionTitle, 'q_id'=>$queryId, 'type'=> $optionType, 'values'=>$values];

	}

	public function check ($id) {

		foreach ($this->options as &$o) if ($o->type != 'range') {
			
			foreach ($o->values as &$v) if ($v['id'] == $id) {
				
				$v['checked'] = true;
			
				return true;

			}
		}

	}
	public function sRange ($key, $value) {

		foreach ($this->options as &$o) if ($o->type == 'range') {
			
			if ($value > $o->values['default'][0] && $value < $o->values['default'][1]) {

				$o->values['values'][$key] = $value;

				return true;

			}

		}

	}
}


class Search
{

	public $ui;

	private $title = 'Browser';

	private $channels = [], $details = [], $genres = [];

	private $results = [];

	public function __construct (App $a) {

		$this->a = $a;

		$this->sort = new SortOptions;
		$this->filtering = new Filtering;

	}

	private function handleError ($err) {

		$this->a->body(div(['class'=>'card channel'], div(['class'=>'error'], $err)));

	}

	private function sChannels () {

		//if (isset($this->i->content)) if (!isset($_SESSION['search_channels'][$this->i->content])) {

			//$tb_contents = \Tables\ContentTypes::instance();
			$channels = \Tables\Channels::instance();
			$details = \Tables\ChannelDetails::instance();
			$genres = \Tables\MediaGenres::instance();

			$this->channels = $channels->rows();
			$this->details = $details->rows();

			foreach ($genres->rows() as $row) {

				$this->genres[$row->genre][$row->channel] = 1;

			}
			//if ($row = $tb_contents->row(["id=". $this->i->content])) {

			//	 = $tb_channels->rows(["content_type=$row->id"]);

			//	$_SESSION['search_channels'][$this->i->content] = $this->channels;
				
		//	}
			
		//} else $this->channels = $_SESSION['search_channels'][$this->i->content];
		
	}


	public function sParam ($g) {

		$this->sChannels();

		$tb = \Tables\Channels::instance();

		if (isset($g->c)) {//content Type
			
			if ($this->filtering->check($g->c)) {
			
				$channels = [];

				foreach ($this->channels as $c) if ($c->content_type == $g->c) $channels[] = $c;

				$this->channels = $channels;

			}
		}

		if (isset($g->l)) {//language
			
			if ($this->filtering->check($g->l)) {
				
				$details = [];

				foreach ($this->details as $d) if ($d->original_language == $g->l) $details[] = $d;

				$this->details = $details;

			}
		}

		if (isset($g->p)) {//in production
			
			if ($this->filtering->check($g->p)) {
				
				$details = [];

				foreach ($this->details as $d) if ($d->in_production) $details[] = $d;

				$this->details = $details;

			}
		}

		if (isset($g->t)) {//Rintime
			
			list($min, $max) = explode(',', $g->t);

			$min_set = $this->filtering->sRange(0, $min);
			$max_set = $this->filtering->sRange(1, $max);

			if ($min_set) $options[] = "runtime>=$min";
			if ($max_set) $options[] = "runtime<=$max";

			$details = [];

			foreach ($this->details as $d) {
				
				$obove_min = false;
				$below_max = false;

				if ($min_set) if ($d->runtime >= $min) $obove_min = true;
				if ($max_set) if ($d->runtime <= $max) $below_max = true;

				if ($obove_min || $below_max) $details[] = $d;
				
			}

			$this->details = $details;


		}

		if (isset($g->g)) {//genre
			
			if ($this->filtering->check($g->g)) {
				
				$details = [];

				$media_belonging_to_genre = @$this->genres[$g->g];

				if ($media_belonging_to_genre) {
					
					foreach ($this->details as $d) if (isset($media_belonging_to_genre[$d->channel])) $details[] = $d;

					//$this->details = $details;//an unknown genre does not affect filtering
					
				}
				
				$this->details = $details;//an unknown genre affects filtering as if no media was matched
			}
		}

		

		$channels = [];

		$channels_keys = array_column($this->channels, 'id');
		$details_keys = array_column($this->details, 'channel');

		$pool =
		array_intersect_key(
			array_combine($details_keys, $this->details),
			array_combine($channels_keys, $this->channels) 
		);
		
		$sort = $this->sort->check(@$g->s ?: 0);
			
		$order = $this->sort->checkOrder(@$g->o ?: 0);
		
		array_multisort(array_column($pool, $sort->gColumn()), $order->gSort(), $pool);

		$page = @$g->pg ?: 1;

		$limit = 12;

		$pages = ceil(count($pool) / $limit);

		$start = $limit * $page - $limit;

		$pool = array_slice($pool, $start, $limit);

		foreach ($pool as $row) {

			$e = new \Channel($row->channel);

			$e->sDetails();

			$e->sPoster();

			$this->results[] =$e;

		}

		$e = [
			'title'=>$this->title,
			'sort'=> $this->sort,
			'filters'=>array_values($this->filtering->options),
			'pagination'=> [
				'pages'=> $pages,
				'current'=>$page
			],
			'results'=>$this->results
		];

		$this->a->code('window.addEventListener("load", t => new Search(' . json_encode($e) . '), false);');
		$this->a->body(div(['class'=>'card search']));
	}


}


$a = new Search($app);

$a->sParam((object) $_GET);

?>