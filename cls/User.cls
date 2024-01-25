<?php

class UserInfo 
{

	public $name;

	private $row, $tb, $user;

	public function __construct (User $user) {

		$this->tb = new \Tables\UserInfo;

		$this->row = $this->tb->gInfo($user);

		if ($this->row) {

			$this->name = $this->row->name;

		}

	}
}
class User
{
	
	public $id, $name = '';

	private $row, $tb, $al, $verified, $info;

	protected static $instance;

	public function __construct ($e) {

		$this->tb = new \Tables\Users;

		$this->row = is_string($e) ? $this->tb->gUser($e) : $e;

		if ($this->row) {

			$this->id = $this->row->id;
			$this->al = $this->row->al;

			self::$instance = $this;
		
		}
	}

	public static function instance () {

		return self::$instance;

	}

	public function gAl () {

		return $this->al;

	}

	public function gConfig ($key, bool $setcookie = false) {
	
		$s_value = @$_SESSION['userconfig'][$key];

		if (!$s_value) {

			$tb = gTables('UserConfig');

			$param = ["config=$key", "user=$this->id"];

			$row = $tb->userconfig->row($param);

			if ($row) {
				
				$_SESSION['userconfig'][$key] = $row->value;

				if ($setcookie) setcookie($key, $row->value, ['expires' => time() + (10 * 365 * 24 * 60 * 60)]);
				
				return $row->value;

			}
		}

		return $s_value;

	}

	public function sConfig ($key, $value, bool $setcookie = false) {

		$s_value = @$_SESSION['userconfig'][$key];

		if ($s_value != $value && in_array($value, ['es','en'])) {

			$tb = gTables('UserConfig');

			$param = ["config=$key", "user=$this->id"];

			$row = $tb->userconfig->row($param);

			if ($row) $tb->userconfig->update($param, ['value'=>$value]); else $tb->userconfig->insert(['config'=>$key, 'value'=>$value, 'user'=>$this->id]);

			$_SESSION['userconfig'][$key] = $value;

			if ($setcookie) setcookie($key, $value, ['expires' => time() + (10 * 365 * 24 * 60 * 60)]);

		}

	}
	
	public function gInfo () {

		if (!$this->info) {
			
			$this->info = new UserInfo($this);

			$this->name = $this->info->name;

		}
	}

	public function isVerified () {

		if (!isset($this->verified)) {
			
			$tb = new \Tables\VerifiedUsers;

			$this->verified = $tb->isVerified($this);

		}

		return $this->verified;

	}


	public function create ($id) {

		//$s = (object) $_SERVER;

		//$fullname = $fullname ? $fullname : $s->HTTP_USER_AGENT;
		//$ip = $ip ? $ip : $s->REMOTE_ADDR;

		$newUser = [
			'id' => $id
			//'mtime' => time()
			//'ip' => $ip,
			//'contact' => $contact,
			//'fullname' => $fullname,
			//'password' => password_hash($password, PASSWORD_DEFAULT),
			//'al' => $al
		];

		$this->tb->sUser($newUser);

		if ($row = $this->tb->gUser($id)) return new User($row);

	}
}


?>