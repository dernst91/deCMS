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
		
		// Info-Box?
		if(isset($_SESSION['boxType']) && isset($_SESSION['boxMessage']))
			$this->Content .= '<div class="'.$_SESSION['boxType'].'">'.$_SESSION['boxMessage'].'</div>';
		
		$CanEdit = $this->HavePermission($this->CurrentModule, 'Edit');
		$CanManage = $this->HavePermission($this->CurrentModule, 'Manage');
		$CanEditPermissions = $this->HavePermission('Permissions', 'Edit');
		$CanDelete = $this->HavePermission($this->CurrentModule, 'Delete');
		
		// List current groups
		if(sizeof($gldat))
		{
			$this->Content .= '<table cellpadding="2" cellspacing="0" width="100%" class="colored"><tr><td>Gruppe</td><td>Aktionen</td></tr>';
			foreach($gldat as $row)
			{
				// Action URLs
				$urls = '';
				if($CanEdit)
					$urls .= '<a href="'.$this->module_url('Edit/'.$row->GID).'"><img src="'.site_url('/img/admin/misc/pencil.png').'" alt="Gruppe bearbeiten" title="Gruppe bearbeiten" /></a>&nbsp;';
				if($CanManage)
					$urls .= '<a href="'.$this->module_url('Manage/'.$row->GID).'"><img src="'.site_url('/img/admin/misc/group_edit.png').'" alt="Gruppenmitglieder verwalten" title="Gruppenmitglieder verwalten" /></a>&nbsp;';
				if($CanEditPermissions)
					$urls .= '<a href="'.$this->admin_url('Permissions/Edit/g/'.$row->GID).'"><img src="'.site_url('/img/admin/module/Permissions.png').'" alt="Gruppenberechtigungen bearbeiten" title="Gruppenberechtigungen bearbeiten" /></a>&nbsp;';
				if($CanDelete)
					$urls .= '<a href="'.$this->module_url('Delete/'.$row->GID).'"><img src="'.site_url('/img/admin/misc/delete.png').'" alt="Gruppe löschen" title="Gruppe löschen" /></a>&nbsp;';
				
				$this->Content .= '<tr><td><img src="'.site_url('/img/admin/module/Groups.png').'" alt="" />&nbsp;'.$row->GroupName.'</td><td>'.$urls.'</td></tr>';
			}
			$this->Content .= '</table><p><b>Derzeit '.(sizeof($gldat) == 1 ? 'ist' : 'sind').' '.sizeof($gldat).' Gruppe'.(sizeof($gldat) == 1 ? '' : 'n').' vorhanden.</p>';
		} else {$this->Content .= '<p>Bisher keine Gruppen vorhanden.</p>';}
		
		// Create group button
		if($this->HavePermission($this->CurrentModule, 'Create'))
			$this->Content .= '<div class="buttonBar"><a href="'.$this->module_url('Create').'"><img src="'.site_url('/img/admin/misc/add.png').'" alt="" />&nbsp;Neue Gruppe erstellen</a></div>';
		
		$this->RenderPage();
	}
	
	public function Create()
	{
		// Load helpers`and libs
		$this->load->helper('form');
		$this->load->library('form_validation');
		
		$this->PageTitle = 'Admin-Benutzergruppe erstellen';
		$this->Content = '<p>Geben Sie hier einen Gruppennamen ein, um eine neue Gruppe zu erstellen.</p>';
		
		// Form sent?
		if(isset($_POST['create']) && $_POST['create'] != '')
		{
			$this->form_validation->set_rules('groupname', 'Gruppenname', 'required|min_length[3]|max_length[50]|alpha_numeric_spaces|is_unique[admin_group.GroupName]');
			if($this->form_validation->run())
			{
				// === Input valid ===
				// Insert into database
				$newGroup = array(
					'GID' => NULL,
					'GroupName' => $_POST['groupname']
				);
				$this->db->insert('admin_group', $newGroup);
				
				// Set success message
				$this->session->set_flashdata('boxType', 'info');
				$this->session->set_flashdata('boxMessage', 'Die Gruppe &quot;'.$_POST['groupname'].'&quot; wurde erfolgreich erstellt.');
				
				// Redirect
				redirect($this->module_url());
				return;
			} else {
				$this->Content .= validation_errors();
			}
		}
		
		// Create group form
		$this->Content .= form_open($this->module_url($this->CurrentAction));
		$this->Content .= '<p>Gruppenname:&nbsp;'.form_input('groupname', (isset($_POST['groupname']) ? $_POST['groupname'] : '')).'</p>';
		$this->Content .= '<p>'.form_submit('create', 'Gruppe erstellen').'&nbsp;oder&nbsp;<a href="'.$this->module_url().'">Abbrechen</a>';
		$this->Content .= form_close();
		
		// Output page
		$this->RenderPage();
	}
	
	public function Edit($GID)
	{
		// Load helpers, models and libs
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->model('Admin_GroupModel');
		
		// Fetch group data
		$gdat = $this->Admin_GroupModel->GetGroupData($GID);
		if($gdat === FALSE)
		{
			// === INVALID GROUP ===
			// Set failure message
			$this->session->set_flashdata('boxType', 'error');
			$this->session->set_flashdata('boxMessage', 'Die zu bearbeitende Gruppe existiert nicht.');
				
			// Redirect
			redirect($this->module_url());
			return;
		}
		
		$newGroupName = $gdat->GroupName;
		$this->PageTitle = 'Admin-Benutzergruppe bearbeiten';
		$this->Content = '<p>Hier können Sie die Gruppe &quot;'.$gdat->GroupName.'&quot; umbenennen.</p>';
		
		// Form sent?
		if(isset($_POST['rename']) && $_POST['rename'] != '')
		{
			$newGroupName = $_POST['groupname'];
			
			$this->form_validation->set_rules('groupname', 'Gruppenname', 'required|min_length[3]|max_length[50]|alpha_numeric_spaces');
			if($this->form_validation->run())
			{
				// Check if group name is unique
				$this->db->select('GID');
				$this->db->from('admin_group');
				$this->db->where('GroupName', $_POST['groupname']);
				$this->db->where_not_in('GID', array($gdat->GID));
				$query = $this->db->get();
				if($query->num_rows())
				{
					// Group name already taken
					$this->Content .= '<div class="error">Der gewählte Gruppenname ist bereits in Verwendung!</div>';
				} else {
					// === Input valid ===
					// Really renamed?
					if($gdat->GroupName != $newGroupName)
					{
						// Insert into database
						$this->db->set('GroupName', $newGroupName);
						$this->db->where('GID', $gdat->GID);
						$this->db->update('admin_group');
						
						// Set success message
						$this->session->set_flashdata('boxType', 'info');
						$this->session->set_flashdata('boxMessage', 'Die Gruppe &quot;'.$gdat->GroupName.'&quot; wurde erfolgreich in &quot;'.$newGroupName.'&quot; umbenannt.');
					}
					
					// Redirect
					redirect($this->module_url());
				}
			} else {
				$this->Content .= validation_errors();
			}
		}
		
		// Create group form
		$this->Content .= form_open($this->module_url($this->CurrentAction.'/'.$GID));
		$this->Content .= '<p>Gruppenname:&nbsp;'.form_input('groupname', $newGroupName).'</p>';
		$this->Content .= '<p>'.form_submit('rename', 'Gruppe umbenennen').'&nbsp;oder&nbsp;<a href="'.$this->module_url().'">Abbrechen</a>';
		$this->Content .= form_close();
		
		// Output page
		$this->RenderPage();
	}
	
	public function Manage($GID)
	{
		// Load helpers, models and libs
		$this->load->helper('form');
		$this->load->model('Admin_GroupModel');
		$this->load->model('Admin_UserModel');
		
		// Fetch group data
		$gdat = $this->Admin_GroupModel->GetGroupData($GID);
		if($gdat === FALSE)
		{
			// === INVALID GROUP ===
			// Set failure message
			$this->session->set_flashdata('boxType', 'error');
			$this->session->set_flashdata('boxMessage', 'Die zu verwaltende Gruppe existiert nicht.');
				
			// Redirect
			redirect($this->module_url());
			return;
		}
		
		// Fetch user data
		$udat = $this->Admin_UserModel->GetUserList();
		
		// Fetch group members
		$members = $this->Admin_GroupModel->GetGroupMembers($gdat->GID);
		
		// Form sent?
		if(isset($_POST['save']) && $_POST['save'] != '')
		{
			// === FORM SENT ===
			// Enumerate valid UIDs
			$validUsers = array();
			foreach($udat as $row)
				$validUsers[] = $row->UID;
				
			// Prepare Insert data
			$insertData = array();
			if(!isset($_POST['uids'])) $_POST['uids'] = array();
			foreach($_POST['uids'] as $uid)
			{
				if(!in_array($uid, $validUsers)) continue;
				$insertData[] = array(
					'GID' => $gdat->GID,
					'UID' => $uid
				);
			}
			
			// Delete old group members
			$this->db->delete('admin_group_membership', array('GID' => $gdat->GID));
			
			// Insert new group members
			if(sizeof($insertData))
				$this->db->insert_batch('admin_group_membership', $insertData);
			
			// Set success message
			$this->session->set_flashdata('boxType', 'info');
			$this->session->set_flashdata('boxMessage', 'Die Mitglieder für die Gruppe &quot;<b>'.$gdat->GroupName.'</b>&quot; wurden aktualisiert.');
			
			// Redirect
			redirect($this->module_url());
			return;
		}
		
		$this->PageTitle = 'Admin-Gruppenmitglieder verwalten';
		$this->Content = '<p>Wählen Sie hier alle Benutzer aus, die Mitglied der Gruppe &quot;<b>'.$gdat->GroupName.'</b>&quot; sein sollen.</p>';
		
		// Group members form
		$this->Content .= form_open($this->module_url('Manage/'.$GID)).form_fieldset('Mitglieder der Gruppe '.$gdat->GroupName);
		foreach($udat as $usr)
		{
			$this->Content .= form_checkbox('uids[]', $usr->UID, in_array($usr->UID, $members), 'id="c'.$usr->UID.'"').'<label for="c'.$usr->UID.'"><b>'.$usr->Username.'</b>&nbsp;('.$usr->EMail.')</label><br />';
		}
		$this->Content .= form_fieldset_close().'<p>'.form_submit('save', 'Speichern').'&nbsp;oder&nbsp;<a href="'.$this->module_url().'">Abbrechen</a></p>'.form_close();
		
		$this->RenderPage();
	}
	
	public function Delete($GID)
	{
		// Load helpers, models and libs
		$this->load->model('Admin_GroupModel');
		$this->load->helper('form');
		
		// Fetch group data
		$gdat = $this->Admin_GroupModel->GetGroupData($GID);
		if($gdat === FALSE)
		{
			// === INVALID GROUP ===
			// Set failure message
			$this->session->set_flashdata('boxType', 'error');
			$this->session->set_flashdata('boxMessage', 'Die zu löschende Gruppe existiert nicht. Möglicherweise wurde sie bereits gelöscht.');
				
			// Redirect
			redirect($this->module_url());
			return;
		}
		
		// Form sent?
		if(isset($_POST['delete']) && $_POST['delete'] != '')
		{
			// Delete group!
			$this->Admin_GroupModel->DeleteGroup($gdat->GID);
			
			// Set success message
			$this->session->set_flashdata('boxType', 'info');
			$this->session->set_flashdata('boxMessage', 'Die Gruppe &quot;<b>'.$gdat->GroupName.'</b>&quot; wurde gelöscht und der Berechtigungs-Cache für <b>'.$_SESSION['Admin_Username'].'</b> aktualisiert.');
			
			// Clear permission cache
			unset($_SESSION['Admin_UserPermission']);
			
			// Redirect
			redirect($this->module_url());
			return;
		}
		
		$this->PageTitle = 'Admin-Benutzergruppe löschen';
		$this->Content = '<p>Möchten Sie die Admin-Benutzergruppe &quot;<b>'.$gdat->GroupName.'</b>&quot; wirklich löschen?</p>';
		$this->Content .= form_open($this->module_url($this->CurrentAction.'/'.$GID));
		$this->Content .= '<p>'.form_submit('delete', 'Löschen').'&nbsp;oder&nbsp;<a href="'.$this->module_url().'">Abbrechen</a></p>'.form_close();
		
		$this->RenderPage();
	}
}
?>