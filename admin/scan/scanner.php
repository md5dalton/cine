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

abstract class MediaElement
{

	public $id, $name;

	protected $prefix;

	public function __construct (string $path) {

		$this->path = $path;

		$this->id = $this->prefix . '_' . sha1($path);

		$this->name = basename($path);

	}

	public function own (...$arrays) {

		foreach ($arrays as &$array) {
			
			if (isset($array[$this->path])) foreach ($array[$this->path] as &$i) {

				$i->owner = $this->id;

			}

		}

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
	public $path, $content_type;
}
class Playlist extends MediaElement
{

	protected $prefix = 'pl';
	public $path, $owner;

}


class Finder
{

	private $fm;
	private $directory;
	private $content;

	private
		$videos = [],
		$images = [];

	public
		$channels = [],
		$playlists = [],

		$media = [],

		$banners = [],
		$posters = [];

	public function __construct ($directory, $content) {

		$this->directory = $directory;
		$this->content = $content;

		$this->path = "$directory->path/";//add trailing /

		$this->fm = new \Finder($this->path, true);
		
		foreach ($this->fm->videos() as $v) $this->videos[dirname($v)][] = $v;
		//foreach ($this->fm->images() as $i) $this->images[dirname($i)][] = $i;

		//$this->sortImages();
		$this->sortVideos();

	}

	private function sortImages () {
		
		foreach ($this->images as $dirname => $images) foreach ($images as $i) {

			list($width, $height) = getimagesize($i);

			if (isset($height) && isset($width)) {
					
				if ($width > $height) {
					
					$this->banners[$dirname][] = new Banner($i, $width, $height);
					
				} else $this->posters[$dirname][] = new Poster($i, $width, $height); 
			}
		}

	}

	private function sortVideos () {
	
		$channels = [];
		$playlists = [];

		$folders_containing_media = array_keys($this->videos);

		foreach ($this->videos as $dirname => $videos) foreach ($videos as $v) {

			$e = new Media($v);

			$this->media[$dirname][] = $e;

		}

		foreach ($folders_containing_media as $folder) {

			$without_root = str_replace($this->path, '', $folder);

			$parts = explode('/', $without_root);

			$channel_path = implode('/', array_slice($parts, 0, $this->directory->cl + 1));

			$channel_full_path = $this->path . $channel_path;

			$channels[$channel_full_path] = $channel_full_path;

			if ($channel_full_path != $folder) $playlists[] = $folder;

		}
		

		foreach ($playlists as $folder) {

			$e = new Playlist($folder);

			$e->own($this->media, $this->posters, $this->banners);

			$this->playlists[dirname($folder)][] = $e;

		}

		foreach ($channels as $folder) {

			$e = new Channel($folder);

			$e->content_type = $this->content->id;

			$e->own($this->media, $this->posters, $this->banners, $this->playlists);

			$this->channels[dirname($folder)][] = $e;

		}

	}

}

class InDatabase
{
	public $channels = [], $playlists = [], $media = [];
}

class DatabaseHandler
{
	public function __construct () {
	
		$this->tables = gTables('Channels', 'Playlists', 'Media');
		
		$this->inDB = new InDatabase;

		$this->removeModified();


	}

	private function removeModified () {

		foreach ($this->tables->channels->rows() as $channel) if (is_dir($channel->path)) {

			$this->inDB->channels[] = $channel->id;
			
			foreach ($this->tables->media->rows(["owner=$channel->id"]) as $media) if (file_exists("$channel->path/$media->basename")) {

				$this->inDB->media[] = $media->id;

			} else {

				$this->tables->media->delete(["id=$media->id"]);

			}

		} else {

			$this->tables->channels->delete(["id=$channel->id"]);

			$this->tables->playlists->delete(["owner=$channel->id"]);

		}

		foreach ($this->tables->playlists->rows() as $playlist) if (is_dir($playlist->path)) {

			$this->inDB->playlists[] = $playlist->id;
			
			foreach ($this->tables->media->rows(["owner=$playlist->id"]) as $media) if (file_exists("$playlist->path/$media->basename")) {

				$this->inDB->media[] = $media->id;

			} else {

				$this->tables->media->delete(["id=$media->id"]);

			}

		} else {

			$this->tables->playlists->delete(["id=$playlist->id"]);

			$this->tables->media->delete(["owner=$playlist->id"]);

		}

	}


	public function addtoDB ($directoriesHander) {

		$dh	= $directoriesHander;

		$channels 	= self::filter($this->inDB->channels, $dh->channels);
		$playlists	= self::filter($this->inDB->playlists, $dh->playlists);
		$media		= self::filter($this->inDB->media, $dh->media);
		//$posters	= self::filter($this->inDB->posters, $dh->posters);
		//$banners	= self::filter($this->inDB->banners, $dh->banners);

		
		msg('NEW-CHANNELS', $channels);
		msg('NEW-PLAYLISTS', $playlists);
		msg('NEW-MEDIA', $media);
		//msg('NEW-POSTERS', $posters);
		//msg('NEW-BANNER', $banners);

		
		msg('DB-ADD', 'channels');
		$this->tables->channels->insert(...$channels);
		
		msg('DB-ADD', 'playlists');
		$this->tables->playlists->insert(...$playlists);
		
		msg('DB-ADD', 'media');
		$this->tables->media->insert(...$media);

		//msg('DB-ADD', 'posters');
		//$this->tables->posters->insert(...$posters);

		//msg('DB-ADD', 'banners');
		//$this->tables->banners->insert(...$banners);

	}

	private static function filter (array $inDB, array $from_dh) {

		$arr = [];

		foreach ($from_dh as $dirname => $items) {
			
			foreach ($items as $i) if (!in_array($i->id, $inDB)) $arr[] = get_object_vars($i);

		}

		return $arr;

	}

}
class ContentType
{

	public $name, $id;

	private $row;

	private $databaseHandler;

	public function __construct ($row) {

		$this->row = $row;

		if ($row) {

			$this->id = $row->id;
			$this->name = $row->name;

		}

	}

	public function sDatabaseHandler (DatabaseHandler $d) {

		$this->databaseHandler = $d;

	}

	public function scan ($tb) {

		$directories = $tb->rows(["contenttype=$this->id"]);

		if (!$directories) return msg('CONTENT-TYPE', 'No directories were found in database');

		$rowsCount = count($directories);

		foreach ($directories as $key => $row) {

			$f = new Finder($row, $this);

			$this->databaseHandler->addtoDB($f);

			msg('DIR-PROGRESS', ['percent'=>($key+1)*100/$rowsCount . '%']);

		}
	}

}
class Scanner
{

	private $id;

	private $contentTypes = [];

	public function __construct ($id) {

		$tb = \Tables\ContentTypes::instance();

		if ($id == 'all') {

			$this->contentTypes = $tb->rows();

		} else {

			$this->contentTypes = [$tb->row(["id=$id"])];
			
		}

	}

	public function run () {

		msg('STATUS', 'Running');

		if (!$this->contentTypes) return msg('CONTENT-TYPES', 'Content types not found');
	
		$tb = \Tables\Directories::instance();
		
		$d = new DatabaseHandler;
		
		foreach ($this->contentTypes as $row) {

			$e = new ContentType($row);

			$e->sDatabaseHandler($d);

			$e->scan($tb);

			msg('CONTENT-TYPE', $e);

		}

	}

}

if (isset($_GET['q'])) {
	
	msg('QUERY', $_GET['q']);

	$scan = new Scanner($_GET['q']);

	$scan->run();

} else msg('QUERY', 'Please specify Content type');



?>