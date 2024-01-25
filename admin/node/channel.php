<?php

class ChannelHandler
{


	private $errormsg, $result = [];

	public function start ($input) {

		$tb	= new \Tables\Series;
		
		$rows = $tb->gRows();

		$media = [];

		foreach ($rows as $row) {

			$media[$row->name] = $row;

		}

		array_multisort(array_column($media, 'name'), SORT_ASC, $media);
		//array_multisort(array_column($this->collections, 'name'), SORT_ASC, $this->collections);

		$this->result['media'] = array_values($media);
		
	}

	public function getresult () {

		return $this->errormsg ? ['errormsg' => $this->errormsg] : ['channel' => $this->result];

	}


}

$app = new ChannelHandler;

$app->start($input);




?>