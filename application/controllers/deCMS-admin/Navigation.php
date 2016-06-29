<?php
class Navigation extends MY_AdminController {
	public function __construct()
	{
		// Call MY_AdminController constructor
		parent::__construct();
		
		// Default page title
		$this->PageTitle = 'Navigation';
	}
	
	public function index()
	{
		$this->RenderPage();
	}
}
?>