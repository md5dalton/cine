<?php

class CollectionsHandler
{


	private $errormsg, $collection;

	public function gCollections () {

		$type = $this->input->type;

		$tb = null;

		if ($type) {
			
			switch ($type) {
				case 'movies':
					$tb = new \Tables\DirectoriesMovies;
					break;
				
				case 'series':
					$tb = new \Tables\DirectoriesSeries;
					break;
			}

		} else $this->errormsg = 'parameter missing $type';

		if ($tb) {

			$dirs = [];

			foreach($tb->gRows() as $row) {

				$dirs[] = ['path' => $row->path];

			}

			$this->collection->collections = $dirs;
		
		}
	}

	public function sCollection () {

		$path = $this->input->path;
		$type = $this->input->type;
		
		$path = str_replace('\\', '/', $path);

		if (is_dir($path)) {

			$tb = null;

			if ($type) {
				
				switch ($type) {
					case 'movies':
						$tb = new \Tables\DirectoriesMovies;
						break;
					
					case 'series':
						$tb = new \Tables\DirectoriesSeries;
						break;
				}

			} else $this->errormsg = 'parameter missing $type';

			if ($tb) {

				$path = realpath($path);

				$path = str_replace('\\', '/', $path);


				$row = $tb->row(["path=$path"]);

				if ($row) {

					$this->errormsg = 'already exists';

				} else {

					$tb->sRow(['path'=>$path]);

					$row = $tb->row(["path=$path"]);

					if (!$row) $this->errormsg = 'could not create'; else $this->collection->collection = $row;

				}

			}

		} else $this->errormsg = 'not directory';

	}

	public function start ($input) {

		$this->input = $input;

		$this->collection = (object) [];

		switch ($input->q) {
			case 'collections':
				$this->gCollections();
				break;
			
			case 'new-collection':
				$this->sCollection($input->path);
				break;
			
			default:
				# code...
				break;
		}
		
	}

	public function getresult () {

		return $this->errormsg ? ['errormsg' => $this->errormsg] : ['collection' => $this->collection];

	}


}

$app = new CollectionsHandler;

$app->start($input);




?>