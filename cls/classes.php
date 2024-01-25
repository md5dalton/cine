<?php

require_once 'User.cls';

require_once 'MediaElement.cls';

require_once 'Channel.cls';
require_once 'Playlist.cls';
require_once 'Media.cls';

//require_once 'Series.cls';
require_once 'HomeFeed.cls';

require_once 'TMDB.cls';


class UserConfig
{

    public $language = 'en';
    
    protected static $instance;

    public static function instance () {

		if (!self::$instance) self::$instance = new self;

        return self::$instance;

    }
    	
}
?>