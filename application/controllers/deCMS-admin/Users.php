<?php
class Users extends MY_AdminController {
	public function __construct() {
		// Call constructor of MY_AdminController
		parent::__construct();
		
		// Load admin user model
		$this->load->model('Admin_UserModel');
	}
	
	public function index()
	{
		// Request user list from database
		$usrobj = $this->Admin_UserModel->GetUserList();
		
		$this->PageTitle = 'Admin-Benutzer';
		
		// Output user list
		if(sizeof($usrobj) > 0)
		{
			// Cache users permissions
			$CanEditPermissions = $this->HavePermission('Permissions', 'Edit');
			
			$this->Content = '<h2>Vorhandene Benutzer</h2>';
			$this->Content .= '<table cellpadding="2" cellspacing="0" border="0" width="100%">
				<tr style="font-weight:bold;"><td>Aktiv</td><td>Benutzername</td><td>Name,&nbsp;Vorname</td><td>E-Mail</td><td>Letzte&nbsp;Anmeldung</td><td>Aktionen</td></tr>';
				
			// Output user rows
			foreach ($usrobj as $row)
			{
				// Generate Action URLs
				$urls = '';
				if($CanEditPermissions)
					$urls .= '<a href="'.$this->admin_url('Permissions/Edit/u/'.$row->UID).'"><img src="'.site_url('img/admin/module/Permissions.png').'" alt="Berechtigungen bearbeiten" title="Berechtigungen bearbeiten" /></a>';
				
				$this->Content .= '<tr>
					<td><img src="'.site_url('img/admin/misc/bullet_'.($row->Active ? 'green' : 'red').'.png').'" alt="" />&nbsp;'.($row->Active ? 'Ja' : 'Nein').'</td>
					<td>'.$row->Username.'</td>
					<td>'.$row->LastName.', '.$row->FirstName.'</td>
					<td>'.$row->EMail.'</td>
					<td>'.$row->LastLogin.'</td>
					<td>'.$urls.'</td>
				</tr>';
			}
			$this->Content .= '</table>';
			$this->Content .= '<p><strong>Derzeit '.(sizeof($usrobj) != 1 ? 'sind' : 'ist').' '.sizeof($usrobj).' Benutzer vorhanden.</strong></p>';
		}
		
		$this->RenderPage();
	}
}
?>