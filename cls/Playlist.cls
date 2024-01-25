<?php

class Playlist extends MediaElement
{

	protected function gTb () {return \Tables\Playlists::instance();}

	protected function sRow () {
		
		if ($this->row) {

			$this->id = $this->row->id;
			$this->name = str_clean($this->row->name);

		}

	}

	
	public function gMedia (int $start = 0, int $length = 0) {

		$this->media = [];

		$tb = \Tables\Media::instance();

		$rows = $tb->gRows(["owner=$this->id"]);

		$last = end($rows);
		$end = false;

		foreach ($rows as $row) {

			$e = new Media($row);

			$this->media[] = $e;

			if ($last->id == $row->id) $end = true;

		}

		return $end;

	}

	
	public function gOwner (Channel $owner = null) :Channel 
	{
	
		if (!@$this->owner) $this->owner = $owner ? $owner : new Channel($this->row->owner);
		
		return $this->owner;
	
	}

	
}

?>