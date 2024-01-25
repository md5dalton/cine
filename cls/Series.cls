<?php

class Series extends MediaElement
{

	protected function gTb () {return \Tables\Channels::instance();}

	protected function sRow () {
		
		if ($this->row) {

			$this->id = $this->row->id;
			$this->name = str_clean($this->row->name);

		}

	}
	
	public function gPoster (bool $asURL = true) {

		$this->poster = '';

		$posters = \Tables\Posters::instance();

		$rows = $posters->gRows(["owner=$this->id"]);

		if ($rows) {
			
			/*
			foreach ($rows as &$row) if (file_exists($row->path)) {

				list($row->width, $row->height) = getimagesize($row->path);

			}*/

			//array_multisort(array_column((array) $rows, 'height'), $rows);

			shuffle($rows);
			
			$selected = reset($rows);

			if ($selected) $this->poster = $asURL ? imagepath($selected->path) : $selected->path;
			
		} 

	}
	public function gStats () {

		$this->stats = (object) ['playlists'=>0];

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
	
}

?>