<?php
namespace Tables;

abstract class PDOTable extends \Tables\Table
{

    /** @var, Table name */

    protected $name;


    /** @var, Database instance */

    protected $db;


    /** @var, Bool create */

    protected $create;
    
	public function __construct () {

        $this->db = \Database::instance();
        
        if ($this->name) $this->db->run("CREATE TABLE IF NOT EXISTS $this->name ($this->columns)");

    }
    

}
?>