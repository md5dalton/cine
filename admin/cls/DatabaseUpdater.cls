<?php


class ArrayTables
{
	public function __construct ($owner, bool $replace) {

		$this->owner = $owner;

		$this->replace = $replace;

		$this->sTable('Companies', 'Production', 'id', 'company');
		$this->sTable('Countries', 'Production', 'iso_3166_1', 'country');
		$this->sTable('Genres', 'Media', 'id', 'genre');
		$this->sTable('Languages', 'Spoken', 'iso_639_1', 'language');
		$this->sTable('Networks', 'TV', 'id', 'network');
		$this->sTable('Collections', 'Channel', 'id', 'collection');

	}
	
	private function sTable ($name, $secondary_table_prefix, $object_table_primary_key, $array_table_secondary_key) {
	
		$name_lower = strtolower($name);

		$object_table_name = "\\Tables\\$name";
		$array_table_name = "\\Tables\\$secondary_table_prefix$name";

		$this->tables[$name_lower] = (object) [
			'object_table'=> $object_table_name::instance(),
			'array_table'=> $array_table_name::instance(),
			'id_key'=> $object_table_primary_key,
			'secondary_column_name'=> $array_table_secondary_key
		];

	}

	public function add () {
	
		foreach ($this->tables as $name => $tables) {

			$values = @$this->{$name};

			if ($values) {
					
				$this->addtoDBObject($tables->object_table, $values, $tables->id_key);

				//addtoDBArray ($table, $arr, $media_column, $media_value, $id_key, $secondary_column) {
				$this->addtoDBArray(
					$tables->array_table, 
					$values, 
					'owner', 
					$this->owner, 
					$tables->id_key,
					$tables->secondary_column_name
				);
			
			}
			
		}

	}
		
 	private function addtoDBObject ($table, $arr, $id_key) {

		foreach ($arr as $e) {

			$param = ["$id_key=" . $e->{$id_key}];

			if (!$table->row($param)) $table->insert($e);

		}
	}

	private function addtoDBArray ($table, $arr, $media_column, $media_value, $id_key, $secondary_column) {

		foreach ($arr as $e) {

			$param = ["$media_column=" . $media_value, "$secondary_column=" . $e->{$id_key}];
			
			$row = $table->row($param);
			
			if ($this->replace) {
			
				if ($row) $table->delete($param);
				
				$row = false;

			}
			
			if (!$row) $table->insert([
				$media_column=>$media_value,
				$secondary_column=>$e->{$id_key}
			]);

		}

	}

}

class TextTables
{
	public function __construct ($owner, bool $replace) {

		$this->owner = $owner;

		$this->replace = $replace;

		$this->sTable('Overviews');
		$this->sTable('Taglines');

	}
	
	private function sTable ($name) {
	
		$name_lower = strtolower($name);

		$table_name = "\\Tables\\$name";

		$this->tables[$name_lower] = (object) [
			'table'=> $table_name::instance()
		];

	}

	public function add () {
	
		foreach ($this->tables as $name => $tables) {

			$value = $this->{$name};

			foreach ($value as $language => $text) {
				
				$e = (object) [
					'language'=>$language, 
					'content'=> $text, 
					'owner'=>$this->owner
				];

				if ($text) $this->addtoDB(
					$tables->table, 
					$e, 
					'owner', 
					'language'
				);

			}
			
		}

	}

	private function addtoDB ($table, $e, $media_column, $secondary_column) {

		$param = ["$media_column=" . $e->{$media_column}, "$secondary_column=" . $e->{$secondary_column}];
		
		$row = $table->row($param);
		
		if ($this->replace) {

			if ($row) $table->delete($param);
		
			$row = false;

		}

		if (!$row) $table->insert((array) $e);

	}
	
}

class GeneralDetails
{

	public function __construct ($data, bool $replace) {

		$this->data = $data;
		$this->replace = $replace;

		$this->tb = \Tables\ChannelDetails::instance();

	} 

	public function sDetails () {
		
		$keys = [
			'backdrop_path',
			'first_air_date',
			'id',
			'in_production',
			'last_air_date',
			'name',
			'number_of_episodes',
			'number_of_seasons',
			'original_language',
			'original_name',
			'popularity',
			'poster_path',
			'release_date',
			'runtime',
			'title',
			'vote_average'
		];

		$selected = (object) array_intersect_key(
			(array) $this->data, 
			array_flip($keys)
		);

		if (isset($this->data->episode_run_time)) $selected->episode_run_time = reset($this->data->episode_run_time);

		$row = $this->tb->row(["id=" . $this->data->id]);
		
		if ($this->replace) {
			
			if ($row) $this->tb->delete(["id=" . $this->data->id]);

			$row = false;

		}
		
		if (!$row) $this->tb->insert($selected);

	}

	private function gCert () {

		if (!isset($this->data->release_dates)) return;

		$production_countries = array_column($this->data->production_countries, 'iso_3166_1');

		$release_dates = $this->data->release_dates->results;
		
		$release_dates = array_combine(
			array_column($release_dates, 'iso_3166_1'), 
			array_column($release_dates, 'release_dates')
		);

		$selected = array_intersect_key(
			$release_dates, 
			array_flip($production_countries)
		);

		foreach ($selected as $country => $certs) {



		}
		$certifications = array_column($selected, 'certification');

		//array_unique($certifications);
		

	}
}
class DatabaseUpdater
{

	public function savePoster ($path_to_image, $e) {

		$channels = new \Tables\Channels;
		$posters = new \Tables\Posters;

		$channel = $channels->row(["id=$e->media_id"]);

		if ($channel) {

			list($width, $height) = getimagesize($path_to_image);

			if ($height && $width) {
					
				$p = new \Scan\Poster($path_to_image, $width, $height);

				$p->owner = $channel->id;
				
				if (!$posters->row(["owner=$channel->id", "id=$p->id"])) {
					
					$posters->insert(get_object_vars($p));

					return $posters->row(["owner=$channel->id", "id=$p->id"]);

				}

			}

		}

	}	

	public function sData ($d) {

		if (is_array($d)) {

			/*
			*	genres
			*	original_language
			*	overview en/es
			*	popularity
			*	production_companies
			*	production_countries
			*	spoken_languages
			*	tagline
			*	vote_average
			
			#	Movie
			*	belongs_to_collection
			*	release_date
			*	runtime
			*	title
			
			#	Tv
			*	episode_run_time
			*	first_air_date
			*	in_production
			*	last_air_date
			*	last_espisode_to_air .name
			*	name
			*	next_espisode_to_air .name
			*	networks
			*	number_of_episodes
			*	number_of_seasons
			*	origin_country
			*	original_name

			*/
			
			$details	= \Tables\ChannelDetails::instance();

			$aTB = new ArrayTables;
			$textTB = new TextTables;

			if ($d) foreach ($d as $data) {

				$id 		= $data->media_id;
				$channel_id = $data->media_id;
				$type		= $data->type;
				$tmDetails	= $data->details;

				$details_row = $details->row(["channel=$id"]);
				
				if ($details_row) $details->delete(["channel=$id"]);

				$keys_to_select = [
					'backdrop_path',
					'first_air_date',
					'id',
					'in_production',
					'last_air_date',
					'name',
					'number_of_episodes',
					'number_of_seasons',
					'original_language',
					'original_name',
					'popularity',
					'poster_path',
					'release_date',
					'runtime',
					'title',
					'vote_average'
				];

				$selected_tmDtetails = array_intersect_key(
					(array) $tmDetails, 
					array_flip($keys_to_select)
				);

				$selected_tmDtetails['channel'] = $id;


				$textTB->overviews = [
					'en'=>$tmDetails->overview, 
					'es'=>$data->es_overview
				];

				$textTB->taglines = [
					'en'=>$tmDetails->tagline
				];
				$textTB->media_id = $channel_id;


				$aTB->companies = $tmDetails->production_companies;
				$aTB->countries = $tmDetails->production_countries;
				$aTB->languages = $tmDetails->spoken_languages;
				$aTB->genres = $tmDetails->genres;
				$aTB->channel_id = $channel_id;
				
				
				if ($type == 'tv') {
					
					$aTB->networks = $tmDetails->networks;
					
					$selected_tmDtetails['episode_run_time'] = reset($tmDetails->episode_run_time);

				}
				
				if ($type == 'movie') {
					
					if ($tmDetails->belongs_to_collection) $aTB->collections = [$tmDetails->belongs_to_collection];

				}

				$details->insert($selected_tmDtetails);
				$aTB->add();
				$textTB->add();
				
			}
			
		}

	}

	public function sDefaultDetails ($channelId, $data, bool $replace = true) {

		$en = $data->en;
		$es = $data->es;

		$general = new GeneralDetails($channelId, $data->en, $replace);

		$general->sDetails();

		
		$array = new ArrayTables($en->id, $replace);
		$text = new TextTables($en->id, $replace);


		$overviews = (object) [];
		$taglines = (object) [];

		if (isset($en->overview)) $overviews->en = $en->overview;
		if (isset($es->overview)) $overviews->es = $es->overview;

		if (isset($en->tagline)) $taglines->en = $en->tagline;
		if (isset($es->tagline)) $taglines->es = $es->tagline;

		$text->overviews = $overviews;
		$text->taglines = $taglines;

		$array->companies = $en->production_companies;
		$array->countries = $en->production_countries;
		$array->languages = $en->spoken_languages;
		$array->genres = $en->genres;
			
		if (isset($en->networks)) $array->networks = $en->networks;
		if (isset($en->belongs_to_collection)) $array->collections = [$en->belongs_to_collection];
			
		$array->add();
		$text->add();

	}

	public function updateChannel ($channelId, $detailsId) {

		$tb = gTables('Channels');

		$tb->channels->update(["id=$channelId"], ['details'=>$detailsId]);

	}
	public function sDetails ($data, bool $replace = true) {

		$en = $data->en;
		$es = $data->es;

		$general = new GeneralDetails($data->en, $replace);

		$general->sDetails();
		
		$array = new ArrayTables($en->id, $replace);
		$text = new TextTables($en->id, $replace);


		$overviews = (object) [];
		$taglines = (object) [];

		if (isset($en->overview)) $overviews->en = $en->overview;
		if (isset($es->overview)) $overviews->es = $es->overview;

		if (isset($en->tagline)) $taglines->en = $en->tagline;
		if (isset($es->tagline)) $taglines->es = $es->tagline;

		$text->overviews = $overviews;
		$text->taglines = $taglines;

		$array->companies = $en->production_companies;
		$array->countries = $en->production_countries;
		$array->languages = $en->spoken_languages;
		$array->genres = $en->genres;
			
		if (isset($en->networks)) $array->networks = $en->networks;
		if (isset($en->belongs_to_collection)) $array->collections = [$en->belongs_to_collection];
			
		$array->add();
		$text->add();

	}

}
?>