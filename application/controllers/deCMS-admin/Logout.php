<?php
class Logout extends MY_AdminController {
	public function index()
	{
		// Logout admin user
		$_SESSION['Admin_LoggedIn'] = FALSE;
		foreach($_SESSION as $key => $value)
			unset($_SESSION[$key]);
		$this->session->set_flashdata('Admin_InfoBox', 'Abmeldung erfolgreich!');
		
		// Redirect to login page
		redirect($this->adminBaseURL.'/Login');
	}
}
?>