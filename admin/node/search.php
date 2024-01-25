<?php
require_once 'cls/classes.php';

define('SORT_TYPES', [
	'string'=>[SORT_ASC=>'a-z',SORT_DESC=>'z-a'],
	'date'=>[SORT_ASC=>'old to new',SORT_DESC=>'new to old'],
	'number'=>[SORT_ASC=>'smallest to biggest',SORT_DESC=>'biggest to smallest']
]);

class Pagination
{

	public $current = [], $pages = [];

	public $max_pages_per_session = 5;

	public function __construct (array $items, int $max_items_per_page) {

		$this->items = $items;

		$this->count = count($items);
		
		$this->max_items_per_page = $max_items_per_page;

		$this->pagesCount = ceil($this->count / $max_items_per_page);

	}
	
	public function gPage (int $pageNumber) {

		$this->currentPage = $pageNumber;

		$start = $this->max_items_per_page * $pageNumber - $this->max_items_per_page;

		$this->current = array_slice($this->items, $start, $this->max_items_per_page);

		$this->sPages();
		
	}

	private function sPages () {

		$firstPageName = $this->currentPage > $this->max_pages_per_session ? 'First' : 1;

		$firstPage = ['id'=>1, 'name'=>$firstPageName];

		$this->pages[] = $firstPage;

		for ($i=$this->currentPage, $count = 0; $i <= $this->pagesCount; $i++, $count++) { 
			
			if ($i != 1) $this->pages[] = ['id'=>$i, 'name'=>$i];

			if ($count >= $this->max_pages_per_session) break;

		}

		if ($this->pagesCount > $this->max_items_per_page) {
			
			if ($this->pagesCount > ($this->currentPage + $this->max_pages_per_session)) {

				$lastPageName = 'Last';

				$lastPage = ['id'=>$this->pagesCount, 'name'=>$lastPageName];

				$this->pages[] = $lastPage;
			}
		}		
	}
}

class Paginator
{
	public $limit = 50000;
	//public $limit = 25;

	public function __construct ($items) {

		$this->items = $items;

		$this->total_items = count($items);

		$this->total_pages = ceil($this->total_items / $this->limit);

	}

	public function gPage (int $num) {
		
		$this->current = $num;

		$start = $this->limit * $num - $this->limit;

		$this->sPages();

		return array_slice($this->items, $start, $this->limit);

	}
	private function sPages () {

		$range = range(1, $this->total_pages);

		$pages = array_combine($range, $range);

		$pages_limit = 5;

		$select = $pages_limit - 2;

		$start = $this->current == 1 ? $this->current-1 : $this->current-2;
		
		if (($this->total_pages - $pages_limit) < $this->current) $start = $this->total_pages - $pages_limit - 2;

		//array_splice()
		$pages = array_slice(
			$pages, 
			$start, 
			$pages_limit+2, 
			true
		);

		foreach ($pages as $key => $p) {

			$this->pages[] = [
				'id'=>$key, 
				'name'=>$p, 
				'current' => $key == $this->current ? true : false
			];

		}

	}
}

class SortOption
{
	public $value, $label;

	private $type, $column, $order;

	private $_label, $label_posfix;

	public function __construct ($type, $value, $column, $label, $order = SORT_ASC) {

		$this->type = $type;

		$this->label_posfix = SORT_TYPES[$type];

		$this->value = $value;

		$this->column = $column;

		$this->_label = $label;

		$this->order = $order;

		$this->sLabel();

	}
	
	public function __clone () {

		$this->value .= 'b';

		$this->order = SORT_DESC;

		$this->sLabel();

	}

	public function gColumn () {

		return $this->column;

	}
	public function gOrder () {

		return $this->order;

	}

	private function sLabel () {

		$this->label = "$this->_label " . @$this->label_posfix[$this->order];

	}


}
class Sort
{
	public $options = [];

	private $labels = [];

	public function __construct () {

	}
	
	public function createOptions (...$options) {

		foreach ($options as $key => $o) {

			$o = (object) $o;
			
			$asc = new SortOption($o->type, $key, $o->column, $o->label);

			$desc = clone $asc;

			$this->options[$asc->value] = $asc;
			$this->options[$desc->value] = $desc;

		}
		
	}

}

class Filtering
{
	public $filters = [];

	public function __construct () {

		$content_tb = \Tables\ContentTypes::instance();

		$content_filters = [];

		foreach ($content_tb->rows() as $key => $row) {

			$e = ['label'=>$row->name, 'value'=>$row->id, 'checked' => $key == 0 ? true : false];

			$content_filters[] = $e;

		}

		$this->filters[] = ['title'=>'Content', 'options'=>$content_filters];

	}
}

class Search
{

	private $channels = [];

	private $errormsg, $result = [];

	public function __construct ($i) {

		$this->sort = new Sort;

		$this->sort->createOptions(
			['column'=>'name','type'=>'string','label'=>'Title'],
			['column'=>'timestamp','type'=>'date','label'=>'Date']
		);

		$this->filtering = new Filtering;

		$this->i = $i;

		$q = @$i->q;

		switch ($q) {
			case 'controls':
				$this->gControls();
				break;
			case 'results':
				$this->gResults();
				break;
			case 'submitdata':
				$this->sData();
				break;
		}


	}


	public function gControls () {
		
		$this->sort->options[0]->checked = true;

		$this->result['controls'] = [
			['id' => 1, 'title'=>'Sort', 'groups' => [
					['title'=>'Sort by', 'options' => array_values($this->sort->options)]
				]
			],
			['id' => 1, 'title'=>'Filters', 'groups' => $this->filtering->filters]
		];

	}

	private function sData () {
		
		if (!isset($this->i->data)) return $this->errormsg = 'Data not set yet';

		$db = new DatabaseUpdater;
		$api = new TMDB;

		$db->sData($this->i->data);
		$api->sData($this->i->data);

		$this->result['submitdata'] = true;

	}

	private function sChannels () {

		if (isset($this->i->content)) //if (!isset($_SESSION['search_channels'][$this->i->content])) {

			$tb_contents = \Tables\ContentTypes::instance();
			$tb_channels = \Tables\Channels::instance();

			if ($row = $tb_contents->row(["id=". $this->i->content])) {

				$this->channels = $tb_channels->rows(["content_type=$row->id"]);

				$_SESSION['search_channels'][$this->i->content] = $this->channels;
				
			}
			
		//} else $this->channels = $_SESSION['search_channels'][$this->i->content];
		
	}

	private function sortChannels () {
		
		if (key_exists($this->i->sort, $this->sort->options)) {

			$sortid = $this->i->sort;

			$sort = $this->sort->options[$sortid];

			array_multisort(array_column($this->channels, $sort->gColumn()), $sort->gOrder(), $this->channels);

		}
	}


	public function gResults () {

		$this->sChannels();
		
		if (isset($this->i->sort)) $this->sortChannels();

		$pageNumber = isset($this->i->page) ? $this->i->page : 1;

		$pagination = new Paginator($this->channels);

		$media = [];

		foreach ($pagination->gPage($pageNumber) as $row) {

			$e = new Channel($row);

			$e->gType();
			$e->gDetails();
			$e->hasDetails();
			$e->hasPoster();
			$e->hasBanner();

			$media[] = $e;

		}

		$this->result['pages'] = $pagination->pages;
		$this->result['results'] = $media;

	}

	public function getresult () {

		return $this->errormsg ? ['errormsg' => $this->errormsg] : ['search' => $this->result];

	}


}

$app = new Search($input);

?>