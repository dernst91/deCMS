<?php
class Login extends CI_Controller {
	public function index()
	{
		// Load form and url helper
		$this -> load -> helper(array('form', 'url'));
		
		// Load form validation library
		$this -> load -> library(array('form_validation', 'session'));
		$this -> form_validation -> set_error_delimiters('<div class="error">', '</div>');
		
		// Login form submitted?
		$loginError = FALSE;
		if(isset($_POST['login_go']) && $_POST['login_go'] != '')
		{
			$this -> form_validation -> set_rules('login_user', 'Benutzername', 'required');
			$this -> form_validation -> set_rules('login_pass', 'Passwort', 'required');
			if($this -> form_validation -> run() == TRUE)
			{
				// === Form was filled in correctly, check database ===
				// Load database library
				$this -> load -> database();
				
				// Check user data
				$this -> db -> select('UID, Username, Password, FirstName, LastName, EMail, LastLogin');
				$this -> db -> from('admin_user');
				$this -> db -> where('Active', TRUE);
				$this -> db -> where('Username', $_POST['login_user']);
				$this -> db -> limit(1);
				$query = $this -> db -> get();
				if(!$query->num_rows())
				{
					// No matching user found
					$loginError = TRUE;
				} else {
					// User found, now check password
					$userdata = $query->row();
					if(!password_verify($_POST['login_pass'], $userdata->Password))
					{
						// Wrong password!
						$loginError = TRUE;
					} else {
						// Password correct, start session and save userdata
						$this -> load -> library('session');
						$newSessionData = array(
							'Admin_LoggedIn' => TRUE,
							'Admin_UID' => $userdata->UID,
							'Admin_Username' => $userdata->Username,
							'Admin_FirstName' => $userdata->FirstName,
							'Admin_LastName' => $userdata->LastName,
							'Admin_EMail' => $userdata->EMail,
							'Admin_LastLoginBefore' => $userdata->LastLogin
						);
						$this -> session -> set_userdata($newSessionData);
						
						// Now redirect to admin dashboard
						redirect('deCMS-admin');
					}
				}
			}
		}
		
		// Show login form
		$this -> load -> view('admin/Login', array('loginError' => $loginError));
	}
}
?>