<?php
	class VerificationcodeModel extends Model
	{
		function putCode($SSN)
		{
			$allowed_chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVXYZ";
			$thestring = "";
			for($i = 0; $i < 20; $i++)
			{
				$thestring .= $allowed_chars[mt_rand(0, strlen($allowed_chars) -1)];
			}
			$this->db->query("INSERT INTO verificationcodes (ssn,code) VALUES ('%s','$thestring');", $SSN);
			return $thestring;
		}
		
		function checkCode($SSN, $code)
		{
			return @count($this->db->query("SELECT * FROM verificationcodes WHERE ssn = '%s' AND code = '%s';", $SSN, $code)) ? true : false;
		}
	}
?>