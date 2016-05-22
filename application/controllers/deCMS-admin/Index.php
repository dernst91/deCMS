<?php
class Index extends MY_AdminController {
	public function __construct() {
		// Call constructor of MY_AdminController
		parent::__construct();
	}
	
	public function index()
	{
		$this->PageTitle = 'Dashboard';
		$this->Content = '<h1>Dashboard</h1>';
		$this->RenderPage();
	}
}
?>