<?php
class Groups extends MY_AdminController {
	public function __construct() {
		// Call constructor of MY_AdminController
		parent::__construct();
		
		// Load admin group model
		$this->load->model('Admin_GroupModel');
	}
	
	public function index()
	{
		// Request group list from database
		$gldat = $this->Admin_GroupModel->GetGroupList();
		
		$this->PageTitle = 'Admin-Benutzergruppen';
		$this->Content = '<h2>Aktuell vorhandene Benutzergruppen</h2>';
		
		// List current groups
		if(sizeof($gldat))
		{
			$this->Content .= '<table cellpadding="2" cellspacing="0" width="100%" class="colored"><tr><td>Gruppe</td><td>Aktionen</td></tr>';
			foreach($gldat as $row)
			{
				$this->Content .= '<tr><td><img src="'.site_url('/img/admin/module/Groups.png').'" alt="" />&nbsp;'.$row->GroupName.'</td><td></td></tr>';
			}
			$this->Content .= '</table>';
		} else {$this->Content .= '<p>Bisher keine Gruppen vorhanden.</p>';}
		
		// Create group button
		// Create group button
		if($this->HavePermission($this->CurrentModule, 'Create'))
			$this->Content .= '<div class="buttonBar"><a href="'.$this->module_url('Create').'"><img src="'.site_url('/img/admin/misc/add.png').'" alt="" />&nbsp;Neue Gruppe erstellen</a></div>';
		
		$this->RenderPage();
	}
}
?>