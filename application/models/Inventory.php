<?php
class Inventory extends MY_Model 
{	
	function insert($inventory_data)
	{
		if(is_numeric($inventory_data['trans_inventory']))
		{
			if (!isset($inventory_data['trans_date']))
			{
				$inventory_data['trans_date'] = date('Y-m-d H:i:s');
			}
			return $this->db->insert('inventory',$inventory_data);
		}
		
		return TRUE;
	}
	
	function set_comment_for_inventory_log($trans_id, $comment)
	{
		$this->db->where('trans_id', $trans_id);
		$this->db->update('inventory',array('trans_comment' =>$comment));
	}
	
	function get_inventory_data_for_item($item_id, $limit, $offset, $location_id = false)
	{
		if (!$location_id)
		{
			$location_id=$this->Employee->get_logged_in_employee_current_location_id();
		}
		
		//get all variations for an item
		$this->load->model('Item_variations');
		//include deleted
		$variations = $this->Item_variations->get_variations($item_id, true);
		$quantity_unit_info = $this->Item->get_quantity_units($item_id);
		if(count($quantity_unit_info) == 1) {
			$quantity_unit_info = $quantity_unit_info[0];
		} else{
			$quantity_unit_info = null;
		}
				
		$this->db->from('inventory');
		$this->db->where('trans_items',$item_id);
		$this->db->where('location_id',$location_id);
		$this->db->order_by("trans_id", "desc");
		$this->db->limit($limit);
		$this->db->offset($offset);
		
		$result = $this->db->get()->result_array();
		
		foreach($result as &$row)
		{
			if(isset($variations[$row['item_variation_id']]['attributes']))
			{
				if ($variations[$row['item_variation_id']]['name'])
				{
					$row['variation'] = $variations[$row['item_variation_id']]['name'];
				}
				else
				{
					$row['variation'] = implode(', ', array_column($variations[$row['item_variation_id']]['attributes'], 'label'));
				}
			}
			else
			{
				$row['variation']= '';
			}

			if($quantity_unit_info){
				$row['quantity_unit_name'] = $quantity_unit_info->unit_name;
				$row['quantity_unit_quantity'] = $quantity_unit_info->unit_quantity;
			}else{
				$row['quantity_unit_name'] = '';
				$row['quantity_unit_quantity'] = '';
			}
		}
		
		return $result;
	}
	
	function count_all($item_id,$location_id = false)
	{
		if (!$location_id)
		{
			$location_id=$this->Employee->get_logged_in_employee_current_location_id();
		}
		
		$this->db->from('inventory');
		$this->db->where('trans_items',$item_id);
		$this->db->where('location_id',$location_id);
		
		return $this->db->count_all_results();
	}
	
	function get_count_by_status($status, $location_id = false)
	{
		if (!$location_id)
		{
			$location_id=$this->Employee->get_logged_in_employee_current_location_id();
		}
		
		$this->db->from('inventory_counts');
		$this->db->where('status',$status);
		$this->db->where('location_id',$location_id);
		
		return $this->db->count_all_results();
	}
	
	function get_counts_by_status($status, $limit = 100, $offset = 0, $location_id = false)
	{
		if (!$location_id)
		{
			$location_id=$this->Employee->get_logged_in_employee_current_location_id();
		}
		
		$this->db->from('inventory_counts');
		$this->db->where('status',$status);
		$this->db->where('location_id',$location_id);
		$this->db->order_by("count_date", "desc");
		$this->db->limit($limit);
		$this->db->offset($offset);

		return $this->db->get();		
	}
	
	function get_number_of_items_counted($inventory_counts_id)
	{
		$this->db->from('inventory_counts_items');
		$this->db->where('inventory_counts_id',$inventory_counts_id);
		return $this->db->count_all_results();
	}
	
	function create_count($date = false, $status = false, $comment = false, $employee_id = false, $location_id = false)
	{
		if (!$date)
		{
			$date = date('Y-m-d H:i:s');	
		}
		
		if (!$status)
		{
			$status = 'open';
		}
		
		if (!$comment)
		{
			$comment = '';
		}
		
		if (!$employee_id)
		{
			$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
		}
		
		if (!$location_id)
		{
			$location_id=$this->Employee->get_logged_in_employee_current_location_id();
		}
		
		
		$count_data = array(
		'count_date'=>$date,
		'employee_id' => $employee_id,
		'location_id'=> $location_id,
		'status' => $status,
		'comment' => $comment,
		);


		if($this->db->insert('inventory_counts', $count_data))
		{
			return $this->db->insert_id();
		}
		
		return FALSE;
	}
	
	function validate_count($count_id)
	{
		$counted_items = $this->get_items_counted($count_id, NULL, 0);
		
		foreach ($counted_items as $item)
		{
			if(isset($item["variations"]))
			{
				if(count($item["variations"]) > 0 && $item["item_variation_id"] == NULL)
				{
					return false;
				}
			}
		}
		
		return true;
	}
		
	function get_count_info($count_id)
	{
		$this->db->from('inventory_counts');
		$this->db->where('id',$count_id);
		$query = $this->db->get();
		
		if($query->num_rows()==1)
		{
			return $query->row();
		}
		
		return NULL;
	}
	
	function get_count_info_from_count_item_id($count_item_id)
	{
		$this->db->select('inventory_counts.*,inventory_counts_items.item_id');
		$this->db->from('inventory_counts');
		$this->db->join('inventory_counts_items','inventory_counts.id=inventory_counts_items.inventory_counts_id');
		$this->db->where('inventory_counts_items.id',$count_item_id);
		$query = $this->db->get();
		
		if($query->num_rows()==1)
		{
			return $query->row();
		}
		
		return NULL;
	}
	
	function set_count($count_id, $status = false, $comment = false)
	{
		$data = array();
		
		if ($status !== FALSE)
		{
			$data['status'] = $status;
		}
		
		if ($comment !== FALSE)
		{
			$data['comment'] = $comment;
		}
		
		$this->db->where('id', $count_id);
		return $this->db->update('inventory_counts', $data);
		
	}
	
	function update_count_item($count_item_id, $item_variation_id = false, $count = false, $comment = false, $actual_quantity = FALSE)
	{
		$this->db->where('id', $count_item_id);
		
		if ($item_variation_id)
		{
			if($item_variation_id == -1)
			{
				$update_data['item_variation_id'] = NULL;
			}
			else
			{
				$update_data['item_variation_id'] = $item_variation_id;
			}
		}
		
		if ($count !== FALSE)
		{
			$update_data['count'] = $count;
		}
		
		if ($comment)
		{
			$update_data['comment'] = $comment;
		}
		
		if ($actual_quantity !== FALSE)
		{
			$update_data['actual_quantity'] = $actual_quantity;
		}
		
		
		if ($this->db->update('inventory_counts_items',$update_data))
		{
			return $count_item_id;
		}
		
	}
	
	function set_count_item($count_id, $item_id, $item_variation_id = false, $count = false, $actual_quantity = false, $comment = false)
	{
		$this->db->from('inventory_counts_items');
		$this->db->where('item_id',$item_id);
		
		if($item_variation_id)
		{
			$this->db->where('item_variation_id',$item_variation_id);
		}
		else
		{
			$this->db->where('item_variation_id',null);
		}
		
		$this->db->where('inventory_counts_id', $count_id);
		$query = $this->db->get();

		$exists = ($query->num_rows()==1);	
		
		$data = array(
			'inventory_counts_id' => $count_id,
			'item_id' => $item_id,
			'item_variation_id' => $item_variation_id ? $item_variation_id : null,
		);
		
		if ($count !== FALSE)
		{
			$data['count'] = $count;			
		}
		
		if ($actual_quantity !== FALSE)
		{
			$data['actual_quantity'] = $actual_quantity;			
		}
		
		if ($comment !== FALSE)
		{
			$data['comment'] = $comment;
		}
		
		if ($exists)
		{			
			$data['actual_quantity'] = $this->get_count_item_actual_quantity($count_id, $item_id, $item_variation_id);
			
			if ($comment === FALSE)
			{
				$data['comment'] = $this->get_count_item_actual_comment($count_id, $item_id, $item_variation_id);
			}
			
			//Remove previous item
			$this->db->where('item_id', $item_id);
			if($item_variation_id)
			{
				$this->db->where('item_variation_id',$item_variation_id);
			}
			else
			{
				$this->db->where('item_variation_id',null);
			}
			$this->db->where('inventory_counts_id', $count_id);
			$this->db->delete('inventory_counts_items');
			
			return $this->db->insert('inventory_counts_items', $data);					
		}
		
		return $this->db->insert('inventory_counts_items', $data);	
	}
	
	function delete_count_item($count_id, $item_id, $item_variation_id = false)
	{		
		if(empty($item_variation_id))
		{
			$item_variation_id = null;
		}
		
		$this->db->delete('inventory_counts_items', array('inventory_counts_id' => $count_id, 'item_id' => $item_id, 'item_variation_id' => $item_variation_id));
	}
	
	function get_count_item_current_quantity($count_id, $item_id, $item_variation_id = false)
	{
		$this->db->from('inventory_counts_items');
		$this->db->where('item_id',$item_id);
		$this->db->where('inventory_counts_id', $count_id);
		if($item_variation_id)
		{
			$this->db->where('item_variation_id', $item_variation_id);
		}
		else
		{
			$this->db->where('item_variation_id', null);
		}
		
		$query = $this->db->get();
		
		if($query->num_rows()==1)
		{
			return $query->row()->count;
		}
		
		return 0;
	}
	
	function get_count_item_actual_quantity($count_id, $item_id, $item_variation_id = false)
	{
		$this->db->from('inventory_counts_items');
		$this->db->where('item_id',$item_id);
		$this->db->where('inventory_counts_id', $count_id);
		if($item_variation_id)
		{
			$this->db->where('item_variation_id', $item_variation_id);
		}
		else
		{
			$this->db->where('item_variation_id', null);
		}
		
		$query = $this->db->get();
		
		if($query->num_rows()==1)
		{
			return $query->row()->actual_quantity;
		}
		
		return NULL;
	}
	
	function get_count_item_actual_comment($count_id, $item_id, $item_variation_id = false)
	{
		$this->db->from('inventory_counts_items');
		$this->db->where('item_id',$item_id);
		$this->db->where('inventory_counts_id', $count_id);
		if($item_variation_id)
		{
			$this->db->where('item_variation_id', $item_variation_id);
		}
		else
		{
			$this->db->where('item_variation_id', null);
		}
		
		$query = $this->db->get();
		
		if($query->num_rows()==1)
		{
			return $query->row()->comment;
		}
		
		return NULL;
	}
	
	function get_counted_variations_for_item($count_id, $item_id)
	{
		$this->db->select('item_variation_id');
		$this->db->from('inventory_counts_items');
		$this->db->where('inventory_counts_id', $count_id);
		$this->db->where('item_id', $item_id);
		
		$result = $this->db->get()->result_array();
				
		return $result;
	}
	
	function get_items_not_counted_count($count_id,$in_stock = 0)
	{
		$count_info = $this->get_count_info($count_id);
		
		$this->db->from('items');
		$this->db->join('item_variations', 'item_variations.item_id = items.item_id', 'left');
		$this->db->group_start();
		$this->db->where('items.item_id NOT IN(SELECT COALESCE(item_id,0) FROM '.$this->db->dbprefix('inventory_counts_items').' WHERE inventory_counts_id='.$this->db->escape($count_id).')');
		$this->db->or_where('item_variations.id NOT IN(SELECT COALESCE(item_variation_id,0) FROM '.$this->db->dbprefix('inventory_counts_items').' WHERE inventory_counts_id='.$this->db->escape($count_id).')');
		$this->db->group_end();
		$this->db->where('items.deleted',0);
		$this->db->group_start();		
		$this->db->where('item_variations.deleted',0);
		$this->db->or_where('item_variations.deleted',NULL);
		$this->db->group_end();
		$this->db->where('items.is_service',0);
		
		if ($in_stock)
		{
			$location_id = $count_info->location_id;
			$location_item_variations_quantity_col =$this->db->dbprefix('location_item_variations').'.quantity';
			$location_items_quantity_col = $this->db->dbprefix('location_items').'.quantity';

			$quantity_query = 'COALESCE('.$location_item_variations_quantity_col.','.$location_items_quantity_col.',0)';

			$this->db->join('location_item_variations', 'location_item_variations.item_variation_id = item_variations.id and location_item_variations.location_id IN('.$location_id.')', 'left');
			$this->db->join('location_items', 'location_items.item_id = items.item_id and location_items.location_id IN('.$location_id.')', 'left');
		
			$this->db->where($quantity_query.' > 0');
		}
		$this->db->order_by('category_id');
		
		return $this->db->count_all_results();
	}
	
	function get_items_not_counted($count_id,$category_ids,$in_stock = 0,$limit = 100, $offset = 0)
	{
		$count_info = $this->get_count_info($count_id);
		
		$this->db->select('items.*,item_variations.id as item_variation_id, IFNULL('.$this->db->dbprefix('item_variations').'.unit_price, 0) as item_variation_unit_price, IFNULL('.$this->db->dbprefix('item_variations').'.cost_price, 0) as item_variation_cost_price, suppliers.company_name as supplier_company_name,location_items.cost_price as location_cost_price,location_items.unit_price as location_unit_price,location_items.location as location,tax_classes.name as tax_group,location_items.quantity as quantity,0 as variation_count, 0 as has_variations');
		$this->db->from('items');
		$this->db->join('item_variations', 'item_variations.item_id = items.item_id', 'left');
		$this->db->join('suppliers', 'suppliers.person_id = items.supplier_id', 'left');
		$this->db->group_start();
		$this->db->where('items.item_id NOT IN(SELECT COALESCE(item_id,0) FROM '.$this->db->dbprefix('inventory_counts_items').' WHERE inventory_counts_id='.$this->db->escape($count_id).')');
		$this->db->or_where('item_variations.id NOT IN(SELECT COALESCE(item_variation_id,0) FROM '.$this->db->dbprefix('inventory_counts_items').' WHERE inventory_counts_id='.$this->db->escape($count_id).')');
		$this->db->group_end();
		$this->db->where('items.deleted',0);
		$this->db->group_start();		
		$this->db->where('item_variations.deleted',0);
		$this->db->or_where('item_variations.deleted',NULL);
		$this->db->group_end();
		
		$this->db->where('items.is_service',0);
		
		if ($category_ids)
		{
			$this->db->where_in('items.category_id',$category_ids);
		}
		
		
		$location_id = $count_info->location_id;
		$location_item_variations_quantity_col =$this->db->dbprefix('location_item_variations').'.quantity';
		$location_items_quantity_col = $this->db->dbprefix('location_items').'.quantity';

		$quantity_query = 'COALESCE('.$location_item_variations_quantity_col.','.$location_items_quantity_col.',0)';

		$this->db->join('location_item_variations', 'location_item_variations.item_variation_id = item_variations.id and location_item_variations.location_id IN('.$location_id.')', 'left');
		$this->db->join('location_items', 'location_items.item_id = items.item_id and location_items.location_id IN('.$location_id.')', 'left');
		$this->db->join('tax_classes', 'tax_classes.id = items.tax_class_id', 'left');
	
		if ($in_stock)
		{
			$this->db->where($quantity_query.' > 0');
		}
		if ($limit !== NULL)
		{
			$this->db->limit($limit);
		}
		
		if ($offset !== NULL)
		{
			$this->db->offset($offset);
		}
		
		return $this->db->get()->result_array();
	}
	
	function get_items_not_counted_total($count_id,$category_ids,$in_stock = 0)
	{
		$count_info = $this->get_count_info($count_id);
		$this->load->model('Item_variations');

		$this->db->select('
			SUM(
				IFNULL(
					IF(
						'.$this->db->dbprefix('item_variations').'.id,
						'.$this->db->dbprefix('location_item_variations').'.quantity,
						'.$this->db->dbprefix('location_items').'.quantity
					),
					0
				) * 
				IFNULL(
					IF(
						'.$this->db->dbprefix('item_variations').'.id,
						'.$this->db->dbprefix('item_variations').'.cost_price,
						'.$this->db->dbprefix('items').'.cost_price
					),
					0
				)
			) as  grand_total_value_by_cost_price,
			SUM(
				IFNULL(
					IF(
						'.$this->db->dbprefix('item_variations').'.id,
						'.$this->db->dbprefix('location_item_variations').'.quantity,
						'.$this->db->dbprefix('location_items').'.quantity
					),
					0
				) * 
				IFNULL(
					IF(
						'.$this->db->dbprefix('item_variations').'.id,
						'.$this->db->dbprefix('item_variations').'.unit_price,
						'.$this->db->dbprefix('items').'.unit_price
					),
					0
				)
			) as  grand_total_value_by_selling_price
		');

		$this->db->from('items');
		$this->db->join('item_variations', 'item_variations.item_id = items.item_id', 'left');
		$this->db->join('suppliers', 'suppliers.person_id = items.supplier_id', 'left');
		$this->db->group_start();
		$this->db->where('items.item_id NOT IN(SELECT COALESCE(item_id,0) FROM '.$this->db->dbprefix('inventory_counts_items').' WHERE inventory_counts_id='.$this->db->escape($count_id).')');
		$this->db->or_where('item_variations.id NOT IN(SELECT COALESCE(item_variation_id,0) FROM '.$this->db->dbprefix('inventory_counts_items').' WHERE inventory_counts_id='.$this->db->escape($count_id).')');
		$this->db->group_end();
		$this->db->where('items.deleted',0);
		$this->db->group_start();		
		$this->db->where('item_variations.deleted',0);
		$this->db->or_where('item_variations.deleted',NULL);
		$this->db->group_end();
		
		$this->db->where('items.is_service',0);
		
		if ($category_ids)
		{
			$this->db->where_in('items.category_id',$category_ids);
		}
		
		
		$location_id = $count_info->location_id;
		$location_item_variations_quantity_col =$this->db->dbprefix('location_item_variations').'.quantity';
		$location_items_quantity_col = $this->db->dbprefix('location_items').'.quantity';

		$quantity_query = 'COALESCE('.$location_item_variations_quantity_col.','.$location_items_quantity_col.',0)';

		$this->db->join('location_item_variations', 'location_item_variations.item_variation_id = item_variations.id and location_item_variations.location_id IN('.$location_id.')', 'left');
		$this->db->join('location_items', 'location_items.item_id = items.item_id and location_items.location_id IN('.$location_id.')', 'left');
		$this->db->join('tax_classes', 'tax_classes.id = items.tax_class_id', 'left');

		if ($in_stock)
		{
			$this->db->where($quantity_query.' > 0');
		}

		$result =  $this->db->get()->result_array();
		return $result[0];
	}

	function get_items_counted($count_id,$limit = 100, $offset = 0,$search='')
	{
		$this->load->model('Item_variations');
			
		$this->db->select('items.*, inventory_counts_items.*, item_variations.id as item_variation_id, inventory_counts.location_id, inventory_counts.employee_id, categories.name as category,suppliers.company_name as supplier_company_name');
		$this->db->from('inventory_counts_items');
		$this->db->where('inventory_counts_id', $count_id);
		$this->db->join('inventory_counts', 'inventory_counts.id = inventory_counts_items.inventory_counts_id');
		$this->db->join('items', 'items.item_id = inventory_counts_items.item_id');
		$this->db->join('item_variations', 'item_variations.id = inventory_counts_items.item_variation_id', 'left');
		$this->db->join('suppliers', 'suppliers.person_id = items.supplier_id', 'left');
		$this->db->join('categories', 'categories.id = items.category_id','left');
		
		if (!empty($search))
		{
			$this->db->group_start();
			$this->db->or_where('items.product_id',$search);
			$this->db->or_where('items.item_number',$search);
			$this->db->or_where('items.item_id',$search);
			
			$this->db->group_end();
		}
		if ($limit !== NULL)
		{
			$this->db->limit($limit);
		}
		
		if ($offset !== NULL)
		{
			$this->db->offset($offset);
		}
		
		$this->db->order_by('inventory_counts_items.id', 'DESC');
		$result = $this->db->get()->result_array();

		
		$item_variation_ids = array();
		$item_ids = array();
		
		foreach($result as $row)
		{
			$item_ids[] = $row['item_id'];
			$item_variation_ids[] = $row['item_variation_id'];
		}
		
		if (count($item_ids) == 0)
		{
			$item_ids = array(-1);
		}
		
		if (count($item_variation_ids) == 0)
		{
			$item_variation_ids = array(-1);
		}
		
		$variations = $this->Item_variations->get_variations(array_unique($item_ids));
		
		foreach($result as &$row)
		{
			if(isset($variations[$row['item_id']]))
			{
				foreach($variations[$row['item_id']] as $item_variation_id => $variation)
				{
					if(!in_array($item_variation_id, $item_variation_ids) || $item_variation_id == $row['item_variation_id'])
					{
						if ($variation['name'])
						{
							$row['variations'][$item_variation_id] = $variation['name'];
						}
						else
						{
							$row['variations'][$item_variation_id] = implode(', ',array_column($variation['attributes'], 'label'));
						}
					}
				}
			}
		}
		
		return $result;
	}

	function get_items_counted_total($count_id,$search='')
	{
		$this->load->model('Item_variations');
			
		$this->db->select('SUM('.$this->db->dbprefix('items').'.cost_price * '.$this->db->dbprefix('inventory_counts_items').'.count) grand_total_value_by_cost_price, 
		SUM('.$this->db->dbprefix('items').'.unit_price * '.$this->db->dbprefix('inventory_counts_items').'.count) grand_total_value_by_selling_price');
		$this->db->from('inventory_counts_items');
		$this->db->where('inventory_counts_id', $count_id);
		$this->db->join('inventory_counts', 'inventory_counts.id = inventory_counts_items.inventory_counts_id');
		$this->db->join('items', 'items.item_id = inventory_counts_items.item_id');
		
		if (!empty($search))
		{
			$this->db->group_start();
			$this->db->or_where('items.product_id',$search);
			$this->db->or_where('items.item_number',$search);
			$this->db->or_where('items.item_id',$search);
			
			$this->db->group_end();
		}

		$this->db->order_by('inventory_counts_items.id', 'DESC');
		$result = $this->db->get()->result_array();
		return $result[0];
	}

	function delete_inventory_count($count_id)
	{
		$this->db->delete('inventory_counts_items', array('inventory_counts_id' => $count_id));
		$this->db->delete('inventory_counts', array('id' => $count_id));
		
		return TRUE;
	}
	
	function update_inventory_from_count($count_id)
	{
		
		$count_info  = $this->get_count_info($count_id);
		if ($count_info->status == 'closed')
		{
			return;
		}
		
		$this->Inventory->set_count($count_id, 'closed');
		$this->load->model('Item_location');
		$this->load->model('Item_variation_location');
		
		foreach($this->get_items_counted($count_id, NULL,NULL) as $count_item)
		{
			$current_inventory_value = $count_item['actual_quantity'];
			$counted_inventory_value = $count_item['count'];
	
			if ($current_inventory_value != $counted_inventory_value)
			{
				$inv_data = array
				(
					'trans_date'=>date('Y-m-d H:i:s'),
					'trans_items'=>$count_item['item_id'],
					'item_variation_id'=>$count_item['item_variation_id'],
					'trans_user'=>$count_item['employee_id'],
					'trans_comment'=>lang('items_inventory_count_update'),
					'trans_inventory'=>$counted_inventory_value - $current_inventory_value,
					'location_id' => $count_item['location_id'],
				);
				
				
				if(isset($count_item['item_variation_id']) && $count_item['item_variation_id'])
				{
					$cur_quantity = $this->Item_variation_location->get_location_quantity($count_item['item_variation_id'],$count_item['location_id']);
					$this->Item_variation_location->save_quantity($cur_quantity + ($counted_inventory_value - $current_inventory_value), $count_item['item_variation_id'],$count_item['location_id']);
				} 
				else
				{
					$cur_quantity = $this->Item_location->get_location_quantity($count_item['item_id'],$count_item['location_id']);
					$this->Item_location->save_quantity($cur_quantity + ($counted_inventory_value - $current_inventory_value), $count_item['item_id'],$count_item['location_id']);
				}
				$inv_data['trans_current_quantity'] = $cur_quantity + ($counted_inventory_value - $current_inventory_value);
				
				$this->insert($inv_data);
				
			}
		}
	}

	//Santosh Changes
	function get_default_columns()
	{
		return array('name','item_variation_id','count','actual_quantity','comment');

	}
	function get_displayable_columns()
	{
		$return  = array(
			'item_id' => 											array('sort_column' => 'item_id', 'label' => lang('common_item_id')),
			'item_number' => 										array('sort_column' => 'item_number','label' => lang('common_item_number_expanded'), 'data_function' => 'item_number_data_function', 'format_function' => 'item_number_formatter'),
			'product_id' => 										array('sort_column' => 'product_id','label' => lang('common_product_id')),
			'name' => 												array('sort_column' => 'name','label' => lang('common_item')),
			'description' => 										array('sort_column' => 'description','label' => lang('common_description')),
			'category' => 											array('sort_column' => 'category','label' => lang('common_category')),
			'item_variation_id' => 									array('sort_column' => 'item_variation_id','label' => lang('common_variation')),
			'quantity' =>											array('sort_column' => 'quantity','label' => lang('items_quantity'),'data_function' => 'item_quantity_data_function','format_function' => 'item_quantity_format', 'html' => TRUE),
			'category_id' => 										array('sort_column' => 'category','label' => lang('common_category_full_path'),'format_function' => 'get_full_category_path'),
			'supplier_company_name' => 								array('sort_column' => 'supplier_company_name','label' => lang('common_supplier')),
			'cost_price' => 										array('sort_column' => 'cost_price','label' => lang('common_cost_price'),'format_function' => 'to_currency_and_edit_item_price','data_function' => 'item_id_data_function', 'html' => TRUE),
			'total_value_by_cost_price' => 							array('sort_column' => 'total_value_by_cost_price','label' => lang('items_total_value_by_cost_price'),'format_function' => 'to_currency','data_function' => 'item_id_data_function', 'html' => TRUE),
			'unit_price' => 										array('sort_column' => 'unit_price','label' => lang('common_unit_price'),'format_function' => 'to_currency_and_edit_item_price','data_function' => 'item_id_data_function', 'html' => TRUE),
			'total_value_by_selling_price' => 						array('sort_column' => 'total_value_by_selling_price','label' => lang('items_total_value_by_selling_price'),'format_function' => 'to_currency','data_function' => 'item_id_data_function', 'html' => TRUE),
			'reorder_level' => 										array('sort_column' => 'reorder_level','label' => lang('items_reorder_level'),'format_function' => 'to_quantity'),
			'replenish_level'  => 									array('sort_column' => 'replenish_level','label' => lang('common_replenish_level'),'format_function' => 'to_quantity'),
			'count' => 												array('sort_column' => 'variation_count','label' => lang('items_count'),'format_function' => 'to_quantity_variation', 'data_function' => 'item_id_data_function', 'html' => TRUE),
			'comment' => 							 				array('sort_column' => 'comment','label' => lang('common_comments')),
			'actual_quantity' =>									array('sort_column' => 'quantity','label' => lang('items_actual_on_hand'),'data_function' => 'item_quantity_data_function','format_function' => 'item_quantity_format', 'html' => TRUE),
			);
		
		if ($this->config->item('verify_age_for_products'))
		{
			$return['verify_age'] = array('sort_column' => 'verify_age','label' => lang('common_requires_age_verification'),'format_function' => 'boolean_as_string');		
			$return['required_age'] = array('sort_column' => 'required_age','label' => lang('common_required_age'),'format_function' => 'to_quantity');		
		}
		
		if ($this->config->item('hide_size_field'))
		{
			unset($return['size']);
		}
		
		for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
		{
			if($this->Item->get_custom_field($k) !== false)
			{
				$field = array();
				$field['sort_column'] ="custom_field_${k}_value";
				$field['label']= $this->Item->get_custom_field($k);
			
				if ($this->Item->get_custom_field($k,'type') == 'checkbox')
				{
					$format_function = 'boolean_as_string';
				}
				elseif($this->Item->get_custom_field($k,'type') == 'date')
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
	function get_custom_field($number,$key="name")
	{
		static $config_data;
		
		if (!$config_data)
		{
			$config_data = unserialize($this->config->item('item_custom_field_prefs'));
		}
		
		return isset($config_data["custom_field_${number}_${key}"]) && $config_data["custom_field_${number}_${key}"] ? $config_data["custom_field_${number}_${key}"] : FALSE;
	}

	//Santosh Changes
	function get_item_not_count_default_columns()
	{
		return array('item_name','category_id','item_number','product_id','cost_price','unit_price','actual_quantity','count');

	}
	function get_item_not_count_displayable_columns()
	{
		$return  = array(
			'item_name' => 													array('sort_column' => 'name','label' => lang('common_item_name'), 'data_function' => 'item_quantity_data_function','format_function' => 'item_name_formatter','html' => TRUE),
			'category_id' => 											array('sort_column' => 'category','label' => lang('common_category')),
			'item_id' => 												array('sort_column' => 'item_id', 'label' => lang('common_item_id')),
			'item_number' => 										array('sort_column' => 'item_number','label' => lang('common_item_number'), 'data_function' => 'item_number_data_function', 'format_function' => 'item_number_formatter'),
			'product_id' => 										array('sort_column' => 'product_id','label' => lang('common_product_id')),
			'cost_price' => 										array('sort_column' => 'cost_price','label' => lang('common_cost_price'),'format_function' => 'to_currency_and_edit_item_price','data_function' => 'item_id_data_function', 'html' => TRUE),
			'total_value_by_cost_price' => 							array('sort_column' => 'total_value_by_cost_price','label' => lang('items_total_value_by_cost_price'),'format_function' => 'to_currency','data_function' => 'item_id_data_function', 'html' => TRUE),
			'unit_price' => 										array('sort_column' => 'unit_price','label' => lang('common_unit_price'),'format_function' => 'to_currency_and_edit_item_price','data_function' => 'item_id_data_function', 'html' => TRUE),
			'total_value_by_selling_price' => 						array('sort_column' => 'total_value_by_selling_price','label' => lang('items_total_value_by_selling_price'),'format_function' => 'to_currency','data_function' => 'item_id_data_function', 'html' => TRUE),
			'actual_quantity' =>									array('sort_column' => 'quantity','label' => lang('items_actual_on_hand'),'data_function' => 'item_quantity_data_function','format_function' => 'item_quantity_format', 'html' => TRUE),
			'count' => 												array('sort_column' => 'variation_count','label' => lang('common_count'),'format_function' => 'to_quantity_variation', 'data_function' => 'item_id_data_function', 'html' => TRUE),	
			'barcode_name' => 									array('sort_column' => 'barcode_name','label' => lang('common_barcode_name'), 'data_function' => 'item_quantity_data_function','format_function' => 'item_name_formatter','html' => TRUE),
			'category_id' => 										array('sort_column' => 'category','label' => lang('common_category_full_path'),'format_function' => 'get_full_category_path'),
			'supplier_company_name' => 					array('sort_column' => 'supplier_company_name','label' => lang('common_supplier')),
			'location_cost_price' => 						array('sort_column' => 'location_cost_price','label' => lang('common_location_cost_price'),'format_function' => 'to_currency_and_edit_location_item_price','data_function' => 'item_id_data_function', 'html' => TRUE),
			'location_unit_price' => 						array('sort_column' => 'location_unit_price','label' => lang('common_location_unit_price'),'format_function' => 'to_currency_and_edit_location_item_price','data_function' => 'item_id_data_function', 'html' => TRUE),
			'tax_group' => 											array('sort_column' => 'tax_group','label' => lang('common_tax_class')),
			//'quantity' =>												array('sort_column' => 'quantity','label' => lang('items_quantity'),'data_function' => 'item_quantity_data_function','format_function' => 'item_quantity_format', 'html' => TRUE),
			'tags' => 													array('sort_column' => 'tags','label' => lang('common_tags')),
			'description' => 										array('sort_column' => 'description','label' => lang('common_description')),
			'long_description' => 							array('sort_column' => 'long_description','label' => lang('common_long_description')),
			'info_popup' => 										array('sort_column' => 'info_popup','label' => lang('common_info_popup')),
			'size' => 													array('sort_column' => 'size','label' => lang('common_size')),
			'tax_included' => 									array('sort_column' => 'tax_included','label' => lang('common_prices_include_tax'),'format_function' => 'boolean_as_string'),
			'promo_price' => 										array('sort_column' => 'promo_price','label' => lang('items_promo_price'),'format_function' => 'promo_price_format'),
			'start_date' => 										array('sort_column' => 'start_date','label' => lang('items_promo_start_date'),'format_function' => 'date_as_display_date'),
			'end_date' => 											array('sort_column' => 'end_date','label' => lang('items_promo_end_date'),'format_function' => 'date_as_display_date'),
			'reorder_level' => 									array('sort_column' => 'reorder_level','label' => lang('items_reorder_level'),'format_function' => 'to_quantity'),
			'expire_days' => 										array('sort_column' => 'expire_days','label' => lang('items_days_to_expiration'),'format_function' => 'to_quantity'),
			'allow_alt_description'  => 				array('sort_column' => 'allow_alt_description','label' => lang('items_allow_alt_desciption'),'format_function' => 'boolean_as_string'),		
			'is_serialized'  => 								array('sort_column' => 'is_serialized','label' => lang('items_is_serialized'),'format_function' => 'boolean_as_string'),		
			'override_default_tax'  => 					array('sort_column' => 'override_default_tax','label' => lang('common_override_default_tax'),'format_function' => 'boolean_as_string'),		
			'is_ecommerce'  => 									array('sort_column' => 'is_ecommerce','label' => lang('items_is_ecommerce'),'format_function' => 'boolean_as_string'),		
			'ecommerce_product_id' => 					array('sort_column' => 'ecommerce_product_id','label' => lang('common_ecommerce_product_id')),
			'is_service'  => 										array('sort_column' => 'is_service','label' => lang('items_is_service'),'format_function' => 'boolean_as_string'),		
			'is_ebt_item'  => 									array('sort_column' => 'is_ebt_item','label' => lang('common_is_ebt_item'),'format_function' => 'boolean_as_string'),		
			'commission_amount'  => 						array('sort_column' => 'commission_percent','label' => lang('common_commission_amount'),'data_function' => 'commission_to_amount','format_function' => 'commission_amount_format'),		
			'commission_percent'  => 						array('sort_column' => 'commission_percent','label' => lang('items_commission_percent'),'format_function' => 'to_quantity'),		
			'commission_percent_type'  => 			array('sort_column' => 'commission_percent_type','label' => lang('items_commission_percent_type'),'format_function' => 'commission_percent_type_formater'),		
			'commission_fixed'  => 							array('sort_column' => 'commission_fixed','label' => lang('items_commission_fixed'),'format_function' => 'to_currency'),		
			'change_cost_price'  => 						array('sort_column' => 'change_cost_price','label' => lang('common_change_cost_price_during_sale'),'format_function' => 'boolean_as_string'),		
			'disable_loyalty'  => 							array('sort_column' => 'disable_loyalty','label' => lang('common_disable_loyalty'),'format_function' => 'boolean_as_string'),		
			'replenish_level'  => 							array('sort_column' => 'replenish_level','label' => lang('common_replenish_level'),'format_function' => 'to_quantity'),
			'max_discount_percent'  => 					array('sort_column' => 'max_discount_percent','label' => lang('common_max_discount_percent'),'format_function' => 'to_percent'),
			'min_edit_price'  => 								array('sort_column' => 'min_edit_price','label' => lang('common_min_edit_price'),'format_function' => 'to_currency'),
			'max_edit_price'  => 								array('sort_column' => 'max_edit_price','label' => lang('common_max_edit_price'),'format_function' => 'to_currency'),
			'has_variations' => 								array('sort_column' => 'has_variations','label' => lang('items_has_variations'),'format_function' => 'boolean_as_string_variation', 'data_function' => 'item_id_data_function','html' => TRUE),
			'variation_count' => 								array('sort_column' => 'variation_count','label' => lang('items_variation_count'),'format_function' => 'to_quantity_variation', 'data_function' => 'item_id_data_function', 'html' => TRUE),
			'last_modified' => 									array('sort_column' => 'last_modified','label' => lang('common_last_modified'),'format_function' => 'date_as_display_datetime', 'html' => TRUE),
			'last_edited' => 										array('sort_column' => 'last_edited','label' => lang('common_last_edited'),'format_function' => 'date_as_display_datetime', 'html' => TRUE),
			'weight'  => 											  array('sort_column' => 'weight','label' => lang('items_weight'),'format_function' => 'to_quantity'),
			'weight_unit'  => 											  array('sort_column' => 'weight_unit','label' => lang('items_weight_unit'),'format_function' => 'strsame'),
			'dimensions' => 								    array('sort_column' => 'length','label' => lang('items_dimensions'),'format_function' => 'dimensions_format', 'data_function' => 'dimensions_data','html' => TRUE),
			'allow_price_override_regardless_of_permissions'  => 	array('sort_column' => 'allow_price_override_regardless_of_permissions','label' => character_limiter(lang('common_allow_price_override_regardless_of_permissions'),38),'format_function' => 'boolean_as_string'),		
			'only_integer'  => 									array('sort_column' => 'only_integer','label' => character_limiter(lang('common_only_integer'),38),'format_function' => 'boolean_as_string'),		
			'is_series_package'  => 						array('sort_column' => 'is_series_package','label' => character_limiter(lang('items_sold_in_a_series'),38),'format_function' => 'boolean_as_string'),		
			'series_quantity'  => 							array('sort_column' => 'series_quantity','label' => character_limiter(lang('common_series_quantity'),38),'format_function' => 'to_quantity'),		
			'series_days_to_use_within'  => 		array('sort_column' => 'series_days_to_use_within','label' => character_limiter(lang('common_series_days_to_use_within'),38),'format_function' => 'to_quantity'),		
			'is_barcoded'  => 									array('sort_column' => 'is_barcoded','label' => lang('common_is_barcoded'),'format_function' => 'boolean_as_string'),		
			'item_inactive'  => 								array('sort_column' => 'item_inactive','label' => lang('common_inactive'),'format_function' => 'boolean_as_string'),		
			'default_quantity' =>								array('sort_column' => 'default_quantity','label' => lang('common_default_quantity'),'format_function' => 'to_quantity', 'html' => FALSE),
			'location' => 											array('sort_column' => 'location', 'label' => lang('items_location_at_store')),
			'disable_from_price_rules'  => 			array('sort_column' => 'disable_from_price_rules','label' => character_limiter(lang('common_disable_from_price_rules'),38),'format_function' => 'boolean_as_string'),		
			'is_favorite'  =>  									array('sort_column' => 'is_favorite','label' => lang('common_is_favorite'),'format_function' => 'boolean_as_string'),
			'loyalty_multiplier'  =>  					array('sort_column' => 'loyalty_multiplier', 'label' => lang('common_loyalty_multiplier'),'format_function' => 'to_quantity'),

		);
		
		if ($this->config->item('verify_age_for_products'))
		{
			$return['verify_age'] = array('sort_column' => 'verify_age','label' => lang('common_requires_age_verification'),'format_function' => 'boolean_as_string');		
			$return['required_age'] = array('sort_column' => 'required_age','label' => lang('common_required_age'),'format_function' => 'to_quantity');		
		}
		
		if ($this->config->item('hide_size_field'))
		{
			unset($return['size']);
		}
		
		for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
		{
			if($this->Item->get_custom_field($k) !== false)
			{
				$field = array();
				$field['sort_column'] ="custom_field_${k}_value";
				$field['label']= $this->Item->get_custom_field($k);
			
				if ($this->Item->get_custom_field($k,'type') == 'checkbox')
				{
					$format_function = 'boolean_as_string';
				}
				elseif($this->Item->get_custom_field($k,'type') == 'date')
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
	function get_item_not_count_custom_field($number,$key="name")
	{
		static $config_data;
		
		if (!$config_data)
		{
			$config_data = unserialize($this->config->item('item_custom_field_prefs'));
		}
		
		return isset($config_data["custom_field_${number}_${key}"]) && $config_data["custom_field_${number}_${key}"] ? $config_data["custom_field_${number}_${key}"] : FALSE;
	}

}

?>