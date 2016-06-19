<?php
class Admin_UserModel extends CI_Model {
	public function GetUserList($order_by = 'Username') {
		$this->db->select('UID, Username, FirstName, LastName, EMail, Active, LastLogin');
		$this->db->from('admin_user');
		$this->db->order_by($order_by);
		$query = $this->db->get();
		return $query->result();
	}
	
	public function GetUserData($UID)
	{
		$this->db->select('UID, Username, FirstName, LastName, EMail, Active, LastLogin');
		$this->db->from('admin_user');
		$this->db->where('UID', $UID);
		$query = $this->db->get();
		if($query->num_rows() == 0) return FALSE;
		return $query->row();
	}
	
	public function DeleteUser($UID)
	{
		// Delete group memberships
		$this->db->delete('admin_group_membership', array('UID' => $UID));
		
		// Delete permissions
		$this->db->delete('admin_permission', array('PermissionType' => 'u', 'GUID' => $UID));
		
		// Delete user
		$this->db->delete('admin_user', array('UID' => $UID));
		
		return true;
	}
}
?>