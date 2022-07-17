<?php
class Appointment extends MY_Model
{
	public function __construct()
	{
      parent::__construct();
			$this->load->model('Inventory');	
	}
	
	public function get_info($appointment_id)
	{
		$this->db->from('appointments');
		$this->db->where('id',$appointment_id);
		$query = $this->db->get();
		
		if($query->num_rows()==1)
		{
			return $query->row();
		}
		else
		{
			//Get empty base parent object, as $item_id is NOT an item
			$appt_obj=new stdClass();

			//Get all the fields from items table
			$fields = $this->db->list_fields('appointments');

			foreach ($fields as $field)
			{
				$appt_obj->$field='';
			}			

			return $appt_obj;
		}
	}
		
	
	/*
	Perform a search on appointments
	*/
	function search($search, $deleted = 0, $limit=20, $offset=0, $column='start_time', $orderby='desc',$location_id_override = NULL)
	{
		if (!$deleted)
		{
			$deleted = 0;
		}
		
		$location_id = $location_id_override ? $location_id_override : $this->Employee->get_logged_in_employee_current_location_id();
		
		$this->db->select('appointment_types.name as appointment_type,appointments.*, person.*,CONCAT(employee.first_name, " ", employee.last_name) as employee, CONCAT(person.first_name, " ", person.last_name) as person, person.last_name as person_last_name', false);
		$this->db->from('appointments');
		$this->db->join('appointment_types', 'appointment_types.id = appointments.appointments_type_id','left');
		
		$this->db->join('people as person', 'person.person_id = appointments.person_id','left');
		$this->db->join('people as employee', 'employee.person_id = appointments.employee_id','left');
		
		$this->db->where('appointments.deleted', $deleted);
		$this->db->where('location_id', $location_id);
				
		if ($search)
		{
			$this->db->where("(person.first_name LIKE '".$this->db->escape_like_str($search)."%' or 
			person.last_name LIKE '".$this->db->escape_like_str($search)."%' or 
			person.email LIKE '".$this->db->escape_like_str($search)."%' or 
			person.phone_number LIKE '".$this->db->escape_like_str($search)."%' or 
			notes LIKE '".$this->db->escape_like_str($search)."%' or 
			person.full_name LIKE '".$this->db->escape_like_str($search)."%') and appointments.deleted=$deleted");		
			
			$this->db->or_where("(employee.first_name LIKE '".$this->db->escape_like_str($search)."%' or 
			employee.last_name LIKE '".$this->db->escape_like_str($search)."%' or 
			employee.email LIKE '".$this->db->escape_like_str($search)."%' or 
			employee.phone_number LIKE '".$this->db->escape_like_str($search)."%' or 
			notes LIKE '".$this->db->escape_like_str($search)."%' or 
			employee.full_name LIKE '".$this->db->escape_like_str($search)."%') and appointments.deleted=$deleted");		
			
		}
				
		$this->db->order_by($column, $orderby);
		$this->db->limit($limit);
		$this->db->offset($offset);
		
		
	 return $this->db->get();
		
	}
	
	function search_count_all($search, $deleted = 0,$limit=10000,$location_id_override = NULL)
	{
		if (!$deleted)
		{
			$deleted = 0;
		}
		
		$location_id = $location_id_override ? $location_id_override : $this->Employee->get_logged_in_employee_current_location_id();
		
		$this->db->select('appointments.*, person.*,CONCAT(employee.first_name, " ", employee.last_name) as employee, CONCAT(person.first_name, " ", person.last_name) as person, person.last_name as person_last_name', false);
		$this->db->from('appointments');
		$this->db->join('people as person', 'person.person_id = appointments.person_id','left');
		$this->db->join('people as employee', 'employee.person_id = appointments.employee_id','left');
		
		$this->db->where('appointments.deleted', $deleted);
		$this->db->where('location_id', $location_id);
				
		if ($search)
		{
			$this->db->where("(person.first_name LIKE '".$this->db->escape_like_str($search)."%' or 
			person.last_name LIKE '".$this->db->escape_like_str($search)."%' or 
			person.email LIKE '".$this->db->escape_like_str($search)."%' or 
			person.phone_number LIKE '".$this->db->escape_like_str($search)."%' or 
			notes LIKE '".$this->db->escape_like_str($search)."%' or 
			person.full_name LIKE '".$this->db->escape_like_str($search)."%') and deleted=$deleted");		
			
			$this->db->or_where("(employee.first_name LIKE '".$this->db->escape_like_str($search)."%' or 
			employee.last_name LIKE '".$this->db->escape_like_str($search)."%' or 
			employee.email LIKE '".$this->db->escape_like_str($search)."%' or 
			employee.phone_number LIKE '".$this->db->escape_like_str($search)."%' or 
			notes LIKE '".$this->db->escape_like_str($search)."%' or 
			employee.full_name LIKE '".$this->db->escape_like_str($search)."%') and appointments.deleted=$deleted");		
			
		}
						
		return $this->db->count_all_results();
		
	}
	
	/*
	Get search suggestions to find appointments
	*/
	function get_search_suggestions($search,$deleted=0,$limit=5)
	{
		if (!trim($search))
		{
			return array();
		}
		if (!$deleted)
		{
			$deleted = 0;
		}
		
			$suggestions = array();
			$location_id = $this->Employee->get_logged_in_employee_current_location_id();
			
			$this->db->select('appointments.*, person.*,CONCAT(employee.first_name, " ", employee.last_name) as employee, CONCAT(person.first_name, " ", person.last_name) as person, person.last_name as person_last_name', false);
			$this->db->from('appointments');
			$this->db->join('people as person', 'person.person_id = appointments.person_id','left');
			$this->db->join('people as employee', 'employee.person_id = appointments.employee_id','left');
			
			$this->db->where('appointments.deleted', $deleted);
			$this->db->where('location_id', $location_id);
			
			$this->db->where("(person.first_name LIKE '".$this->db->escape_like_str($search)."%' or
			CONCAT(person.`first_name`,' ',person.`last_name`) LIKE '".$this->db->escape_like_str($search)."%' or 
		  person.last_name LIKE '".$this->db->escape_like_str($search)."%' or 
		  notes LIKE '".$this->db->escape_like_str($search)."%' or 
			CONCAT(person.`last_name`,', ',person.`first_name`) LIKE '".$this->db->escape_like_str($search)."%')");		
			
			$this->db->limit($limit);
			
			$query=$this->db->get();
			
			$temp_suggestions = array();
						
			foreach($query->result() as $row)
			{
				$data = array(
					'name' => $row->first_name . ' ' .  $row->last_name,
					'subtitle' => '',
					'avatar' => base_url()."assets/img/user.png",
					 );
				$temp_suggestions[$row->id] = $data;
			}
		
		
			$this->load->helper('array');
			uasort($temp_suggestions, 'sort_assoc_array_by_name');
			
			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['name'],'avatar'=>$value['avatar'],'subtitle'=>$value['subtitle']);		
			}
		
			$this->db->select('appointments.*, employee.*,CONCAT(employee.first_name, " ", employee.last_name) as employee, CONCAT(person.first_name, " ", person.last_name) as person, person.last_name as person_last_name', false);
			$this->db->from('appointments');
			$this->db->join('people as person', 'person.person_id = appointments.person_id','left');
			$this->db->join('people as employee', 'employee.person_id = appointments.employee_id','left');
			
			$this->db->where('appointments.deleted', $deleted);
			$this->db->where('location_id', $location_id);
			
			$this->db->where("(employee.first_name LIKE '".$this->db->escape_like_str($search)."%' or
			CONCAT(employee.`first_name`,' ',employee.`last_name`) LIKE '".$this->db->escape_like_str($search)."%' or 
		  employee.last_name LIKE '".$this->db->escape_like_str($search)."%' or 
		  notes LIKE '".$this->db->escape_like_str($search)."%' or 
			CONCAT(employee.`last_name`,', ',employee.`first_name`) LIKE '".$this->db->escape_like_str($search)."%')");		
			
			$this->db->limit($limit);
			
			$query=$this->db->get();
			
			$temp_suggestions = array();
						
			foreach($query->result() as $row)
			{
				$data = array(
					'name' => $row->first_name . ' ' .  $row->last_name,
					'subtitle' => '',
					'avatar' => base_url()."assets/img/user.png",
					 );
				$temp_suggestions[$row->id] = $data;
			}
		
		
			uasort($temp_suggestions, 'sort_assoc_array_by_name');
			
			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['name'],'avatar'=>$value['avatar'],'subtitle'=>$value['subtitle']);		
			}
		
			return $suggestions;
	}	
	
	function get_all_for_range($deleted=0,$start_date=NULL,$end_date=NULL,$col='start_time')
	{	
		if (!$deleted)
		{
			$deleted = 0;
		}
		
		$location_id = $this->Employee->get_logged_in_employee_current_location_id();
		$this->db->select('appointment_types.name as type, appointments.*, CONCAT(employee.first_name, " ", employee.last_name) as employee, CONCAT(person.first_name, " ", person.last_name) as person, person.last_name as person_last_name', false);
		$this->db->from('appointments');
		$this->db->join('appointment_types', 'appointment_types.id = appointments.appointments_type_id','left');
		
		$this->db->join('people as person', 'person.person_id = appointments.person_id','left');
		$this->db->join('people as employee', 'employee.person_id = appointments.employee_id','left');
		$this->db->where('appointments.deleted', $deleted);
		$this->db->where('location_id', $location_id);
		$this->db->where($col. ' >= ',$start_date);
		$this->db->where($col. ' <= ',$end_date.' 23:59:59');
		
		$this->db->order_by($col);
		return $this->db->get();
	}
	
	
	function get_all($deleted=0,$limit=10000, $offset=0,$col='start_time',$order='desc',$location_id_override = NULL)
	{	
		if (!$deleted)
		{
			$deleted = 0;
		}
		
	$location_id = $location_id_override ? $location_id_override : $this->Employee->get_logged_in_employee_current_location_id();
	$this->db->select('appointment_types.name as appointment_type,appointments.*, CONCAT(employee.first_name, " ", employee.last_name) as employee, CONCAT(person.first_name, " ", person.last_name) as person, person.last_name as person_last_name', false);
	$this->db->from('appointments');
	$this->db->join('appointment_types', 'appointment_types.id = appointments.appointments_type_id','left');
	$this->db->join('people as person', 'person.person_id = appointments.person_id','left');
	$this->db->join('people as employee', 'employee.person_id = appointments.employee_id','left');
	$this->db->where('appointments.deleted', $deleted);
	$this->db->where('location_id', $location_id);
	$this->db->order_by($col, $order);
	$this->db->limit($limit);
	$this->db->offset($offset);
  return $this->db->get();
		
	}
	
	function count_all($deleted=0,$location_id_override = NULL)
	{
		if (!$deleted)
		{
			$deleted = 0;
		}
		
		$location_id = $location_id_override ? $location_id_override : $this->Employee->get_logged_in_employee_current_location_id();
		$this->db->select('appointment_types.name as appointment_type,appointments.*, CONCAT(employee.first_name, " ", employee.last_name) as employee, CONCAT(person.first_name, " ", person.last_name) as person, person.last_name as person_last_name', false);
		$this->db->from('appointments');
		$this->db->join('people as person', 'person.person_id = appointments.person_id','left');
		$this->db->join('people as employee', 'employee.person_id = appointments.employee_id','left');
		$this->db->where('appointments.deleted', $deleted);
		$this->db->where('location_id', $location_id);
		return $this->db->count_all_results();		
	}
	
	function exists($id)
	{
		$this->db->from('appointments');
		$this->db->where('id',$id);
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}
	
	/*
	Inserts or updates a appointment
	*/
	function save(&$appointment_data, $appointment_id = false)
	{		
		if (!$appointment_id or !$this->exists($appointment_id))
		{
			if($this->db->insert('appointments',$appointment_data))
			{
				$appointment_data['id']=$this->db->insert_id();
				return true;
			}
			return false;
		}
		
		$this->db->where('id', $appointment_id);
		return $this->db->update('appointments',$appointment_data);
		
	}
	
	function delete($id)
	{	
		$this->db->where('id', $id);
		return $this->db->update('appointments', array('deleted' => 1));
	}
	
	function delete_list($appointment_ids)
	{
		foreach($appointment_ids as $appointment_id)
		{
			$result = $this->delete($appointment_id);
			
			if(!$result)
			{
				return false;
			}
		}
		
		return true;
 	}
		
	function undelete($id)
	{	
		$this->db->where('id', $id);
		return $this->db->update('appointments', array('deleted' => 0));
	}
	
	function undelete_list($appointment_ids)
	{
		foreach($appointment_ids as $appointment_id)
		{
			$result = $this->undelete($appointment_id);
			
			if(!$result)
			{
				return false;
			}
		}
		
		return true;
 	}
		
	function get_displayable_columns()
	{
		return array(
		);
	}
	
	function get_default_columns()
	{
		return array();
	}
	
	
  function get_info_category($appointment_type_id) {
      $this->db->from('appointment_types');
      $this->db->where('id', $appointment_type_id);
      $query = $this->db->get();

      if ($query->num_rows() == 1) {
          return $query->row();
      } else {
          //Get empty base parent object, as $supplier_id is NOT an supplier
          $fields = $this->db->list_fields('appointment_types');
          $appointment_type_obj = new stdClass;
          //Get all the fields from Expenses table
          $fields = $this->db->list_fields('appointment_types');
          //append those fields to base parent object, we we have a complete empty object
          foreach ($fields as $field) {
              $appointment_type_obj->$field = '';
          }
          return $appointment_type_obj;
      }
  }

	
	public function get_all_categories($limit=10000, $offset=0,$col='name',$order='asc')
	{
		$this->db->from('appointment_types');
		$this->db->where('deleted', 0);
		
		if (!$this->config->item('speed_up_search_queries'))
		{
			$this->db->order_by($col, $order);
		}
		
		$this->db->limit($limit);
		$this->db->offset($offset);
		
		$return = array();
		
		foreach($this->db->get()->result_array() as $result)
		{
			$return[$result['id']] = array('name' => $result['name']);
		}
		
		return $return;
	}
	
	public function count_all_categories()
	{
		$this->db->from('appointment_types');
		$this->db->where('deleted', 0);
		return $this->db->count_all_results();
	}
	
	
	public function save_category($category_name, $category_id = -1)
	{
		if ($category_id == -1)
		{
			if($this->db->insert('appointment_types',array('name' => $category_name)))
			{
				return $this->db->insert_id();
			}
		}
		else
		{
			
			$this->db->where('id',$category_id);
			$this->db->update('appointment_types',array('name' => $category_name));
			return $category_id;
		}
		
		return FALSE;
	}
	
	public function delete_category($category_id)
	{
		$this->db->where('id',$category_id);
		$this->db->update('appointment_types',array('deleted' => 1));
		
		return TRUE;
	}
	
	
  function search_category_count_all($search, $deleted=0,$limit = 10000) 
	{
		if (!$deleted)
		{
			$deleted = 0;
		}
		
		$this->db->from('appointment_types');
				 
		if ($search)
		{
				$this->db->where("appointment_types.name LIKE '".$this->db->escape_like_str($search)."%' and deleted=$deleted");			
		}
		else
		{
			$this->db->where('appointment_types.deleted',$deleted);
		}

		$this->db->limit($limit);
	    $result = $this->db->get();
	    return $result->num_rows();
	 }

  /*
    Preform a search on tags
   */

  function search_category($search, $deleted=0,$limit = 20, $offset = 0, $column = 'id', $orderby = 'asc') 
	{
				
		if (!$deleted)
		{
			$deleted = 0;
		}
		
		
 		$this->db->from('appointment_types');
		if ($search)
		{
				$this->db->where("appointment_types.name LIKE '".$this->db->escape_like_str($search)."%' and deleted=$deleted");			
		}
		else
		{
			$this->db->where('appointment_types.deleted',$deleted);
		}
	
     $this->db->order_by($column,$orderby);
 
     $this->db->limit($limit);
    $this->db->offset($offset);
		$return = array();
		
		foreach($this->db->get()->result_array() as $result)
		{
			$return[$result['id']] = array('name' => $result['name']);
		}
		
		return $return;
		
  }
	
	function send_email($appointments_request){
		if($appointments_request['person_id']){
			$person_info = $this->Person->get_info($appointments_request['person_id']);
			$employee_info = $this->Person->get_info($appointments_request['employee_id']);
			if($person_info->email){
				$this->load->library('email');
				$config['mailtype'] = 'html';
				$this->email->initialize($config);
				$company = ($company = $this->Location->get_info_for_key('company', $appointments_request["location_id"])) ? $company : $this->config->item('company');
				$this->email->subject(lang('appointments_one_or_multiple') .", ".$company.", ".date(get_date_format().' '.get_time_format(),strtotime($appointments_request["start_time"])));
				$this->email->from($this->Location->get_info_for_key('email') ? $this->Location->get_info_for_key('email') : 'no-reply@mg.phppointofsale.com', $this->config->item('company'));
				$this->email->to($person_info->email);

				$cc_mail = [];
				if($this->Location->get_info_for_key('cc_email')){
					$cc_mail[] = $this->Location->get_info_for_key('cc_email');
				}

				if($employee_info->email){
					$cc_mail[] = $employee_info->email;
				}
				$this->email->cc($cc_mail);
				
				$this->email->bcc($this->Location->get_info_for_key('bcc_email') ? $this->Location->get_info_for_key('bcc_email') : "");

				$data = array(
					"company_name" => $company,
					"appointment_data" => $appointments_request
				);

				$this->email->message($this->load->view("appointments/email_appointment",$data, true));
				return $this->email->send();
			}
		}
		return false;
	}
}
