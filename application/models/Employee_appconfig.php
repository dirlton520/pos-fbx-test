<?php
class Employee_appconfig extends MY_Model 
{
	
	function exists($key,$employee_id = FALSE)
	{
		if (!$employee_id)
		{
			$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
		}
		
		$this->db->from('employees_app_config');	
		$this->db->where('employees_app_config.key',$key);
		$this->db->where('employees_app_config.employee_id',$employee_id);
		$query = $this->db->get();
		
		return ($query->num_rows()==1);
	}
	
	function get_key_for_all_employees($key)
	{
		$this->db->from('employees_app_config');	
		$this->db->where('employees_app_config.key',$key);
		$query = $this->db->get();
		
		return $query->result_array();
	}
	
	function get($key,$employee_id = FALSE)
	{
		if (!$employee_id)
		{
			$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
		}
		
		$this->db->from('employees_app_config');	
		$this->db->where('employees_app_config.key',$key);
		$this->db->where('employees_app_config.employee_id',$employee_id);
		$query = $this->db->get();
		
		if($query->num_rows()==1)
		{
			return $query->row()->value;
		}
		
		return NULL;
	}
	
	function save($key,$value,$employee_id = FALSE)
	{
		if (!$employee_id)
		{
			$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
		}
		
		$config_data=array(
		'employee_id' => $employee_id,
		'key'=>$key,
		'value'=>$value,
		);
		return $this->db->replace('employees_app_config', $config_data);
	}
	
	function delete($key,$employee_id = FALSE)
	{
		if (!$employee_id)
		{
			$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
		}
			
		return $this->db->delete('employees_app_config',array('employee_id' => $employee_id,'key' => $key));
	}
}

?>