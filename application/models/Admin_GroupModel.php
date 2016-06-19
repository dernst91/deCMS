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
}
?>