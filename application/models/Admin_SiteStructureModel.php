<?php
class Admin_SiteStructureModel extends CI_Model {
	public function GetFirstPID()
	{
		$this->db->select('PageID');
		$this->db->from('structure');
		$this->db->where('PIDparent', NULL);
		$this->db->where('PIDprev', NULL);
		$this->db->limit(1);
		$query = $this->db->get();
		if($query->num_rows())
			return $query->row()->PageID;
		else
			return FALSE;
	}
}
?>