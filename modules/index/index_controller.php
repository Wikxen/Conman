<?php
	class IndexController extends Controller{
		private function checkPnr($pnr) {
			if ( !preg_match("/^\d{6}\-\d{4}$/", $pnr) ) {
				return false;
			}
			$pnr = str_replace("-", "", $pnr);
			$n = 2;
			$sum = 0;
			for ($i=0; $i<9; $i++) {
				$tmp = $pnr[$i] * $n;
				($tmp > 9) ? $sum += 1 + ($tmp % 10) : $sum += $tmp; ($n == 2) ? $n = 1 : $n = 2;
			}
		 
			return !( ($sum + $pnr[9]) % 10);
		}
		
		function index()
		{
			if(Auth::user())
				$this->redirect('/ticket/index');
		}
		
		function login()
		{
			Auth::login($_REQUEST['username'], $_REQUEST['password']);
			$this->view = 'index.index.php';
			$this->index();
		}
		
		function logout()
		{
			Auth::logout();
			$this->view = 'index.index.php';
			$this->index();
		}
		
		function sendEmail($the_member, $pnr)
		{
			$verificationcode = Model::getModel('verificationcode');
			$thecode = $verificationcode->putCode($pnr);
			$mailer = CFactory::getMailer();
			$this->set('email', $the_member[0]['eMail']);
			$mailer->AddAddress($the_member[0]['eMail']);
			$mailer->Subject = 'Registering till Chibi-Con';
			$mailer->MsgHTML("<a href=\"".Router::url("validatecode/$pnr/$thecode", true)."\">Klicka h�r f�r att verifiera din emailadress</a>");
			$mailer->Send();
		}
		
		function register()
		{
			$pnr = implode('-', $_REQUEST['pnr']);
			if(!$this->checkPnr($pnr))
			{
				echo $pnr;
				$this->set('status', 'wrong_ssid');
			} else {
				$member = Model::getModel('member');
			
				if(!empty($_REQUEST['memberdata']))
				{
					$must_have = array('gender','firstName','lastName','streetAddress','zipCode','city','country','phoneNr','eMail');
					$has_everything = true;
					foreach($must_have as $m)
					{
						if(empty($_REQUEST['memberdata'][$m]))
							$has_everything = false;
					}
					if($has_everything)
					{
						if($_REQUEST['seen_rules'])
						{
							$_REQUEST['memberdata']['socialSecurityNumber'] = $pnr;
							$member->create($_REQUEST['memberdata']);
						} else {
							$this->set('not_accepted', true);
						}
					} else {
						$this->set('not_filled', true);
					}
				}
				
				$the_member = $member->getMemberBySSN($pnr);
				if(count($the_member))
				{
					$this->sendEmail($the_member, $pnr);
					$this->set('status', 'emailsent');
				} else {
					$this->set('status', 'not_member');
				}
			}
		}
		
		function validatecode($pnr, $thecode)
		{
			$verificationcode = Model::getModel('verificationcode');
			$this->set('valid', $verificationcode->checkCode($pnr, $thecode));
			$this->set('SSN', $pnr);
			$this->set('code', $thecode);
		}
		
		function createuser()
		{
			$user = Model::getModel('user');
			$verificationcode = Model::getModel('verificationcode');
			$validate = array();
			if(empty($_REQUEST['username']) || empty($_REQUEST['password']) || empty($_REQUEST['password_again']))
			{
				$validate['general'][] = 'Du m�ste fylla i alla f�lten!';
			}
			if($_REQUEST['password'] != $_REQUEST['password_again'])
			{
				$validate['password'] = 'Du m�ste skriva samma i b�da l�senordsrutorna.';
			}
			if($user->user_exists($_REQUEST['username']))
			{
				$validate['user'] = 'Det finns redan en anv�ndare med det h�r anv�ndarnamnet';
			}
			if(!$verificationcode->checkCode($_REQUEST['SSN'], $_REQUEST['code']))
			{
				$validate['general'][] = 'Den g�mda kontrollkoden �r felaktig O_o';
			}
			if(!empty($validate))
			{
				$this->set('validate', $validate);
				$this->view = 'index.validatecode.php';
				$this->validatecode($_REQUEST['SSN'], $_REQUEST['code']);
			} else {
				$member = Model::getModel('member');
				$the_member = $member->getMemberBySSN($_REQUEST['SSN']);
				if(empty($the_member[0]))
				{
					die("Ov�ntat fel! Din medlem finns inte!");
				}
				$user->create(array('username' => $_REQUEST['username'], 'password' => $_REQUEST['password'], 'member_id' => $the_member[0]['PersonID']));
			}
		}
	}
?>