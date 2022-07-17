<?php
class Delivery extends MY_Model
{
	public function __construct()
	{
      parent::__construct();
			$this->load->model('Inventory');	
	}
	
	public function get_info($delivery_id)
	{
		$this->db->from('sales_deliveries');
		$this->db->where('id',$delivery_id);
		return $this->db->get();
	}
	
	function get_delivery_person_id($sale_id)
	{
		$this->db->from('sales_deliveries');
		$this->db->where('sale_id',$sale_id);
		$query = $this->db->get();
		
		if($query->num_rows()==1)
		{
			return $query->row()->shipping_address_person_id;
		}
		
		return FALSE;
	}
	
	function get_delivery_person_info_by_sale_id($sale_id)
	{
		if ($person_id =  $this->get_delivery_person_id($sale_id))
		{
			$this->load->model('Person');
			return (array)$this->Person->get_info($person_id);
		}
		
		return NULL;
	}
	
	
	function get_delivery_tax_group_id($sale_id)
	{
		$this->db->from('sales_deliveries');
		$this->db->where('sale_id', $sale_id);
		$query = $this->db->get();
		
		if($query->num_rows()==1)
		{
			return $query->row()->tax_class_id;
		}
		
		return FALSE;
	}
	
	function get_info_by_sale_id($sale_id)
	{
		$this->db->from('sales_deliveries');
		$this->db->where('sale_id',$sale_id);
		return $this->db->get();
	}
	
	function has_delivery_for_sale($sale_id)
	{
		return $this->get_info_by_sale_id($sale_id)->num_rows() == 1;
	}
	
	/*
	Perform a search on deliveries
	*/
	function search($search, $deleted = 0,$filters = array(), $limit=20, $offset=0, $column='estimated_shipping_date', $orderby='asc',$location_id_override = NULL)
	{
		if (!$deleted)
		{
			$deleted = 0;
		}
		
		$location_id = $location_id_override ? $location_id_override : $this->Employee->get_logged_in_employee_current_location_id();
		
		$this->db->select('customer_sales_person.company_name as company_name');
		$this->db->select('IFNULL(GROUP_CONCAT(DISTINCT '.$this->db->dbprefix('items').'.name,": ",FLOOR('.$this->db->dbprefix('sales_items').'.quantity_purchased)  SEPARATOR "<br /> "), GROUP_CONCAT(DISTINCT item_temp.name,": ",FLOOR('.$this->db->dbprefix('delivery_items').'.quantity)  SEPARATOR "<br /> ")) as items');
		$this->db->select('IFNULL(GROUP_CONCAT(DISTINCT '.$this->db->dbprefix('item_kits').'.name,": ",FLOOR('.$this->db->dbprefix('sales_item_kits').'.quantity_purchased)  SEPARATOR "<br /> "), GROUP_CONCAT(DISTINCT item_kit_temp.name,": ",FLOOR('.$this->db->dbprefix('delivery_item_kits').'.quantity)  SEPARATOR "<br /> ")) as item_kits');

		$this->db->select('sales.comment as sale_comment,sales_deliveries.location_id as location_id,shipping_zones.name as shipping_zone_name, sales_deliveries.*,
		CONCAT(customer_person.address_1, " ", customer_person.address_2, " ", customer_person.city, " ", customer_person.state, " ", customer_person.zip, " ", customer_person.country) as full_address,
		customer_person.*,
		employee_person.full_name as delivery_employee,
		shipping_methods.name as `shipping_method_name`,
		shipping_providers.name as `shipping_provider_name`, locations.name as `location_name`,
		delivery_categories.name as category, delivery_categories.color as category_color
		');
		$this->db->from('sales_deliveries');

		$this->db->join('sales', 'sales.sale_id = sales_deliveries.sale_id','left');

		$this->db->join('delivery_categories', 'delivery_categories.id = sales_deliveries.category_id','left');

		$this->db->join('sales_items', 'sales.sale_id = sales_items.sale_id', 'left');
		$this->db->join('sales_item_kits', 'sales.sale_id = sales_item_kits.sale_id', 'left');

		$this->db->join('items', 'sales_items.item_id = items.item_id and items.system_item = 0','left');
		$this->db->join('item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id','left');

		$this->db->join('delivery_items', 'sales_deliveries.id = delivery_items.delivery_id', 'left');
		$this->db->join('delivery_item_kits', 'sales_deliveries.id = delivery_item_kits.delivery_id', 'left');

		$this->db->join('items as item_temp','delivery_items.item_id = item_temp.item_id and item_temp.system_item = 0', 'left');
		$this->db->join('item_kits as item_kit_temp', 'delivery_item_kits.item_kit_id = item_kit_temp.item_kit_id', 'left');

		$this->db->join('shipping_zones', 'shipping_zones.id = sales_deliveries.shipping_zone_id','left');
		$this->db->join('people as customer_person', 'sales_deliveries.shipping_address_person_id = customer_person.person_id');
		$this->db->join('people as employee_person', 'sales_deliveries.delivery_employee_person_id = employee_person.person_id', 'left');
		$this->db->join('customers as customer_sales_person', 'sales.customer_id = customer_sales_person.person_id', 'left');
		$this->db->join('shipping_methods', 'sales_deliveries.shipping_method_id = shipping_methods.id','left');
		$this->db->join('shipping_providers', 'shipping_methods.shipping_provider_id = shipping_providers.id','left');
		$this->db->join('locations', 'sales_deliveries.location_id = locations.location_id','left');

		if ($search)
		{
			$this->db->where("(
			tracking_number LIKE '".$this->db->escape_like_str($search)."%' or
			delivery_categories.name LIKE '".$this->db->escape_like_str($search)."%' or
			shipping_zones.name LIKE '".$this->db->escape_like_str($search)."%' or
			customer_person.first_name LIKE '".$this->db->escape_like_str($search)."%' or
			employee_person.last_name LIKE '".$this->db->escape_like_str($search)."%' or
			employee_person.first_name LIKE '".$this->db->escape_like_str($search)."%' or
			customer_person.last_name LIKE '".$this->db->escape_like_str($search)."%' or
			customer_person.address_1 LIKE '".$this->db->escape_like_str($search)."%' or
			customer_person.address_2 LIKE '".$this->db->escape_like_str($search)."%' or
			customer_person.city LIKE '".$this->db->escape_like_str($search)."%' or
			customer_person.state LIKE '".$this->db->escape_like_str($search)."%' or
			customer_person.zip LIKE '".$this->db->escape_like_str($search)."%' or
			CONCAT(customer_person.address_1,', ',customer_person.address_2,', ',customer_person.city,', ',customer_person.state,', ',customer_person.zip,', ',customer_person.country)  = ".$this->db->escape($search)." or
			sales_deliveries.sale_id  = ".$this->db->escape($search)." or
			customer_person.email LIKE '".$this->db->escape_like_str($search)."%' or 
			customer_person.phone_number LIKE '".$this->db->escape_like_str($search)."%' or 
			CONCAT(employee_person.`first_name`,' ',employee_person.`last_name`) LIKE '".$this->db->escape_like_str($search)."%' or 
			CONCAT(employee_person.`last_name`,', ',employee_person.`first_name`) LIKE '".$this->db->escape_like_str($search)."%' or 
			CONCAT(employee_person.`last_name`,', ',employee_person.`first_name`) LIKE '".$this->db->escape_like_str($search)."%' or
			CONCAT(customer_person.`first_name`,' ',customer_person.`last_name`) LIKE '".$this->db->escape_like_str($search)."%' or 
			CONCAT(customer_person.`last_name`,', ',customer_person.`first_name`) LIKE '".$this->db->escape_like_str($search)."%' or 
			CONCAT(customer_person.`last_name`,', ',customer_person.`first_name`) LIKE '".$this->db->escape_like_str($search)."%')");		
		}
		
		if(isset($filters) && count($filters) > 0)
		{
			
			$this->db->group_start();

			if (isset($filters['is_pickup']))
			{
				$this->db->where_in('is_pickup', $filters['is_pickup']);
			}
		
			if (isset($filters['status']))
			{
				$this->db->where_in('status', $filters['status']);
			}

			if (isset($filters['category']))
			{
				$this->db->where_in('sales_deliveries.category_id', $filters['category']);
			}
			
			if(isset($filters['shipping_start']))
			{
				$this->db->where('estimated_shipping_date >=', date('Y-m-d H:i:s',strtotime($filters['shipping_start'])));
			}
			
			if(isset($filters['shipping_end']))
			{
				$this->db->where('estimated_shipping_date <=', date('Y-m-d H:i:s',strtotime($filters['shipping_end'])));
			}
			
			if(isset($filters['delivery_start']))
			{
				$this->db->where('estimated_delivery_or_pickup_date >=',  date('Y-m-d H:i:s',strtotime($filters['delivery_start'])));
			}
			
			if(isset($filters['delivery_end']))
			{
				$this->db->where('estimated_delivery_or_pickup_date <=', date('Y-m-d H:i:s',strtotime($filters['delivery_end'])));
			}

			if(isset($filters['locations']))
			{
				$this->db->group_start();
					$this->db->where_in('sales_deliveries.location_id', $filters['locations']);
					$this->db->or_where_in('sales.location_id', $filters['locations']);
				$this->db->group_end();
			}else{
				$this->db->group_start();
					$this->db->where('sales_deliveries.location_id', $location_id);
					$this->db->or_where('sales.location_id', $location_id);
				$this->db->group_end();
			}

			if(isset($filters['deliveries_with_or_without_sales'])){
				$this->db->where_in('sales_deliveries.delivery_type', $filters['deliveries_with_or_without_sales']);
			}
			
			$this->db->group_end();
		}else{
			$this->db->group_start();
				$this->db->where('sales_deliveries.location_id', $location_id);
				$this->db->or_where('sales.location_id', $location_id);
			$this->db->group_end();
		}

		$this->db->group_start();
			$this->db->where('sales.deleted', 0);
			$this->db->or_where('sales_deliveries.sale_id', NULL);
		$this->db->group_end();

		$this->db->where('sales_deliveries.deleted', $deleted);

		$this->db->group_by('sales_deliveries.id');
		
		if (!$this->config->item('speed_up_search_queries'))
		{
			$this->db->order_by($column, $orderby);
		}
		
		$this->db->limit($limit);
		$this->db->offset($offset);
	
	 return $this->db->get();
		 
	}
	
	function search_count_all($search, $deleted = 0,$filters = array(),$limit=10000,$location_id_override = NULL)
	{
		if (!$deleted)
		{
			$deleted = 0;
		}
		$location_id = $location_id_override ? $location_id_override : $this->Employee->get_logged_in_employee_current_location_id();
		
		$this->db->select('customer_sales_person.company_name as company_name');
		$this->db->select('IFNULL(GROUP_CONCAT(DISTINCT '.$this->db->dbprefix('items').'.name,": ",FLOOR('.$this->db->dbprefix('sales_items').'.quantity_purchased)  SEPARATOR "<br /> "), GROUP_CONCAT(DISTINCT item_temp.name,": ",FLOOR('.$this->db->dbprefix('delivery_items').'.quantity)  SEPARATOR "<br /> ")) as items');
		$this->db->select('IFNULL(GROUP_CONCAT(DISTINCT '.$this->db->dbprefix('item_kits').'.name,": ",FLOOR('.$this->db->dbprefix('sales_item_kits').'.quantity_purchased)  SEPARATOR "<br /> "), GROUP_CONCAT(DISTINCT item_kit_temp.name,": ",FLOOR('.$this->db->dbprefix('delivery_item_kits').'.quantity)  SEPARATOR "<br /> ")) as item_kits');

		$this->db->select('sales.comment as sale_comment, shipping_zones.name as shipping_zone_name, sales_deliveries.*,
		CONCAT(customer_person.address_1, " ", customer_person.address_2, " ", customer_person.city, " ", customer_person.state, " ", customer_person.zip, " ", customer_person.country) as full_address,
		customer_person.*,
		employee_person.full_name as delivery_employee,
		shipping_methods.name as `shipping_method_name`,
		shipping_providers.name as `shipping_provider_name`, locations.name as `location_name`,
		delivery_categories.name as category, delivery_categories.color as category_color
		');
		$this->db->from('sales_deliveries');

		$this->db->join('sales', 'sales.sale_id = sales_deliveries.sale_id','left');

		$this->db->join('delivery_categories', 'delivery_categories.id = sales_deliveries.category_id','left');

		$this->db->join('sales_items', 'sales.sale_id = sales_items.sale_id', 'left');
		$this->db->join('sales_item_kits', 'sales.sale_id = sales_item_kits.sale_id', 'left');

		$this->db->join('items', 'sales_items.item_id = items.item_id and items.system_item = 0','left');
		$this->db->join('item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id','left');

		$this->db->join('delivery_items', 'sales_deliveries.id = delivery_items.delivery_id', 'left');
		$this->db->join('delivery_item_kits', 'sales_deliveries.id = delivery_item_kits.delivery_id', 'left');

		$this->db->join('items as item_temp','delivery_items.item_id = item_temp.item_id and item_temp.system_item = 0', 'left');
		$this->db->join('item_kits as item_kit_temp', 'delivery_item_kits.item_kit_id = item_kit_temp.item_kit_id', 'left');

		$this->db->join('shipping_zones', 'shipping_zones.id = sales_deliveries.shipping_zone_id','left');
		$this->db->join('people as customer_person', 'sales_deliveries.shipping_address_person_id = customer_person.person_id');
		$this->db->join('people as employee_person', 'sales_deliveries.delivery_employee_person_id = employee_person.person_id', 'left');
		$this->db->join('customers as customer_sales_person', 'sales.customer_id = customer_sales_person.person_id', 'left');
		$this->db->join('shipping_methods', 'sales_deliveries.shipping_method_id = shipping_methods.id','left');
		$this->db->join('shipping_providers', 'shipping_methods.shipping_provider_id = shipping_providers.id','left');
		$this->db->join('locations', 'sales_deliveries.location_id = locations.location_id','left');
				
		if ($search)
		{
			$this->db->where("(
			tracking_number LIKE '".$this->db->escape_like_str($search)."%' or
			delivery_categories.name LIKE '".$this->db->escape_like_str($search)."%' or
			shipping_zones.name LIKE '".$this->db->escape_like_str($search)."%' or
			customer_person.first_name LIKE '".$this->db->escape_like_str($search)."%' or
			employee_person.last_name LIKE '".$this->db->escape_like_str($search)."%' or
			employee_person.first_name LIKE '".$this->db->escape_like_str($search)."%' or
			customer_person.last_name LIKE '".$this->db->escape_like_str($search)."%' or
			customer_person.address_1 LIKE '".$this->db->escape_like_str($search)."%' or
			customer_person.address_2 LIKE '".$this->db->escape_like_str($search)."%' or
			customer_person.city LIKE '".$this->db->escape_like_str($search)."%' or
			customer_person.state LIKE '".$this->db->escape_like_str($search)."%' or
			customer_person.zip LIKE '".$this->db->escape_like_str($search)."%' or
			CONCAT(customer_person.address_1,', ',customer_person.address_2,', ',customer_person.city,', ',customer_person.state,', ',customer_person.zip,', ',customer_person.country)  = ".$this->db->escape($search)." or
			sales_deliveries.sale_id  = ".$this->db->escape($search)." or
			customer_person.email LIKE '".$this->db->escape_like_str($search)."%' or 
			customer_person.phone_number LIKE '".$this->db->escape_like_str($search)."%' or 
			CONCAT(employee_person.`first_name`,' ',employee_person.`last_name`) LIKE '".$this->db->escape_like_str($search)."%' or 
			CONCAT(employee_person.`last_name`,', ',employee_person.`first_name`) LIKE '".$this->db->escape_like_str($search)."%' or 
			CONCAT(employee_person.`last_name`,', ',employee_person.`first_name`) LIKE '".$this->db->escape_like_str($search)."%' or
			CONCAT(customer_person.`first_name`,' ',customer_person.`last_name`) LIKE '".$this->db->escape_like_str($search)."%' or 
			CONCAT(customer_person.`last_name`,', ',customer_person.`first_name`) LIKE '".$this->db->escape_like_str($search)."%' or 
			CONCAT(customer_person.`last_name`,', ',customer_person.`first_name`) LIKE '".$this->db->escape_like_str($search)."%')");		
		}
		
		if(isset($filters) && count($filters) > 0)
		{
			
			$this->db->group_start();
			$this->db->where("1=1");
			if (isset($filters['is_pickup']))
			{
				$this->db->where_in('is_pickup', $filters['is_pickup']);
			}
		
			if (isset($filters['status']))
			{
				$this->db->where_in('status', $filters['status']);
			}

			if (isset($filters['category']))
			{
				$this->db->where_in('sales_deliveries.category_id', $filters['category']);
			}
			
			if(isset($filters['shipping_start']))
			{
				$this->db->where('estimated_shipping_date >=', date('Y-m-d H:i:s',strtotime($filters['shipping_start'])));
			}
			
			if(isset($filters['shipping_end']))
			{
				$this->db->where('estimated_shipping_date <=', date('Y-m-d H:i:s',strtotime($filters['shipping_end'])));
			}
			
			if(isset($filters['delivery_start']))
			{
				$this->db->where('estimated_delivery_or_pickup_date >=',  date('Y-m-d H:i:s',strtotime($filters['delivery_start'])));
			}
			
			if(isset($filters['delivery_end']))
			{
				$this->db->where('estimated_delivery_or_pickup_date <=', date('Y-m-d H:i:s',strtotime($filters['delivery_end'])));
			}

			if(isset($filters['locations']))
			{
				$this->db->group_start();
					$this->db->where_in('sales_deliveries.location_id', $filters['locations']);
					$this->db->or_where_in('sales.location_id', $filters['locations']);
				$this->db->group_end();
			}else{
				$this->db->group_start();
					$this->db->where('sales_deliveries.location_id', $location_id);
					$this->db->or_where('sales.location_id', $location_id);
				$this->db->group_end();
			}

			if(isset($filters['deliveries_with_or_without_sales'])){
				$this->db->where_in('sales_deliveries.delivery_type', $filters['deliveries_with_or_without_sales']);
			}
			
			$this->db->group_end();
		}else{
			$this->db->group_start();
				$this->db->where('sales_deliveries.location_id', $location_id);
				$this->db->or_where('sales.location_id', $location_id);
			$this->db->group_end();
		}
		
		$this->db->group_start();
			$this->db->where('sales.deleted', 0);
			$this->db->or_where('sales_deliveries.sale_id', NULL);
		$this->db->group_end();

		$this->db->where('sales_deliveries.deleted', $deleted);
		
		$this->db->group_by('sales_deliveries.id');

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

			$this->db->from('sales_deliveries');
			$this->db->join('sales', 'sales.sale_id = sales_deliveries.sale_id');
			
			$this->db->join('people', 'sales_deliveries.shipping_address_person_id = people.person_id');
			$this->db->where('sales.deleted',0);
			$this->db->where('sales_deliveries.deleted',$deleted);		
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


			$this->db->from('sales_deliveries');
			$this->db->join('sales', 'sales.sale_id = sales_deliveries.sale_id');
			
			$this->db->join('people', 'sales_deliveries.delivery_employee_person_id = people.person_id');
			$this->db->where('sales.deleted',0);
			$this->db->where('sales_deliveries.deleted',$deleted);		
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
					'subtitle' => '',
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


			
			$this->db->from('sales_deliveries');
			$this->db->join('sales', 'sales.sale_id = sales_deliveries.sale_id');
			
			$this->db->join('people', 'sales_deliveries.shipping_address_person_id = people.person_id');
		
			$this->db->where('sales.deleted',0);
			$this->db->where('sales_deliveries.deleted',$deleted);
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
			
			
			
			$this->db->from('sales_deliveries');
			$this->db->join('sales', 'sales.sale_id = sales_deliveries.sale_id');
			
			$this->db->join('people', 'sales_deliveries.shipping_address_person_id = people.person_id');
			$this->db->where("phone_number LIKE '".$this->db->escape_like_str($search)."%'");
			$this->db->where('sales.location_id',$location_id);
			$this->db->where('sales.deleted',0);
			$this->db->where('sales_deliveries.deleted',$deleted);
			
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
			
			
			
			
			$this->db->from('sales_deliveries');
			$this->db->join('sales', 'sales.sale_id = sales_deliveries.sale_id');
			
			$this->db->join('people', 'sales_deliveries.shipping_address_person_id = people.person_id');
			$this->db->where("email LIKE '".$this->db->escape_like_str($search)."%'");
			$this->db->where('sales.location_id',$location_id);
			$this->db->where('sales.deleted',0);
			$this->db->where('sales_deliveries.deleted',$deleted);
			
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
			
			
			$this->db->from('sales_deliveries');
			$this->db->join('sales', 'sales.sale_id = sales_deliveries.sale_id');
			
			$this->db->join('people', 'sales_deliveries.shipping_address_person_id = people.person_id');
			$this->db->where("tracking_number LIKE '".$this->db->escape_like_str($search)."%'");
			$this->db->where('sales.location_id',$location_id);
			$this->db->where('sales.deleted',0);
			$this->db->where('sales_deliveries.deleted',$deleted);
			$this->db->limit($limit);
			
			$query=$this->db->get();
			$temp_suggestions = array();
						
			foreach($query->result() as $row)
			{
				$data = array(
					'name' => $row->tracking_number,
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
	
	function get_all_for_range($deleted=0,$start_date=null,$end_date=null,$col='estimated_delivery_or_pickup_date')
	{	
		if (!$deleted)
		{
			$deleted = 0;
		}
		
		$location_id = $this->Employee->get_logged_in_employee_current_location_id();
		$this->db->select('sales.comment as sale_comment, sales.location_id as location_id,shipping_zones.name as shipping_zone_name, sales_deliveries.id as delivery_id,sales_deliveries.*,sales.sale_time,
		CONCAT(address_1, " ", address_2, " ", city, " ", state, " ", zip, " ", country) as full_address,
		people.*,
		shipping_methods.name as `shipping_method_name`,
		shipping_providers.name as `shipping_provider_name`,
		delivery_categories.name as category, delivery_categories.color as category_color
		');
		$this->db->from('sales_deliveries');
		$this->db->join('delivery_categories', 'delivery_categories.id = sales_deliveries.category_id','left');
		$this->db->join('shipping_zones', 'shipping_zones.id = sales_deliveries.shipping_zone_id','left');
		$this->db->join('sales', 'sales.sale_id = sales_deliveries.sale_id','left');
		$this->db->join('people', 'sales_deliveries.shipping_address_person_id = people.person_id');
		$this->db->join('shipping_methods', 'sales_deliveries.shipping_method_id = shipping_methods.id','left');
		$this->db->join('shipping_providers', 'shipping_methods.shipping_provider_id = shipping_providers.id','left');
		$this->db->where($col. ' >= ',$start_date);
		$this->db->where($col. ' <= ',$end_date.' 23:59:59');

		$this->db->group_start();
			$this->db->where('sales_deliveries.location_id', $location_id);
			$this->db->or_where('sales.location_id', $location_id);
		$this->db->group_end();

		$this->db->group_start();
			$this->db->where('sales.deleted', 0);
			$this->db->or_where('sales_deliveries.sale_id', NULL);
		$this->db->group_end();

		$this->db->where('sales_deliveries.deleted',$deleted);
		$this->db->order_by($col);
		return $this->db->get();
	}
	
	
	function get_all($deleted=0,$limit=10000, $offset=0,$col='estimated_shipping_date',$order='asc',$filters = array(),$location_id_override = NULL)
	{	
		if (!$deleted)
		{
			$deleted = 0;
		}
		
		$location_id = $location_id_override ? $location_id_override : $this->Employee->get_logged_in_employee_current_location_id();
		$this->db->select('customer_sales_person.company_name as company_name');
		$this->db->select('IFNULL(GROUP_CONCAT(DISTINCT '.$this->db->dbprefix('items').'.name,": ",FLOOR('.$this->db->dbprefix('sales_items').'.quantity_purchased)  SEPARATOR "<br /> "), GROUP_CONCAT(DISTINCT item_temp.name,": ",FLOOR('.$this->db->dbprefix('delivery_items').'.quantity)  SEPARATOR "<br /> ")) as items');
		$this->db->select('IFNULL(GROUP_CONCAT(DISTINCT '.$this->db->dbprefix('item_kits').'.name,": ",FLOOR('.$this->db->dbprefix('sales_item_kits').'.quantity_purchased)  SEPARATOR "<br /> "), GROUP_CONCAT(DISTINCT item_kit_temp.name,": ",FLOOR('.$this->db->dbprefix('delivery_item_kits').'.quantity)  SEPARATOR "<br /> ")) as item_kits');

		$this->db->select('sales.comment as sale_comment,sales_deliveries.location_id as location_id,shipping_zones.name as shipping_zone_name, sales_deliveries.*,
		CONCAT(customer_person.address_1, " ", customer_person.address_2, " ", customer_person.city, " ", customer_person.state, " ", customer_person.zip, " ", customer_person.country) as full_address,
		customer_person.*,
		employee_person.full_name as delivery_employee,
		shipping_methods.name as `shipping_method_name`,
		shipping_providers.name as `shipping_provider_name`, locations.name as `location_name`,
		delivery_categories.name as category, delivery_categories.color as category_color
		');
		$this->db->from('sales_deliveries');

		$this->db->join('delivery_categories', 'delivery_categories.id = sales_deliveries.category_id','left');

		$this->db->join('sales', 'sales.sale_id = sales_deliveries.sale_id','left');

		$this->db->join('sales_items', 'sales.sale_id = sales_items.sale_id', 'left');
		$this->db->join('sales_item_kits', 'sales.sale_id = sales_item_kits.sale_id', 'left');

		$this->db->join('items', 'sales_items.item_id = items.item_id and items.system_item = 0','left');
		$this->db->join('item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id','left');

		$this->db->join('delivery_items', 'sales_deliveries.id = delivery_items.delivery_id', 'left');
		$this->db->join('delivery_item_kits', 'sales_deliveries.id = delivery_item_kits.delivery_id', 'left');

		$this->db->join('items as item_temp','delivery_items.item_id = item_temp.item_id and item_temp.system_item = 0', 'left');
		$this->db->join('item_kits as item_kit_temp', 'delivery_item_kits.item_kit_id = item_kit_temp.item_kit_id', 'left');

		$this->db->join('shipping_zones', 'shipping_zones.id = sales_deliveries.shipping_zone_id','left');
		$this->db->join('people as customer_person', 'sales_deliveries.shipping_address_person_id = customer_person.person_id');
		$this->db->join('people as employee_person', 'sales_deliveries.delivery_employee_person_id = employee_person.person_id', 'left');
		$this->db->join('customers as customer_sales_person', 'sales.customer_id = customer_sales_person.person_id', 'left');
		$this->db->join('shipping_methods', 'sales_deliveries.shipping_method_id = shipping_methods.id','left');
		$this->db->join('shipping_providers', 'shipping_methods.shipping_provider_id = shipping_providers.id','left');
		$this->db->join('locations', 'sales_deliveries.location_id = locations.location_id','left');
		
		if(isset($filters) && count($filters) > 0){
			$this->db->group_start();
			if (isset($filters['is_pickup']))
			{
				$this->db->where_in('is_pickup', $filters['is_pickup']);
			}
		
			if (isset($filters['status']))
			{
				$this->db->where_in('status', $filters['status']);
			}

			if (isset($filters['category']))
			{
				$this->db->where_in('sales_deliveries.category_id', $filters['category']);
			}
			
			if(isset($filters['shipping_start']))
			{
				$this->db->where('estimated_shipping_date >=', date('Y-m-d H:i:s',strtotime($filters['shipping_start'])));
			}
			
			if(isset($filters['shipping_end']))
			{
				$this->db->where('estimated_shipping_date <=', date('Y-m-d H:i:s',strtotime($filters['shipping_end'])));
			}
			
			if(isset($filters['delivery_start']))
			{
				$this->db->where('estimated_delivery_or_pickup_date >=',  date('Y-m-d H:i:s',strtotime($filters['delivery_start'])));
			}
			
			if(isset($filters['delivery_end']))
			{
				$this->db->where('estimated_delivery_or_pickup_date <=', date('Y-m-d H:i:s',strtotime($filters['delivery_end'])));
			}

			if(isset($filters['locations']))
			{
				$this->db->group_start();
					$this->db->where_in('sales_deliveries.location_id', $filters['locations']);
					$this->db->or_where_in('sales.location_id', $filters['locations']);
				$this->db->group_end();
			}else{
				$this->db->group_start();
					$this->db->where('sales_deliveries.location_id', $location_id);
					$this->db->or_where('sales.location_id', $location_id);
				$this->db->group_end();
			}

			if(isset($filters['deliveries_with_or_without_sales'])){
				$this->db->where_in('sales_deliveries.delivery_type', $filters['deliveries_with_or_without_sales']);
			}
			
			$this->db->group_end();
		}else{
			$this->db->group_start();
				$this->db->where('sales_deliveries.location_id', $location_id);
				$this->db->or_where('sales.location_id', $location_id);
			$this->db->group_end();
		}

		$this->db->group_start();
			$this->db->where('sales.deleted', 0);
			$this->db->or_where('sales_deliveries.sale_id', NULL);
		$this->db->group_end();

		$this->db->where('sales_deliveries.deleted', $deleted);

		$this->db->group_by('sales_deliveries.id');

		if(!$this->config->item('speed_up_search_queries'))
		{
			$this->db->order_by($col, $order);
		}
		
		$this->db->limit($limit, $offset);
 	 $return = $this->db->get();
	  
 	 return $return;
	}
	
	function count_all($deleted=0, $location_id_override = NULL)
	{
		if (!$deleted)
		{
			$deleted = 0;
		}
		
		$location_id = $location_id_override ? $location_id_override : $this->Employee->get_logged_in_employee_current_location_id();
		
		$this->db->from('sales_deliveries');
		$this->db->join('sales', 'sales.sale_id = sales_deliveries.sale_id','left');

		$this->db->group_start();
			$this->db->where('sales_deliveries.location_id', $location_id);
			$this->db->or_where('sales.location_id', $location_id);
		$this->db->group_end();

		$this->db->group_start();
			$this->db->where('sales.deleted', 0);
			$this->db->or_where('sales_deliveries.sale_id', NULL);
		$this->db->group_end();

		$this->db->where('sales_deliveries.deleted',$deleted);
		
		return $this->db->count_all_results();
	}
	
	function exists($id)
	{
		$this->db->from('sales_deliveries');
		$this->db->where('id',$id);
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}
	
	/*
	Inserts or updates a delivery
	*/
	function save(&$delivery_data, $delivery_id = false, $delivery_items = false)
	{		
		//If we are overwriting a delivery make sure sale is gone
		if (isset($delivery_data['sale_id']))
		{
			$this->delete_by_sale_id($delivery_data['sale_id']);
		}
		
		if (!$delivery_id or !$this->exists($delivery_id))
		{			
			if($this->db->replace('sales_deliveries',$delivery_data))
			{
				$delivery_data['id'] = $this->db->insert_id();
				if($delivery_items){
					$this->save_items($delivery_items, $delivery_data['id']);
				}
				return true;
			}
			
			return false;
		}

		if($delivery_items){
			$this->save_items($delivery_items, $delivery_id);
		}
		

		$this->db->where('id', $delivery_id);
		return $this->db->update('sales_deliveries', $delivery_data);
	}
	
	function delete($id)
	{	
		$this->db->where('id', $id);
		return $this->db->update('sales_deliveries', array('deleted' => 1));
	}
	
	function delete_list($delivery_ids)
	{
		foreach($delivery_ids as $delivery_id)
		{
			$result = $this->Delivery->delete($delivery_id);
			
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
		return $this->db->delete('sales_deliveries'); 
	}
	
	function undelete($id)
	{	
		$this->db->where('id', $id);
		return $this->db->update('sales_deliveries', array('deleted' => 0));
	}
	
	function undelete_list($delivery_ids)
	{
		foreach($delivery_ids as $delivery_id)
		{
			$result = $this->Delivery->undelete($delivery_id);
			
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
		$this->lang->load('deliveries');
		$this->load->helper('sale');
		
		return array(
			'sale_id' =>                           array('sort_column' => 'sales_deliveries.sale_id', 'label' => lang('common_sale_id'),'format_function' => 'sale_id_receipt_link_formatter','html' => TRUE),
			'first_name' =>                        array('sort_column' => 'customer_person.first_name', 'label' => lang('common_first_name')),
			'last_name' =>                         array('sort_column' => 'customer_person.last_name', 'label' => lang('common_last_name')),
			'company_name' =>                      array('sort_column' => 'customer_sales_person.company_name', 'label' => lang('common_company')),
			'full_address' =>                      array('sort_column' => 'customer_person.address_1', 'label' => lang('common_address'), 'format_function' => 'address', 'html' => TRUE),
			'address_1' =>                         array('sort_column' => 'customer_person.address_1', 'label' => lang('common_address_1')),
			'address_2' =>                         array('sort_column' => 'customer_person.address_2', 'label' => lang('common_address_2')),
			'city' =>                              array('sort_column' => 'customer_person.city', 'label' => lang('common_city')),
			'state' =>                             array('sort_column' => 'customer_person.state', 'label' => lang('common_state')),
			'zip' =>                               array('sort_column' => 'customer_person.zip', 'label' => lang('common_zip')),
			'country' =>                           array('sort_column' => 'customer_person.country', 'label' => lang('common_country')),
			'email' =>                             array('sort_column' => 'customer_person.email', 'label' => lang('common_email'), 'format_function' => 'email_formatter', 'html' => TRUE),
			'phone_number' =>                      array('sort_column' => 'customer_person.phone_number', 'label' => lang('common_phone_number'), 'format_function' => 'tel', 'html' => TRUE),
			'estimated_shipping_date' =>           array('sort_column' => 'sales_deliveries.estimated_shipping_date', 'label' => lang('deliveries_estimated_shipping_date'), 'format_function' => 'datetime_as_display_date', 'html' => FALSE),
			'actual_shipping_date' =>              array('sort_column' => 'sales_deliveries.actual_shipping_date', 'label' => lang('deliveries_actual_shipping_date'), 'format_function' => 'datetime_as_display_date', 'html' => FALSE),
			'estimated_delivery_or_pickup_date' => array('sort_column' => 'sales_deliveries.estimated_delivery_or_pickup_date', 'label' => lang('deliveries_estimated_delivery_or_pickup_date'), 'format_function' => 'datetime_as_display_date', 'html' => FALSE),
			'actual_delivery_or_pickup_date' => array('sort_column' => 'sales_deliveries.actual_delivery_or_pickup_date', 'label' => lang('deliveries_actual_delivery_or_pickup_date'), 'format_function' => 'datetime_as_display_date', 'html' => FALSE),
			'is_pickup' =>                         array('sort_column' => 'sales_deliveries.is_pickup', 'label' => lang('deliveries_is_pickup'), 'format_function' => 'boolean_as_string', 'html' => FALSE),
			'shipping_method_name' =>              array('sort_column' => 'shipping_methods.name', 'label' => lang('deliveries_shipping_method')),
			'shipping_provider_name' =>            array('sort_column' => 'shipping_providers.name', 'label' => lang('deliveries_shipping_provider')),
			'shipping_zone_name' =>            		 array('sort_column' => 'shipping_zone_name', 'label' => lang('delivery_shipping_zone')),
			'tracking_number' =>                   array('sort_column' => 'sales_deliveries.tracking_number', 'label' => lang('deliveries_tracking_number')),
			'status' =>                            array('sort_column' => 'sales_deliveries.status', 'label' => lang('common_status'), 'format_function' => 'delivery_status_badge', 'html' => TRUE),
			'comment' =>                           array('sort_column' => 'sales_deliveries.comment', 'label' => lang('common_comment')),
			'sale_comment' =>                      array('sort_column' => 'sales.comment', 'label' => lang('deliveries_sale_comment')),
			'items' =>                     				 array('sort_column' => '', 'label' => lang('reports_items'), 'html' => TRUE),
			'item_kits' =>                     		 array('sort_column' => '', 'label' => lang('module_item_kits'), 'html' => TRUE),
			'delivery_employee' =>       					 array('sort_column' => 'employee_person.last_name', 'label' => lang('deliveries_delivery_employee')),
			'location_name' =>       					 array('sort_column' => 'phppos_locations.name', 'label' => lang('common_location')),
			'category_id' 								=> array('sort_column' => 'delivery_categories.id','label' => lang('common_category'), 'format_function' => 'delivery_category_badge', 'html' => TRUE),
		);
	}
	
	function get_default_columns()
	{
		return array('sale_id','status','first_name','last_name', 'full_address','delivery_employee', 'category_id');
	}
	
	function update_status_bulk($ids,$status)
	{
		if (!empty($ids))
		{
			$this->db->group_start();
			$ids_chunk = array_chunk($ids,25);
			foreach($ids_chunk as $ids)
			{
					$this->db->or_where_in('id',$ids);
			}
			$this->db->group_end();
		}
		else
		{
			$this->db->where('1', '2', FALSE);
		}
		
		$this->db->update('sales_deliveries',array('status' => $status));
	}
	
	function get_delivery_statuses(){
		$status = array(
			'not_scheduled' => lang('deliveries_not_scheduled'),
			'scheduled' => lang('deliveries_scheduled'),
			'shipped' => lang('delivieries_shipped'),
			'delivered' => lang('deliveries_delivered'),
		);
		
		return $status;
	}

	function get_delivery_fields(){
		return $this->db->list_fields('sales_deliveries');
	}

	function save_items($delivery_items, $delivery_id){
		$this->db->where('delivery_id', $delivery_id);
		$this->db->delete('delivery_items');

		$this->db->where('delivery_id', $delivery_id);
		$this->db->delete('delivery_item_kits');

		foreach($delivery_items as $k => $v){
			if($v['item_kit_id']){
				$this->db->insert('delivery_item_kits', array(
					'delivery_id' => $delivery_id,
					'item_kit_id' => $v['item_kit_id'],
					'quantity' => $v['quantity']
				));
			}else{
				$this->db->insert('delivery_items', array(
					'delivery_id' => $delivery_id,
					'item_id' => $v['item_id'],
					'item_variation_id' => ($v['item_variation_id']) ? $v['item_variation_id'] : NULL,
					'quantity' => $v['quantity']
				));
			}
		}
	}
	
	function get_delivery_items($delivery_id)
	{
		$this->db->select("delivery_items.*, items.name, categories.name as category");
		$this->db->from("delivery_items");
		$this->db->join('items', 'items.item_id = delivery_items.item_id');
		$this->db->join('categories', 'categories.id = items.category_id', 'left');
		$this->db->where(array("delivery_id" => $delivery_id));
		return $this->db->get();
	}

	function get_delivery_item_kits($delivery_id)
	{
		$this->db->select("delivery_item_kits.*, item_kits.name, categories.name as category");
		$this->db->from("delivery_item_kits");
		$this->db->join('item_kits', 'item_kits.item_kit_id = delivery_item_kits.item_kit_id');
		$this->db->join('categories', 'categories.id = item_kits.category_id', 'left');
		$this->db->where(array("delivery_id" => $delivery_id));
		return $this->db->get();
	}

	function update_event($delivery_id, $start_date, $date_field, $duration){
		if($date_field == 'sale_time'){
			$sale_id = $this->db->get_where('sales_deliveries', array('id' => $delivery_id))->row('sale_id');
			$this->db->where(array('sale_id' => $sale_id));
			$this->db->update('sales', array(
				'sale_time' => date('Y-m-d H:i:s', strtotime($start_date))
			));

			$this->db->where(array('id' => $delivery_id));
			$this->db->update('sales_deliveries', array(
				'duration' => $duration
			));
		}else{
			$this->db->where(array('id' => $delivery_id));
			$this->db->update('sales_deliveries', array(
				$date_field => date('Y-m-d H:i:s', strtotime($start_date)),
				'duration' => $duration
			));
		}

		return true;
	}
	
	function get_category_info($category_id, $can_cache = FALSE)
	{
		if ($can_cache)
		{
			static $cache = array();

			if (isset($cache[$category_id]))
			{
				return $cache[$category_id];
			}
		}
		else
		{
			$cache = array();
		}
		
		$this->db->from('delivery_categories');	
		$this->db->where('id',$category_id);
		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			$cache[$category_id] = $query->row();
			return $cache[$category_id];
		}
		else
		{
			$man_obj = new stdclass();
	
			$fields = $this->db->list_fields('delivery_categories');
	
			foreach ($fields as $field)
			{
				$man_obj->$field='';
			}
	
			return $man_obj;
		}
	}

	function get_all_statuses($limit=10000, $offset=0, $col='id',$order='asc')
	{
		$this->db->from('delivery_statuses');
		$this->db->order_by($col, $order);
		
		
		$this->db->limit($limit);
		$this->db->offset($offset);
		
		$return = array();
		
		foreach($this->db->get()->result_array() as $result)
		{
			$return[$result['id']] = array('name' => $this->get_status_name($result['name']), 'description' => $result['description'], 'notify_by_email' => $result['notify_by_email'], 'notify_by_sms' => $result['notify_by_sms'], 'color' => $result['color']);
		}
		
		return $return;
	}

	function get_all_categories($limit=10000, $offset=0, $col='id',$order='asc')
	{
		$this->db->from('delivery_categories');
		$this->db->order_by($col, $order);
		
		
		$this->db->limit($limit);
		$this->db->offset($offset);
		
		$return = array();
		
		foreach($this->db->get()->result_array() as $result)
		{
			$return[$result['id']] = array('name' => $result['name'], 'color' => $result['color']);
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
				
		$this->db->from('delivery_statuses');	
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
			
			$fields = $this->db->list_fields('delivery_statuses');
			
			foreach ($fields as $field)
			{
				$man_obj->$field='';
			}
			
			return $man_obj;
		}
	}

	function get_status_id_by_name($status_name)
	{
		$this->db->from('delivery_statuses');
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
		$this->db->from('delivery_statuses');
		$this->db->where('id',$status_id);
		$query = $this->db->get();

		return ($query->num_rows()==1);
	}

	function status_save(&$status_data,$status_id=false)
	{
		if (!$status_id or !$this->status_exists($status_id))
		{
			if($this->db->insert('delivery_statuses',$status_data))
			{
				$status_data['id']=$this->db->insert_id();
				return true;
			}
			return false;
		}

		$this->db->where('id', $status_id);
		return $this->db->update('delivery_statuses',$status_data);
	}

	function delete_status($status_id)
	{
		$this->db->where('id', $status_id);
		return $this->db->delete('delivery_statuses');
	}

	function change_status($id, $status){
		$this->db->where('id', $id);
		return $this->db->update('sales_deliveries', array('status' => $status));
	}

	function get_files($delivery_id)
	{
		$this->db->select('delivery_files.*,app_files.file_name');
		$this->db->from('delivery_files');
		$this->db->join('app_files','app_files.file_id = delivery_files.file_id');
		$this->db->where('delivery_id',$delivery_id);
		$this->db->order_by('delivery_files.id');
		return $this->db->get();
	}

	function add_file($delivery_id,$file_id)
	{
		$this->db->insert('delivery_files', array('file_id' => $file_id, 'delivery_id' => $delivery_id));
	}

	function delete_file($file_id)
	{
		$this->db->where('file_id',$file_id);
		$this->db->delete('delivery_files');
		$this->load->model('Appfile');
		return $this->Appfile->delete($file_id);
	}


	function get_status_id($id)
	{
		$this->db->from('delivery_email_templates');
		$this->db->where('status_id', $id);

		return $this->db->get()->row();
	}
	/*
	Inserts or updates a delivery
	*/
	function save_template($data)
	{		
		$status_id = $data['status_id'];
		$content   = $data['content'];
		$this->db->where('status_id', $status_id);
		$this->db->from('delivery_email_templates');

		if ($this->db->get()->num_rows()) {
			$this->db->where('status_id', $status_id);
			return $this->db->update('delivery_email_templates', array('content' => $content));
		} else {
			$this->db->insert('delivery_email_templates', array('status_id' => $status_id,'content' => $content));
			return TRUE;
		}
	}

}
