<?php
namespace Tables;

/**
 *  Table - A table class 
 *
 * @author		Author: md5dalton
 * @version     2021.07.14
 *
 */

## Changelog 
## [0.0.0] - 2021-07-25

### Added
#- Implemented use of In clause for select function
#- This Changelog


abstract class Table
{
    public function __construct () {}

    protected function prepare (string $sql) {

        $stmt = $this->db->dbc->prepare($sql);
        
        return $stmt; 

    }

    public function query (string $sql) {

        return $this->db->query($sql);

    }
    
    public function row ($args = '', $columns = '*') {
    
        return $this->select($args, $columns, '', '', '')->fetch();

    }

	public function rows ($args = '', $columns = '*', $sort = '', $limit = '', $in = '', $offset = '') {
    
        return $this->select($args, $columns, $sort, $limit, $in, $offset)->fetchAll();

    }

    
	public function like (string $column, string $value, int $limit = 10) {

		$search = "%$value%";

		$sql = "SELECT * FROM $this->name WHERE $column LIKE ?";
		
		if ($limit) $sql .= " LIMIT $limit";
		
		//return $this->run($sql, [$search])->fetchAll();

	}

	public function column ($name, $args = [], $sort = '', $limit = '', $in = '') {
    
        return $this->select($args, $name, $sort, $limit, $in)->fetchAll(\PDO::FETCH_COLUMN);

    }

	public function select ($args, $columns, $sort, $limit, $in, $offset = '') {

        $sql = "SELECT " . $columns . " FROM $this->name";
        
        if ($args) $values = $this->where($sql, $args, $in);

        if ($sort) $sql .= " ORDER BY $sort";

        if ($limit) $sql .= " LIMIT $limit";
        if ($offset) $sql .= " OFFSET $offset";
        
		return $args ? $this->stmt($sql, $values) : $this->db->query($sql);

    }

	protected function stmt (string $sql, $vars) {

        $stmt = $this->prepare($sql);
        
        $stmt->execute($vars);
        
        return $stmt;

    }

    private function where (string &$sql, array $params, $in = '') {

        $comparisons = ['=','>','<','>=','<='];

        $values = [];
            
        foreach ($params as &$arg) foreach ($comparisons as $compa) if (strpos($arg, $compa)) {
                
            list($param, $value) = explode($compa, $arg);

            $arg = $param . "$compa:$param";

            $values[$param] = $value;

        }

        $sql .= " WHERE " . implode(' AND ', $params);

        
        if ($in) {
            
            foreach ($in as $column => $values) {

                $sql .= " AND $column IN (" . implode(',', $values) . ")";

            }

        }


        return $values;

    }

    public function delete ($args) {
        
        $sql = "DELETE FROM $this->name";
        
        $values = $this->where($sql, $args);

        return $this->stmt($sql, $values);

    }

    
    public function update ($args, $values) {
        
        $keys = [];

        foreach (array_keys($values) as $key) $keys[] = "$key=:$key";

        $sql = "UPDATE $this->name SET " . implode(', ', $keys);
        
        $data = $this->where($sql, $args);
        
        $this->db->dbc->prepare($sql)->execute(array_merge($data, $values));

    }


    public function insert (...$items) {

        if (count($items) > 1) $this->insertMultiple(...$items); elseif (count($items) == 1) $this->insertSingle((array) reset($items));

    }

    protected function insertSingle (array $item) {
        
        $sql = "INSERT INTO $this->name (" . implode(',', array_keys($item)) . ") VALUES (:" . implode(',:', array_keys($item)) . ")";

        return $this->db->dbc->prepare($sql)->execute($item);

    }

    protected function insertMultiple (...$items) {
        
        $count = 0;

        if (!$items) return;

        foreach ($items as $i) {

            $keys = array_keys((array) $i);

            if (count($keys) > $count) {
             
                $count = count($keys);

                $columns = $keys;

            }

        }

        foreach ($columns as &$column) $column = "$column=:$column";

        $sql = "INSERT INTO $this->name SET " . implode(',', $columns);
        
        $stmt = $this->db->dbc->prepare($sql);
        
        foreach ($items as $item) $stmt->execute((array) $item);

    }


}

?>