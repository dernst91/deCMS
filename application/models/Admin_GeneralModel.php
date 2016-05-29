<?php
class Admin_GeneralModel extends CI_Model {
	public function GetAdminModuleCategoryList()
	{
		$this->db->select('CID, CategoryName');
		$this->db->from('admin_module_category');
		$this->db->order_by('Ordering');
		$query = $this->db->get();
		if($query->num_rows())
			return $query;
		else
			return FALSE;
	}
	
	public function GetAdminModuleList()
	{
		$this->db->select('MID, CID, ModuleName, DisplayName');
		$this->db->from('admin_module');
		$this->db->order_by('Ordering');
		$query = $this->db->get();
		if($query->num_rows())
			return $query;
		else
			return FALSE;
	}
}
?>