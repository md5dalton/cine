<?php



class Genre
{
	
	public $id, $name;

	protected $row, $tb;
	
	public function __construct ($e) {

		$this->tb = \Tables\Genres::instance();

		$this->row = is_string($e) ? $this->tb->row(["id=$e"]) : $e;

		if ($this->row) {

			$this->id = $this->row->id;
			$this->name = $this->row->name;

		}

	}

}
class Overview
{
	
	public $language, $content;

	protected $row, $tb;
	
	public function __construct ($e) {

		$this->tb = \Tables\Overviews::instance();

		$this->row = is_string($e) ? $this->tb->row(["owner=$e"]) : $e;

		if ($this->row) {

			$this->language = $this->row->language;
			$this->content = $this->row->content;

		}

	}

}


class ChannelDetails
{
	private $details;

	private $sizes, $api;

	private $row;

	public function __construct ($e) {

		$tb = \Tables\ChannelDetails::instance();

		$this->row = is_numeric($e) ? $tb->row(["id=$e"]) : $e;


		if ($this->row) {

			$this->id = $this->row->id;
			$this->details = $this->row; 

		}
		

		$this->api = new \TMDB;

		$this->sizes = (object) [
			'poster' => 'w500',
			'banner' => 'w780'
		];

	}

	public function __get ($prop) {

		return @$this->details->{$prop};

	}

	public function gPoster () {

		if ($this->details) {

			$fullpath = $this->api->root . "images/" . $this->sizes->poster . $this->details->poster_path;

			return $fullpath;

		}

	}

	public function gBanner () {

		if ($this->details) {

			$fullpath = $this->api->root . "images/" . $this->sizes->banner . $this->details->backdrop_path;

			return $fullpath;

		}

	}

	
	public function gGenres () {

		$tb = \Tables\MediaGenres::instance();

		$rows = $tb->rows(["owner=$this->id"]);

		$genres = [];

		foreach ($rows as $row) {

			$e = new Genre($row->genre);

			$genres[] = $e->name;

		}

		return $genres;

	}
	public function gTagline ($language) {

		$tb = \Tables\Taglines::instance();

		if ($row = $tb->row(["owner=$this->id", "language=$language"])) return $row->content;
		
	}

	public function gOverview ($language) {
		
		$tb = \Tables\Overviews::instance();

		$userConfig = \UserConfig::instance();

		if ($row = $tb->row(["owner=$this->id", "language=$language"])) return $row->content;
		
	}
}

class Channel extends MediaElement
{

	protected $details;

	protected function gTb () {return \Tables\Channels::instance();}

	protected function sRow () {
		
		if ($this->row) {

			$this->id = $this->row->id;
			$this->name = str_clean($this->row->name);

			$this->{$this->row->content_type} = true;
			
		}
		
	}
	
	public function gType () {
		
		$this->type = $this->row->content_type;

		return $this->row->content_type;

	}
	

	public function gRunningTime () {

		if ($this->details) {

			$this->runtime = $this->details->release_date ?: $this->details->last_air_date;

		}
	}


	public function gYear () {

		if (!$this->_date) $this->sDate();

		$this->year = @reset(explode('-', $this->_date));
	
	}

	public function gDate () {

		if (!$this->_date) $this->sDate();
		
		$this->date = formatdateAmerican($this->_date);
	
	}

	public function sDate () {

		if ($this->details) {

			$this->_date = $this->details->release_date ?: $this->details->last_air_date;

		}

	}

	
	public function gSeasonsCount () {

		if ($this->details) {
			
			$this->number_of_seasons = $this->details->number_of_seasons;
			
		} 
	}
	
	public function gBanner () {

		$path = realpath('./bg/series-banner.jpg');

		if ($this->details) if (file_exists($path)) $path = $this->details->gBanner();

		$this->banner = imagepath($path);

		return $path;

	}
	
	public function gPoster () {

		$this->poster = '';

		if ($this->details) {

			$path = $this->details->gPoster();

			if (file_exists($path)) {
				
				$this->poster = imagepath($path);
			
				return $path;

			}

		} 
	}
	
	public function gStats () {

		if (!isset($this->stats)) $this->stats = new ChannelStats($this);

	}
	public function gGenres () {
		
		if (!isset($this->genres)) {

			$tb = \Tables\MediaGenres::instance();

			$rows = $tb->rows(["channel=$this->id"]);

			foreach ($rows as $row) {

				$e = new Genre($row->genre);

				$this->genres[] = $e;

			}

		}

	}
	public function gOverviews () {
		
		if (!isset($this->overviews)) {
			
			$this->overviews = [];

			$tb = \Tables\Overviews::instance();

			$rows = $tb->rows(["owner=$this->id"]);

			foreach ($rows as $row) {

				$e = new Overview($row);

				$this->overviews[] = $e;

			}

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



	public function gDetails () {

		return $this->details;

	}

	public function sDetails ($detailsRow = null) {

		$this->details = new ChannelDetails($detailsRow ?: $this->row->details);

		$this->name = $this->details->name ?: $this->details->title ?: $this->name;

	}
	
	
	public function sPoster () {

		$path = $this->details->gPoster();
		
		if (file_exists($path)) $this->poster = imagepath($path);

	}
	public function sBanner () {

		$path = $this->details->gBanner();
		
		if (file_exists($path)) $this->banner = imagepath($path);

	}
	protected function sImages () {
		
		$this->sPoster();
		$this->sbanner();
	
	}
	protected function sScore () {

		$this->score = $this->details->vote_average;
	
	}
	protected function sTrailer () {

		$this->trailer = '';
	
	}
	protected function sRating () {

		$this->rating = '';
	
	}
	protected function sGenres () {

		$this->genres = $this->details->gGenres();

	}
	protected function sTagline () {

		$user = \User::instance();

		$this->tagline = $this->details->gTagline($user->gConfig('language') ?: 'en');
		
	}
	protected function sOverview () {
		
		$user = \User::instance();

		$this->overview = $this->details->gOverview($user->gConfig('language') ?: 'en');
		
	}
	protected function sOriginalLanguage () {

		$this->original_language = $this->details->original_language;
	
	}
	protected function sTheme () {
		
		$image =  $this->details->gPoster() ?: $this->details->gBanner();
		
		$theme = hexToRgb(gImgColor(
			@file_exists($image) ? $image : realpath($this->default_banner)
		));

		//$this->theme = array_values($theme);
		$this->theme = $theme;

	}

	
	protected function sName () {}
	public function sYear () {
	
		$this->year = @reset(explode('-', $this->details->last_air_date ?: $this->details->release_date));
	
	}
	protected function sRuntime () {}

	public function setup () {

		$this->sDetails();

		$this->sImages();
		$this->sName();
		$this->sYear();
		$this->sScore();
		$this->sTrailer();
		$this->sRating();
		$this->sRuntime();
		$this->sGenres();
		$this->sTagline();
		$this->sOverview();
		$this->sTheme();
		$this->sOriginalLanguage();
		
	}
	
}

class Series extends Channel
{
	protected $default_banner = './bg/series-banner.jpg';

	//public function setup () {

		//poster
		//banner
	
		//name
		//year aka last_air_date select year
		//score aka vote average
		//trailer
		//rating
		//episode_runtime
		//genres
		//tagline
		//overviews

	//}

	
	protected function sName () {

		$this->name = $this->details->name;

	}
	public function sYear () {

		$this->year = @reset(explode('-', $this->details->last_air_date));
	
	}
	protected function sRuntime () {

		$this->runtime = $this->details->episode_run_time;
	
	}
}

class Movie extends Channel
{
	protected $default_banner = './bg/series-banner.jpg';
	
	protected function sName () {

		$this->name = $this->details->title;

	}
	public function sYear () {

		$this->year = @reset(explode('-', $this->details->release_date));
	
	}
	protected function sRuntime () {

		$this->runtime = $this->details->runtime;
	
	}
}

?>