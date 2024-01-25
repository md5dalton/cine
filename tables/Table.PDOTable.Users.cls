<?php
namespace Tables;

class Users extends Table
{
    use SingleInstanceTable;

    protected $columns = 'id VARCHAR(255) PRIMARY KEY, mtime INT DEFAULT CURRENT_TIMESTAMP, al INT DEFAULT 10';

    public function gUser (string $id) {

        return $this->row(["id=$id"]);

    }
    
    public function sUser ($user) {

        return $this->insert($user);

    }
}

class UsersInfo extends Table
{

    use SingleInstanceTable;
    
    protected $columns = 'id VARCHAR(255) PRIMARY KEY, contact VARCHAR(48), name VARCHAR(26), othernames VARCHAR(26), password VARCHAR(255)';

    public function gInfo (User $user) {

        return $this->row(["id=$user->id"]);

    }
    
    public function sInfo ($info) {

        return $this->insert($info);

    }
}
class VerifiedUsers extends Table
{

    use SingleInstanceTable;
    
    protected $columns = 'user VARCHAR(255) PRIMARY KEY';

    public function isVerified (\User $user) {

        return $this->row(["user=$user->id"]) ? true : false;

    }
}
class UserConfig extends Table
{

    use SingleInstanceTable;
    
    protected $columns = '
        no INT PRIMARY KEY AUTO_INCREMENT,    
        config VARCHAR(25),
        value VARCHAR(255),
        user VARCHAR(255),

        INDEX config_index (config),
        INDEX user_index (user)
    ';
    
}
?>