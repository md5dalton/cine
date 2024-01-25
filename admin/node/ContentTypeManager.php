<?php
namespace ContentTypeManager;

class ContentType
{

	public $id, $name;

	protected $row, $tb;
	
	public function __construct ($e) {

		$this->tb = \Tables\ContentTypes::instance();

		$this->row = is_string($e) ? $this->tb->row(["id=$e"]) : $e;

		$this->sRow();

	}

	protected function sRow () {
		
		if ($this->row) {

			$this->id = $this->row->id;
			$this->name = str_clean($this->row->name);

		}

	}

	public function gDirectories () {
		
		$tb = \Tables\Directories::instance();

		$this->directories = [];

		foreach ($tb->rows(["contenttype=$this->id"]) as $row) {

			$d = new Directory($row);

			$this->directories[] = $d;

		}

	}
}
class Directory
{

	public $id, $path, $type;

	protected $row, $tb;
	
	public function __construct ($e) {

		$this->tb = \Tables\Directories::instance();

		$this->row = is_string($e) ? $this->tb->row(["id=$e"]) : $e;

		$this->sRow();

	}

	protected function sRow () {
		
		if ($this->row) {

			$this->id = $this->row->id;
			$this->path = $this->row->path;
			$this->type = $this->row->contenttype;

		}

	}

	public function create (string $path, $type, $content_level) {

		$id = sha1($path);

		$e = [
			'id'=>$id,
			'path'=>$path,
			'cl'=>$content_level,
			'contenttype'=>$type
		];

		$this->tb->insert($e);

		return new Directory($id);

	}
}
class ContentTypeManager
{

	private $errormsg, $result = [];

	public function start ($input) {

		$i = $input;

		$this->i = $i;

		if (isset($i->q)) {

			switch ($i->q) {
				case 'media':
					$this->gContent();
					break;
					case 'new-content':
						$this->sNewContent();
						break;
					case 'new-directory':
						$this->sNewDirectory();
						break;
			}

		}

	}

	private function gContent () {

		$tb = \Tables\ContentTypes::instance();

		$media = [];

		foreach ($tb->rows() as $row) {

			$e = new ContentType($row);

			$e->gDirectories();

			$media[] = $e;

		}

		$this->result['media'] = $media;

	}

	private function sNewContent () {

		if (!isset($this->i->name)) return $this->errormsg = 'Name not set';

		$name = $this->i->name;
		$ml = isset($this->i->ml) ? $this->i->ml : 0;
		$id = isset($this->i->id) ? $this->i->id : str_replace(' ', '_', strtolower($name));

		$tb = \Tables\ContentTypes::instance();

		$names = $tb->column('name');

		if (in_array($name, $names)) return $this->errormsg = 'Name already in use';

		$tb->insert(['name'=>$name, 'id'=>$id, 'ml'=>$ml]);

		$row = $tb->row(["name=$name"]);

		if (!$row)  return $this->errormsg = 'Name not added';
		
		$e = new ContentType($row);

		$e->gDirectories();

		$this->result['new_content'] = ['media'=>[$e]];

	}

	private function sNewDirectory () {

		if (!isset($this->i->path)) return $this->errormsg = 'Path not set';

		$path = str_replace('\\', '/', $this->i->path);
		$type = isset($this->i->type) ? $this->i->type : 0;
		$cl = isset($this->i->cl) ? $this->i->cl : 0;

		$id = sha1($path);

		$e = new Directory($id);
		
		if ($e->id) return $this->errormsg = 'Path already in use';

		$new = $e->create($path, $type, $cl);

		if (!isset($new->id))  return $this->errormsg = 'Directory not added';

		$this->result['new_directory'] = ['media'=>[$new]];

	}

	public function getresult () {

		return $this->errormsg ? ['errormsg' => $this->errormsg] : ['content' => $this->result];

	}


}

$app = new ContentTypeManager;

$app->start($input);




?>