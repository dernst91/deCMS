<?php
class Permissions extends MY_AdminController {
	protected $Actions;
	
	public function __construct()
	{
		// Call parent constructor
		parent::__construct();
		
		// Translations for Actions
		$this->Actions = array(
			'Index' => 'Übersicht',
			'Create' => 'Erstellen',
			'Edit' => 'Bearbeiten',
			'Manage' => 'Verwalten',
			'Delete' => 'Löschen'
		);
	}
	
	public function Edit($PermissionType, $GUID)
	{
		// Check params
		if(!in_array($PermissionType, array('u', 'g')) || intval($GUID) =='' || trim($GUID) == '')
		{
			$this->PageTitle = 'Fehler';
			$this->Content = $this->load->view('admin/Error_InvalidURL', NULL, TRUE);
			$this->RenderPage();
			return;
		}
		
		// Load required models
		$this->load->model('Admin_GeneralModel');
		$this->load->model('Admin_UserModel');
		$this->load->model('Admin_GroupModel');
		$this->load->model('Admin_PermissionModel');
		
		// Load required helpers
		$this->load->helper('form');
		
		// Get current user / group data and prepare page header
		$currentPermissions = array();
		if($PermissionType =='u')
		{
			// Request user data
			$udat = $this->Admin_UserModel->GetUserData($GUID);
			if($udat === FALSE)
			{
				$this->PageTitle = 'Fehler';
				$this->Content = $this->load->view('admin/Error_InvalidUser', NULL, TRUE);
				$this->RenderPage();
				return;
			}
			$this->Content = '<h2>Benutzerberechtigungen für &quot;'.$udat->Username.'&quot; bearbeiten</h2>';
			
			// Request user permissions
			$currentPermissions = $this->Admin_PermissionModel->GetUserPermissions($udat->UID, FALSE);
			
			// Back-Link
			$backLink = $this->admin_url('Users');
		} else {
			// Request group data
			$gdat = $this->Admin_GroupModel->GetGroupData($GUID);
			if($gdat === FALSE)
			{
				$this->PageTitle = 'Fehler';
				$this->Content = $this->load->view('admin/Error_InvalidGroup', NULL, TRUE);
				$this->RenderPage();
				return;
			}
			$this->Content = '<h2>Gruppenberechtigungen für &quot;'.$gdat->GroupName.'&quot; bearbeiten</h2>';
			
			// Request user permissions
			$currentPermissions = $this->Admin_PermissionModel->GetGroupPermissions($gdat->GID);
			
			// Back-Link
			$backLink = $this->admin_url('Groups');
		}
		
		// Prepare page header
		$this->PageTitle = 'Berechtigungen bearbeiten';
		$this->Content .= '<p>Wählen Sie hier für jedes Modul aus, welche Berechtigungen '.($PermissionType == 'u' ? 'dem Benutzer' : 'Benutzern der Gruppe').' zur Verfügung stehen sollen. Damit das Modul im Menü erscheint, muss die Berechtigung &quot;Übersicht&quot; aktiviert sein!</p>';
		
		// Load module list
		$mods = array();
		$modlist = $this->Admin_GeneralModel->GetAdminModuleList()->result();
		foreach($modlist as $mod)
			$mods[$mod->MID] = array(
				'DisplayName' => $mod->DisplayName,
				'ModuleName' => $mod->ModuleName,
				'AIDs' => array()
			);
		
		// Load available actions
		$validPermissions = array();
		$aidlist = $this->Admin_GeneralModel->GetAdminModuleActions();
		foreach($aidlist as $aid)
		{
			if(isset($mods[$aid->MID]['AIDs']))
			{
				$mods[$aid->MID]['AIDs'][$aid->AID] = $aid->Action;
				$validPermissions['m'.$aid->MID.'a'.$aid->AID] = array('MID' => $aid->MID, 'AID' => $aid->AID);
			}
		}
			
		// === CHECK IF FORM SENT ===
		if(isset($_POST['save']) && $_POST['save'] != '')
		{
			$_guid = trim($GUID);
			$insertData = array();
			// Form was sent! Validate permissions and generate permission array
			// Delete old permissions
			$this->db->delete('admin_permission', array('PermissionType' => $PermissionType, 'GUID' => $_guid));
			if(isset($_POST['permissions']))
			{
				foreach($_POST['permissions'] as $_perm)
				{
					if(!isset($validPermissions[$_perm])) continue;
					$insertData[] = array(
						'PermissionType' => $PermissionType,
						'GUID' => $_guid,
						'AID' => $validPermissions[$_perm]['AID']
					);
				}
				
				// Insert new permissions
				$this->db->insert_batch('admin_permission', $insertData);
			}
			
			// Set success message
			$this->session->set_flashdata('boxType', 'info');
			$this->session->set_flashdata('boxMessage', 'Die neuen Berechtigungen wurden gespeichert und der Berechtigungs-Cache für <b>'.$_SESSION['Admin_Username'].'</b> aktualisiert.');
			
			// Clear permission cache
			unset($_SESSION['Admin_UserPermission']);
			
			// Redirect
			redirect($backLink);
			return;
		}
			
			
		// Show permissions edit form
		$this->Content .= form_open($this->module_url('Edit/'.$PermissionType.'/'.$GUID));
		// Walk through all modules
		foreach($mods as $_MID => $_mod)
		{
			// Skip modules without permissions
			if(!sizeof($_mod['AIDs'])) continue;
			
			// Output fieldset for permissions
			$this->Content .= form_fieldset('Berechtigungen für Admin-Modul <b>'.$_mod['DisplayName'].'</b>');
			foreach($_mod['AIDs'] as $_aid => $_action)
			{
				$id = 'm'.$_MID.'a'.$_aid;
				$this->Content .= form_checkbox('permissions[]', $id, (in_array(strtolower($_mod['ModuleName']).'/'.strtolower($_action), $currentPermissions) ? TRUE : FALSE), 'id="'.$id.'"').'<label for="'.$id.'" class="cbLine">'.(isset($this->Actions[$_action]) ? $this->Actions[$_action] : $_action.' (nicht Übersetzt)').'</label>';
			}
			$this->Content .= form_fieldset_close().'<br /><br />';
		}
		
		// Submit button
		$this->Content .= form_submit('save', 'Änderungen speichern').' oder <a href="'.$backLink.'"><b>Abbrechen</b>';
		
		$this->Content .= form_close();
		
		$this->RenderPage();
	}
}
?>