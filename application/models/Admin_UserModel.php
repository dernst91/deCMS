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
}
?>