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
			$CanEdit = $this->HavePermission($this->CurrentModule, 'Edit');
			$CanEditPermissions = $this->HavePermission('Permissions', 'Edit');
			$CanDelete = $this->HavePermission($this->CurrentModule, 'Delete');
			$userIcon = '<img src="'.site_url('img/admin/module/Users.png').'" alt="" />&nbsp;';
			
			$this->Content = '<h2>Vorhandene Benutzer</h2>';
			
			// Info-Box?
			if(isset($_SESSION['boxType']) && isset($_SESSION['boxMessage']))
				$this->Content .= '<div class="'.$_SESSION['boxType'].'">'.$_SESSION['boxMessage'].'</div>';
			
			$this->Content .= '<table cellpadding="2" cellspacing="0" border="0" width="100%" class="colored">
				<tr style="font-weight:bold;"><td>Aktiv</td><td>Benutzername</td><td>Name,&nbsp;Vorname</td><td>E-Mail</td><td>Letzte&nbsp;Anmeldung</td><td>Aktionen</td></tr>';
				
			// Output user rows
			foreach ($usrobj as $row)
			{
				// Generate Action URLs
				$urls = '';
				if($CanEdit)
					$urls .= '<a href="'.$this->module_url('Edit/'.$row->UID).'"><img src="'.site_url('img/admin/misc/pencil.png').'" alt="Benutzer bearbeiten" title="Benutzer bearbeiten" /></a>&nbsp;';
				if($CanEditPermissions)
					$urls .= '<a href="'.$this->admin_url('Permissions/Edit/u/'.$row->UID).'"><img src="'.site_url('img/admin/module/Permissions.png').'" alt="Berechtigungen bearbeiten" title="Berechtigungen bearbeiten" /></a>&nbsp;';
				if($CanDelete)
					$urls .= '<a href="'.$this->module_url('Delete/'.$row->UID).'"><img src="'.site_url('img/admin/misc/delete.png').'" alt="Benutzer löschen" title="Benutzer löschen" /></a>&nbsp;';
				
				$this->Content .= '<tr>
					<td><img src="'.site_url('img/admin/misc/bullet_'.($row->Active ? 'green' : 'red').'.png').'" alt="" />&nbsp;'.($row->Active ? 'Ja' : 'Nein').'</td>
					<td>'.$userIcon.$row->Username.'</td>
					<td>'.$row->LastName.', '.$row->FirstName.'</td>
					<td>'.$row->EMail.'</td>
					<td>'.($row->LastLogin != NULL ? $row->LastLogin : '<i>nie</i>').'</td>
					<td>'.$urls.'</td>
				</tr>';
			}
			$this->Content .= '</table>';
			$this->Content .= '<p><strong>Derzeit '.(sizeof($usrobj) != 1 ? 'sind' : 'ist').' '.sizeof($usrobj).' Benutzer vorhanden.</strong></p>';
		}
		
		// Create user button
		if($this->HavePermission($this->CurrentModule, 'Create'))
			$this->Content .= '<div class="buttonBar"><a href="'.$this->module_url('Create').'"><img src="'.site_url('/img/admin/misc/add.png').'" alt="" />&nbsp;Neuen Benutzer erstellen</a></div>';
		
		$this->RenderPage();
	}
	
	public function Create()
	{
		// Load helpers, libs and models
		$this->load->helper('form');
		$this->load->library('form_validation');
		
		// Basic page setup
		$this->PageTitle = 'Admin-Benutzer erstellen';
		$this->Content = '<p>Füllen Sie das folgende Formular aus, um einen neuen Admin-Benutzer zu erstellen.</p>';
		
		// Form sent?
		if(isset($_POST['create']) && $_POST['create'] != '')
		{
			// Set up rules and validate form
			$this->form_validation->set_rules('user', 'Benutzername', 'required|alpha_numeric|min_length[3]|max_length[50]|is_unique[admin_user.Username]');
			$this->form_validation->set_rules('pass1', 'Passwort', 'required|min_length[8]');
			$this->form_validation->set_rules('pass2', 'Passwort (wdh.)', 'required|min_length[8]|matches[pass1]');
			$this->form_validation->set_rules('firstname', 'Vorname', 'required|min_length[2]|max_length[25]');
			$this->form_validation->set_rules('lastname', 'Nachname', 'required|min_length[2]|max_length[25]');
			$this->form_validation->set_rules('email', 'E-Mail', 'required|min_length[8]|max_length[50]|valid_email');
			if($this->form_validation->run())
			{
				// Valid data submitted - format data and create user
				$insertData = array(
					'UID' => NULL,
					'Username' => $_POST['user'],
					'Password' => password_hash($_POST['pass1'], PASSWORD_DEFAULT),
					'FirstName' => $_POST['firstname'],
					'LastName' => $_POST['lastname'],
					'EMail' => $_POST['email'],
					'Active' => (isset($_POST['active']) && intval($_POST['active']) == 1 ? TRUE : FALSE),
					'LastLogin' => NULL
				);
				$this->db->insert('admin_user', $insertData);
				
				// Set success message
				$this->session->set_flashdata('boxType', 'info');
				$this->session->set_flashdata('boxMessage', 'Der Benutzer &quot;<b>'.$_POST['user'].'</b>&quot; wurde erfolgreich erstellt.');
				
				// Redirect
				redirect($this->module_url());
				return;
			}
		}
		
		// Validation errors
		$this->Content .= validation_errors();
		
		// === new user form ===
		$this->Content .= form_open($this->module_url($this->CurrentAction));
		$this->Content .= '<table cellpadding="0" cellspacing="10" border="0">';
		// Username
		$this->Content .= '<tr><td>Benutzername:</td><td>'.form_input('user', (isset($_POST['user']) ? $_POST['user'] : '')).'</td></tr>';
		// Password
		$this->Content .= '<tr><td>Passwort:</td><td>'.form_password('pass1', '').'</td></tr>';
		$this->Content .= '<tr><td>Passwort (wdh.):</td><td>'.form_password('pass2', '').'</td></tr>';
		// First Name
		$this->Content .= '<tr><td>Vorname:</td><td>'.form_input('firstname', (isset($_POST['firstname']) ? $_POST['firstname'] : '')).'</td></tr>';
		// Last Name
		$this->Content .= '<tr><td>Nachname:</td><td>'.form_input('lastname', (isset($_POST['lastname']) ? $_POST['lastname'] : '')).'</td></tr>';
		// E-Mail
		$this->Content .= '<tr><td>E-Mail:</td><td>'.form_input('email', (isset($_POST['email']) ? $_POST['email'] : '')).'</td></tr>';
		// Active
		$this->Content .= '<tr><td>Aktiv:</td><td>'.form_checkbox('active', 1, (isset($_POST['active']) && $_POST['active'] == 1 ? TRUE : FALSE), 'id="active"').'<label for="active">Benutzerkonto aktiv</label></td></tr>';
		// Submit
		$this->Content .= '<tr><td></td><td>'.form_submit('create', 'Benutzer erstellen').'&nbsp;oder&nbsp;<a href="'.$this->module_url().'">Abbrechen</a></td></tr>';
		$this->Content .= '</table>'.form_close();
		$this->RenderPage();
	}
	
	public function Edit($UID)
	{
		// Load helpers, libs and models
		$this->load->helper('form');
		$this->load->library('form_validation');
		
		// Load User data
		$udat = $this->Admin_UserModel->GetUserData($UID);
		if($udat === FALSE)
		{
			// === INVALID USER ===
			// Set failure message
			$this->session->set_flashdata('boxType', 'error');
			$this->session->set_flashdata('boxMessage', 'Der zu bearbeitende Benutzer existiert nicht.');
				
			// Redirect
			redirect($this->module_url());
			return;
		} else {
			$edUser = $udat->Username;
			$edFirstName = $udat->FirstName;
			$edLastName = $udat->LastName;
			$edEMail = $udat->EMail;
			$edActive = $udat->Active;
		}
		
		// Basic page setup
		$this->PageTitle = 'Admin-Benutzer bearbeiten';
		$this->Content = '<p>Mit dem unten stehenden Formular können Sie den Benutzer &quot;<b>'.$udat->Username.'</b>&quot; bearbeiten. Lassen Sie die Passwort-Felder frei, um das aktuelle Passwort beizubehalten.</p>';
		
		// Form sent?
		if(isset($_POST['edit']) && $_POST['edit'] != '')
		{
			// Substitute database by sent values
			$edUser = $_POST['user'];
			$edFirstName = $_POST['firstname'];
			$edLastName = $_POST['lastname'];
			$edEMail = $_POST['email'];
			$edActive = (isset($_POST['active']) && intval($_POST['active']) == 1 ? TRUE : FALSE);
			
			// Set up rules and validate form
			$this->form_validation->set_rules('user', 'Benutzername', 'required|alpha_numeric|min_length[3]|max_length[50]');
			$this->form_validation->set_rules('pass1', 'Passwort', 'matches[pass2]');
			$this->form_validation->set_rules('pass2', 'Passwort (wdh.)', 'matches[pass1]');
			$this->form_validation->set_rules('firstname', 'Vorname', 'required|min_length[2]|max_length[25]');
			$this->form_validation->set_rules('lastname', 'Nachname', 'required|min_length[2]|max_length[25]');
			$this->form_validation->set_rules('email', 'E-Mail', 'required|min_length[8]|max_length[50]|valid_email');
			if($this->form_validation->run())
			{
				// Check if username is unique
				$this->db->select('UID');
				$this->db->from('admin_user');
				$this->db->where('Username', $edUser);
				$this->db->where_not_in('UID', array($udat->UID));
				$query = $this->db->get();
				if($query->num_rows())
				{
					// User name already taken
					$this->Content .= '<div class="error">Der gewählte Benutzername ist bereits in Verwendung!</div>';
				} else {		
					// Valid data submitted - format data and update user
					$updateData = array(
						'Username' => $edUser,
						'FirstName' => $edFirstName,
						'LastName' => $edLastName,
						'EMail' => $edEMail,
						'Active' => $edActive,
					);
					// Password changed?
					if($_POST['pass1'] != '' && strlen($_POST['pass1']) >= 8)
						$updateData['Password'] = password_hash($_POST['pass1'], PASSWORD_DEFAULT);
					
					// Password strong enough?
					if($_POST['pass1'] != '' && strlen($_POST['pass1']) < 8)
					{
						// password too short
						$this->Content .= '<div class="error">Das gewählte Passwort ist zu kurz! (mind. 8 Zeichen)</div>';
					} else {
						// Update user data
						$this->db->where('UID', $udat->UID);
						$this->db->update('admin_user', $updateData);
						
						// Set success message
						$this->session->set_flashdata('boxType', 'info');
						$this->session->set_flashdata('boxMessage', 'Der Benutzer &quot;<b>'.$_POST['user'].'</b>&quot; wurde erfolgreich aktualisiert.');
						
						// Redirect
						if($udat->UID == $_SESSION['Admin_UID'] && $udat->Username != $edUser)
						{
							// Need to relogin if current logged in user name changed
							redirect($this->admin_url('Logout'));
						} else {
							// Redirect to user list
							redirect($this->module_url());
						}
						return;
					}
				}
			}
		}
		
		// Validation errors
		$this->Content .= validation_errors();
		
		// === new user form ===
		$this->Content .= form_open($this->module_url($this->CurrentAction.'/'.$UID));
		$this->Content .= '<table cellpadding="0" cellspacing="10" border="0">';
		// Username
		$this->Content .= '<tr><td>Benutzername:</td><td>'.form_input('user', $edUser).'</td></tr>';
		// Password
		$this->Content .= '<tr><td>Passwort:</td><td>'.form_password('pass1', '').'</td></tr>';
		$this->Content .= '<tr><td>Passwort (wdh.):</td><td>'.form_password('pass2', '').'</td></tr>';
		// First Name
		$this->Content .= '<tr><td>Vorname:</td><td>'.form_input('firstname', $edFirstName).'</td></tr>';
		// Last Name
		$this->Content .= '<tr><td>Nachname:</td><td>'.form_input('lastname', $edLastName).'</td></tr>';
		// E-Mail
		$this->Content .= '<tr><td>E-Mail:</td><td>'.form_input('email', $edEMail).'</td></tr>';
		// Active
		$this->Content .= '<tr><td>Aktiv:</td><td>'.form_checkbox('active', 1, $edActive, 'id="active"').'<label for="active">Benutzerkonto aktiv</label></td></tr>';
		// Submit
		$this->Content .= '<tr><td></td><td>'.form_submit('edit', 'Änderungen speichern').'&nbsp;oder&nbsp;<a href="'.$this->module_url().'">Abbrechen</a></td></tr>';
		$this->Content .= '</table>'.form_close();
		$this->RenderPage();
	}
	
	public function Delete($UID)
	{
		// Load helpers, models and libs
		$this->load->model('Admin_UserModel');
		$this->load->helper('form');
		
		// Fetch group data
		$udat = $this->Admin_UserModel->GetUserData($UID);
		if($udat === FALSE)
		{
			// === INVALID USER ===
			// Set failure message
			$this->session->set_flashdata('boxType', 'error');
			$this->session->set_flashdata('boxMessage', 'Der zu löschende Benutzer existiert nicht. Möglicherweise wurde er bereits gelöscht.');
				
			// Redirect
			redirect($this->module_url());
			return;
		}
		
		// Form sent?
		if(isset($_POST['delete']) && $_POST['delete'] != '')
		{
			// Delete group!
			$this->Admin_UserModel->DeleteUser($udat->UID);
			
			// Set success message
			$this->session->set_flashdata('boxType', 'info');
			$this->session->set_flashdata('boxMessage', 'Der Benutzer &quot;<b>'.$udat->Username.'</b>&quot; wurde gelöscht und der Berechtigungs-Cache für <b>'.$_SESSION['Admin_Username'].'</b> aktualisiert.');
			
			// Clear permission cache
			unset($_SESSION['Admin_UserPermission']);
			
			// Redirect
			redirect($this->module_url());
			return;
		}
		
		$this->PageTitle = 'Admin-Benutzer löschen';
		$this->Content = '<p>Möchten Sie den Admin-Benutzer &quot;<b>'.$udat->Username.'</b>&quot; wirklich löschen?</p>';
		$this->Content .= form_open($this->module_url($this->CurrentAction.'/'.$UID));
		$this->Content .= '<p>'.form_submit('delete', 'Löschen').'&nbsp;oder&nbsp;<a href="'.$this->module_url().'">Abbrechen</a></p>'.form_close();
		
		$this->RenderPage();
	}
}
?>