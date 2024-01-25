<?php

namespace Tables;

trait SingleInstanceTable
{
    protected $name;

    protected $db;

    protected static $instance;

    public function __construct () {
       
        parent::__construct();

        $this->name = basename(__CLASS__);

        $this->db = \Database::instance();
        
        $this->db->run("CREATE TABLE IF NOT EXISTS $this->name ($this->columns)");

    }

    public static function instance () {

		if (!self::$instance) self::$instance = new self;

        return self::$instance;

    }
    
}

require_once (__DIR__ . "/../cls/Database.php");

require_once 'Table.cls';
require_once 'Table.PDOTable.cls';
require_once 'Table.PDOTable.Users.cls';
require_once 'Table.PDOTable.Stats.cls';


class Channels extends Table
{
    use SingleInstanceTable;

    protected $columns = '
        id VARCHAR(255) PRIMARY KEY, 
        path VARCHAR(255), 
        name VARCHAR(255), 
        content_type VARCHAR(20), 
        details INT, 
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX id_index (id)
    ';

}
class ChannelDetails extends Table
{
    use SingleInstanceTable;

    protected $columns = '
        id INT PRIMARY KEY, 
        backdrop_path VARCHAR(255), 
        episode_run_time INT, 
        first_air_date VARCHAR(10), 
        in_production BOOLEAN, 
        last_air_date VARCHAR(10), 
        name VARCHAR(255), 
        number_of_episodes INT, 
        number_of_seasons INT, 
        original_language VARCHAR(3), 
        original_name VARCHAR(255), 
        popularity FLOAT, 
        poster_path VARCHAR(255), 
        release_date VARCHAR(10), 
        runtime INT, 
        title VARCHAR(255), 
        vote_average FLOAT, 
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX id_index (id)
    ';

}
class Playlists extends Table
{
    use SingleInstanceTable;
    
    protected $columns = '
        id VARCHAR(255) PRIMARY KEY, 
        path VARCHAR(255), 
        name VARCHAR(255), 
        owner VARCHAR(255), 
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX id_index (id)
    ';

}


class Media extends Table
{
   
    use SingleInstanceTable;
    
    protected $columns = '
        id VARCHAR(255) PRIMARY KEY, 
        basename VARCHAR(255), 
        name VARCHAR(255), 
        owner VARCHAR(255), 
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX id_index (id)
    ';

}


abstract class ImagesTable extends Table
{
    protected $columns = '
        id VARCHAR(255) PRIMARY KEY, 
        path VARCHAR(255), 
        owner VARCHAR(255), 
        height INT,
        width INT,

        INDEX id_index (id)
    ';
}
class Posters extends ImagesTable
{
    use SingleInstanceTable;
}
class Banners extends ImagesTable
{
    use SingleInstanceTable;
}


class ContentTypes extends Table
{
    use SingleInstanceTable;
    
    protected $columns = '
        id VARCHAR(20) PRIMARY KEY, 
        name VARCHAR(255),
        ml INT DEFAULT 0
    ';
    
}

class ContentGenres extends Table
{
    use SingleInstanceTable;
    
    protected $columns = '
        no INT PRIMARY KEY AUTO_INCREMENT,    
        content VARCHAR(255)
    ';
    
}

class Links extends PDOTable
{
    
    use SingleInstanceTable;
    
    protected $columns = '
        no INT PRIMARY KEY AUTO_INCREMENT,    
        path VARCHAR(255),
        url VARCHAR(255),

        INDEX url_index (url)
    ';
    
}


//$overviews	= \Tables\Overviews::instance();			
class Overviews extends Table
{
    use SingleInstanceTable;
    
    protected $columns = '
        no INT PRIMARY KEY AUTO_INCREMENT,    
        owner VARCHAR(255),
        language VARCHAR(3),
        content TEXT,

        INDEX owner_index (owner),
        INDEX lang_index (language)
    ';
    
}

//$taglines	= \Tables\Taglines::instance();
class Taglines extends Table
{
    use SingleInstanceTable;
    
    protected $columns = '
        no INT PRIMARY KEY AUTO_INCREMENT,    
        owner VARCHAR(255),
        language VARCHAR(3),
        content TEXT,

        INDEX owner_index (owner),
        INDEX lang_index (language)
    ';
    
}

//oject tables (id) primary
//$companies 		= \Tables\Companies::instance();
class Companies extends Table
{
    use SingleInstanceTable;
    
    protected $columns = '
        id VARCHAR(255) PRIMARY KEY, 
        name VARCHAR(255),
        logo_path VARCHAR(255),
        origin_country VARCHAR(25),

        INDEX id_index (id)
    ';
    
    
}

//$countries 		= \Tables\Countries::instance();
class Countries extends Table
{
    use SingleInstanceTable;
    
    protected $columns = '
        iso_3166_1 VARCHAR(25) PRIMARY KEY, 
        name VARCHAR(255),

        INDEX iso_index (iso_3166_1)
    ';
    
    
}

//$genres 		= \Tables\Genres::instance();
class Genres extends Table
{
    use SingleInstanceTable;
    
    protected $columns = '
        id INT PRIMARY KEY, 
        name VARCHAR(255),

        INDEX id_index (id)
    ';  
}

//$languages 		= \Tables\Languages::instance();
class Languages extends Table
{
    use SingleInstanceTable;
    
    protected $columns = '
        iso_639_1 VARCHAR(25) PRIMARY KEY,
        english_name VARCHAR(25),
        name VARCHAR(255),

        INDEX iso_index (iso_639_1)
    ';
}
class People extends Table
{
    use SingleInstanceTable;
    
    protected $columns = '
        id INT PRIMARY KEY,
        name VARCHAR(255),
        profile_path VARCHAR(255),

        INDEX id_index (id)
    ';
}
class Cast extends Table
{
    use SingleInstanceTable;
    
    protected $columns = '
        no INT PRIMARY KEY AUTO_INCREMENT,    
        owner INT,
        person INT,
        character VARCHAR(255),
        order INT,

        INDEX owner_index (owner)
    ';
    
}
class Crew extends Table
{
    use SingleInstanceTable;
    
    protected $columns = '
        no INT PRIMARY KEY AUTO_INCREMENT,    
        owner INT,
        person INT,
        job VARCHAR(255),
        order INT,

        INDEX owner_index (owner)
    ';
    
}

//array tables (no auto incremnent)
//$production_companies 	= \Tables\ProductionCompanies::instance();
class ProductionCompanies extends Table
{
    use SingleInstanceTable;
    
    protected $columns = '
        no INT PRIMARY KEY AUTO_INCREMENT,    
        owner VARCHAR(255),
        company VARCHAR(255),

        INDEX owner_index (owner),
        INDEX network_index (company)
    ';
    
}
//$production_countries 	= \Tables\ProductionCountries::instance();
class ProductionCountries extends Table
{
    use SingleInstanceTable;
    
    protected $columns = '
        no INT PRIMARY KEY AUTO_INCREMENT,    
        owner VARCHAR(255),
        country VARCHAR(25),

        INDEX owner_index (owner),
        INDEX country_index (country)
    ';
    
}
//$spoken_languages 		= \Tables\SpokenLanguages::instance();
class SpokenLanguages extends Table
{
    use SingleInstanceTable;
    
    protected $columns = '
        no INT PRIMARY KEY AUTO_INCREMENT,    
        owner VARCHAR(255),
        language VARCHAR(255),

        INDEX owner_index (owner)
    ';
    
}
//$media_genres 			= \Tables\MediaGenres::instance();
class MediaGenres extends Table
{
    use SingleInstanceTable;
    
    protected $columns = '
        no INT PRIMARY KEY AUTO_INCREMENT,    
        owner VARCHAR(255),
        genre VARCHAR(25),

        INDEX owner_index (owner)
    ';
    
}

#	Movies
//$collections	= \Tables\Collections::instance();
class Collections extends Table
{
    use SingleInstanceTable;
    
    protected $columns = '
        id VARCHAR(255) PRIMARY KEY, 
        name VARCHAR(255),
        backdrop_path VARCHAR(255),
        poster_path VARCHAR(255),

        INDEX id_index (id)
    ';
    
}
class ChannelCollections extends Table
{
    use SingleInstanceTable;
    
    protected $columns = '
        no INT PRIMARY KEY AUTO_INCREMENT,    
        owner VARCHAR(255),
        collection VARCHAR(255),

        INDEX owner_index (owner),
        INDEX collection_index (collection)
    ';
    
}

#	Tv
//$networks 		= \Tables\Networks::instance();
class Networks extends Table
{
    use SingleInstanceTable;
    
    protected $columns = '
        id VARCHAR(255) PRIMARY KEY, 
        name VARCHAR(255),
        logo_path VARCHAR(255),
        origin_country VARCHAR(25),

        INDEX id_index (id)
    ';
    
    
}

//array tables (no auto incremnent)
//$tvnetworks 	= \Tables\TVNetworks::instance();
class TVNetworks extends Table
{
    use SingleInstanceTable;
    
    protected $columns = '
        no INT PRIMARY KEY AUTO_INCREMENT,    
        owner VARCHAR(255),
        network VARCHAR(255),

        INDEX owner_index (owner),
        INDEX network_index (network)
    ';
    
}




?>