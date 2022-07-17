<?php
require_once(APPPATH.'models/MY_Woo.php');

class Woo_products extends MY_Woo
{
	protected $batch_images;

	const get_endpoint = "products";
	const post_endpoint = "products";
	const put_endpoint = "products/<id>";
	const delete_endpoint = "products/<id>";
	const batch_endpoint= "products/batch";
	
	public function __construct($woo)
	{
		parent::__construct($woo);
	}
	
	protected function reset()
	{
		$this->batch_images = array();
		
		parent::reset();
	}
	
	private static function put_endpoint($woo_product_id)
	{
		if(!$woo_product_id)
		{
			throw new Exception('No Woo Product Id');
		}
		
		return str_replace("<id>", $woo_product_id, self::put_endpoint);
	}
	
	private static function delete_endpoint($woo_product_id)
	{
		if(!$woo_product_id)
		{
			throw new Exception('No Woo Product Id');
		}
		
		return str_replace("<id>", $woo_product_id, self::delete_endpoint);
	}
		
	public function get_products() 
	{
		$this->reset();
		
		$this->response = parent::do_get(self::get_endpoint);
		
		return $this->response;
	}
	
	public function delete_product($item_id)
	{
		$this->reset();
		
		try
		{
			$woo_product_id = $this->woo->get_ecommerce_product_id_for_item_id($item_id);
			
			$this->parameters['force'] = true;
			$this->response = parent::do_delete(self::delete_endpoint($woo_product_id), $this->parameters);
			
			if ($this->response)
			{
				$this->woo->unlink_item($item_id);
				return $this->response['id'];
			}
		}
		catch(Exception $e)
		{
			$this->woo->unlink_item($item_id);
			$this->woo->log("*******".lang('common_EXCEPTION').": ".var_export($e->getMessage(),TRUE));
		}
		
		return NULL;
	}
	
	public function update_product($item_id, $data = array())
	{	
		$this->reset();
		$woo_product_variations	=	new Woo_product_variations($this->woo);
						
		$item = $this->woo->get_items_for_ecommerce($item_id)->row();
			
		if (!$item)
		{
			return NULL;
		}
									
		if(!empty($data))
		{
			$this->data = $data;
		}
		else
		{
			$this->data = $this->make_product_data($item);
		}
		
		try
		{	
			
			$this->response = parent::do_put(self::put_endpoint($item->ecommerce_product_id));
			if ($this->response && isset($this->response['id']))
			{
				$this->woo->link_item($item_id, $this->response['id'], $this->response['stock_quantity'], getDateFromGMT($this->response['date_modified_gmt']));	
				
				if (!empty($this->batch_images[$item_id]))
				{
					for($j=0;$j<count($this->response['images']);$j++)
					{
						$woo_image_id = $this->response['images'][$j]['id'];
						$this->CI->Item->link_image_to_ecommerce($this->batch_images[$item_id][$j], $woo_image_id);	
					}
				}
				
				if($this->response['type'] == "variable")
				{
					$woo_product_variations->batch_product_variations($item_id, $this->response['id']);
				}
				
				return $this->response['id'];
			}			
		}
		catch(Exception $e)
		{
			$this->woo->log("*******".lang('common_EXCEPTION').": ".var_export($e->getMessage(),TRUE));
		}
		
		return NULL;		
	}
	
	public function save_product($item_id)
	{
		$this->reset();
		$woo_product_variations	=	new Woo_product_variations($this->woo);
				
		$item = $this->woo->get_items_for_ecommerce($item_id)->row();
		
		if (!$item)
		{
			return NULL;
		}
		
		$this->data = $this->make_product_data($item);
		
		try
		{
			$this->response = parent::do_post(self::post_endpoint);
			
			if ($this->response && isset($this->response['id']))
			{
				$this->woo->link_item($item_id, $this->response['id'], $this->response['stock_quantity'], getDateFromGMT($this->response['date_modified_gmt']));	
				
				if (!empty($this->batch_images[$item_id]))
				{
					for($j=0;$j<count($this->response['images']);$j++)
					{
						$woo_image_id = $this->response['images'][$j]['id'];
						$this->CI->Item->link_image_to_ecommerce($this->batch_images[$item_id][$j], $woo_image_id);	
					}
				}
							
				if($this->response['type'] == "variable")
				{
					$woo_product_variations->batch_product_variations($item_id, $this->response['id']);
				}
				
				return $this->response['id'];
			}
		}
		catch(Exception $e)
		{
			$this->woo->log("*******".lang('common_EXCEPTION').": ".var_export($e->getMessage(),TRUE));
		}
		
		return NULL;
	}
	
	private function make_product_data($item)
	{		
		static $phppos_cats;
		if(!$phppos_cats)
		{
			$this->CI->load->model('Category');
			$phppos_cats = $this->CI->Category->get_all_categories_and_sub_categories_as_indexed_by_category_id(FALSE);
		}
		static $phppos_tags;
		if(!$phppos_tags)
		{
			$phppos_tags = $this->CI->Tag->get_all();
		}
		static $woo_cats;
		if(!$woo_cats)
		{
			$woo_cats = $this->woo->get_categories();
		}
				
		static $woo_tags;
		if(!$woo_tags)
		{
			$woo_tags = $this->woo->get_tags();
		}
		
		$item_id = $item->item_id;
		$this->CI->load->model('Item_location');
		$item_location_info = $this->CI->Item_location->get_info($item_id,$this->CI->config->item('ecom_store_location') ? $this->CI->config->item('ecom_store_location') : 1);
		
		$this->CI->load->model('Item_variations');
		$variations = $this->CI->Item_variations->get_all($item_id);
		
		$quantity = $this->woo->ecommerce_store_locations ? $this->woo->get_item_quantity($item_id) : $item->quantity;
		
		if ($this->CI->config->item('online_price_tier'))
		{
			$this->CI->load->model('Tier');
			$this->CI->load->model('Item_location');
			$this->CI->load->model('Item');
			$online_price = $this->CI->Item->get_sale_price(array('item_id' => $item_id,'tier_id' => $this->CI->config->item('online_price_tier')));	
		}
		else
		{
			$online_price = to_currency_no_money($item_location_info->unit_price ? $item_location_info->unit_price : $item->unit_price);
		}
		
		$data = array(
			'name' =>$item->name,
			'regular_price' => $online_price,
			'description' =>$item->long_description,
			'enable_html_description' => $this->CI->config->item('woo_enable_html_desc') ? TRUE : FALSE,
			'short_description' =>$item->description,
			'enable_html_short_description' => $this->CI->config->item('woo_enable_html_desc') ? TRUE : FALSE,
			'stock_quantity' => $quantity ? floor($quantity) : 0,
			'manage_stock'=> (isset($item->is_service) && $item->is_service) || count($variations) > 0 ? FALSE : TRUE,
			'virtual' => isset($item->is_service) && $item->is_service && !$this->CI->config->item('do_not_treat_service_items_as_virtual') ? TRUE : FALSE,
		);
		
		if ($item->weight)
		{
			$data['weight'] = (string)to_quantity($item->weight);
		}
		
		if ($item->length && $item->width && $item->height)
		{
			$data['dimensions'] = array('length' => (string)to_quantity($item->length), 'width' => (string)to_quantity($item->width),'height' => (string)to_quantity($item->height));
		}
		
		if ($item->ecommerce_shipping_class_id)
		{
			$data['shipping_class'] = (string)$item->ecommerce_shipping_class_id;
		}
		else
		{
			$data['shipping_class'] = "";
		}
		
		
		if($item->ecommerce_product_id)
		{
			$data['id'] = $item->ecommerce_product_id;
		}
		
		if ($item->tax_class_id)
		{
			$this->CI->load->model('Tax_class');
			if ($ecom_tax_class = $this->CI->Tax_class->get_ecommerce_tax_id($item->tax_class_id))
			{
				$data['tax_class'] = $ecom_tax_class;
			}
		}
		
		
		
		if (!$item_location_info->promo_price)
		{
			$data['sale_price'] = $item->promo_price ? to_currency_no_money($item->promo_price) : '';
			$data['date_on_sale_from'] = $item->start_date ? $item->start_date : '';
			$data['date_on_sale_to'] = $item->end_date ? $item->end_date. '23:59:59' : '';
		}
		else
		{
			$data['sale_price'] = $item_location_info->promo_price ? to_currency_no_money($item_location_info->promo_price) : '';	
			$data['date_on_sale_from'] = $item_location_info->start_date ? $item_location_info->start_date : ($item->start_date ? $item->start_date : '');
			$data['date_on_sale_to'] = $item_location_info->end_date ? $item_location_info->end_date. '23:59:59' : ($item->end_date ? $item->end_date. '23:59:59' : '');
		}

			
		$sync_field = $this->CI->config->item('sku_sync_field') ? $this->CI->config->item('sku_sync_field') : 'item_number';
		
		if($item->$sync_field)
		{
			$data['sku'] = $item->$sync_field ? $item->$sync_field : '';
		}
		
		
		$item_images = $this->woo->get_item_images_for_ecommerce($item_id);
		
		if(count($item_images) > 0 && !$this->CI->config->item('do_not_upload_images_to_ecommerce'))
		{
			$data['images'] = array();
			
			$this->CI->load->model('Appfile');
			
			$possition = 0;
			foreach($item_images as $item_image)
			{
					$image_data = array('alt' =>$item_image['alt_text'], 'name' => $item_image['title'], 'src' => $this->CI->Appfile->get_url_for_file_with_extension($item_image['image_id']), 'position' => $possition);
					if($item_image['ecommerce_image_id'])
					{
						$image_data['id'] = $item_image['ecommerce_image_id'];
					}
					$data['images'][] = $image_data;
					$possition ++;
			}
	
			$this->batch_images[$item_id] = array_column($item_images, 'image_id');
		}
		
		$woo_cat_id = NULL;

		if (isset($phppos_cats[$item->category_id]))
		{
			$woo_cat_id = $this->woo->get_woo_category_id($phppos_cats[$item->category_id]);
			
			if (!$woo_cat_id)
			{
				$this->woo->export_phppos_categories_to_ecommerce($this->CI->Category->get_root_parent_category_id($item->category_id));
				$woo_cat_id = $this->woo->get_woo_category_id($this->CI->Category->get_full_path($item->category_id, '|'));	
			}
		}
			
		if ($woo_cat_id)
		{
			$data['categories'] = array(array('id' => $woo_cat_id));
		}
		
		$secondary_categories = $this->CI->Item->get_secondary_categories($item_id)->result();
		
		if (count($secondary_categories))
		{
			foreach($secondary_categories as $sec_category_id)
			{
				$sec_phppos_category_id = $sec_category_id->category_id;
				
				$woo_cat_id = $this->woo->get_woo_category_id($phppos_cats[$sec_phppos_category_id]);
			
				if (!$woo_cat_id)
				{
					$this->woo->export_phppos_categories_to_ecommerce($this->CI->Category->get_root_parent_category_id($sec_phppos_category_id));
					$woo_cat_id = $this->woo->get_woo_category_id($this->CI->Category->get_full_path($sec_phppos_category_id, '|'));	
				}
				
				if (!isset($data['categories']))
				{
					$data['categories'] = array();
				}
				
				$data['categories'][] = array('id' => $woo_cat_id);
				
				
			}
		}

		if (isset($item->tags))
		{
			$tag_created = FALSE;
			
			$phppos_item_tags = explode(',', $item->tags);
			foreach($phppos_item_tags as $phppos_tag)
			{
				if ($phppos_tag && !$this->woo->get_woo_tag_id($phppos_tag,$woo_tags))
				{
					$this->woo->save_tag($phppos_tag);
					$tag_created = TRUE;
				}	
			}
						
			if ($tag_created)
			{
				$woo_tags = $this->woo->get_tags();
			}
						
			$woo_tags_ids = array();
			
			foreach($phppos_item_tags as $phppos_tag)
			{
				if ($phppos_tag)
				{
					$woo_tags_ids[] = array('id' => $woo_tags[strtoupper($phppos_tag)]);
				}
			}
				
			if (!empty($woo_tags_ids))
			{
				$data['tags'] = $woo_tags_ids;
			}
			else
			{
				$data['tags'] = array();
			}
		}
		
		//atttributes
		$this->CI->load->model("Item_attribute");
		$this->CI->load->model("Item_variations");
		
		$attr_with_attr_values = $this->CI->Item_attribute->get_attributes_for_item_with_attribute_values($item_id);
		
		//make sure we synced all the attributes we need
		$attributes_to_save = array();
		foreach($attr_with_attr_values as $attribute_id => $attribute)
		{
			if(!$attribute['ecommerce_attribute_id'])
			{
				$attributes_to_save[] = $attribute_id;
			}
		}
		
		if(count($attributes_to_save) > 0)
		{
			$woo_attributes = new Woo_attributes($this->woo);
			$woo_attributes->batch_attributes($attributes_to_save);
			
			//fetch
			$attr_with_attr_values = $this->CI->Item_attribute->get_attributes_for_item_with_attribute_values($item_id);
		}
				
		foreach($attr_with_attr_values as $attribute_id => $attribute)
		{
			$options = array_column($attribute['attr_values'], 'name');
			
			$data['attributes'][] = array(
				'id' => intval($attribute['ecommerce_attribute_id']),
				'name' => $attribute['name'],
				'variation' => true,
				'visible' => true,
				'options' => $options,
			);
		}
		
		$data['type'] = count($variations) > 0 ? 'variable': 'simple';
		
		
		return $data;
	}

	public function batch_products($item_ids = array(), $operations = array('create','update','delete'))
	{
		$this->reset();				
		$this->CI->load->model('Item');
		
		$items = $this->woo->get_items_for_ecommerce($item_ids);
		
		$num_rows = $items->num_rows();
		$i = 0;
		$chunk_size = 0;
				
		while ($item = $items->unbuffered_row('object'))
		{
			$i ++;
						
			if($item->is_ecommerce && !$item->deleted)
			{
				$data = $this->make_product_data($item);
				
				
				if(!isset($data['id']) && in_array('create', $operations))
				{
					$this->data['create'][] = $data;
					$this->batch_create_ids[] = $item->item_id;
					$chunk_size ++;
				}
				elseif(in_array('update', $operations))
				{
					$this->data['update'][] = $data;					
					$this->batch_update_ids[] = $item->item_id;
					$chunk_size ++;
				}
			} 
			elseif(in_array('delete', $operations)) 
			{
				$this->data['delete'][] = $item->ecommerce_product_id;
				$this->batch_delete_ids[] = $item->item_id;
				$chunk_size ++;
			}
							
			if($chunk_size == $this->woo_write_chunk_size || $i == $num_rows)
			{
				try
				{
					parent::do_batch(self::batch_endpoint, array($this, 'batch_callback'));				
				}
				catch(Exception $e)
				{
					$this->woo->log("*******".lang('common_EXCEPTION').": ".var_export($e->getMessage(),TRUE));
				}
				
				$this->reset();
				
				$chunk_size = 0;
			
			}
		}
		
		return TRUE;
	}
	
	protected function batch_callback($response)
	{		
		$woo_product_variations	=	new Woo_product_variations($this->woo);
		
		if ($response)
		{
			if (count($this->batch_create_ids) > 0 && isset($response['create']) && count($response['create']) > 0)
			{
				for($k=0; $k < count($response['create']); $k++)
				{
					$item_id = $this->batch_create_ids[$k];

					if(isset($response['create'][$k]['error']))
					{
						$this->woo->log("*******".lang('common_error').": ".var_export($response['create'][$k]['error'],TRUE));
						continue;
					}

					//item_id, ecommerce_product_id, ecommerce_product_quantity
					$this->woo->link_item($item_id, $response['create'][$k]['id'], $response['create'][$k]['stock_quantity'], getDateFromGMT($response['create'][$k]['date_modified_gmt']));

					if (!empty($this->batch_images[$item_id]))
					{
						for($j=0;$j<count($response['create'][$k]['images']);$j++)
						{
							$woo_image_id = $response['create'][$k]['images'][$j]['id'];
							$this->CI->Item->link_image_to_ecommerce($this->batch_images[$item_id][$j], $woo_image_id);
						}
					}

					if($response['create'][$k]['type'] == "variable")
					{
						$woo_product_variations->batch_product_variations($item_id, $response['create'][$k]['id']);
					}
				}
			}

			if (count($this->batch_update_ids) > 0 && isset($response['update']) && count($response['update']) > 0)
			{
				for($k=0; $k < count($response['update']); $k++)
				{
					$item_id = $this->batch_update_ids[$k];

					if(isset($response['update'][$k]['error']))
					{
						$this->woo->log("*******".lang('common_error').": ".var_export($response['update'][$k]['error'],TRUE));
						continue;
					}

					//item_id, ecommerce_product_id, ecommerce_product_quantity
					$this->woo->link_item($item_id, $response['update'][$k]['id'], $response['update'][$k]['stock_quantity'], getDateFromGMT($response['update'][$k]['date_modified_gmt']));

					if (!empty($this->batch_images[$item_id]))
					{
						for($j=0; $j<count($response['update'][$k]['images']); $j++)
						{
							$woo_image_id = $response['update'][$k]['images'][$j]['id'];
							$this->CI->Item->link_image_to_ecommerce($this->batch_images[$item_id][$j], $woo_image_id);
						}
					}

					if($response['update'][$k]['type'] == "variable")
					{
						$woo_product_variations->batch_product_variations($item_id, $response['update'][$k]['id']);
					}
				}
			}

			if (count($this->batch_delete_ids) > 0 && isset($response['delete']) && count($response['delete']) > 0)
			{
				for($k=0;$k<count($response['delete']);$k++)
				{
					$item_id = $this->batch_delete_ids[$k];
					
					if(isset($response['delete'][$k]['error']))
					{
						$this->woo->log("*******".lang('common_error').": ".var_export($response['delete'][$k]['error'],TRUE));
						continue;
					}
					
					$this->woo->unlink_item($item_id);

					if(isset($response['delete'][$k]['images']))
					{
						for($j=0;$j<count($response['delete'][$k]['images']);$j++)
						{
							$woo_image_id = $response['delete'][$k]['images'][$j]['id'];
							$this->CI->Item->unlink_image_from_ecommerce($woo_image_id);
						}
					}
				}
			}
		}
	}
}
?>