<?php
class SiteStructure extends MY_AdminController {
	public function __construct()
	{
		// Call MY_AdminController contructor
		parent::__construct();
		
		// Load required models
		$this->load->model('Admin_SiteStructureModel');
		
		$this->PageTitle = 'Seitenstruktur &raquo; ';
	}
	
	public function index()
	{
		// Set page title and heading
		$this->PageTitle .= 'Übersicht';
		$this->Content = '<p>Hier können Sie die Struktur Ihrer Webseite bearbeiten.</p>';
		
		// Load structure data
		$FirstPID = $this->Admin_SiteStructureModel->GetFirstPID();
		if($FirstPID === FALSE)
		{
			$this->Content .= '<div class="info">Es sind noch keine Seite vorhanden!</div>';
		}
		
		// Show current site structure
		$this->Content .= '<table cellpadding="2" cellspacing="0" border="0" width="100%" class="colored">
			<tr><td width="100%">Seite</td><td>Aktionen</td></tr>
			<tr><td><img src="'.site_url('img/admin/misc/world.png').'" alt="" />&nbsp;<b><i>Webseite</i></b></td><td></td></tr>';
		
		$this->Content .= '</table>';
		
		// Create new page link
		if($this->HavePermission($this->CurrentModule, 'Create'))
			$this->Content .= '<div class="buttonBar"><a href="'.$this->module_url('Create').'"><img src="'.site_url('/img/admin/misc/add.png').'" alt="" />&nbsp;Neue Seite erstellen</a></div>';
		
		$this->RenderPage();
	}
	
	public function Create()
	{	
		// Load required stuff
		$this->load->helper('form');
		$this->load->library('form_validation');
		
		// Definitions
		$PageTypes = array(
			'RP'	=> 'Normale Seite',
			'IR'	=> 'Interne Weiterleitung',
			'ER'	=> 'Externe Weiterleitung',
		);
		
		// Form data
		$fdat = array(
			'pgName'	=> '',
			'pgURL'		=> '',
			'pgType'	=> 'RP',
			'pgPos'		=> ''
		);
		
		// === FORM SENT? ===
		if(isset($_POST['CreateDo']) && $_POST['CreateDo'] != '')
		{
			// Set posted data to fdat-array
			$fdat = array(
				'pgName'	=> $_POST['pgName'],
				'pgURL'		=> $_POST['pgURL'],
				'pgType'	=> $_POST['pgType'],
				'pgPos'		=> (isset($_POST['pgPos']) ? $_POST['pgPos'] : '')
			);
			
			// === FORM WAS SENT ===
			// Set up form validation rules
			$this->form_validation->set_rules('pgName', 'Name der Seite', 'required|min_length[1]|max_length[50]');
			$this->form_validation->set_rules('pgURL', 'URL-Bezeichnung', 'regex_match[/^[a-zA-Z0-9-]*$/]');
			$this->form_validation->set_rules('pgPos', 'Position der Seite', 'required');
			if($this->form_validation->run())
			{
				// CI Validation successful, now try to create page
				
			}
		}
	
		// PageTitle
		$this->PageTitle .= 'Neue Seite erstellen';
	
		// form
		$this->Content = '<p>Geben Sie hier die Daten für die neue Seite ein.</p>'.validation_errors().'
			'.form_open($this->module_url('Create')).'<table cellpadding="0" cellspacing="10">
				<tr>
					<td>Name der Seite:</td>
					<td>'.form_input('pgName', $fdat['pgName']).' (Wird in Menüs angezeigt)</td>
				</tr>
				<tr>
					<td>URL-Bezeichnung:</td>
					<td>'.form_input('pgURL', $fdat['pgURL']).' (leer lassen für Startseite)</td>
				</tr>
				<tr>
					<td>Art der Seite:</td>
					<td>'.form_dropdown('pgType', $PageTypes, $fdat['pgType']).'</td>
				</tr>
			</table>';
		// page position
		$img_in = '<img src="'.site_url('img/admin/misc/paste_in.png').'" alt="" />&nbsp;';
		$this->Content .= '<table cellpadding="2" cellspacing="0" border="0" width="100%" class="colored">
			<tr><td width="100%">Seite</td><td>Position&nbsp;der&nbsp;neuen&nbsp;Seite</td></tr>
			<tr>
				<td><img src="'.site_url('img/admin/misc/world.png').'" alt="" />&nbsp;<b><i>Webseite</i></b></td>
				<td>'.form_radio('pgPos', 'FirstRootChild', ($fdat['pgPos'] == 'FirstRootChild' ? true : false), array('id' => 'pgPost-FirstRootChild')).form_label($img_in.'unterhalb dieser', 'pgPost-FirstRootChild').'</td>
			</tr>';
		
		$this->Content .= '</table>';
		// Finish form
		$this->Content .= '<p>'.form_submit('CreateDo', 'Neue Seite erstellen').' <b>oder</b> <a href="'.$this->module_url().'">Zurück</a></p>'.form_close();
		
		// Show page
		$this->RenderPage();
	}
}
?>