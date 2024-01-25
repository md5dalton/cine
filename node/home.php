<?php
namespace HomeFeed;

class UserActivity extends Section
{
	public $type = 'wide';

	public $title = 'Your Activity';

	public $media = [];

	public function __construct () {

		$views = \Tables\Views::instance();
		$likes = \Tables\Likes::instance();
		$watchlists = \Tables\Watchlists::instance();

		$lastviewed = $views->gMedia('timestamp ASC', 3);
		$lastliked = $likes->gMedia('timestamp ASC', 3);
		$watchlist = $watchlists->gMedia('timestamp ASC', 2);

		foreach (array_merge($lastviewed, $lastliked, $watchlist) as $id) {

			$e = new Media($id);

			$this->media[] = $e;

		}

		$this->media = [
			['name'=>'Building a nice Itx Build', 'poster'=>imagepath('a:/_thumbs/515da45cde15be38b8bf1df30b441cded35890ef.jpg'), 'duration'=>'7:08', 'viewd_percent'=>'35', 'owner'=>['name'=>'Linus tech Tips']],
			['name'=>'Building a nice Itx Build', 'poster'=>imagepath('a:/_thumbs/515da45cde15be38b8bf1df30b441cded35890ef.jpg'), 'duration'=>'7:08', 'viewd_percent'=>'35', 'owner'=>['name'=>'Linus tech Tips']],
			['name'=>'Building a nice Itx Build', 'poster'=>imagepath('a:/_thumbs/515da45cde15be38b8bf1df30b441cded35890ef.jpg'), 'duration'=>'7:08', 'viewd_percent'=>'35', 'owner'=>['name'=>'Linus tech Tips']],
			['name'=>'Building a nice Itx Build', 'poster'=>imagepath('a:/_thumbs/515da45cde15be38b8bf1df30b441cded35890ef.jpg'), 'duration'=>'7:08', 'viewd_percent'=>'35', 'owner'=>['name'=>'Linus tech Tips']],
			['name'=>'Building a nice Itx Build', 'poster'=>imagepath('a:/_thumbs/515da45cde15be38b8bf1df30b441cded35890ef.jpg'), 'duration'=>'7:08', 'viewd_percent'=>'35', 'owner'=>['name'=>'Linus tech Tips']],
			['name'=>'Building a nice Itx Build', 'poster'=>imagepath('a:/_thumbs/515da45cde15be38b8bf1df30b441cded35890ef.jpg'), 'duration'=>'7:08', 'viewd_percent'=>'35', 'owner'=>['name'=>'Linus tech Tips']],
			['name'=>'Building a nice Itx Build', 'poster'=>imagepath('a:/_thumbs/515da45cde15be38b8bf1df30b441cded35890ef.jpg'), 'duration'=>'7:08', 'viewd_percent'=>'35', 'owner'=>['name'=>'Linus tech Tips']],
			['name'=>'Building a nice Itx Build', 'poster'=>imagepath('a:/_thumbs/515da45cde15be38b8bf1df30b441cded35890ef.jpg'), 'duration'=>'7:08', 'viewd_percent'=>'35', 'owner'=>['name'=>'Linus tech Tips']]
		];

	}

}
class Trending extends Section
{
	public $type = 'wide';

	public $title = "What's Latest";

	public $banner = 'bg/trailers-banner.jpg';

	public $media = [];

	public function __construct () {

		$this->media = [
			['name'=>'S05E3', 'poster'=>imagepath('a:/_thumbs/e6195de1098503a4ba20162f8221189ce5b1d44d.jpg'), 'duration'=>'50:23', 'viewd_percent'=>'100', 'owner'=>['name'=>'Queen Sugar']],
			['name'=>'S05E3', 'poster'=>imagepath('a:/_thumbs/e6195de1098503a4ba20162f8221189ce5b1d44d.jpg'), 'duration'=>'50:23', 'viewd_percent'=>'100', 'owner'=>['name'=>'Queen Sugar']],
			['name'=>'S05E3', 'poster'=>imagepath('a:/_thumbs/e6195de1098503a4ba20162f8221189ce5b1d44d.jpg'), 'duration'=>'50:23', 'viewd_percent'=>'100', 'owner'=>['name'=>'Queen Sugar']],
			['name'=>'S05E3', 'poster'=>imagepath('a:/_thumbs/e6195de1098503a4ba20162f8221189ce5b1d44d.jpg'), 'duration'=>'50:23', 'viewd_percent'=>'100', 'owner'=>['name'=>'Queen Sugar']],
			['name'=>'S05E3', 'poster'=>imagepath('a:/_thumbs/e6195de1098503a4ba20162f8221189ce5b1d44d.jpg'), 'duration'=>'50:23', 'viewd_percent'=>'100', 'owner'=>['name'=>'Queen Sugar']],
			['name'=>'S05E3', 'poster'=>imagepath('a:/_thumbs/e6195de1098503a4ba20162f8221189ce5b1d44d.jpg'), 'duration'=>'50:23', 'viewd_percent'=>'100', 'owner'=>['name'=>'Queen Sugar']],
			['name'=>'S05E3', 'poster'=>imagepath('a:/_thumbs/e6195de1098503a4ba20162f8221189ce5b1d44d.jpg'), 'duration'=>'50:23', 'viewd_percent'=>'100', 'owner'=>['name'=>'Queen Sugar']],
			['name'=>'S05E3', 'poster'=>imagepath('a:/_thumbs/e6195de1098503a4ba20162f8221189ce5b1d44d.jpg'), 'duration'=>'50:23', 'viewd_percent'=>'100', 'owner'=>['name'=>'Queen Sugar']]
		];

	}

}
class Popular extends Section
{
	public $type = 'tall';

	public $title = "What's popular";

	public $media = [];

	public function __construct () {

		$details = \Tables\ChannelDetails::instance();
		$channels = \Tables\Channels::instance();

		$ids = $details->column('id', [], 'popularity DESC', 15);

		foreach ($ids as $id) {

			if ($row = $channels->row(["details=$id"])) {
					
				$e = new \Channel($row);

				$e->sDetails();
				$e->sPoster();
				$e->sYear();

				$this->media[] = $e;

			}

		}

	}

}
class Featured extends Section
{
	public $type = 'tall';

	public $title = "Featured Today";

	public $media = [];

	public function __construct () {
		
		
		$details = \Tables\ChannelDetails::instance();
		$channels = \Tables\Channels::instance();

		$ids = $details->column('id', [], 'timestamp DESC', 15);

		foreach ($ids as $id) {

			if ($row = $channels->row(["details=$id"])) {
					
				$e = new \Channel($row);

				$e->sDetails();
				$e->sPoster();
				$e->sYear();

				$this->media[] = $e;

			}

		}

	}

}
class Sections
{
	public $sections = [];

	public function __construct () {


		//$this->sections[] = new UserActivity;
		//$this->sections[] = new Trending;
		$this->sections[] = new Featured;
		
		$this->sRegister();
		
		$this->sections[] = new Popular;

	}

	private function sRegister () {

		$e = new Register;

		$this->sections[] = $e;

	}
}

class HomeFeed
{

	private $errormsg, $result = [];

	public function start ($input) {
		
		$this->i = $input;

		if (isset($input->q)) switch ($input->q) {
			case 'sections':
				$this->gSections();
				break;
		}

	}

	private function gSections () {

		$e = new Sections;

		$this->result['sections'] = $e->sections;

	}

	public function getresult () {

		return $this->errormsg ? ['errormsg' => $this->errormsg] : ['home' => $this->result];

	}



}

$app = new HomeFeed;

$app->start($input);




?>