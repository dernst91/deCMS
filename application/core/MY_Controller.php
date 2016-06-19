<?php
class MY_AdminController extends CI_Controller {
	protected $adminBaseURL;
	protected $PageTitle;
	protected $Content;
	protected $CurrentModule;
	protected $CurrentAction;
	
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
			return;
		}
		
		// Load database helper / connect to database
		$this -> load -> database();
		
		// Check if permissions are cached
		if(!isset($_SESSION['Admin_UserPermission']))
		{
			// Rebuild permission cache
			$this->RebuildPermissionCache();
			// Rebuild sidebar cache
			$this->RebuildSidebarCache();
		}
		
		// Check for permissions
		$this->CurrentModule = $this->router->fetch_class();
		$this->CurrentAction = $this->router->fetch_method();
		if(!$this->HavePermission($this->CurrentModule, $this->CurrentAction))
		{
			// No permission, show error page
			$this->PageTitle = 'Fehler';
			$this->Content = $this->load->view('admin/Error_NoPermission', NULL, TRUE);
			$this->RenderPage();
			$this->output->_display();
			die;
		}
	}
	
	protected function admin_url($url = '')
	{
		return $this->adminBaseURL.'/'.$url;
	}
	
	protected function module_url($url = '')
	{
		return $this->adminBaseURL.'/'.$this->CurrentModule.'/'.$url;
	}
	
	protected function RenderPage()
	{
		$PageData = array(
			'PageTitle' => $this->PageTitle,
			'Content' => $this->Content
		);
		$this -> load -> view('admin/BackendTheme.php', $PageData);
	}
	
	protected function HavePermission($Module, $Action)
	{
		return in_array(strtolower($Module).'/'.strtolower($Action), $_SESSION['Admin_UserPermission']);
	}
	
	private function RebuildPermissionCache()
	{
		// Request permissions from database
		$this->load->model('Admin_PermissionModel');
		
		// Cache user permissions
		$_SESSION['Admin_UserPermission'] = $this->Admin_PermissionModel->GetUserPermissions($_SESSION['Admin_UID']);
	}
	
	private function RebuildSidebarCache()
	{
		// Load admin backend model
		$this->load->model('Admin_GeneralModel');
		// Load category list
		$_catList = $this->Admin_GeneralModel->GetAdminModuleCategoryList();
		if($_catList === FALSE) return;
		$sbArray = array();
		foreach($_catList->result() as $row)
		{
			$sbArray[$row->CID]['Label'] = $row->CategoryName;
			$sbArray[$row->CID]['Entries'] = array();
		}
		
		// Load module list
		$_modList = $this->Admin_GeneralModel->GetAdminModuleList();
		if($_modList === FALSE) return;
		foreach($_modList->result() as $row)
		{
			// Check if category exists
			if(!isset($sbArray[$row->CID]['Label'])) continue;
			
			// Check if user has permission to use module
			if(!$this->HavePermission($row->ModuleName, 'Index')) continue;
			
			// Append module to categories' list
			$sbArray[$row->CID]['Entries'][] = array('MID' => $row->MID, 'ModuleName' => $row->ModuleName, 'DisplayName' => $row->DisplayName);
		}
		
		// === RENDER SIDEBAR ===
		$sbCache = '';
		foreach($sbArray as $cid => $cidData)
		{
			// Check if category has any entries
			if(!sizeof($sbArray[$cid]['Entries'])) continue;
			
			// Category label
			$sbCache .= '<div class="sidebarCategory">'.$cidData['Label'].'</div>';
			
			// Modules
			foreach($sbArray[$cid]['Entries'] as $_Module)
			{
				$sbCache .= '<a href="'.$this->adminBaseURL.'/'.$_Module['ModuleName'].'"><img src="'.site_url('img/admin/module/'.$_Module['ModuleName'].'.png').'" alt="" border="0" />&nbsp;'.$_Module['DisplayName'].'</a><br />';
			}
		}
		
		// Save sidebar cache in session
		$_SESSION['Admin_SidebarCache'] = $sbCache;
	}
}
?>