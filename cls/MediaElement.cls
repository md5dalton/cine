<?php

abstract class MediaElement
{
	
	public $id, $name;

	protected $row, $tb;

	
	public function __construct ($e) {

		$this->tb = $this->gTb();

		$this->row = is_string($e) ? $this->tb->row(["id=$e"]) : $e;

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

?>