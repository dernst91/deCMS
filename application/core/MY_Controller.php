<?php
class MY_AdminController extends CI_Controller {
	protected $adminBaseURL;
	protected $PageTitle;
	protected $Content;
	
	public function __construct()
	{
		// Call CI_Controller constructor
		parent::__construct();
		
		// Load session library and url helper
		$this -> load -> library('session');
		$this -> load -> helper('url');
		
		// Initialize variables
		$this->adminBaseURL = site_url('deCMS-admin');
		$this->PageTitle = 'DummyPageTitle';
		$this->Content = 'DummyContent';
		
		// Check if user is logged in
		if(!isset($_SESSION['Admin_LoggedIn']) || $_SESSION['Admin_LoggedIn'] != TRUE)
		{
			// User is not logged in!
			// Redirect to login page
			redirect($this->adminBaseURL.'/Login');
		}
		
		// Load database helper / connect to database
		$this -> load -> database();
	}
	
	protected function RenderPage()
	{
		$PageData = array(
			'PageTitle' => $this->PageTitle,
			'Content' => $this->Content
		);
		$this -> load -> view('admin/BackendTheme.php', $PageData);
	}
}
?>