<?php
/*
This interface(abstract class) is implemented by any model intergating online store with PHPPOS
*/
abstract class Ecom extends MY_Model
{
	public $log;
	public $ecommerce_store_location;
	public $ecommerce_store_locations;
	
	public function __construct()
	{
		$this->log ='';
		$this->ecommerce_store_location=$this->config->item('ecom_store_location') ? $this->config->item('ecom_store_location') : 1;
		
		if ($this->db->table_exists('ecommerce_locations'))
		{
			$this->ecommerce_store_locations = array_keys($this->Appconfig->get_ecommerce_locations());			
		}
		
		set_time_limit(0);
		ini_set('max_input_time','-1');
		$this->load->helper('date');
		
	}
		
	protected function build_category_paths($tree, $path = '') 
	{
	  $result = array();
	  foreach ($tree as $id => $cat) 
		{
	      $result[$id] = $path . $cat['name'];
	      if (isset($cat['children'])) 
				{
	          $result += $this->build_category_paths($cat['children'], $result[$id] . '|');
	      }
	  }
	  return $result;
	}
	
	public function get_categories_from_db()
	{
		$cat_map = array();
		//Get cats from database cache
		$this->load->model('Category');
		$phppos_cats = array_flip($this->Category->get_all_categories_and_sub_categories_as_indexed_by_name_key(TRUE,'strsame'));

		$this->db->from('categories');
		foreach($this->db->get()->result_array() as $row)
		{
			if(isset($phppos_cats[$row['id']]))
			{
				$cat_map[$phppos_cats[$row['id']]] = $row['ecommerce_category_id'];
			}
		}
		
		return $cat_map;
	}
	
	public function get_tax_classes_from_db()
	{
		$tax_classes = array();
		$this->load->model('Tax_class');
		
		foreach($this->Tax_class->get_all_for_ecommerce() as $phppos_tax_class_id => $phppos_tax_class)
		{
			if($phppos_tax_class['ecommerce_tax_class_id'])
			{
				$tax_classes[strtoupper($phppos_tax_class['name'])] = $phppos_tax_class['ecommerce_tax_class_id'];
			}
		}
		
		return $tax_classes;
	}
	
	public function get_tax_classes_taxes_from_db($phppos_tax_class_id)
	{
		$this->load->model('Tax_class');
		return $this->Tax_class->get_taxes($phppos_tax_class_id, false);
	}
	
	public function get_tags_from_db()
	{
		$tags = array();
		$this->load->model('Tag');
		
		foreach($this->Tag->get_all_for_ecommerce() as $phppos_tag_id => $phppos_tag)
		{
			if($phppos_tag['ecommerce_tag_id'])
			{
				$tags[strtoupper($phppos_tag['name'])] = $phppos_tag['ecommerce_tag_id'];
			}
		}
		
		return $tags;
	}
	
	public function get_attributes_from_db()
	{
		$attributes = array();
		$this->db->from('attributes');
		$this->db->where('attributes.deleted',0);
		foreach($this->db->get()->result_array() as $row)
		{
			$ecommerce_attribute_id = $row['ecommerce_attribute_id'];
			$name = $row['name'];
			$attributes[] = array('name' =>$name,'ecommerce_attribute_id' =>(int)$ecommerce_attribute_id);
		}
		return $attributes;
	}
	
	public function get_attribute_values_from_db($woo_attribute_id)
	{
		$this->load->model('Item_attribute_value');
			
		//Get attributes from database cache
		$attribute_values= array();
		$phppos_attr_id = $this->get_attribute_id_from_ecommerce_attribute_id($woo_attribute_id);
		
		if($phppos_attr_id)	
		{
			foreach($this->Item_attribute_value->get_values_for_attribute($phppos_attr_id)->result_array() as $row)
			{			
				if ($row['ecommerce_attribute_term_id'])
				{
					$attribute_values[] = array('name' =>$row['name'],'ecommerce_attribute_term_id' =>(int)$row['ecommerce_attribute_term_id']);
				}
			}
		}
		
		return $attribute_values;
	}
	
	public static function get_ecom_model()
	{
    	$CI =& get_instance();	
		
		$CI->load->model('Appconfig');
		
		if($CI->Appconfig->get_key_directly_from_database("ecommerce_platform") == 'woocommerce')
		{
			$CI->load->model('Woo');
			return $CI->Woo;
		}
		
		if($CI->Appconfig->get_key_directly_from_database("ecommerce_platform") == 'shopify')
		{
			$CI->load->model('Shopify');
			return $CI->Shopify;
		}
		
	}
	
	function get_attribute_id_from_ecommerce_attribute_name($attribute_name, $item_id = false)
	{
		$this->db->from('attributes');
		
		if($attribute_name)
		{
			$this->db->where('name',$attribute_name);
		}
		
		if($item_id !== FALSE)
		{
			$this->db->where('item_id',$item_id);
		}
		
		if ($row = $this->db->get()->row_array())
		{
			return $row['id'];
		}
		
		return null;
	}
	
	function get_attribute_id_from_ecommerce_attribute_id($ecommerce_attribute_id, $item_id = false, $attribute_name = false)
	{
		$this->db->from('attributes');
		
		if($ecommerce_attribute_id)
		{
			$this->db->where('ecommerce_attribute_id',$ecommerce_attribute_id);
		}
		else
		{
			if(!$item_id || !$attribute_name)
			{
				return null;
			}
			$this->db->where('name',$attribute_name);
			$this->db->where('item_id',$item_id);
		}
		
		if ($row = $this->db->get()->row_array())
		{
			return $row['id'];
		}
		
		return null;
	}
	
	function get_ecommerce_attribute_id_from_attribute_id($attribute_id)
	{
		$this->db->from('attributes');
		$this->db->where('id',$attribute_id);
		
		if ($row = $this->db->get()->row_array())
		{
			return $row['ecommerce_attribute_id'];
		}
		
		return null;
	}
	
	function get_attribute_value_id_from_ecommerce_attribute_term_id($ecommerce_attribute_term_id)
	{
		$this->db->from('attribute_values');
		$this->db->where('ecommerce_attribute_term_id',$ecommerce_attribute_term_id);
		
		if ($row = $this->db->get()->row_array())
		{
			return $row['id'];
		}
		
		return null;
	}
	
	function lookup_attribute_value_id_from_attribute_id_and_option($attribute_id, $option_value,$item_id = FALSE)
	{
		$this->db->from('attribute_values');
		$this->db->where('attribute_id',$attribute_id);
		$this->db->where('name',(string)$option_value);
		
		if ($item_id)
		{
			$this->db->join('item_attribute_values','item_attribute_values.attribute_value_id = attribute_values.id');
			$this->db->where('item_id',$item_id);
		}
		
		if($row = $this->db->get()->row_array())
		{
			return $row['id'];
		}
		
		return null;
	}
	
	/*
	Gets ecommerce_attribute_id of attribute given an attribute_id
	*/
	function get_ecommerce_attribute_id_for_attribute($attribute_id)
	{
		$this->db->from('attributes');
		$this->db->where('id', $attribute_id);
		$result = $this->db->get();
		
		if ($result->num_rows() > 0) {
			$attribute = $result->row_array();
			return $attribute['ecommerce_attribute_id'];
		}
		
		return null;
	}
	
	/*
	Gets ecommerce_attribute_id of attribute given an attribute_id
	*/
	function get_ecommerce_variation_id_for_variation($item_variation_id)
	{
		$this->db->from('item_variations');
		$this->db->where('id', $item_variation_id);
		$result = $this->db->get();
		
		if ($result->num_rows() > 0) {
			$variation = $result->row_array();
			return $variation['ecommerce_variation_id'];
		}
		
		return null;
	}
	
	/*
	Gets id of item variation with given ecommerce_product_variation_id
	*/
	function get_variation_id_for_ecommerce_product_variation($ecommerce_variation_id)
	{
		$this->db->from('item_variations');
		$this->db->where('ecommerce_variation_id', $ecommerce_variation_id);
		$result = $this->db->get();
		if ($result->num_rows() == 1)
		{
			$variation = $result->row_array();
			return $variation['id'];
		}
		
		return null;
	}
	
	function get_ecommerce_orders_not_completed()
	{
		$this->db->from('sales');
		$this->db->where('is_ecommerce', 1);
		$this->db->where('ecommerce_status!="completed"');
		
		return $this->db->get()->result_array();
	}
	
	function get_ecommerce_order_ids_not_completed()
	{
		$order_ids = array();
		foreach($this->get_ecommerce_orders_not_completed() as $sale)
		{
			$order_ids[] = (int)$sale['ecommerce_order_id'];
		}
		
		return $order_ids;
	}
	
	/*
	Gets id of item with given ecommerce_product_id
	*/
	function get_sale_id_for_ecommerce_order_id($ecommerce_order_id)
	{
		$this->db->from('sales');
		$this->db->where('ecommerce_order_id', $ecommerce_order_id);
		$result = $this->db->get();
		if ($result->num_rows() >= 1)
		{
			$item=$result->row_array();
			return $item['sale_id'];
		}
		
		return null;
	}
		
	/*
	Gets id of item with given ecommerce_product_id
	*/
	function get_item_id_for_ecommerce_product($ecommerce_product_id)
	{
		$this->db->from('items');
		$this->db->where('ecommerce_product_id', (string)$ecommerce_product_id);
		$result = $this->db->get();
		if ($result->num_rows() == 1)
		{
			$item=$result->row_array();
			return $item['item_id'];
		}
		else
		{
			return $this->Item->create_or_update_ecommerce_item();
		}
		
		return null;
	}
		
	/*
	Gets id of item with given ecommerce_product_id
	*/
	function get_ecommerce_product_id_for_item_id($item_id)
	{		
		$this->db->from('items');
		$this->db->where('item_id', $item_id);
		$result = $this->db->get();
		if ($result->num_rows() == 1)
		{
			$item=$result->row_array();
			
			if ($item['ecommerce_product_id'])
			{
				return $item['ecommerce_product_id'];
			}
		}
		
		return null;
	}
	
	/*
	Get ecommerce product quantity stored from previous sync
	*/
	function get_ecommerce_product_quantity($item_id)
	{
		$this->db->from('items');
		$this->db->where('item_id', $item_id);
		$result = $this->db->get();
		if ($result->num_rows() == 1)
		{
			$item=$result->row_array();
			return $item['ecommerce_product_quantity'];
		}
		
		return null;
	}
	
	/*
	Get ecommerce variation quantity stored from previous sync
	*/
	function get_ecommerce_variation_quantity($variation_id)
	{
		$this->db->from('item_variations');
		$this->db->where('id', $variation_id);
		$result = $this->db->get();
		if ($result->num_rows() == 1)
		{
			$item=$result->row_array();
			return $item['ecommerce_variation_quantity'];
		}
		
		return null;
	}
	
	/*
	Get ecommerce product quantity stored from previous sync
	*/
	function get_ecommerce_product_id($item_id)
	{
		$this->db->from('items');
		$this->db->where('item_id', $item_id);
		$result = $this->db->get();
		if ($result->num_rows() == 1)
		{
			$item=$result->row_array();
			return $item['ecommerce_product_id'];
		}
		
		return null;
	}
	
	function get_image_file_id_for_ecommerce_image($ecommerce_image_id)
	{
		if(!$ecommerce_image_id)
		{
			return null;
		}
		
		$this->db->from('item_images');
		$this->db->where('ecommerce_image_id', $ecommerce_image_id);
		$result = $this->db->get();
		if ($result->num_rows() >= 1)
		{
			$image=$result->row_array();
			return $image['image_id'];
		}
		
		return null;
	}
		
	
	function update_ecommerce_product_quantity($item_id, $quantity)
	{
		$this->db->where('item_id', $item_id);
		return $this->db->update('items', array('ecommerce_product_quantity' => $quantity));	
	}
	
	function update_ecommerce_variation_quantity($variation_id, $quantity)
	{
		$this->db->where('id', $variation_id);
		return $this->db->update('item_variations', array('ecommerce_variation_quantity' => $quantity));	
	}
	
	/*
	Get PHPPOS item quantity for e-commerce location
	*/
	function get_item_quantity($item_id)
	{
		$this->load->model('Item_location');
		$return = 0;
		
		foreach ($this->ecommerce_store_locations as $location_id)
		{
			$return+= $this->Item_location->get_location_quantity($item_id, $location_id);
		}
		
		return $return;
	}
		
	/*
	Get PHPPOS item variation quantity for e-commerce location
	*/
	function get_item_variation_quantity($variation_id)
	{
		$this->load->model('Item_variation_location');
		$return = 0;
		
		foreach ($this->ecommerce_store_locations as $location_id)
		{
			$return+=$this->Item_variation_location->get_location_quantity($variation_id,$location_id);
		}
		
		return $return;
	}
	
	function get_all_item_images_for_ecommerce_with_main_image_1st($item_id, $variation_included = true)
	{
		$item_id = $this->db->escape($item_id);
		$variation_where = '';
		if($variation_included == false){
			$variation_where = ' AND  `phppos_item_images`.`item_variation_id` IS NULL';
		}
		return $this->db->query("SELECT `phppos_item_images`.* FROM `phppos_item_images` JOIN `phppos_items` ON `phppos_items`.`item_id` = `phppos_item_images`.`item_id` WHERE `phppos_item_images`.`item_id` = $item_id $variation_where ORDER BY image_id=main_image_id DESC, `id`")->result_array();
	}
	
	function get_all_item_images_for_ecommerce($item_id)
	{
		if($this->config->item('use_main_image_as_default_image_in_e_commerce')){
			$all_images = $this->get_all_item_images_for_ecommerce_with_main_image_1st($item_id);
			return $all_images;
		}

		$this->db->from('item_images');
		$this->db->where('item_id',$item_id);
		$this->db->order_by('id');
	  	return $this->db->get()->result_array();
	}	
	
	function get_item_images_for_ecommerce($item_id)
	{
		if($this->config->item('use_main_image_as_default_image_in_e_commerce')){
			$all_images = $this->get_all_item_images_for_ecommerce_with_main_image_1st($item_id, false);
			return $all_images;
		}

		$this->db->from('item_images');
		$this->db->where('item_id',$item_id);
		$this->db->where('item_variation_id', null);
		$this->db->order_by('id');
		
	  	return $this->db->get()->result_array();
	}
	
	function get_item_variation_images_for_ecommerce($item_variation_id)
	{
		$this->db->from('item_images');
		$this->db->where('item_variation_id', $item_variation_id);
		$this->db->order_by('id');
	  return $this->db->get()->result_array();
	}
	
	function link_tag($tag_id, $ecommerce_tag_id)
	{
		$this->db->where('id', $tag_id);
		$this->db->update('tags', array(
			'ecommerce_tag_id' => $ecommerce_tag_id,
		));
	}
	
	function unlink_tag($tag_id)
	{
		$this->db->where('id', $tag_id);
		$this->db->update('tags', array(
			'ecommerce_tag_id' => NULL,
		));
	}
	
	function link_tax_class($phppos_tax_class_id, $ecommerce_tax_id)
	{
		$this->db->where('id', $phppos_tax_class_id);
		$this->db->update('tax_classes', array(
			'ecommerce_tax_class_id' => $ecommerce_tax_id,
		));
	}
	
	function unlink_tax_class($tax_class_id)
	{
		$this->db->where('id', $tax_class_id);
		$this->db->update('tax_classes', array(
			'ecommerce_tax_class_id' => NULL,
		));
	}
	
	function link_category($category_id, $ecommerce_category_id)
	{
		$this->db->where('id', $category_id);
		$this->db->update('categories', array(
			'ecommerce_category_id' => $ecommerce_category_id,
		));
	}
	
	function unlink_category($category_id)
	{
		$this->db->where('id', $category_id);
		$this->db->update('categories', array(
			'ecommerce_category_id' => NULL,
	));
	}
	
	function link_attribute_value($attribute_value_id, $ecommerce_attribute_term_id)
	{
		$this->db->where('id', $attribute_value_id);
		$this->db->update('attribute_values', array(
			'ecommerce_attribute_term_id' => $ecommerce_attribute_term_id,
		));
	}
	
	function unlink_attribute_value($attribute_value_id)
	{
		$this->db->where('id', $attribute_value_id);
		$this->db->update('attribute_values', array('ecommerce_attribute_term_id' => NULL));
	}
	
	function link_attribute($attribute_id, $ecommerce_attribute_id)
	{
		$this->db->where('id', $attribute_id);
		$this->db->update('attributes', array(
			'ecommerce_attribute_id' => $ecommerce_attribute_id,
		));
	}
	
	function unlink_attribute($attribute_id)
	{
		$this->db->where('id', $attribute_id);
		$this->db->update('attributes', array(
			'ecommerce_attribute_id' => NULL,
		));
	}
		
	
	function link_item_variation($variation_id, $ecommerce_variation_id, $ecommerce_variation_quantity, $ecommerce_last_modified,$ecommerce_inventory_item_id = NULL)
	{
		$data = array(
			'ecommerce_variation_id' => $ecommerce_variation_id, 
			'ecommerce_last_modified' => $ecommerce_last_modified,
			'last_modified' => $ecommerce_last_modified,
			'ecommerce_inventory_item_id' => $ecommerce_inventory_item_id,
		);
		
		if ($ecommerce_variation_quantity !== NULL)
		{
			$data['ecommerce_variation_quantity'] = $ecommerce_variation_quantity;
			
		}
		$this->db->where('id', $variation_id);
		$this->db->update('item_variations', $data);
	}
	
	function unlink_item_variation($item_variation_id)
	{
		$this->db->where('id', $item_variation_id);
		$this->db->update('item_variations',array(
			'ecommerce_variation_id' => NULL,
			'ecommerce_variation_quantity' => NULL,
			'ecommerce_last_modified' => NULL
		));
	}
	
	function link_item($item_id, $ecommerce_product_id, $ecommerce_product_quantity, $ecommerce_last_modified,$ecommerce_inventory_item_id=NULL)
	{
			$this->db->where('item_id', $item_id);
			$this->db->update('items', array(
				'ecommerce_product_id' => $ecommerce_product_id,
				'ecommerce_product_quantity' => $ecommerce_product_quantity,
				'ecommerce_last_modified' => $ecommerce_last_modified,
				'last_modified' => $ecommerce_last_modified,
				'ecommerce_inventory_item_id' => $ecommerce_inventory_item_id,
			));
	}
	
	function unlink_item($item_id)
	{
		$this->db->where('item_id', $item_id);
		$this->db->update('items',array(
			'ecommerce_product_id' => NULL,
			'ecommerce_product_quantity' => NULL,
			'ecommerce_last_modified' => NULL,
		));
		
		$this->db->where('item_id', $item_id);
		$this->db->update('item_variations', array(
			'ecommerce_variation_id' => NULL,
			'ecommerce_variation_quantity' => NULL,
			'ecommerce_last_modified' => NULL,
		));
	}

	function unlink_all()
	{
		$this->db->update('items',array(
			'ecommerce_product_id' => NULL,
			'ecommerce_product_quantity' => NULL,
			'ecommerce_last_modified' => NULL,
		));
		
		$this->db->update('item_variations', array(
			'ecommerce_variation_id' => NULL,
			'ecommerce_variation_quantity' => NULL,
			'ecommerce_last_modified' => NULL,
		));
	}

	function unlink_items($item_ids)
	{
		$this->db->where_in('item_id', $item_ids);
		$this->db->update('items',array(
			'ecommerce_product_id' => NULL,
			'ecommerce_product_quantity' => NULL,
			'ecommerce_last_modified' => NULL,
		));
		
		$this->db->where_in('item_id', $item_ids);
		$this->db->update('item_variations', array(
			'ecommerce_variation_id' => NULL,
			'ecommerce_variation_quantity' => NULL,
			'ecommerce_last_modified' => NULL,
		));
	}
	
	function get_item_variations_for_ecommerce($item_id,$dont_include_non_ecommerce_variations = false)
	{
		$this->load->model('Item_variations');
		
		$this->db->select('items.unit_price as item_unit_price, 
		items.cost_price as item_cost_price, 
		items.promo_price as item_promo_price,
		items.start_date as item_start_date,
		items.end_date as item_end_date,
		item_variations.*, location_item_variations.quantity as quantity');
		$this->db->from('item_variations');
		$this->db->join('items', 'item_variations.item_id = items.item_id');
		$this->db->join('location_item_variations', 'item_variations.id = location_item_variations.item_variation_id and location_id ='.$this->ecommerce_store_location, 'left');
		
		$this->db->where('items.is_ecommerce', 1);
				
		$this->db->group_start();
		$this->db->where('item_variations.deleted',0);
		$this->db->or_where('ecommerce_variation_id IS NOT NULL');
		$this->db->group_end();
		
		if ($dont_include_non_ecommerce_variations)
		{
			$this->db->group_start();
			$this->db->where('item_variations.deleted',0);
			$this->db->where('item_variations.is_ecommerce', 1);
			$this->db->group_end();
		}
		
		$this->db->where('item_variations.item_id', $item_id);
		
		//We need order by so we always get a consistent order for shopify variations
		$this->db->order_by('item_variations.id');
		$return = array();
		
		$results = $this->db->get()->result_array();
		
		foreach($results as $result)
		{
			$attributes = $this->Item_variations->get_attributes($result['id']);
			
			if (count($attributes) > 0)
			{
				$return[$result['id']]['id'] = $result['id'];
				$return[$result['id']]['item_id'] = $result['item_id'];
				$return[$result['id']]['name'] = $result['name'];
				$return[$result['id']]['is_ecommerce'] = $result['is_ecommerce'];
				$return[$result['id']]['item_number'] = $result['item_number'];
				$return[$result['id']]['cost_price'] = $result['cost_price'] ? $result['cost_price'] : $result['item_cost_price'];
				$return[$result['id']]['unit_price'] = $result['unit_price'] ? $result['unit_price'] : $result['item_unit_price'];
				$return[$result['id']]['promo_price'] = $result['promo_price'] ? $result['promo_price'] : $result['item_promo_price'];
				$return[$result['id']]['start_date'] = $result['start_date'] ? $result['start_date'] : $result['item_start_date'];
				$return[$result['id']]['end_date'] = $result['end_date'] ? $result['end_date'] : $result['item_end_date'];
				$return[$result['id']]['reorder_level'] = $result['reorder_level'];
				$return[$result['id']]['replenish_level'] = $result['replenish_level'];
				$return[$result['id']]['quantity'] = $result['quantity'];
				$return[$result['id']]['ecommerce_variation_id'] = $result['ecommerce_variation_id'];
				$return[$result['id']]['ecommerce_variation_quantity'] = $result['ecommerce_variation_quantity'];
				$return[$result['id']]['last_modified'] = $result['last_modified'];
				$return[$result['id']]['ecommerce_last_modified'] = $result['ecommerce_last_modified'];
				$return[$result['id']]['attributes'] = $attributes;
				$return[$result['id']]['image'] = $this->Item_variations->get_image($result['id']);
				$return[$result['id']]['deleted'] = $result['deleted'];
			}
		}
			
		return $return;
	}
	
	function get_items_for_ecommerce($item_ids = array())
	{
		$this->db->select('GROUP_CONCAT('.$this->db->dbprefix('tags').'.name) as tags,items.*,coalesce('.$this->db->dbprefix('location_items').'.quantity,0) as quantity');
		$this->db->from('items');
		$this->db->join('location_items','location_items.item_id = items.item_id and location_items.location_id = '.$this->ecommerce_store_location,'left');
			
		$this->db->join('items_tags', 'items_tags.item_id = items.item_id', 'left');
		$this->db->join('tags', 'tags.id = items_tags.tag_id', 'left');
		
		$this->db->where('items.system_item',0);
		
		$this->db->where('items.category_id NOT IN(SELECT id FROM phppos_categories WHERE exclude_from_e_commerce = 1)');
		
		if(!empty($item_ids))
		{
			if(is_array($item_ids))
			{
				$this->db->group_start();
				$item_ids_chunk = array_chunk($item_ids,25);
				foreach($item_ids_chunk as $item_ids)
				{
					$this->db->or_where_in('items.item_id',$item_ids);
				}
				$this->db->group_end();		
			}
			else
			{
				$this->db->where('items.item_id', $item_ids);
			}
		} else {
			
			$this->db->group_start();
		
			$this->db->where('items.last_modified > items.ecommerce_last_modified');
			$this->db->or_where('items.ecommerce_last_modified IS NULL');
		
			$this->db->group_end();
			
		}
		
		$this->db->group_start();
		
		$this->db->group_start();
		$this->db->where('items.deleted',0);
		$this->db->where('is_ecommerce',1);
		$this->db->group_end();

		$this->db->or_group_start();
		$this->db->where('ecommerce_product_id IS NOT NULL');
		$this->db->group_end();
		
		$this->db->group_end();
		
		$this->db->group_by('items.item_id');
		
		$return = $this->db->get();
		return $return;
	}
	
	function get_sync_progress()
	{
		return array('percent_complete' => $this->config->item('ecommerce_sync_percent_complete'), 'message'=> $this->config->item('ecommerce_sync_message'));
	}
	
	function update_sync_progress($progress,$message)
	{
		$this->Appconfig->save('ecommerce_sync_percent_complete',$progress);
		$this->Appconfig->save('ecommerce_sync_message', $message ? $message : '');
	}
	
	function log($msg)
	{
		$msg = date(get_date_format().' h:i:s ').': '.$msg."\n"; 
		
		if (is_cli())
		{
			echo $msg;
		}
		$this->log.=$msg;
	}
	
	function save_log()
	{
    $CI =& get_instance();	
		$CI->load->model("Appfile");
		$this->Appfile->save('ecom_log.txt',$this->log,'+72 hours');
	}
	
	function reset_item($item_id)
	{
		$this->db->where('item_id',$item_id);
		$this->db->update('items', array(
			'ecommerce_product_id' => NULL, 
			'ecommerce_product_quantity' => NULL,
			'ecommerce_last_modified' => NULL,
			'ecommerce_inventory_item_id' => NULL,
		));
		
		$this->db->where('item_id',$item_id);
		$this->db->update('item_variations', array(
			'ecommerce_variation_id' => NULL,
			'ecommerce_variation_quantity' => NULL,
			'ecommerce_last_modified' => NULL,
			'ecommerce_inventory_item_id' => NULL,
		));
		
		$this->db->where('item_id',$item_id);
		$this->db->update('item_images', array('ecommerce_image_id' => NULL));
	}
	
	//Makes php pos not linked to any e-commerce items
	function reset_ecom()
	{
				
		$this->db->update('items', array(
			'ecommerce_product_id' => NULL, 
			'ecommerce_product_quantity' => NULL,
			'ecommerce_last_modified' => NULL,
			'ecommerce_inventory_item_id' => NULL,
		));
		
		$this->db->update('item_variations', array(
			'ecommerce_variation_id' => NULL,
			'ecommerce_variation_quantity' => NULL,
			'ecommerce_last_modified' => NULL,
			'ecommerce_inventory_item_id' => NULL,
		));
		
		$this->db->update('item_images', array('ecommerce_image_id' => NULL));
		$this->db->update('categories',array('ecommerce_category_id' => NULL));
		$this->db->update('tags',array('ecommerce_tag_id' => NULL));
		$this->db->update('attributes',array('ecommerce_attribute_id' => NULL));
		$this->db->update('attribute_values',array('ecommerce_attribute_term_id' => NULL));
		$this->db->update('tax_classes',array('ecommerce_tax_class_id' => NULL));
		$this->db->update('tax_classes_taxes',array('ecommerce_tax_class_tax_rate_id' => NULL));
		
	}
	
	/*
	Get categoreis and sub categories for implementation
	*/
	abstract protected function get_categories();
	
	/*
	Get tags
	*/
	abstract protected function get_tags();
	
	/*
	export products from php pos to ecommerce.
	*/
	abstract function export_phppos_items_to_ecommerce();

	/*
	export products from php pos to ecommerce.
	*/
	abstract function export_phppos_tags_to_ecommerce();


	/*
	export products from php pos to ecommerce.
	*/
	abstract function export_phppos_categories_to_ecommerce();
	
	/*
	Import products from online store.
	It will import only those products which are not present in the phppos items list. 
	*/
	abstract protected function import_ecommerce_items_into_phppos();
	
	/*
	Push new POS product to online store
	*/
	abstract protected function save_item_from_phppos_to_ecommerce($item_id);
		
	/*
	Sync inventory counts
	*/
	abstract protected function sync_inventory_changes();	
	
	//Import tags from e-commmerce
	abstract protected function import_ecommerce_tags_into_phppos();
	
	//Import categories from e-commmerce
	abstract protected function import_ecommerce_categories_into_phppos();
	
	abstract protected function export_phppos_attributes_to_ecommerce();
	abstract protected function import_ecommerce_attributes_into_phppos();
	
	abstract protected function import_ecommerce_orders_into_phppos();
	
	//Tax classes
	abstract protected function get_tax_classes();
	abstract protected function import_tax_classes_into_phppos();
	abstract protected function export_tax_classes_into_phppos();
	
	//shipping classes
	abstract protected function import_shipping_classes_into_phppos();
	
	abstract protected function delete_item($item_id);
	abstract protected function delete_items($item_ids);
	
	abstract protected function undelete_item($item_id);
	abstract protected function undelete_items($item_ids);
	abstract protected function undelete_all();	
}
?>