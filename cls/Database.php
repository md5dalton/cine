<?php

class Database
{
	
	static private $instance;

	protected $config;

	public $dbc;

	public function __construct ($config) {

		$this->config = (object) $config;

		self::$instance = $this;

		$this->connect();

	}

    
	public static function instance () {

		//if (!self::$instance) self::$instance = new self;

		return self::$instance;
		
	}

    /*public function __call ($method, $args) {

        return call_user_func(array($this->dbc, $method), $args);

    }*/


	private function connect () {

        $options = [
            //PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];

        $dsn = $this->config->driver . ":host=" . $this->config->host . ";dbname=" . $this->config->dbname;

        try {
            
            $this->dbc = new \PDO($dsn, $this->config->username, $this->config->password, $options);
            
        } catch (\PDOException $e) {
            
            throw new \PDOException($e->getMessage(), (int) $e->getCode());

        }

	}

	public function run ($sql) {

		try {
			
			$count = $this->dbc->exec($sql);// or print_r($this->dbc->errorInfo());
			
		} catch (PDOException $e) {
				
			echo __LINE__ . $e->getMessage();

		}


		return $count;

	}

	public function query ($sql) {

		return $this->dbc->query($sql);

	}

}

?>