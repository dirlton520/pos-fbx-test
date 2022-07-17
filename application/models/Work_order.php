<?php
class Work_order extends CI_Model
{
	public function __construct()
	{
      parent::__construct();
	}
	
	public function get_info($work_order_id)
	{
		$this->db->select('sales_work_orders.*,sales.sale_time,CONCAT(first_name, " ",last_name) as employee_name,people.email,people.phone_number,sales.customer_id');
		$this->db->from('sales_work_orders');
		$this->db->join('sales', 'sales.sale_id = sales_work_orders.sale_id');
		$this->db->join('people', 'people.person_id = sales_work_orders.employee_id','left');
		$this->db->where('id',$work_order_id);
		return $this->db->get();
	}
	
	
	function get_info_by_sale_id($sale_id)
	{
		$this->db->from('sales_work_orders');
		$this->db->where('sale_id',$sale_id);
		return $this->db->get();
	}
	
	/*
	Perform a search on work orders
	*/
	function search($search, $deleted = 0, $limit=20, $offset=0, $column='id', $orderby='desc',$status='',$technician='',$hide_completed_work_orders='')
	{
		if (!$deleted)
		{
			$deleted = 0;
		}
		
		$location_id = $this->Employee->get_logged_in_employee_current_location_id();
		$complete_status_id = $this->get_status_id_by_name('lang:work_orders_complete');

		$this->db->select('sales.suspended,sales_work_orders.*,sales.sale_time,sales.location_id as location_id,CONCAT(customer_person.address_1, " ", customer_person.address_2) as full_address,customer_person.*,CONCAT(employee_person.first_name, " ", employee_person.last_name) as technician_name,items.name as item_name_being_repaired');
		$this->db->from('sales_work_orders');
		$this->db->join('sales', 'sales.sale_id = sales_work_orders.sale_id');
		$this->db->join('people as customer_person', 'sales.customer_id = customer_person.person_id','left');
		$this->db->join('people as employee_person', 'sales_work_orders.employee_id = employee_person.person_id','left');
		$this->db->join('sales_items', 'sales_items.sale_id = sales_work_orders.sale_id and sales_items.line = 0','left');
		$this->db->join('items', 'items.item_id = sales_items.item_id','left');

		if ($search)
		{
			$this->db->where("(
			customer_person.first_name LIKE '".$this->db->escape_like_str($search)."%' or
			customer_person.last_name LIKE '".$this->db->escape_like_str($search)."%' or
			customer_person.address_1 LIKE '".$this->db->escape_like_str($search)."%' or
			customer_person.address_2 LIKE '".$this->db->escape_like_str($search)."%' or
			customer_person.city LIKE '".$this->db->escape_like_str($search)."%' or
			customer_person.state LIKE '".$this->db->escape_like_str($search)."%' or
			customer_person.zip LIKE '".$this->db->escape_like_str($search)."%' or
			sales_work_orders.sale_id  = ".$this->db->escape($search)." or
			customer_person.email LIKE '".$this->db->escape_like_str($search)."%' or 
			customer_person.phone_number LIKE '".$this->db->escape_like_str($search)."%' or
			CONCAT(customer_person.`first_name`,' ',customer_person.`last_name`) LIKE '".$this->db->escape_like_str($search)."%' or 
			CONCAT(customer_person.`last_name`,', ',customer_person.`first_name`) LIKE '".$this->db->escape_like_str($search)."%')");		
		}

		if($status){
			$this->db->where('sales_work_orders.status',$status);
		}
		
		if($technician){
			$this->db->where('sales_work_orders.employee_id',$technician);
		}
		
		if($hide_completed_work_orders){
			$this->db->where('sales_work_orders.status !=',$complete_status_id);
		}
		
		$this->db->where('sales.location_id',$location_id);
		$this->db->where('sales.deleted',0);
		$this->db->where('sales_work_orders.deleted',$deleted);
		
		if (!$this->config->item('speed_up_search_queries'))
		{
			$this->db->order_by($column, $orderby);
		}
		
		$this->db->limit($limit);
		$this->db->offset($offset);
		
		
	 	return $this->db->get();
		 
	}
	
	function search_count_all($search, $deleted = 0,$limit=10000,$status='',$technician='',$hide_completed_work_orders='')
	{
		if (!$deleted)
		{
			$deleted = 0;
		}
		$location_id = $this->Employee->get_logged_in_employee_current_location_id();
		$complete_status_id = $this->get_status_id_by_name('lang:work_orders_complete');

		$this->db->select('sales_work_orders.*,sales.sale_time,sales.location_id as location_id,CONCAT(customer_person.address_1, " ", customer_person.address_2) as full_address,customer_person.*');
		$this->db->from('sales_work_orders');
		$this->db->join('sales', 'sales.sale_id = sales_work_orders.sale_id');
		$this->db->join('people as customer_person', 'sales.customer_id = customer_person.person_id','left');
		
		if ($search)
		{
			$this->db->where("(
			customer_person.first_name LIKE '".$this->db->escape_like_str($search)."%' or
			customer_person.last_name LIKE '".$this->db->escape_like_str($search)."%' or
			customer_person.address_1 LIKE '".$this->db->escape_like_str($search)."%' or
			customer_person.address_2 LIKE '".$this->db->escape_like_str($search)."%' or
			customer_person.city LIKE '".$this->db->escape_like_str($search)."%' or
			customer_person.state LIKE '".$this->db->escape_like_str($search)."%' or
			customer_person.zip LIKE '".$this->db->escape_like_str($search)."%' or
			sales_work_orders.sale_id  = ".$this->db->escape($search)." or
			customer_person.email LIKE '".$this->db->escape_like_str($search)."%' or 
			customer_person.phone_number LIKE '".$this->db->escape_like_str($search)."%' or
			CONCAT(customer_person.`first_name`,' ',customer_person.`last_name`) LIKE '".$this->db->escape_like_str($search)."%' or 
			CONCAT(customer_person.`last_name`,', ',customer_person.`first_name`) LIKE '".$this->db->escape_like_str($search)."%')");		
		}

		if($status){
			$this->db->where('sales_work_orders.status',$status);
		}

		if($technician){
			$this->db->where('sales_work_orders.employee_id',$technician);
		}

		if($hide_completed_work_orders){
			$this->db->where('sales_work_orders.status !=',$complete_status_id);
		}
		
		$this->db->where('sales.location_id',$location_id);
		$this->db->where('sales.deleted',0);
		$this->db->where('sales_work_orders.deleted',$deleted);
		
		$this->db->limit($limit);
		
		return $this->db->count_all_results();
	}
	
	/*
	Get search suggestions to find deliveries
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

			$this->db->from('sales_work_orders');
			$this->db->join('sales', 'sales.sale_id = sales_work_orders.sale_id');
			
			$this->db->join('people', 'sales.customer_id = people.person_id','left');
			$this->db->where('sales.deleted',0);
			$this->db->where('sales_work_orders.deleted',$deleted);		
			$this->db->where("(first_name LIKE '".$this->db->escape_like_str($search)."%' or
			CONCAT(`first_name`,' ',`last_name`) LIKE '".$this->db->escape_like_str($search)."%' or 
		  last_name LIKE '".$this->db->escape_like_str($search)."%' or 
			CONCAT(`last_name`,', ',`first_name`) LIKE '".$this->db->escape_like_str($search)."%')");		
			$this->db->where('sales.location_id',$location_id);
			$this->db->limit($limit);
			
			$query=$this->db->get();
			
			$temp_suggestions = array();
						
			foreach($query->result() as $row)
			{
				$data = array(
					'name' => $row->first_name . ' ' .  $row->last_name,
					'subtitle' => $row->address_1 . ', ' . $row->address_2 . ', ' . $row->city . ', ' . $row->state . ', ' . $row->zip . ', ' . $row->country,
					'avatar' => base_url()."assets/img/giftcard.png",
					 );
				$temp_suggestions[$row->id] = $data;
			}
		
		
			$this->load->helper('array');
			uasort($temp_suggestions, 'sort_assoc_array_by_name');
			
			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['name'],'avatar'=>$value['avatar'],'subtitle'=>$value['subtitle']);		
			}


			$this->db->from('sales_work_orders');
			$this->db->join('sales', 'sales.sale_id = sales_work_orders.sale_id');
			
			$this->db->join('people', 'sales.customer_id = people.person_id','left');
		
			$this->db->where('sales.deleted',0);
			$this->db->where('sales_work_orders.deleted',$deleted);
			$this->db->where("(address_1 LIKE '".$this->db->escape_like_str($search)."%' or
			address_2 LIKE '".$this->db->escape_like_str($search)."%' or 
		  city LIKE '".$this->db->escape_like_str($search)."%' or 
		  state LIKE '".$this->db->escape_like_str($search)."%' or 
			zip LIKE '".$this->db->escape_like_str($search)."%')");		
			$this->db->where('sales.location_id',$location_id);
			
			$this->db->limit($limit);
			
			$query=$this->db->get();
			
			$temp_suggestions = array();
						
			foreach($query->result() as $row)
			{
				$data = array(
					'name' => $row->address_1 . ', ' . $row->address_2 . ', ' . $row->city . ', ' . $row->state . ', ' . $row->zip . ', ' . $row->country,
					'subtitle' => $row->first_name . ' ' .  $row->last_name,
					'avatar' => base_url()."assets/img/giftcard.png",
					 );
				$temp_suggestions[$row->id] = $data;
			}
		
		
			$this->load->helper('array');
			uasort($temp_suggestions, 'sort_assoc_array_by_name');
			
			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['name'],'avatar'=>$value['avatar'],'subtitle'=>$value['subtitle']);		
			}
			
			
			
			$this->db->from('sales_work_orders');
			$this->db->join('sales', 'sales.sale_id = sales_work_orders.sale_id');
			
			$this->db->join('people', 'sales.customer_id = people.person_id','left');
			$this->db->where("phone_number LIKE '".$this->db->escape_like_str($search)."%'");
			$this->db->where('sales.location_id',$location_id);
			$this->db->where('sales.deleted',0);
			$this->db->where('sales_work_orders.deleted',$deleted);
			
			$this->db->limit($limit);
			
			$query=$this->db->get();
			
			$temp_suggestions = array();
						
			foreach($query->result() as $row)
			{
				$data = array(
					'name' => $row->phone_number,
					'subtitle' => $row->first_name.' '.$row->last_name,
					'avatar' => base_url()."assets/img/giftcard.png",
					 );
				$temp_suggestions[$row->id] = $data;
			}
		
		
			$this->load->helper('array');
			uasort($temp_suggestions, 'sort_assoc_array_by_name');
			
			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['name'],'avatar'=>$value['avatar'],'subtitle'=>$value['subtitle']);		
			}


			
			
			$this->db->from('sales_work_orders');
			$this->db->join('sales', 'sales.sale_id = sales_work_orders.sale_id');
			
			$this->db->join('people', 'sales.customer_id = people.person_id','left');
			$this->db->where("email LIKE '".$this->db->escape_like_str($search)."%'");
			$this->db->where('sales.location_id',$location_id);
			$this->db->where('sales.deleted',0);
			$this->db->where('sales_work_orders.deleted',$deleted);
			
			$this->db->limit($limit);
			
			$query=$this->db->get();
			
			$temp_suggestions = array();
						
			foreach($query->result() as $row)
			{
				$data = array(
					'name' => $row->email,
					'subtitle' => $row->first_name.' '.$row->last_name,
					'avatar' => base_url()."assets/img/giftcard.png",
					 );
				$temp_suggestions[$row->id] = $data;
			}
		
		
			$this->load->helper('array');
			uasort($temp_suggestions, 'sort_assoc_array_by_name');
			
			foreach($temp_suggestions as $key => $value)
			{
				$suggestions[]=array('value'=> $key, 'label' => $value['name'],'avatar'=>$value['avatar'],'subtitle'=>$value['subtitle']);		
			}
			
			
			
		
		$suggestions = array_map("unserialize", array_unique(array_map("serialize", $suggestions)));
		if(count($suggestions) > $limit)
		{
			$suggestions = array_slice($suggestions, 0,$limit);
		}
		return $suggestions;
	
	}
	
	
	function get_all($deleted=0,$limit=10000, $offset=0,$col='id',$order='desc')
	{	
		if (!$deleted)
		{
			$deleted = 0;
		}
		
		$location_id = $this->Employee->get_logged_in_employee_current_location_id();
		$this->db->select('sales.suspended,sales_work_orders.*,sales.sale_time,sales.location_id as location_id,CONCAT(customer_person.address_1, " ", customer_person.address_2) as full_address,customer_person.*,CONCAT(employee_person.first_name, " ", employee_person.last_name) as technician_name,items.name as item_name_being_repaired');
		$this->db->from('sales_work_orders');
		$this->db->join('sales', 'sales.sale_id = sales_work_orders.sale_id');
		$this->db->join('people as customer_person', 'sales.customer_id = customer_person.person_id','left');
		$this->db->join('people as employee_person', 'sales_work_orders.employee_id = employee_person.person_id','left');
		$this->db->join('sales_items', 'sales_items.sale_id = sales_work_orders.sale_id and sales_items.line = 0','left');
		$this->db->join('items', 'items.item_id = sales_items.item_id','left');

		$this->db->where('sales.location_id',$location_id);
		$this->db->where('sales.deleted',0);
		$this->db->where('sales_work_orders.deleted',$deleted);
		if(!$this->config->item('speed_up_search_queries'))
		{
			$this->db->order_by($col, $order);
		}
		
		$this->db->limit($limit, $offset);
 		$return = $this->db->get();
 	 	return $return;
	}

	function get_by_id($id)
	{	
		
		$this->db->select('sales_work_orders.*,sales.sale_time,sales.location_id as location_id,CONCAT(customer_person.address_1, " ", customer_person.address_2) as full_address,customer_person.*');
		$this->db->from('sales_work_orders');
		$this->db->join('sales', 'sales.sale_id = sales_work_orders.sale_id');
		$this->db->join('people as customer_person', 'sales.customer_id = customer_person.person_id','left');
				
		$this->db->where('sales_work_orders.id',$id);
		
		return $this->db->get()->row();
	}
	
	function count_all($deleted=0)
	{
		if (!$deleted)
		{
			$deleted = 0;
		}
		
		$location_id = $this->Employee->get_logged_in_employee_current_location_id();
		
		$this->db->from('sales_work_orders');
		$this->db->join('sales', 'sales.sale_id = sales_work_orders.sale_id');
		$this->db->where('sales.location_id',$location_id);
		$this->db->where('sales.deleted',0);
		$this->db->where('sales_work_orders.deleted',$deleted);
		return $this->db->count_all_results();
	}
	
	function exists($id)
	{
		$this->db->from('sales_work_orders');
		$this->db->where('id',$id);
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}
	
	/*
	Inserts or updates a delivery
	*/
	function save(&$work_order_data, $work_order_id = false)
	{		
		//If we are overwriting a delivery make sure sale is gone
		if (isset($work_order_data['sale_id']))
		{
			$this->delete_by_sale_id($work_order_data['sale_id']);
		}
		
		if (!$work_order_id or !$this->exists($work_order_id))
		{			
			if($this->db->insert('sales_work_orders',$work_order_data))
			{
				$work_order_data['id'] = $this->db->insert_id();
				return true;
			}
			
			return false;
		}

		$this->db->where('id', $work_order_id);
		return $this->db->update('sales_work_orders', $work_order_data);
	}
	
	function delete($id)
	{	
		$this->db->where('id', $id);
		return $this->db->update('sales_work_orders', array('deleted' => 1));
	}
	
	function delete_list($work_order_ids)
	{
		foreach($work_order_ids as $work_order_id)
		{
			$result = $this->Work_order->delete($work_order_id);
			
			if(!$result)
			{
				return false;
			}
		}
		
		return true;
 	}
	
	function delete_by_sale_id($sale_id)
	{
		$this->db->where('sale_id', $sale_id);
		return $this->db->delete('sales_work_orders'); 
	}
	
	function undelete($id)
	{	
		$this->db->where('id', $id);
		return $this->db->update('sales_work_orders', array('deleted' => 0));
	}
	
	function undelete_list($work_order_ids)
	{
		foreach($work_order_ids as $work_order_id)
		{
			$result = $this->Work_order->undelete($work_order_id);
			
			if(!$result)
			{
				return false;
			}
		}
		
		return true;
 	}
		
	function get_displayable_columns()
	{
				
		$this->load->helper('people_helper');
		$this->lang->load('work_orders');
		$this->load->helper('sale');
		
		$return = array(
			'id' =>       	                       array('sort_column' => 'sales_work_orders.ID', 'label' => lang('common_id'),'format_function'),
			'sale_id' =>                           array('sort_column' => 'sales_work_orders.sale_id', 'label' => lang('work_orders_work_order').' '.lang('common_sale_id'),'format_function' => 'sale_id_receipt_link_formatter','html' => TRUE),
			'sale_time' =>                         array('sort_column' => 'sales.sale_time', 'label' => lang('work_orders_date'), 'format_function' => 'date_time_to_date'),
			'estimated_repair_date' =>             array('sort_column' => 'sales_work_orders.estimated_repair_date', 'label' => lang('work_orders_estimated_repair_date'), 'format_function' => 'date_time_to_date'),
			'estimated_parts' =>                   array('sort_column' => 'sales_work_orders.estimated_parts', 'label' => lang('work_orders_estimated_parts'), 'format_function' => 'to_currency'),
			'estimated_labor' =>                   array('sort_column' => 'sales_work_orders.estimated_labor', 'label' => lang('work_orders_estimated_labor'), 'format_function' => 'to_currency'),
			'status' =>                            array('sort_column' => 'sales_work_orders.status', 'label' => lang('common_status'), 'format_function' => 'work_order_status_badge', 'html' => TRUE),
			'technician_name' =>                   array('sort_column' => 'employee_person.first_name', 'label' => lang('work_orders_technician')),
			'first_name' =>                        array('sort_column' => 'customer_person.first_name', 'label' => lang('common_first_name')),
			'last_name' =>                         array('sort_column' => 'customer_person.last_name', 'label' => lang('common_last_name')),
			'item_name_being_repaired' =>          array('sort_column' => 'items.name', 'label' => lang('work_orders_item_name_being_repaired')),
			'full_address' =>                      array('sort_column' => 'customer_person.address_1', 'label' => lang('common_address'), 'html' => TRUE),
			'city' =>                              array('sort_column' => 'customer_person.city', 'label' => lang('common_city')),
			'state' =>                             array('sort_column' => 'customer_person.state', 'label' => lang('common_state')),
			'zip' =>                               array('sort_column' => 'customer_person.zip', 'label' => lang('common_zip')),
			'email' =>                             array('sort_column' => 'customer_person.email', 'label' => lang('common_email'), 'format_function' => 'email_formatter', 'html' => TRUE),
			'phone_number' =>                      array('sort_column' => 'customer_person.phone_number', 'label' => lang('common_phone_number'), 'format_function' => 'tel', 'html' => TRUE),
		);

		for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
		{
			if($this->get_custom_field($k) !== false)
			{
				$field = array();
				$field['sort_column'] ="custom_field_${k}_value";
				$field['label']= $this->get_custom_field($k);
			
				if ($this->get_custom_field($k,'type') == 'checkbox')
				{
					$format_function = 'boolean_as_string';
				}
				elseif($this->get_custom_field($k,'type') == 'date')
				{
					$format_function = 'date_as_display_date';				
				}
				elseif($this->get_custom_field($k,'type') == 'email')
				{
					$this->load->helper('url');
					$format_function = 'mailto';					
					$field['html'] = TRUE;
				}
				elseif($this->get_custom_field($k,'type') == 'url')
				{
					$this->load->helper('url');
					$format_function = 'anchor_or_blank';					
					$field['html'] = TRUE;
				}
				elseif($this->get_custom_field($k,'type') == 'phone')
				{
					$this->load->helper('url');
					$format_function = 'tel';					
					$field['html'] = TRUE;
				}
				elseif($this->get_custom_field($k,'type') == 'image')
				{
					$this->load->helper('url');
					$format_function = 'file_id_to_image_thumb';					
					$field['html'] = TRUE;
				}
				elseif($this->get_custom_field($k,'type') == 'file')
				{
					$this->load->helper('url');
					$format_function = 'file_id_to_download_link';					
					$field['html'] = TRUE;
				}
				else
				{
					$format_function = 'strsame';
				}
				$field['format_function'] = $format_function;
				$return["custom_field_${k}_value"] = $field;
			}
		}

		return $return;


	}
	
	function get_default_columns()
	{
		return array('id','sale_id','sale_time','status','technician_name','estimated_repair_date','first_name','last_name','item_name_being_repaired','email','phone_number');
	}

	function change_status($id,$status)
	{	
		$this->db->where('id', $id);
		return $this->db->update('sales_work_orders', array('status' => $status));
	}
	
	function change_status_list($work_order_ids,$status)
	{
		foreach($work_order_ids as $work_order_id)
		{
			$result = $this->Work_order->change_status($work_order_id,$status);
			
			if(!$result)
			{
				return false;
			}
		}
		
		return true;
	}
	 
	 public function get_raw_print_data($work_order_id)
	{
		$this->db->from('sales_work_orders');
		$this->db->join('sales', 'sales.sale_id = sales_work_orders.sale_id','left');
		$this->db->join('sales_items', 'sales_items.sale_id = sales_work_orders.sale_id','left');
		$this->db->join('items', 'items.item_id = sales_items.item_id','left');
		$this->db->join('people', 'people.person_id = sales.customer_id','left');
		$this->db->where('id',$work_order_id);
		return $this->db->get()->result_array();
	}

	public function get_customer_info($work_order_id)
	{
		$this->db->select('people.*');
		$this->db->from('sales_work_orders');
		$this->db->join('sales', 'sales.sale_id = sales_work_orders.sale_id','left');
		$this->db->join('people', 'people.person_id = sales.customer_id','left');
		$this->db->where('id',$work_order_id);
		return $this->db->get()->row_array();
	}

	public function get_item_being_repaired_info($work_order_id)
	{
		$this->db->select('items.*,sales_items.serialnumber');
		$this->db->from('sales_work_orders');
		$this->db->join('sales_items', 'sales_items.sale_id = sales_work_orders.sale_id','left');
		$this->db->join('items', 'items.item_id = sales_items.item_id','left');
		$this->db->where('id',$work_order_id);
		$this->db->where('line',0);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}

	function get_sales_items_notes($work_order_id)
	{
		$this->db->select('sales_items_notes.*,people.first_name,people.last_name');
		$this->db->from('sales_items_notes');
		$this->db->join('sales_work_orders', 'sales_items_notes.sale_id = sales_work_orders.sale_id','left');
		$this->db->join('people', 'people.person_id = sales_items_notes.employee_id','left');
		$this->db->where('sales_work_orders.id',$work_order_id);
		$this->db->order_by('sales_items_notes.note_timestamp', 'desc');
		return $this->db->get()->result_array();
	}

	function get_first_line_note($work_order_id)
	{
		$this->db->select('sales_items_notes.*');
		$this->db->from('sales_items_notes');
		$this->db->join('sales_work_orders', 'sales_items_notes.sale_id = sales_work_orders.sale_id','left');
		$this->db->where('sales_work_orders.id',$work_order_id);
		$this->db->where('sales_items_notes.line',0);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}

	public function get_work_order_items($work_order_id)
	{
		$this->db->select('sales_items.*,items.name as item_name');
		$this->db->from('sales_work_orders');
		$this->db->join('sales_items', 'sales_items.sale_id = sales_work_orders.sale_id','left');
		$this->db->join('items', 'items.item_id = sales_items.item_id','left');
		$this->db->where('sales_work_orders.id',$work_order_id);
		$this->db->where('sales_items.line !=',0);
		$this->db->order_by('sales_items.line', 'desc');
		return $this->db->get()->result_array();
	}

	function get_custom_field($number,$key="name")
	{
		static $config_data;
		
		if (!$config_data)
		{
			$config_data = unserialize($this->config->item('work_order_custom_field_prefs'));
		}
		
		return isset($config_data["custom_field_${number}_${key}"]) && $config_data["custom_field_${number}_${key}"] ? $config_data["custom_field_${number}_${key}"] : FALSE;
	}

	// function get_all_statuses()
	// {
	// 	$this->db->from('workorder_statuses');
	// 	$this->db->order_by('sort_order','asc');
		
	// 	return $this->db->get()->result_array();
	// }

	function get_all_statuses($limit=10000, $offset=0,$col='sort_order',$order='asc')
	{
		$this->db->from('workorder_statuses');
		$this->db->order_by($col, $order);
		
		
		$this->db->limit($limit);
		$this->db->offset($offset);
		
		$return = array();
		
		foreach($this->db->get()->result_array() as $result)
		{
			$return[$result['id']] = array('name' => $this->get_status_name($result['name']),'description' => $result['description'],'notify_by_email' => $result['notify_by_email'],'notify_by_sms' => $result['notify_by_sms'],'color' => $result['color'],'sort_order' => $result['sort_order']);
		}
		
		return $return;
	}
	
	function get_status_name($status_string)
	{
		if (strpos($status_string,'lang:') !== FALSE)
		{
			return lang(str_replace('lang:','',$status_string));
		}
		return $status_string;
	}

	function get_status_info($status_id, $can_cache = FALSE)
	{
		if ($can_cache)
		{
			static $cache = array();
		
			if (isset($cache[$status_id]))
			{
				return $cache[$status_id];
			}
		}
		else
		{
			$cache = array();
		}
				
		$this->db->from('workorder_statuses');	
		$this->db->where('id',$status_id);
		$query = $this->db->get();
		
		if($query->num_rows()==1)
		{
			$cache[$status_id] = $query->row();
			return $cache[$status_id];
		}
		else
		{
			$man_obj = new stdclass();
			
			$fields = $this->db->list_fields('workorder_statuses');
			
			foreach ($fields as $field)
			{
				$man_obj->$field='';
			}
			
			return $man_obj;
		}
	}

	function get_status_id_by_name($status_name)
	{
		$this->db->from('workorder_statuses');
		$this->db->group_start();
		$this->db->where('name', $status_name);
		$this->db->or_where('name', $this->get_status_name($status_name));
		$this->db->group_end();
		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			$row = $query->row();
			return $row->id;
		}
		
		return FALSE;
		
	}

	function status_exists( $status_id )
	{
		$this->db->from('workorder_statuses');
		$this->db->where('id',$status_id);
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}

	function status_save(&$status_data,$status_id=false)
	{
		if (!$status_id or !$this->status_exists($status_id))
		{
			if($this->db->insert('workorder_statuses',$status_data))
			{
				$status_data['id']=$this->db->insert_id();
				return true;
			}
			return false;
		}

		$this->db->where('id', $status_id);
		return $this->db->update('workorder_statuses',$status_data);
	}

	function delete_status($status_id)
	{		
		$this->db->where('id', $status_id);
		return $this->db->delete('workorder_statuses');
	}

	function delete_note($note_id)
	{		
		$this->db->where('note_id', $note_id);
		return $this->db->delete('sales_items_notes');
	}

	function save_new_work_order($customer_id,$item_id,$serial_number){
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$location_id = $this->Employee->get_logged_in_employee_current_location_id();
		$register_id = $this->Register->get_first_register_id_by_location_id($location_id);

		$item_info = $this->Item->get_info($item_id);
		$item_location_info = $this->Item_location->get_info($item_id,$location_id);
		
		//insert to phppos_sales
		$sales_data = array(
			'customer_id'=> $customer_id,
			'employee_id'=>$employee_id,
			'suspended'=>2,
			'location_id' => $location_id,
			'register_id' =>$register_id,
			'total_quantity_purchased' => 1,
			'subtotal' => 0,
			'total' => 0,
			'tax' => 0,
			'profit' =>0,
			'exchange_rate'=>1,
			'exchange_currency_symbol' => $this->config->item('currency_symbol') ? $this->config->item('currency_symbol') : '$',
			'exchange_currency_symbol_location'=>"before",
			'exchange_thousands_separator'=>",",
			'exchange_decimal_point'=>".",
		);
		$this->db->insert('sales',$sales_data);
		$sale_id = $this->db->insert_id();

		//insert to phppos_sales_work_orders
		$status_id = $this->Work_order->get_status_id_by_name('lang:work_orders_new');
		if(!$status_id){
			$work_order_status_data = array(
				'name'=>'lang:work_orders_new',
				'color' => '#4594cc',
			);
			$this->Work_order->status_save($work_order_status_data);
			$status_id = $work_order_status_data['id'];
		}
		$work_order_data = array(
			'sale_id'=>$sale_id,
			'status' => $status_id,
			'estimated_labor' => $item_info->unit_price,
		);
		$this->Work_order->save($work_order_data);
		$work_order_id = $this->db->insert_id();

		//insert to phppos_items_serialnumbers
		$this->Item_serial_number->add_serial($item_id, $serial_number,0,0);

		//insert to phppos_sales_items
		$sales_items_data = array
		(
			'sale_id'=>$sale_id,
			'item_id'=>$item_id,
			'line'=>0,
			'description'=>$item_info->description,
			'serialnumber'=>$serial_number,
			'quantity_purchased'=>1,
			'item_cost_price' =>$item_info->cost_price,
			'item_unit_price'=>$item_info->unit_price,
			'commission' =>0,
			'subtotal' => 0,
			'total' => 0,
			'tax' => 0,
			'profit' => 0,
		);

		$this->db->insert('sales_items',$sales_items_data);
		
		$inv_data = array
		(
			'trans_date'=>date('Y-m-d H:i:s'),
			'trans_items'=>$item_id,
			'trans_user'=>$employee_id,
			'trans_comment'=>$this->config->item('sale_prefix').' '.$sale_id,
			'trans_inventory'=> 1,
			'location_id'=>$location_id,
			'trans_current_quantity' => ($item_location_info->quantity ? $item_location_info->quantity : 0) + 1,
		);
	
		$this->Inventory->insert($inv_data);
		
		//Update stock quantity
		$this->Item_location->save_quantity(($item_location_info->quantity ? $item_location_info->quantity : 0) + 1,$item_id);
		
		
		return $work_order_id;
	}

	function get_work_orders_by_status()
	{	
		$location_id = $this->Employee->get_logged_in_employee_current_location_id();
		$phppos_workorder_statuses = $this->db->dbprefix('workorder_statuses');
		$phppos_sales_work_orders = $this->db->dbprefix('sales_work_orders');
		$phppos_sales = $this->db->dbprefix('sales');

		$query = "SELECT `$phppos_workorder_statuses`.`id`,`$phppos_workorder_statuses`.`name`,`$phppos_workorder_statuses`.`color`, IF(sales_work_orders_query.total_number is NULL,0,sales_work_orders_query.total_number) as total_number
			FROM `$phppos_workorder_statuses`
			LEFT JOIN(
				SELECT `$phppos_sales_work_orders`.`status`, COUNT(*) as total_number
	       		FROM `$phppos_sales_work_orders`
				INNER JOIN `$phppos_sales` ON `$phppos_sales`.`sale_id` = `$phppos_sales_work_orders`.`sale_id`
				WHERE `$phppos_sales`.`location_id` = $location_id
				AND `$phppos_sales`.`deleted` = 0
				AND `$phppos_sales_work_orders`.`deleted` = 0
				GROUP BY `$phppos_sales_work_orders`.`status`
			) as sales_work_orders_query ON sales_work_orders_query.status = `$phppos_workorder_statuses`.`id` ORDER BY `$phppos_workorder_statuses`.`sort_order` ASC";
		
		return $this->db->query($query)->result_array();
	}


    /*
      Gets work_order attached files
     */

	function get_files($work_order_id)
	{
		$this->db->select('work_order_files.*,app_files.file_name');
		$this->db->from('work_order_files');
		$this->db->join('app_files','app_files.file_id = work_order_files.file_id');
		$this->db->where('work_order_id',$work_order_id);
		$this->db->order_by('work_order_files.id');
		return $this->db->get();
	}

	function add_file($work_order_id,$file_id)
	{
		$this->db->insert('work_order_files', array('file_id' => $file_id, 'work_order_id' => $work_order_id));
	}

	function delete_file($file_id)
	{
		$this->db->where('file_id',$file_id);
		$this->db->delete('work_order_files');
		$this->load->model('Appfile');
		return $this->Appfile->delete($file_id);
	}
	
}
