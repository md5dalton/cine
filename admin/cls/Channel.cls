<?php
namespace Admin;

class Channel extends \Channel
{
    
	public function setup () {

		$this->sDetails();

		$this->sType();
		$this->hasDetails();
		$this->hasPoster();
		$this->hasBanner();
		
	}

	public function sType () {
		
		$this->type = $this->row->content_type;

	}
	public function hasDetails () {

		$this->has_details = $this->details->id ? true : false;

	}

	public function hasPoster () {

		if ($path = $this->details->gPoster()) {

			$this->has_poster = file_exists($path);
		
			if (!$this->has_poster) $this->poster_path = $this->details->poster_path;

		}
	}
	public function hasBanner () {

		if ($path = $this->details->gBanner()) {

			$this->has_banner = file_exists($path);
		
			if (!$this->has_banner) $this->banner_path = $this->details->backdrop_path;

		}

	}

}

?>