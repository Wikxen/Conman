<?php
	class UserModel extends Model {
		function user_exists($name)
		{
			return @count($this->db->query("SELECT * FROM users WHERE username = '%s';", $name)) ? true : false;
		}
		function get($id)
		{
			return $this->db->query("SELECT * FROM users WHERE id = '%s'", $id);
		}
		function create($data)
		{
			$allowed_chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVXYZ";
			$thestring = "";
			for($i = 0; $i < 20; $i++)
			{
				$thestring .= $allowed_chars[mt_rand(0, strlen($allowed_chars) -1)];
			}
			$this->db->query("INSERT INTO users(username,password,salt,member_id) VALUES('%s','%s','%s','%s');", $data['username'], hash('SHA512', $data['password'] . $thestring), $thestring, $data['member_id']);
		}
		
		function auth($username, $password)
		{
			$user = $this->db->query("SELECT * FROM users WHERE username = '%s';", $username);
			if(!count($user))
			{
				return false;
			}
			return (hash('SHA512', $password . $user[0]['salt']) == $user[0]['password']) ? $user[0]['id'] : false;
		}
	}
?>