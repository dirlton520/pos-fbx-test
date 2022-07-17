<?php
class Register extends MY_Model
{
	
	function get_default_register_info($location_id = FALSE)
	{
		if (!$location_id)
		{
			$location_id=$this->Employee->get_logged_in_employee_current_location_id();
		}
		
		$this->db->from('registers');	
		$this->db->where('location_id',$location_id);
		$this->db->limit(1);
		$query = $this->db->get();
		
		if($query->num_rows()==1)
		{
			$row = $query->row();
			return $this->get_info($row->register_id);
			
		}
		
		return $this->get_info(1);
		
	}
	
	/*
	Gets information about a particular register
	*/
	function get_info($register_id)
	{
		$this->db->from('registers');	
		$this->db->where('register_id',$register_id);
		$query = $this->db->get();
		
		if($query->num_rows()==1)
		{
			return $query->row();
		}
		else
		{
			$register_obj = new stdClass;
			
			//Get all the fields from registers table
			$fields = array('emv_pinpad_ip','emv_pinpad_port','register_id','location_id','name','iptran_device_id','emv_terminal_id','deleted');
						
			//append those fields to base parent object, we we have a complete empty object
			foreach ($fields as $field)
			{
				$register_obj->$field='';
			}
			
			return $register_obj;
		}
	}
	
	function get_register_name($register_id)
	{
		$info = $this->get_info($register_id);
		
		if ($info && $info->name)
		{
			return $info->name;
		}
		
		return false;
	}
	
	/*
	Determines if a given register_id is a register
	*/
	function exists($register_id)
	{
		$this->db->from('registers');	
		$this->db->where('register_id',$register_id);
		$query = $this->db->get();
		
		return ($query->num_rows()==1);
	}
	
	function get_all($location_id = false)
	{
		if (!$location_id)
		{
			$location_id=$this->Employee->get_logged_in_employee_current_location_id();
		}
		
		$this->db->from('registers');
		$this->db->where('location_id', $location_id);
		$this->db->where('deleted', 0);
		$this->db->order_by('register_id');
		return $this->db->get();
	}
	
	function get_all_open($location_id = false)
	{
		if (!$location_id)
		{
			$location_id=$this->Employee->get_logged_in_employee_current_location_id();
		}
		
		$this->db->select('registers.*');
		$this->db->from('registers');
		$this->db->join('register_log', 'registers.register_id = register_log.register_id');
		$this->db->where('shift_end','0000-00-00 00:00:00');
		$this->db->where('registers.deleted', 0);
		$this->db->where('location_id', $location_id);
		$this->db->order_by('register_id');
		return $this->db->get();
	}

	function count_all($location_id = false)
	{
		if (!$location_id)
		{
			$location_id=$this->Employee->get_logged_in_employee_current_location_id();
		}
		
		$this->db->from('registers');
		$this->db->where('location_id', $location_id);
		$this->db->where('deleted', 0);
		return $this->db->count_all_results();
	}
	
	/*
	Inserts or updates a register
	*/
	function save(&$register_data,$register_id=false)
	{
		if (!$register_id or !$this->exists($register_id))
		{
			if($this->db->insert('registers',$register_data))
			{
				$register_data['register_id']=$this->db->insert_id();
				return true;
			}
			return false;
		}

		$this->db->where('register_id', $register_id);
		return $this->db->update('registers',$register_data);
	}
	
	function delete($register_id)
	{
		$this->db->where('register_id', $register_id);
		return $this->db->update('registers', array('deleted' => 1));
	}
	
	function get_register_currency_denominations()
	{
		$this->db->from('register_currency_denominations');
		$this->db->where('deleted',0);
		$this->db->order_by('id');
		return $this->db->get();
	}
	
	//TODO fix to use ids for update
	function save_register_currency_denominations($names, $values,$ids,$deleted_ids)
	{		
		for($k = 0; $k< count($names); $k++)
		{			
			$name = $names[$k];
			$value = $values[$k];
			$id = $ids[$k];
			
			
			$this->db->from('register_currency_denominations');
			$this->db->where('id',$id);
			$query = $this->db->get();
		  $exists = $query->num_rows();
			
			if(!$exists)
			{
				$this->db->insert('register_currency_denominations', array('name' => $name, 'value' => (float)$value));
			}
			else
			{
				$this->db->where('id',$id);
				$this->db->update('register_currency_denominations', array('name' => $name, 'value' => (float)$value));
			}
		}
		
		if (!empty($deleted_ids))
		{
			$this->db->where_in('id',$deleted_ids);
			$this->db->update('register_currency_denominations',array('deleted' => 1));
		}
		return true;
	}
	
	/**
	 * added for cash register
	 * insert a log for track_cash_log
	 * @param array $data
	 */
	
	function update_register_log($data,$register_id = false) {
		
		if (!$register_id)
		{
			$register_id = $this->Employee->get_logged_in_employee_current_register_id();
		}
		
		$this->db->where('shift_end','0000-00-00 00:00:00');
		$this->db->where('register_id', $register_id);
		$return = $this->db->update('register_log', $data) ? true : false;		
		return $return;
	}

	function get_existing_register_log($register_log_id) {

		$this->db->from('register_log');
		$this->db->where('register_log_id',$register_log_id);
		$this->db->where('deleted', 0);
		$query = $this->db->get();
		
		if($query->num_rows())
		return $query->row();
		else
		return false;

	}
	
	function get_last_closing_register_log_id($register_id)
	{
		$this->db->from('register_log');
		$this->db->where('register_id',$register_id);
		$this->db->order_by('register_log_id DESC');
		$this->db->where('deleted', 0);
		$query = $this->db->get();
		
		if($query->num_rows())
		return $query->row()->register_log_id;
		else
		return 0;
		
	}

	function get_closing_amounts($register_log_id)
	{
		$this->db->from('register_log_payments');
		$this->db->join('register_log','register_log.register_log_id = register_log_payments.register_log_id');
		$this->db->where('register_log_payments.register_log_id',$register_log_id);
		$this->db->where('deleted', 0);
		$query = $this->db->get();
		if($query->num_rows())
		{
			$return = array();
			$payments = $query->result_array();
			
			foreach($payments as $payment)
			{
				$return[$payment['payment_type']] = $payment['close_amount'];
			}
			
			return $return;
		}
		else
		{
			return NULL;
		}
	}
	
	function get_opening_amounts($register_log_id)
	{
		$this->db->from('register_log_payments');
		$this->db->join('register_log','register_log.register_log_id = register_log_payments.register_log_id');
		$this->db->where('register_log_payments.register_log_id',$register_log_id);
		$this->db->where('deleted', 0);
		$query = $this->db->get();
		
		if($query->num_rows())
		{
			$return = array();
			$payments = $query->result_array();
			
			foreach($payments as $payment)
			{
				$return[$payment['payment_type']] = $payment['open_amount'];
			}
			
			return $return;
		}
		else
		{
			return NULL;
		}
	}
	
	function get_total_payment_additions($register_log_id)
	{
		$this->db->from('register_log_payments');
		$this->db->join('register_log','register_log.register_log_id = register_log_payments.register_log_id');
		$this->db->where('register_log_payments.register_log_id',$register_log_id);
		$this->db->where('deleted', 0);
		$query = $this->db->get();
		
		if($query->num_rows())
		{
			$return = array();
			$payments = $query->result_array();
			
			foreach($payments as $payment)
			{
				$return[$payment['payment_type']] = $payment['total_payment_additions'];
			}
			
			return $return;
		}
		else
		{
			return NULL;
		}
	}
	
	function get_total_payment_subtractions($register_log_id)
	{
		$this->db->from('register_log_payments');
		$this->db->join('register_log','register_log.register_log_id = register_log_payments.register_log_id');
		$this->db->where('register_log_payments.register_log_id',$register_log_id);
		$this->db->where('deleted', 0);
		$query = $this->db->get();
		
		if($query->num_rows())
		{
			$return = array();
			$payments = $query->result_array();
			
			foreach($payments as $payment)
			{
				$return[$payment['payment_type']] = $payment['total_payment_subtractions'];
			}
			
			return $return;
		}
		else
		{
			return NULL;
		}
	}
	
	function get_closeout_amounts($register_log_id,$total_payments)
	{
		$this->db->from('register_log_payments');
		$this->db->join('register_log','register_log.register_log_id = register_log_payments.register_log_id');
		$this->db->where('register_log_payments.register_log_id',$register_log_id);
		$this->db->where('deleted', 0);
		$query = $this->db->get();
		
		if($query->num_rows())
		{
			$return = array();
			$payments = $query->result_array();
			
			foreach($payments as $payment)
			{
				$return[$payment['payment_type']] = $payment['open_amount'] + $total_payments[$payment['payment_type']] + $payment['total_payment_additions'] - $payment['total_payment_subtractions'];
			}
			
			return $return;
		}
		else
		{
			return NULL;
		}
	}
	
	

	function update_existing_register_log($data, $register_log_id) {
		$this->db->where('register_log_id', $register_log_id);
		return $this->db->update('register_log', $data) ? true : false;
	}


	function insert_register($register_data,$register_payment_data) {
		$register_log_id = $this->db->insert('register_log', $register_data) ? $this->db->insert_id() : false;
		
		if ($register_log_id)
		{
			foreach($register_payment_data as $register_payment)
			{
				$register_payment['register_log_id'] = $register_log_id;
				$this->db->insert('register_log_payments', $register_payment);
			}
		}	
		
		return false;
	}
	
	function update_register_log_payment($register_log_id,$payment_type,$payment_data)
	{
		$this->db->where('register_log_id',$register_log_id);
		$this->db->where('payment_type',$payment_type);
		return $this->db->update('register_log_payments',$payment_data);
	}
	
	function add_expense_amount_to_register_log($register_log_id,$payment_type,$expense_amount)
	{
		$this->db->where('register_log_id',$register_log_id);
		$this->db->where('payment_type',$payment_type);
		$this->db->set('total_payment_subtractions', "total_payment_subtractions+$expense_amount", FALSE);		
		return $this->db->update('register_log_payments');
	}
	
	
	function is_register_log_open()
	{
		$register_id = $this->Employee->get_logged_in_employee_current_register_id();

		$this->db->from('register_log');
		$this->db->where('shift_end','0000-00-00 00:00:00');
		$this->db->where('register_id',$register_id);
		$this->db->where('deleted', 0);
		$query = $this->db->get();
		if($query->num_rows())
		return true	;
		else
		return false;
	
	 }

	function get_current_register_log()
	{
		$register_id = $this->Employee->get_logged_in_employee_current_register_id();

		$this->db->from('register_log');
		$this->db->where('shift_end','0000-00-00 00:00:00');
		$this->db->where('register_id',$register_id);
		$this->db->where('deleted',0);
		
		$query = $this->db->get();
		if($query->num_rows())
		return $query->row();
		else
		return false;
	
	 }
	 
 	function get_register_log_by_id($id)
 	{
 		$register_id = $this->Employee->get_logged_in_employee_current_register_id();

 		$this->db->from('register_log');
 		$this->db->where('shift_end','0000-00-00 00:00:00');
 		$this->db->where('register_id',$id);
		$this->db->where('deleted',0);
		
 		$query = $this->db->get();
 		if($query->num_rows())
 		return $query->row();
 		else
 		return false;
	}
	 
	 
	 function get_register_log($id)
	 {
 		$this->db->select("registers.name as register_name, open_person.first_name as open_first_name, open_person.last_name as open_last_name, close_person.first_name as close_first_name, close_person.last_name as close_last_name, register_log_payments.*,register_log.*, (close_amount - open_amount - payment_sales_amount - total_payment_additions + total_payment_subtractions) as difference");
 		$this->db->from('register_log as register_log');
 		$this->db->join('people as open_person', 'register_log.employee_id_open=open_person.person_id');
 		$this->db->join('people as close_person', 'register_log.employee_id_close=close_person.person_id', 'left');
 		$this->db->join('registers', 'registers.register_id = register_log.register_id');
 		$this->db->join('register_log_payments', 'register_log.register_log_id = register_log_payments.register_log_id');
 		$this->db->where('register_log.register_log_id', $id);
		
 		$register_log = $this->db->get()->result();
		
		return $register_log;
	 }
	 
	 function get_register_log_details($id)
	 {
		$this->db->select('register_log_audit.*, CONCAT(employee.first_name, " ",employee.last_name) as employee_name', false);
  		$this->db->from('register_log_audit');
 		$this->db->join('people as employee', 'register_log_audit.employee_id=employee.person_id');
  		$this->db->where('register_log_id',$id);
		$this->db->order_by('id');
  		$query = $this->db->get();
  		if($query->num_rows())
  		return $query->result_array();
  		else
  		return false;
	 }
	 
	function insert_audit_log($data)
	{
 	  return $this->db->insert('register_log_audit', $data) ? $this->db->insert_id() : false;		
	}
	
  function search_count_all($search, $deleted=0,$limit = 10000,$location_id_override = NULL) {
		if (!$deleted)
		{
			$deleted = 0;
		}
		$location_id = $location_id_override ? $location_id_override : $this->Employee->get_logged_in_employee_current_location_id();
		
		$this->db->from('registers');
				 
	if ($search)
	{
			$this->db->where("(name LIKE '".$this->db->escape_like_str($search)."%') and ".$this->db->dbprefix('registers').".deleted=$deleted");			
	}
	else
	{
		$this->db->where('registers.deleted',$deleted);
	}
	
	$this->db->where('registers.location_id', $location_id);

	$this->db->limit($limit);
    $result = $this->db->get();
    return $result->num_rows();
  }

  /*
    Preform a search on registers
   */

  function search($search, $deleted=0,$limit = 20, $offset = 0, $column = 'register_id', $orderby = 'asc',$location_id_override = NULL) {
		
		$location_id = $location_id_override ? $location_id_override : $this->Employee->get_logged_in_employee_current_location_id();
		
		if (!$deleted)
		{
			$deleted = 0;
		}
		
		
 		$this->db->from('registers');
		
 		if ($search)
 		{
			$this->db->where("(name LIKE '".$this->db->escape_like_str($search)."%') and ".$this->db->dbprefix('registers').".deleted=$deleted");			
		}
		else
		{
			$this->db->where('registers.deleted',$deleted);
		}
		
		$this->db->where('registers.location_id', $location_id);
	
     $this->db->order_by($column,$orderby);
 
     $this->db->limit($limit);
    $this->db->offset($offset);
    return $this->db->get();
		
  }
	
	function insert_denoms($cash_register,$denoms,$type)
	{
		if (count($denoms) > 0)
		{
			$this->db->where('type',$type);
			$this->db->where('register_log_id',$cash_register->register_log_id);
			$this->db->delete('register_log_denoms');
		
			foreach($denoms as $id=>$count)
			{
				$insert =  array(
					'register_log_id' => $cash_register->register_log_id,
					'register_currency_denominations_id' => $id,
					'count' => $count,
					'type' => $type,
				); 
				
				$this->db->insert('register_log_denoms',$insert);
			}
		}
	}
	
	function get_cash_count_details($register_log_id,$type)
	{
		$this->db->select('count,name');
		$this->db->from('register_log_denoms');
		$this->db->join('register_currency_denominations','register_currency_denominations.id=register_log_denoms.register_currency_denominations_id');
		$this->db->where('type',$type);
		$this->db->where('register_log_id',$register_log_id);
		
		$return = array();
		$result = $this->db->get()->result_array();
		foreach($result as $row)
		{
			$return[$row['name']] = $row['count'];
		}
		return $return;
		
	}
	
  	public function get_first_register_id_by_location_id($location_id)
	{
		$this->db->from('registers');
		$this->db->where('deleted',0);
		$this->db->where('location_id',$location_id);
		$this->db->limit(1);
		return $this->db->get()->row()->register_id;
	}
}
?>
