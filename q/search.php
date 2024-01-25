<?php



class SortOption
{
	public $id, $name;

	private $column, $order, $flag;

	public function __construct ($column, $label, $value, $order, $flag) {

		$this->column = $column;
		$this->name = $label;
		$this->id = $value;
		$this->order = $order;
		$this->flag = $flag;

	}

	public function gColumn () {

		return $this->column;

	}

	public function gOrder () {

		return $this->order;

	}

	public function gFlag () {

		return $this->flag;

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

	public function __construct ($contentType) {
		
		$name = ['name', 'TV Show name (a-z)', SORT_ASC, SORT_REGULAR];
		$name2 = ['name', 'TV Show name (z-a)', SORT_DESC, SORT_REGULAR];
		$date = ['last_air_date', 'Latest release', SORT_DESC, SORT_REGULAR];
		
		if ($contentType == 'movie') {
			
			$name = ['title', 'Movie Title (a-z)', SORT_ASC, SORT_REGULAR];
			$name2 = ['title', 'Movie Title (z-a)', SORT_DESC, SORT_REGULAR];
			$date = ['release_date', 'Latest release', SORT_DESC, SORT_REGULAR];

		}

		$options = [
			$name,
			$name2,
			$date,
			['popularity', 'Most Popular', SORT_DESC, SORT_NUMERIC],
			['timestamp', 'Recently Added', SORT_DESC, SORT_REGULAR]
		];


		foreach ($options as $key => $o) {

			list($column, $label, $order, $flag) = $o;

			$this->sOption($column, $label, $key, $order, $flag);

		}

		//$this->order = [
		//	new SortOrder(0, 'Asc', SORT_ASC),
		//	new SortOrder(1, 'Desc', SORT_DESC)
		//];

	}	

	private function sOption ($column, $label, $id, $order, $flag) {

		$e = new SortOption($column, $label, $id, $order, $flag);

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

	
	public function apply (&$items, $sort/*, $order*/) {

		$sort = $this->check($sort ?: 0);
			
		//$order = $this->checkOrder($order ?: 0);
		
		array_multisort(array_column($items, $sort->gColumn()), $sort->gOrder(), $sort->gFlag(), $items);

	}
}


class Filter
{
	public $id, $name;

	protected $column;

	public function __construct ($id, $name, $column) {

		$this->id = $id;
		$this->name = $name;
		$this->column = $column;

	}

	public function apply ($items) {

		$_items = [];

		foreach ($items as $i) if ($i->{$this->column} == $this->id) $_items[] = $i;

		return $_items;

	}

}
class ArrayTableFilter extends Filter
{
	private $tb;

	public function __construct ($id, $name, $column, $tb) {

		parent::__construct($id, $name, $column);
		
		$this->tb = $tb;

	}

	public function apply ($items) {

		$media = $this->tb->column('owner', ["$this->column=$this->id"]);
		
		$items = array_combine(array_column($items, 'details'), $items);

		$intersect = array_intersect_key($items, array_flip($media));

		return $intersect;

	}
	
}

class Filtering
{
	public $filters = [];

	private $start_id = 1;

	public function __construct ($contentType) {
		
		$this->tb = gTables('ContentTypes', 'Genres', 'MediaGenres', 'Networks', 'TVNetworks');

		$options = [
			['Content Type', 'c', 'rad', $this->gContentTypeOptions()],
			['Genre', 'g', 'rad', $this->gArrayTableOptions($this->tb->genres, $this->tb->mediagenres, 'genre')]
			//['Original Language', 'l', 'rad', $this->gLanguageOptions()],
			//['In Production', 'p', 'check', [['name'=> 'Yes', 'id'=>1]]],
			//['Running Time', 't', 'range', ['default'=>[30, 180]]]
		];

		if ($contentType == 'tv') $options[] = [
			'Providers', 'pv', 'rad', $this->gArrayTableOptions($this->tb->networks, $this->tb->tvnetworks, 'network')
		];

		foreach ($options as $o) {

			list($optionTitle, $queryId, $optionType, $values) = $o;

			$this->sOption($optionTitle, $queryId, $optionType, $values);
			
		}
		

	}

	private function gArrayTableOptions ($objectTable, $arrayTable, $column) {

		$rows = $arrayTable->rows();

		$count = array_count_values(array_column($rows, $column));

		arsort($count);

		$options = [];

		$max = array_slice($count, 0, 20, true);

		foreach ($max as $id => $num) {

			$row = $objectTable->row(["id=$id"]);
			
			if ($row) $options[] = new ArrayTableFilter($row->id, $row->name, $column, $arrayTable);

		}

		return $options;

	}

	private function gContentTypeOptions () {
		
		$rows = $this->tb->contenttypes->rows();

		$options = [];

		foreach ($rows as $row) {

			$options[] = new Filter($row->id, $row->name, 'content_type');

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

		foreach ($this->options as $o) if ($o->type != 'range') {
			
			foreach ($o->values as $v) if ($v->id == $id) {
				
				$v->checked = true;
			
				return $v;

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

	public function apply ($items, $filter) {

		$f = $this->check($filter);

		if (!$f) return [];
		
		return $f->apply($items);

	}
}

class Pagination
{

	private $items;

	public $pages = 1;

	private $limit = 15;

	public function __construct (array $items) {

		$this->items = $items;
		
		$this->pages = ceil(count($items) / $this->limit);

	}

	public function gPage (int $page) {

		return array_slice($this->items, ($page - 1) * $this->limit, $this->limit);

	}
}

function _array_combine (array $keys, array $values) {

	$result = [];

	foreach ($keys as $i => $k) $result[$k][] = $values[$i];
	
	array_walk($result, function (&$v) {
		
		$v = (count($v) == 1) ? array_pop($v) : $v;

	});

	return $result;

}
class Search
{

	public $ui;

	private $title = 'Browser';

	private $channels = [], $details = [], $genres = [];

	private $results = [], $output = [];

	public function __construct (App $a, $g) {

		$this->a = $a;
		$this->g = $g;

		$contentType = @$g->c ?: 'movie';

		$this->sort = new SortOptions($contentType);
		$this->filtering = new Filtering($contentType);

		$this->page = @$g->pg ?: 1;

		//if ($this->page == 1) {
	
		$this->tb = gTables('Channels', 'ChannelDetails');

		$this->sMedia();

		$this->channels = $this->filtering->apply($this->channels, $contentType);//content type
		
		if (isset($g->g)) $this->channels = $this->filtering->apply($this->channels, $g->g);//genre
		if (isset($g->pv)) $this->channels = $this->filtering->apply($this->channels, $g->pv);//networks
		
		$this->sort->apply($this->channels, @$g->s/*, @$g->o*/);
		
		$_SESSION['search-channels'] = $this->channels;

		/*
		} else {

			$this->channels = @$_SESSION['search-channels'] ?: [];
			
			$this->filtering->check($contentType);
			$this->filtering->check(@$g->g);
			$this->filtering->check(@$g->pv);

			$this->sort->check(@$g->s);
			$this->sort->checkOrder(@$g->o);

		}*/

		$this->sResults();

	}

	private function handleError ($err) {

		$this->a->body(div(['class'=>'card channel'], div(['class'=>'error'], $err)));

	}

	private function gDetails () {

		$rows = $this->tb->channeldetails->rows();

		$details = [];

		foreach ($rows as &$row) {

			$id = $row->id;
			
			unset($row->id);

			$details[$id] = $row;

		}

		return $details;

	}
	private function sMedia () {

		$details = $this->gDetails();
		
		foreach($this->tb->channels->rows() as $row) {

			if ($row->details) {
				
				$chd = @$details[$row->details];
			
				if ($chd) $this->channels[] = (object) array_merge((array) $row, (array) $chd);
					
			}

		}
			
	}

	private function sResultsOLd () {
		
		$channels = [];

		foreach ($this->channels as $c) $channels[$c->details][] = $c;
		
		foreach ($this->details as $d) {
			
			if (isset($channels[$d->id])) foreach($channels[$d->id] as $channel) {

				$this->results[] = (object) ['channel'=>$channel, 'details'=>$d];

				//if (count($this->results) >= $this->limit) return;

			}

		}

		$_SESSION['search-results'] = $this->results;

	}

	private function sResults () {

		$pagination = new Pagination($this->channels);

		foreach ($pagination->gPage($this->page) as $r) {

			$e = new \Channel($r->id);

			$e->sDetails();

			$e->sPoster();

			$this->results[] =$e;

		}
		

		$e = [
			'title'=>$this->title,
			'sort'=> $this->sort,
			'filters'=>array_values($this->filtering->options),
			'pagination'=> [
				'pages'=> $pagination->pages,
				'current'=>$this->page
			],
			'results'=>$this->results
		];

		$this->a->code('window.addEventListener("load", t => new Search(' . json_encode($e) . '), false);');
		$this->a->body(div(['class'=>'card search']));

	}

}


$a = new Search($app, (object) $_GET);

?>