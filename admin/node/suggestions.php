<?php

class Suggestions
{

	private $errormsg, $result = [];

	private $pool = [];


	private function sPool () {

		$tb = \Tables\Channels::instance();
		
		$rows = $tb->rows();

		foreach ($rows as $row) {

			$this->pool[] = $row;

		}

		$_SESSION['pool'] = $this->pool;

	}

	public function start ($i) {

		$this->sPool();
		
		$term = $i->t;

		$matches = [];

		if ($term) {
			
			foreach ($this->pool as $d) {

				$strpos = stripos($d->name, $term);

				if ($strpos !== false) $matches[] = ['pos' => $strpos, 'str' => $d->name, 'd' => $d];

			}

			array_multisort(
				array_column($matches, 'pos'), SORT_DESC, 
				array_column($matches, 'str'), SORT_REGULAR, 
			$matches);

			$this->gSuggestions(array_column(array_slice($matches, 0, 9, true), 'd'));
		
		} else {

			shuffle($this->pool);
			
			$this->gSuggestions(array_slice($this->pool, 0, 9, true));

		}


	}

	public function gSuggestions (array $suggested) {

		$suggestions = [];

		foreach ($suggested as $key => $row) {

			$e = new \Channel($row);

			$suggestions[] = $e;

		}

		$this->result['results'] = $suggestions;

	}

	public function getresult () {

		return $this->errormsg ? ['errormsg' => $this->errormsg] : ['suggestions' => $this->result];

	}

}

$app = new Suggestions;

$app->start($input);


?>