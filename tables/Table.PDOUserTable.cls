<?php


abstract class PDOUserTable extends Table
{

    /** @var, Table name */

    protected $name;


    /** @var, Database instance */

    protected $db;


    /** @var, Bool create */

    protected $create;
    
    protected $user;

	public function __construct () {

        $this->db = \Database::instance();
        
        $this->user = \User::instance();
        
        if ($this->name) $this->db->run("CREATE TABLE IF NOT EXISTS $this->name ($this->columns)");

    }
    

    public function gRows (array $opts = [], string $columns = '*') {

		return $this->rows(array_merge($opts, ["al<" . $this->user->gAl()]), $columns);

    }

    public function gRow (string $id) {

		return $this->row(["id=$id", "al<" . $this->user->gAl()]);
	
    }

    public function sRows (...$rows) {

		return $this->insertMultiple(...$rows);
	
    }
    public function sRow ($row) {

		return $this->insertSingle($row);
	
    }

    public function gColumn (string $name, array $opts = []) {

		return $this->column($name, array_merge(["al<" . $this->user->gAl()], $opts));
	
    }
}
?>