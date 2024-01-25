<?php

class ChannelViewer
{

	private $errormsg, $result = [];

	public function __construct () {

		$this->result = (object) [];

	}
	public function start ($i) {

		$e = new \Channel($i->id);

		if (!$e->id) return $this->errormsg = 'channel not found';

		switch ($i->q) {
			case 'info':
				$e->sDetails();

				$details = $e->gDetails();

				$date = $details->last_air_date ?: $details->release_date;

				$year = @reset(explode('-', $date));
				$language = $details->original_language == 'en' ? 'Inglés' : 'Español';


				$text = "#" . $e->gType() ."\n\n";

				$text .= "*" . ($details->name ?: $details->title) . "($year)*\n\n";

				$text .= "*Género:* " . implode(', ', $details->gGenres()) . "\n";
				$text .= "*Idioma:* $language\n\n";

				$text .= "*Sinopsis:*\n";
				$text .= $details->gOverview('en') . "\n\n";
				$text .= $details->gOverview('es');

				$this->result->info = ['text'=>$text];

				break;
		}


	}

	public function getresult () {

		return $this->errormsg ? ['errormsg' => $this->errormsg] : $this->result;

	}


}

$app = new ChannelViewer;

$app->start($input);




?>