<?php
namespace Tables;

abstract class Stats extends Table
{

    protected $columns = '
        no INT PRIMARY KEY AUTO_INCREMENT, 
        media VARCHAR(255), 
        user VARCHAR(255), 
        count INT DEFAULT 1, 
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ';

    protected $user;

    public function __construct () {

        $this->user = \User::instance();
        
    }
    
    public function gMedia (string $sort = '', int $limit = 0) {

		return $this->column('media', ["user=" . $this->user->id], $sort, $limit);
	
    }
    public function gUsers (string $mediaId, string $sort = '', int $limit = 0) {

		return $this->column('user', ["media=$mediaId"], $sort, $limit);
	
    }
}

class Likes extends Stats
{
    use SingleInstanceTable;
}
class Views extends Stats
{
    use SingleInstanceTable;
}
class Watchlists extends Stats
{
    use SingleInstanceTable;
}
?>