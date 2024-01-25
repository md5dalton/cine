<?php

class Suggestions
{

	private $errormsg, $result;

	private $pool = [];

	public function __construct () {

		$this->result = (object) [];

		$this->tb = gTables('Channels', 'ChannelDetails');

	}

	private function sPool () {

		$channels = $this->tb->channels->rows();
		$details = $this->tb->channeldetails->rows();

		$channels = array_combine(array_column($channels, 'details'), $channels);
		$details = array_combine(array_column($details, 'id'), $details);

		$intersect = array_intersect_key($channels, $details);

		foreach ($intersect as $details_id => $channel_row) {

			$detail = $details[$details_id];

			$this->pool[] = (object) ['name'=>$detail->name ?: $detail->title, 'row'=>$channel_row];

		}

		//$_SESSION['pool'] = $this->pool;

	}

	public function start ($input) {
		
		$i = $input;

		if (!isset($i->t)) return $this->errormsg = 'no term specified';

		$this->sPool();

		$term = $i->t;

		$matches = [];

		if ($term) {
				
			foreach ($this->pool as $p) {

				$strpos = stripos($p->name, $term);

				if ($strpos !== false) $matches[] = ['pos' => $strpos, 'str' => $p->name, 'row' => $p->row];

			}
			
			array_multisort(
				array_column($matches, 'pos'), SORT_ASC, 
				array_column($matches, 'str'), SORT_REGULAR, 
			$matches);

		} else {

			shuffle($this->pool);

			$matches = $this->pool;

		}

		$this->gSuggestions(array_column(array_slice($matches, 0, 9), 'row'));

	}

	private function gSuggestions (array $suggested) {

		$suggestions = [];

		foreach ($suggested as $row) {

			$e = new \Channel($row);

			$e->sDetails();

			$suggestions[] = $e;

		}

		$this->result->results = $suggestions;

	}

	public function getresult () {

		return $this->errormsg ? ['errormsg' => $this->errormsg] : ['suggestions' => $this->result];

	}

}

$app = new Suggestions;

$app->start($input);


?>