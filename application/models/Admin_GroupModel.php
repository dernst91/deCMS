<?php
class Admin_GroupModel extends CI_Model {
	public function GetGroupData($GID)
	{
		$this->db->select('GID, GroupName');
		$this->db->from('admin_group');
		$this->db->where('GID', $GID);
		$query = $this->db->get();
		if($query->num_rows() == 0) return FALSE;
		return $query->row();
	}
	
	public function GetGroupList()
	{
		$this->db->select('GID, GroupName');
		$this->db->from('admin_group');
		$this->db->order_by('GroupName');
		$query = $this->db->get();
		if($query->num_rows() == 0) return FALSE;
		return $query->result();
	}
	
	public function GetGroupMembers($GID)
	{
		$this->db->select('UID');
		$this->db->from('admin_group_membership');
		$this->db->where('GID', $GID);
		$query = $this->db->get();
		$uids = array();
		foreach($query->result() as $row)
			$uids[] = $row->UID;
		return $uids;
	}
	
	public function DeleteGroup($GID)
	{
		// Delete group memberships
		$this->db->delete('admin_group_membership', array('GID' => $GID));
		
		// Delete permissions
		$this->db->delete('admin_permission', array('PermissionType' => 'g', 'GUID' => $GID));
		
		// Delete group
		$this->db->delete('admin_group', array('GID' => $GID));
		
		return true;
	}
}
?>