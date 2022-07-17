<?php
class Module_action extends MY_Model 
{
    function __construct()
    {
        parent::__construct();
    }
	
	function get_module_action_name($module_id, $action_id)
	{
		$query = $this->db->get_where('modules_actions', array('module_id' => $module_id, 'action_id' => $action_id), 1);
		
		if ($query->num_rows() ==1)
		{
			$row = $query->row();
			return lang($row->action_name_lang_key);
		}
		
		$this->lang->load('error');
		return lang('error_unknown');
	}
		
	function get_module_actions($module_id)
	{
		$this->db->from('modules_actions');
		$this->db->where('module_id', $module_id);
		$this->db->order_by("sort", "asc");
		return $this->db->get();		
	}
	
	function get_allowed_module_actions($person_id)
	{
		$query = $this->db->get_where('permissions_actions', array('person_id' => $person_id));
		
		$return = array();
		
		foreach($query->result_array() as $row)
		{
			$return[] = $row['module_id'].'|'.$row['action_id'];
		}
		
		return $return;
	}

}
?>
