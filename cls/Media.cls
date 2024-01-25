<?php

class Media extends MediaElement
{

	protected function gTb () {return \Tables\Media::instance();}

	protected function sRow () {
		
		if ($this->row) {

			$this->id = $this->row->id;
			$this->name = str_clean($this->row->name);

		}

	}

	public function gPath (Playlist $owner = null) :Media 
	{
	
		$this->gOwner($owner)->gPath();

		$this->path = $this->owner->path . '/' . $this->row->basename;
		
		return $this;
	
	}
	
	public function gOwner (Playlist $owner = null) :Playlist 
	{
	
		if (!$this->owner) $this->owner = $owner ? $owner : new Playlist($this->row->owner);
		
		return $this->owner;
	
	}

}

?>