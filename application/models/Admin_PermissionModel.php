<?php
class Admin_PermissionModel extends CI_Model {
	public function GetUserPermissions($UID = NULL)
	{
		// Array to store all AIDs
		$aids = array();
		// Array to store final (user) permissions
		$perms = array();
		$userperms = array();
		
		// Request AIDs for UID
		$this->db->select('AID');
		$this->db->from('admin_permission');
		$this->db->where('PermissionType', 'u');
		$this->db->where('GUID', $UID);
		$query = $this->db->get();
		foreach($query->result() as $row)
			$aids[] = $row->AID;
			
		// Request UIDs groups
		$groups = array();
		$this->db->select('GID');
		$this->db->from('admin_group_membership');
		$this->db->where('UID', $UID);
		$query = $this->db->get();
		foreach($query->result() as $row)
			$groups[] = $row->GID;
			
		// Request AIDs for UIDs groups
		if(sizeof($groups))
		{
			$this->db->select('AID');
			$this->db->from('admin_permission');
			$this->db->where('PermissionType', 'g');
			$this->db->where_in('GUID', $groups);
			$query = $this->db->get();
			foreach($query->result() as $row)
				$aids[] = $row->AID;
		}
		
		// Remove double permission elements
		$aids = array_unique($aids);
			
		// Request available permissions
		$this->db->select('AID, ModuleName, Action');
		$this->db->from('admin_module');
		$this->db->join('admin_module_action', 'admin_module.MID = admin_module_action.MID');
		$query = $this->db->get();
		foreach($query->result() as $row)
			$perms[$row->AID] = $row->ModuleName.'/'.$row->Action;
		
		// Determine users permissions
		foreach($aids as $aid)
			$userperms[] = strtolower($perms[$aid]);
		
		return $userperms;
	}
}
?>