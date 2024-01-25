<?php

namespace Scan;

/*

tables/Elements

Media
Playlists
Channels
collections


no channel/playlist
series
seasons
movies


*/


function gFilesize (string $path) {

	$fp = @fopen($path, 'r');

	@fseek($fp, 0, SEEK_END);

	$size = ftell($fp);

	fclose($fp);

	return $size;

}

class MediaElement
{

	public $id, $name;

	public $al = 3;

	protected $prefix;

	public function __construct (string $path) {

		$this->path = $path;

		$this->id = $this->prefix . '_' . sha1($path);

		$this->name = basename($path);

	}

	public function gPosters ($images) {

		if ($images) foreach ($images as &$i) $i->owner = $this->id;

		return $images ?? [];

	}

	public function gBanners ($images) {

		if ($images) foreach ($images as &$i) $i->owner = $this->id;

		return $images ?? [];

	}

	public function gTrailers ($videos) {

		$trailers = [];

		if ($videos) foreach ($videos as $v) {

			$e = new Trailer($v);

			$e->owner = $this->id;

			$trailers[] = $e;

		}

		return $trailers;

	}
}

class Media extends MediaElement
{
	protected $prefix = 'md';

	//private coz its not needed in DB
	private $path;
	
	public function __construct (string $path) {

		$info = (object) pathinfo($path);

		$this->path = $path;

		$this->name = $info->filename;

		$this->basename = $info->basename;

		$this->id = $this->prefix . '_' . sha1($path);

	}
}
class Channel extends MediaElement
{
	protected $prefix = 'ch';
	public $path;
}
class Playlist extends MediaElement
{

	protected $prefix = 'pl';
	public $path, $owner;
	
	public function gMedia ($videos) {

		$media = [];

		if ($videos) foreach ($videos as $v) {

			$e = new Media($v);

			$e->owner = $this->id;

			$media[] = $e;

		}

		return $media;

	}


}
class ImageElement
{

	public $id, $path, $owner;

	public function __construct (string $path, int $width, int $height) {

		$this->path = $path;
		$this->width = $width;
		$this->height = $height;

		$this->id = $this->prefix . '_' . sha1($path);

	}

}
class Poster extends ImageElement
{

	protected $prefix = 'ps';

}
class Banner extends ImageElement
{

	protected $prefix = 'bn';

}

class Trailer
{

	public $id, $path, $owner;

	public $al = 2;

	protected $prefix = 'tr';

	public function __construct (string $path) {

		$this->path = $path;

		$this->id = $this->prefix . '_' . sha1($path);

	}

}
class DirectoryHandler
{
	public 
		$files = [],

		$videos = [],
		$images = [],
		$subtitles = [],
		$_trailers = [],
		$_posters = [],
		$_banners = [],
		
		$trailers = [],
		$posters = [],
		$banners = [],
		$playlists = [],
		$channels = [];

	public function __construct () {

		$this->fm = new \Finder('', true);

	}

	public function scan (array $directories) {

		foreach ($directories as $dir) {
			
			foreach ($this->fm->videos($dir) as $v) $this->videos[dirname($v)][] = $v;
			foreach ($this->fm->images($dir) as $i) $this->images[dirname($i)][] = $i;
			//foreach ($this->fm->gFiles(['srt'], $dir) as $s) $this->subtitles[dirname($s)][] = $s;

		}

		msg('VIDEOS', $this->videos);

		$this->sortVideos();
		$this->sortImages();

		$this->createPlaylists();
		$this->createChannels();
		//$this->createTrailers();

		msg('POSTERS', $this->posters);
		msg('POSTERS', $this->banners);
		msg('TRAILERS', $this->trailers);

		msg('MEDIA', $this->media);
		msg('CHANNELS', $this->channels);
		msg('PLAYLISTS', $this->playlists);

	}

	protected function sortVideos () {}
	protected function sortImages () {

		foreach ($this->images as $dirname => $images) foreach ($images as $i) {

			list($width, $height) = getimagesize($i);

			if ($width > $height) {
				
				$this->_banners[$dirname][] = new Banner($i, $width, $height);
				
			} else $this->_posters[$dirname][] = new Poster($i, $width, $height); 

		}

	}

	protected function createPlaylists () {

		foreach ($this->videos as $path => $media) {

			$e = new Playlist($path);

			foreach ($e->gMedia(@$this->videos[$e->path]) as $m) $this->media[] = $m;
			foreach ($e->gPosters(@$this->_posters[$e->path]) as $p) $this->posters[] = $p;
			foreach ($e->gBanners(@$this->_banners[$e->path]) as $b) $this->banners[] = $b;
			foreach ($e->gTrailers(@$this->_trailers[$e->path]) as $t) $this->trailers[] = $t;

			$this->playlists[] = $e;

		}

	}

	
	protected function createTrailers () {//not working

		foreach ($this->trialers as $path => $media) {

			$e = new Playlist($path);

			$this->posters = array_merge($this->posters, $e->gPosters(@$this->posters[$e->path]));

			foreach ($e->gMedia(@$this->videos[$e->path]) as $m) $this->media[] = $m;

			$this->playlists[] = $e;

		}

		msg('PLAYLISTS', $this->playlists);
		msg('MEDIA', $this->media);

	}

	protected function createChannels () {}


}

class DirectoryHandlerSeries extends DirectoryHandler
{
	
	protected function createChannels () {

		$channels = [];

		foreach ($this->playlists as $pl) $channels[dirname($pl->path)][] = $pl;
		
		foreach ($channels as $path => $playlists) {

			$e = new Channel($path);

			foreach ($playlists as $p) $p->owner = $e->id;
			foreach ($e->gPosters(@$this->_posters[$e->path]) as $p) $this->posters[] = $p;
			foreach ($e->gBanners(@$this->_banners[$e->path]) as $b) $this->banners[] = $b;
			foreach ($e->gTrailers(@$this->_trailers[$e->path]) as $t) $this->trailers[] = $t;

			$this->channels[] = $e;

		}

	}
	
}

class DirectoryHandlerMovies extends DirectoryHandler
{

	protected function sortVideos () {
		
		$movies = [];
		$trailers = [];

		foreach ($this->videos as $path => $media) {

			$trailer = false;
			$parent = dirname($path);

			if (@$this->videos[$parent]) {
				//if parent directory has media, current directory contains trailer
	
					$trailer = true;
					foreach ($media as $med) $trailers[$parent][] = $med;

			//if (strpos(strtolower($path), 'trailer') !== false) {

			//	$trailer = true;
			//	foreach ($media as $med) $trailers[$path][] = $med;

			//} else
			} elseif (count($media) > 1) {//in this case the trailer is in $path not subpath

				foreach ($media as $med) if (gFilesize($med) < 100000000) {
					
					$trailers[$path][] = $med;

				} else $movies[$path] = $media;

			} else {

				$movies[$path] = $media;

			}


		}

		$this->videos = $movies;
		$this->_trailers = $trailers;
		
	}
	
}

class DatabaseHandler
{
	public function __construct () {
	
		$this->tables	= (object) [];
		$this->inDB		= (object) [];

		$this->tables->media		= new \Tables\Media;
		$this->tables->posters		= new \Tables\Posters;
		$this->tables->banners		= new \Tables\Banners;
		$this->tables->trailers		= new \Tables\Trailers;
		$this->tables->playlists	= new \Tables\Playlists;

		$this->init();

		$this->inDB->media		= $this->tables->media->column('id');
		$this->inDB->posters	= $this->tables->posters->column('id');
		$this->inDB->banners	= $this->tables->banners->column('id');
		$this->inDB->trailers	= $this->tables->trailers->column('id');
		$this->inDB->playlists	= $this->tables->playlists->column('id');

	
	}

	protected function init () {}//aka construct just specific stuffs of child
	protected function final () {}//aka addtodba ....


	public function addtoDB ($directoriesHander) {

		$dh			= $directoriesHander;
		$this->dh	= $dh;

		$this->media		= self::filter($this->inDB->media, $dh->media);
		$this->posters		= self::filter($this->inDB->posters, $dh->posters);
		$this->banners		= self::filter($this->inDB->banners, $dh->banners);
		$this->trailers		= self::filter($this->inDB->trailers, $dh->trailers);
		$this->playlists	= self::filter($this->inDB->playlists, $dh->playlists);

		msg('NEW-MEDIA', $this->media);
		msg('NEW-POSTERS', $this->posters);
		msg('NEW-POSTERS', $this->banners);
		msg('NEW-TRAILERS', $this->trailers);
		msg('NEW-PLAYLISTS', $this->playlists);

		
		msg('DB-ADD', 'media');
		$this->tables->media->sRows(...$this->media);

		msg('DB-ADD', 'posters');
		$this->tables->posters->sRows(...$this->posters);

		msg('DB-ADD', 'banners');
		$this->tables->banners->sRows(...$this->banners);

		msg('DB-ADD', 'trailers');
		$this->tables->trailers->sRows(...$this->trailers);

		msg('DB-ADD', 'playlists');
		$this->tables->playlists->sRows(...$this->playlists);
		
		$this->final();

	}

	protected static function filter (array $inDB, array $elms) {

		$arr = [];

		foreach ($elms as $elm) if (!in_array($elm->id, $inDB)) $arr[] = get_object_vars($elm);

		return $arr;

	}

	protected static function diff (array $multiDarray, array $plainArray, string $column = 'id') {

		$arr = [];

		foreach ($multiDarray as $e) if (!in_array($e->{$column}, $plainArray)) $arr[] = get_object_vars($e);

		return $arr;

	}


	public function gRows () {

		return $this->tables->directories->gRows();

	}
}
class DatabaseHandlerSeries extends DatabaseHandler
{
	
	protected function init () {

		$this->tables->directories	= new \Tables\DirectoriesSeries;
		$this->tables->channels		= new \Tables\Channels;

		$this->tables->series		= new \Tables\Series;
		$this->tables->seasons		= new \Tables\Seasons;

		$this->inDB->channels 	= $this->tables->channels->column('id');

		$this->inDB->series 	= $this->tables->series->column('channel');
		$this->inDB->seasons 	= $this->tables->seasons->column('playlist');
		
	}

	protected function final () {
	
		$this->channels 	= self::diff($this->dh->channels, $this->inDB->channels);

		$this->series 		= self::array_change_Key(self::diff($this->dh->channels, $this->inDB->series), 'id', 'channel');
		$this->seasons 		= self::array_change_Key(self::diff($this->dh->playlists, $this->inDB->seasons), 'id', 'playlist');
		
		msg('NEW-CHANNELS', $this->channels);
		msg('NEW-SERIES', $this->series);
		msg('NEW-SEASON', $this->seasons);
		
		msg('DB-ADD', 'channels');
		$this->tables->channels->sRows(...$this->channels);
	
		msg('DB-ADD', 'series');
		$this->tables->series->sRows(...$this->series);

		msg('DB-ADD', 'seasons');
		$this->tables->seasons->sRows(...$this->seasons);

	}

	
	protected static function array_change_Key (array $multiDarray, string $oldKey, string $newkey) {

		$arr = [];

		foreach ($multiDarray as $e) {
			
			$arr[] = [$newkey => $e[$oldKey]];

		}

		return $arr;

	}


}
class DatabaseHandlerMovies extends DatabaseHandler
{
	protected function init () {
	
		$this->tables->directories	= new \Tables\DirectoriesMovies;
		$this->tables->playlists	= new \Tables\Movies;

	}

}
class Scanner
{

	public function __construct ($contentType) {

		switch ($contentType) {
			case 'series':
				$this->directoryHandler = new DirectoryHandlerSeries;
				$this->databaseHandler = new DatabaseHandlerSeries;
				break;
			
			case 'movies':
				//$this->directoriesTable = new \Tables\DirectoriesMovies;
				$this->directoryHandler = new DirectoryHandlerMovies;
				$this->databaseHandler = new DatabaseHandlerMovies;
				break;
			
			default:
				# code...
				break;
		}

	}

	public function run (array $directories) {

		if (!count($directories)) msg('QUERY', 'No directories were specified');

		$dirs = [];

		foreach($this->databaseHandler->gRows() as $row) {

			$dirs[] = $row->path;//(object) ['path'=>$row->path, 'al' => $row->al];

		}

		$this->directoryHandler->scan($dirs);

		$this->databaseHandler->addtoDB($this->directoryHandler);
		

	}

}

if (isset($_GET['q'])) {
	
	msg('QUERY', $_GET['q']);

	$scan = new Scanner($_GET['q']);

	$dirs = [];

	if (isset($_GET['ds'])) {

		msg('QUERY', 'Directories specified');

		$dirs = explode(';', $_GET['ds']);

	}

	$scan->run($dirs);

} else msg('QUERY', 'Please specify q');



?>