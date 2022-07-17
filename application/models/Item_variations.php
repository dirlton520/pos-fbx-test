<?php
class Item_variations extends MY_Model
{
	function get_info($item_variation_id)
	{
		$this->db->select('*');
		$this->db->from("item_variations");
		$this->db->where("item_variations.id", $item_variation_id);
		
		return $this->db->get()->row();
	}
	
	function get_multiple_info($item_variation_ids = array())
	{
		$return = array();
		
		//get attributes
		$this->db->select('item_variations.item_number as item_variation_item_number,attributes.name as attribute_name, item_variations.unit_price as unit_price, item_variations.cost_price as cost_price, item_variations.name as variation_name, '.$this->db->dbprefix('attributes').'.id as attribute_id,'.$this->db->dbprefix('attribute_values').'.name as attribute_value_name,CONCAT('.$this->db->dbprefix('attributes').'.name,": ",'.$this->db->dbprefix('attribute_values').'.name) as label, item_variation_attribute_values.attribute_value_id as attribute_value_id, '.$this->db->dbprefix('item_variations').'.id as item_variation_id', FALSE);
		$this->db->from('item_variations');
		$this->db->join('item_variation_attribute_values', 'item_variations.id = item_variation_attribute_values.item_variation_id');
		$this->db->join('attribute_values', 'attribute_values.id = item_variation_attribute_values.attribute_value_id');
		$this->db->join('attributes', 'attributes.id = attribute_values.attribute_id');
		
		if (!empty($item_variation_ids))
		{
			$this->db->group_start();
			$item_variation_ids_chunk = array_chunk($item_variation_ids,25);
			foreach($item_variation_ids_chunk as $item_variations)
			{
				$this->db->or_where_in('item_variations.id',$item_variations);
			}
			$this->db->group_end();
		}
		else
		{
			$this->db->where('1', '2', FALSE);
		}
		
		$attributes_and_values = $this->db->get()->result_array();
		
		foreach($attributes_and_values as $attr_value)
		{
			$attribute_value_data = array(
				'unit_price' => $attr_value['unit_price'],
				'cost_price' => $attr_value['cost_price'],
				'variation_name' => $attr_value['variation_name'],
				'item_variation_item_number' => $attr_value['item_variation_item_number'],
				'attribute_name' => $attr_value['attribute_name'],
				'attribute_id' => $attr_value['attribute_id'],
				'attribute_value_name' => $attr_value['attribute_value_name'],
				'label' =>$attr_value['label'] ,
				'value' => $attr_value['attribute_value_id'],
			);
		
			
			$return[$attr_value['item_variation_id']] = $attribute_value_data;
		}
		
		return $return;
	}
	
	function get_item_info_for_variation($item_variation_id)
	{
		$this->load->model('Item');
		$var_info = $this->get_info($item_variation_id);
		return $this->Item->get_info($var_info->item_id);
	}
	
	function get_all($item_id = false)
	{
		$this->db->select('*');
		$this->db->from("item_variations");
		
		if($item_id)
		{
			$this->db->where("item_variations.item_id", $item_id);
		}
		
		$this->db->group_by("id");
		$this->db->where('deleted',0);
		$query = $this->db->get();
				
		return $query->result_array();
	}
	
	function get_variation_name($variation_id)
	{
		$this->db->select('name');
		$this->db->from("item_variations");
		$this->db->where("item_variations.id", $variation_id);
		
		$query = $this->db->get();
				
		if($query->num_rows() == 1)
		{
			$row = $query->row();
			if($row->name)
			{
				return $row->name;
			}
		}
		
		$attributes = $this->get_attributes($variation_id);
		return implode(', ', array_column($attributes,'label'));
	}
	
	/*
	Returns all the variations for a given item
	*/
	function get_variations($item_id = array(), $include_deleted = false)
	{
		$this->db->from('item_variations');
		
		if (is_array($item_id))
		{
			if (!empty($item_id))
			{
				$this->db->group_start();
				$item_id_chunks = array_chunk($item_id,25);
				
				foreach($item_id_chunks as $item_id_chunk)
				{
					$this->db->or_where_in('item_id',$item_id_chunk);
				}
				
				$this->db->group_end();
			}
		}
		else
		{
			$this->db->where('item_id', $item_id);
		}
		
		if(!$include_deleted)
		{
			$this->db->where('deleted',0);
		}
		
		$return = array();
		
		if (is_array($item_id))
		{
			foreach($this->db->get()->result_array() as $result)
			{
				$return[$result['item_id']][$result['id']]['name'] = $result['name'];
				$return[$result['item_id']][$result['id']]['item_number'] = $result['item_number'];
				$return[$result['item_id']][$result['id']]['is_ecommerce'] = $result['is_ecommerce'];
				$return[$result['item_id']][$result['id']]['cost_price'] = $result['cost_price'];
				$return[$result['item_id']][$result['id']]['unit_price'] = $result['unit_price'];
				$return[$result['item_id']][$result['id']]['promo_price'] = $result['promo_price'];
				$return[$result['item_id']][$result['id']]['start_date'] = $result['start_date'];
				$return[$result['item_id']][$result['id']]['end_date'] = $result['end_date'];
				$return[$result['item_id']][$result['id']]['reorder_level'] = $result['reorder_level'];
				$return[$result['item_id']][$result['id']]['replenish_level'] = $result['replenish_level'];
				$return[$result['item_id']][$result['id']]['ecommerce_variation_id'] = $result['ecommerce_variation_id'];
				$return[$result['item_id']][$result['id']]['attributes'] = $this->get_attributes($result['id']);
				$return[$result['item_id']][$result['id']]['image'] = $this->get_image($result['id']);
			}
		}
		else
		{
			foreach($this->db->get()->result_array() as $result)
			{
				$return[$result['id']]['name'] = $result['name'];
				$return[$result['id']]['item_number'] = $result['item_number'];
				$return[$result['id']]['is_ecommerce'] = $result['is_ecommerce'];
				$return[$result['id']]['cost_price'] = $result['cost_price'];
				$return[$result['id']]['unit_price'] = $result['unit_price'];
				$return[$result['id']]['promo_price'] = $result['promo_price'];
				$return[$result['id']]['start_date'] = $result['start_date'];
				$return[$result['id']]['end_date'] = $result['end_date'];
				$return[$result['id']]['reorder_level'] = $result['reorder_level'];
				$return[$result['id']]['replenish_level'] = $result['replenish_level'];
				$return[$result['id']]['ecommerce_variation_id'] = $result['ecommerce_variation_id'];
				$return[$result['id']]['attributes'] = $this->get_attributes($result['id']);
				$return[$result['id']]['image'] = $this->get_image($result['id']);
			}
		}
		
		return $return;
	}
	
	function get_image($item_variation_id)
	{
		$this->db->from('item_images');
		$this->db->where('item_variation_id', $item_variation_id);	
		return $this->db->get()->row_array();
	}
	
	function get_attributes($item_variation_id = array())
	{
		//get attributes
		$this->db->select('attributes.name as attribute_name, '.$this->db->dbprefix('attributes').'.id as attribute_id,'.
		$this->db->dbprefix('attribute_values').'.name as attribute_value_name, CONCAT('.$this->db->dbprefix('attributes').'.name,": ",'.
		$this->db->dbprefix('attribute_values').'.name) as label, item_variation_attribute_values.attribute_value_id as attribute_value_id, '.
		$this->db->dbprefix('item_variations').'.id as item_variation_id', FALSE);
		
		$this->db->from('item_variations');
		$this->db->join('item_variation_attribute_values', 'item_variations.id = item_variation_attribute_values.item_variation_id');
		$this->db->join('attribute_values', 'attribute_values.id = item_variation_attribute_values.attribute_value_id');
		$this->db->join('attributes', 'attributes.id = attribute_values.attribute_id');
		
		if (is_array($item_variation_id))
		{
			if (!empty($item_variation_id))
			{
				$this->db->group_start();
				$item_variation_ids_chunk = array_chunk($item_variation_id,25);
				foreach($item_variation_ids_chunk as $item_variations)
				{
					$this->db->or_where_in('item_variations.id',$item_variations);
				}
				$this->db->group_end();

			}
		}
		else
		{
			$this->db->where('item_variations.id', $item_variation_id);
		}
		
		//This order by is here so we get consistent order for variation attribute grid selection
		$this->db->order_by('attributes.id');
		
		$attributes_and_values = $this->db->get()->result_array();
		
		$return = array();
		
	
			foreach($attributes_and_values as $attr_value)
			{
				$attribute_value_data = array(
					'attribute_name' => $attr_value['attribute_name'],
					'attribute_id' => $attr_value['attribute_id'],
					'attribute_value_name' => $attr_value['attribute_value_name'],
					'label' =>$attr_value['label'] ,
					'value' => $attr_value['attribute_value_id']
				);
				
				if (is_array($item_variation_id))
				{	
					$return[$attr_value['item_variation_id']][] = $attribute_value_data;
				}	
				else
				{
						$return[] = $attribute_value_data;
				}
		}	
				
		return $return;
	}
	
	function lookup_item_by_item_variation_item_number_quantity_unit($item_identifer)
	{
		$this->db->select("item_id,id");
		$this->db->from("items_quantity_units");	
		$this->db->where("quantity_unit_item_number", $item_identifer);
		
		$this->db->limit(1);
		
		$query = $this->db->get();
				
		if($query->num_rows() == 1)
		{
			$row = $query->row();
			return $row->item_id.'@'.$row->id;
		}
		
		return false;
		
		
	}
	
	function lookup_item_by_item_variation_item_number($item_identifer)
	{
		$item_variation_id = false;
		if (($item_identifer_parts = explode('#', $item_identifer)) !== false)
		{
				$item_identifer = $item_identifer_parts[0];
				$item_variation_id = isset($item_identifer_parts[1]) ? $item_identifer_parts[1] : false;
		}
		
		$this->db->select("item_id,id");
		$this->db->from("item_variations");
		
		if(!empty($item_variation_id))
		{
			$this->db->where("id", $item_variation_id);
		}
		else 
		{
			$this->db->where("item_number", $item_identifer);
		}
		
		$this->db->limit(1);
		
		$query = $this->db->get();
				
		if($query->num_rows() == 1)
		{
			$row = $query->row();
			return $row->item_id.'#'.$row->id;
		}
		
		return false;
	}
	
	
	function lookup_item_variation_id($item_identifer)
	{
		$item_variation_id = false;
		if (($item_identifer_parts = explode('#', $item_identifer)) !== false)
		{
				$item_identifer = $item_identifer_parts[0];
				$item_variation_id = isset($item_identifer_parts[1]) ? $item_identifer_parts[1] : false;
		}
		
		$this->db->select("id", false);
		$this->db->from("item_variations");
		
		if(!empty($item_variation_id))
		{
			$this->db->where("id", $item_variation_id);
		}
		else 
		{
			$this->db->where("item_number", $item_identifer);
		}
		
		$this->db->limit(1);
		
		$query = $this->db->get();
				
		if($query->num_rows() == 1)
		{
			return $query->row()->id;;
		}
		
		return false;
	}
	
	function lookup($item_id, $attribute_value_ids)
	{
		if (count($attribute_value_ids) == 0)
		{
			return false;
		}
		
		$this->db->select("id, count(*) as count");
		$this->db->from("item_variations");
		$this->db->join("item_variation_attribute_values", "item_variations.id = item_variation_attribute_values.item_variation_id", "inner");
		$this->db->where("id IN (SELECT `item_variation_id` FROM `".$this->db->dbprefix('item_variation_attribute_values')."` GROUP BY `item_variation_id` HAVING count(*) = ".count($attribute_value_ids).")",NULL,FALSE);
		$this->db->where_in("item_variation_attribute_values.attribute_value_id", $attribute_value_ids);
		$this->db->where("item_variations.item_id", $item_id);
		
		$this->db->group_by("id");
		$this->db->having("count = ".count($attribute_value_ids));
		
		$this->db->limit(1);
		
		$query = $this->db->get();
						
		if($query->num_rows() == 1)
		{
			return $query->row()->id;
		}
		
		return false;
	}
	
	private function save_attributes($attribute_value_ids, $item_variation_id)
	{
		foreach ($attribute_value_ids as $value)
		{
		    $value = trim($value);
		    if (empty($value))
				{
					return null;
				}
		    else
				{
					continue;
				}
		}
		
		$this->load->model('Item_attribute_value');
	
		$this->db->delete('item_variation_attribute_values', array('item_variation_id' => $item_variation_id));
	
		foreach($attribute_value_ids as $attribute_value_id)
		{	
			$data = array(
        'attribute_value_id' => $attribute_value_id,
        'item_variation_id' => $item_variation_id
			);

			$this->db->replace('item_variation_attribute_values', $data);
		}
		
		//update last modified on variation
		return $this->save(array(), $item_variation_id);
	}
	
	function save($data, $item_variation_id = false, $variation_attribute_value_ids = false)
	{
		if(isset($data['ecommerce_last_modified']))
		{//if comming from ecommerce
			if(!$item_variation_id)
			{//new item variation
				$data['last_modified'] = $data['ecommerce_last_modified'];
			}
			//otherwise dont set last_modified (we want the current value preserved)
		}
		else
		{ //existing
			$data['last_modified'] = date('Y-m-d H:i:s');
		}
		
		if(is_array($variation_attribute_value_ids) && count($variation_attribute_value_ids) == 0)
		{
			return false;
		}		
		
		if($item_variation_id)
		{
			
			if (isset($data['unit_price']) || isset($date['cost_price']))
			{
				$item_info = $this->get_info($item_variation_id);
				$item_id = $item_info->item_id;
				$this->Item->save_price_history($item_id,$item_variation_id,NULL,isset($data['unit_price']) ? $data['unit_price'] : NULL,isset($data['cost_price']) ? $data['cost_price'] : NULL);
			}
			
			if (!isset($data['deleted']))
			{
				$data['deleted'] = 0;
			}
			
			$this->db->where('id', $item_variation_id);
			$this->db->update('item_variations', $data);
		}
		else
		{
			if($variation_attribute_value_ids)
			{
				$this->db->insert('item_variations', $data);
				$item_variation_id = $this->db->insert_id();
				$item_info = $this->get_info($item_variation_id);
				$item_id = $item_info->item_id;
				
				if (isset($data['unit_price']) || isset($date['cost_price']))
				{
					$this->Item->save_price_history($item_id,$item_variation_id,NULL,isset($data['unit_price']) ? $data['unit_price'] : NULL,isset($data['cost_price']) ? $data['cost_price'] : NULL, TRUE);
				}
				
			}
		}
		
		if($variation_attribute_value_ids)
		{
			$this->save_attributes($variation_attribute_value_ids,$item_variation_id);
		}
		
		return $item_variation_id;
	}
	
	function cleanup()
	{
		$item_variations = $this->db->dbprefix('item_variations');
		$items_table = $this->db->dbprefix('items');
		$now =  date('Y-m-d H:i:s');
		return $this->db->query("UPDATE $item_variations SET deleted=1,item_number=NULL, last_modified='$now' WHERE item_id IN (SELECT item_id FROM $items_table WHERE deleted = 1)");
	}
	
	function delete($item_variation_id)
	{
		$this->db->where('id',$item_variation_id);
		$this->db->update('item_variations',array('deleted' => 1,'item_number' => NULL,'last_modified' => date('Y-m-d H:i:s')));
	}
	
	function auto_create($item_id)
	{
		$this->load->model('Item_attribute');
		$this->load->model('Item_attribute_value');
		
		$attributes = $this->Item_attribute->get_attributes_for_item_with_attribute_values($item_id);
		$attribute_ids = array_keys($attributes);
		$attribute_values = $this->db->dbprefix('attribute_values');
		$item_attribute_values = $this->db->dbprefix('item_attribute_values');
		
		$sql_selects = '';
		$sql_joins = '';
		$sql_wheres = '';
		
		if(count($attribute_ids) == 0)
		{
			return false;
		}
		
		for($k=0;$k<count($attribute_ids);$k++)
		{
			if ($k==0)
			{
				$sql_wheres = 'WHERE t0.attribute_id ='.$attribute_ids[$k] .' and t0.id IN (SELECT attribute_value_id FROM '.$item_attribute_values.' WHERE item_id = '.$item_id.')';
			}
			else
			{
				$sql_joins.=" JOIN $attribute_values as t$k ON t$k.attribute_id = ".$attribute_ids[$k].' and t'.$k.'.id IN (SELECT attribute_value_id FROM '.$item_attribute_values.' WHERE item_id = '.$item_id.')'; 
			}
			$sql_selects.= "t$k.name as 'name_${k}',t$k.id as 'id_${k}',";
		}
		
		$sql_selects = rtrim($sql_selects,',');
		
		$query = "SELECT $sql_selects FROM $attribute_values as t0 $sql_joins $sql_wheres";
		
		$variations = $this->db->query($query)->result_array();
		foreach($variations as $variation)
		{
			$attribute_value_ids_for_variation = array();
			
			for($k=0;$k<count($attribute_ids);$k++)
			{
				$attribute_value_ids_for_variation[] = $variation['id_'.$k];
			}
			
			$variation_id = $this->lookup($item_id, $attribute_value_ids_for_variation);
			
	 	  $data = array('item_id' => $item_id);
			
		  $this->save($data, $variation_id, $attribute_value_ids_for_variation);
		}
	}
}
?>