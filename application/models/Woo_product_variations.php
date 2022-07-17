<?php
require_once(APPPATH.'models/MY_Woo.php');

class Woo_product_variations extends MY_Woo
{
	protected $batch_images;
	
	const get_endpoint = "products/<product_id>/variations";
	const batch_endpoint="products/<product_id>/variations/batch";
	const put_endpoint = "products/<product_id>/variations/<id>";
	
	public function __construct($woo)
	{
		parent::__construct($woo);
	}
	
	protected function reset()
	{
		$this->batch_images = array();
		
		parent::reset();
	}
	
	private static function get_endpoint($woo_product_id)
	{
		$search = array("<product_id>");
		$replace = array($woo_product_id);
		return str_replace($search, $replace, self::get_endpoint);
	}
	
	private static function put_endpoint($woo_product_id, $woo_variation_id)
	{
		$search = array("<product_id>", "<id>");
		$replace = array($woo_product_id, $woo_variation_id);
		return str_replace($search, $replace, self::put_endpoint);
	}
	
	private static function batch_endpoint($woo_product_id)
	{
		$search = array("<product_id>");
		$replace = array($woo_product_id);
		return str_replace($search, $replace, self::batch_endpoint);
	}
		
	public function get_product_variations($woo_product_id) 
	{
		$this->reset();
		
		return parent::do_get(self::get_endpoint($woo_product_id));
	}
	
	public function update_product_variation($variation_id, $data = array())
	{
		$this->reset();
	
		$this->CI->load->model('Item_variations');
		$item_variation = $this->CI->Item_variations->get_info($variation_id);
	
		if(!empty($data))
		{
			$this->data = $data;
		}
		else
		{
			$this->data = $this->make_product_variation_data($item_variation);
		}
			
		try
		{	
			$url = self::put_endpoint($this->woo->get_ecommerce_product_id_for_item_id($item_variation->item_id), $item_variation->ecommerce_variation_id);			
			$this->response = parent::do_put($url);
	
			if ($this->response && isset($this->response['id']))
			{				
				//variation_id, ecommerce_variation_id, ecommerce_variation_quantity, date
				$this->woo->link_item_variation($variation_id, $this->response['id'], $this->response['stock_quantity'], getDateFromGMT($this->response['last_modified_gmt']));
				
				if (!empty($this->batch_images[$variation_id]))
				{
					$woo_image_id = $this->response['image']['id'];
					$this->CI->Item->link_image_to_ecommerce($variation_id, $woo_image_id);	
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

	
	private function make_product_variation_data($item_variation)
	{	
		$item_info = $this->CI->Item->get_info($item_variation['item_id']);
		$manage_stock = $item_info->is_service ? FALSE : TRUE;
		
		$attributes_for_variation = array();
		foreach($item_variation['attributes'] as $attribute)
		{
			$option = $attribute['attribute_value_name'];
			$name = $attribute['attribute_name'];
			$attribute_id = $attribute['attribute_id'];
			$ecom_attr_id = $this->woo->get_ecommerce_attribute_id_for_attribute($attribute_id);
			
			$attribute_for_woo = array();
			
			if($ecom_attr_id)
			{
				$attribute_for_woo['id'] = (int)$ecom_attr_id;
			} else {
				$attribute_for_woo['name'] = $name;
			}
			
			$attribute_for_woo['option'] = $option;
			
			$attributes_for_variation[] = $attribute_for_woo;
		}
		
		$data = array(
			'attributes' => $attributes_for_variation,
			'manage_stock' => $manage_stock,
			'stock_quantity' => $this->woo->get_item_variation_quantity($item_variation['id']),
			'sku' => $item_variation['item_number'],
		);
		
		if (!$manage_stock)
		{
			$data['in_stock'] = TRUE;
		}
		
		if ($this->CI->config->item('online_price_tier'))
		{
			$this->CI->load->model('Tier');
			$this->CI->load->model('Item_location');
			$this->CI->load->model('Item');
			$online_price = $this->CI->Item->get_sale_price(array('item_id' => $item_variation['item_id'],'variation_id' => $item_variation['id'],'tier_id' => $this->CI->config->item('online_price_tier')));	
		}
		else
		{
			$online_price = $item_variation['unit_price'] ? to_currency_no_money($item_variation['unit_price']) : '';
		}
		$data['regular_price'] = $online_price;
		$data['sale_price'] = $item_variation['promo_price'] ? to_currency_no_money($item_variation['promo_price']) : '';
		$data['date_on_sale_from'] = $item_variation['start_date'] ? $item_variation['start_date'] : '';
		$data['date_on_sale_to'] = $item_variation['end_date'] ? $item_variation['end_date']. '23:59:59' : '';
		
		if($item_variation['ecommerce_variation_id'])
		{
			$data['id'] = $item_variation['ecommerce_variation_id'];
		}
		
		if(!empty($item_variation['image']) && !$this->CI->config->item('do_not_upload_images_to_ecommerce'))
		{
			$this->CI->load->model('Appfile');
			
			$image_data = array('alt' =>$item_variation['image']['alt_text'], 'name' => $item_variation['image']['title'], 'src' => $this->CI->Appfile->get_url_for_file_with_extension($item_variation['image']['image_id']), 'position' => 0);
			if(isset($item_variation['image']['ecommerce_image_id']) && $item_variation['image']['ecommerce_image_id'])
			{
				$image_data['id'] = $item_variation['image']['ecommerce_image_id'];
			}
			$data['image'] = $image_data;
			
			$this->batch_images[$item_variation['id']] = $item_variation['image']['image_id'];
		}
						
		return $data;
	}

	public function batch_product_variations($item_id, $woo_product_id)
	{
		$this->reset();
		
		$item_variations = $this->woo->get_item_variations_for_ecommerce($item_id);
		
		foreach($item_variations as $variation_id => $item_variation)
		{
			if($item_variation['deleted'] == 0)
			{
				//Variation is going to be deleted
				if ($item_variation['is_ecommerce'] == 0)
				{
					$this->data['delete'][] = $item_variation['ecommerce_variation_id'];
					$this->batch_delete_ids[] = $variation_id;
				}
				else
					{
					$data = $this->make_product_variation_data($item_variation);
									
					if(!$item_variation['ecommerce_variation_id'])
					{
						//create
						$this->data['create'][] = $data;
						$this->batch_create_ids[] = $variation_id;
					}
					else
					{
						//update
						$this->data['update'][] = $data;					
						$this->batch_update_ids[] = $variation_id;	
					}
				}
			} else {
				$this->data['delete'][] = $item_variation['ecommerce_variation_id'];
				$this->batch_delete_ids[] = $variation_id;
			}
		}
				
		try
		{	
			$this->response = parent::do_batch(self::batch_endpoint($woo_product_id));
						
			if ($this->response)
			{
				if ($this->batch_create_ids > 0 && isset($this->response['create']) && count($this->response['create']) > 0)
				{
					for($k=0; $k < count($this->response['create']); $k++)
					{
						//variaion_id, woo_variation_id, woo_quantity
						$this->woo->link_item_variation($this->batch_create_ids[$k],$this->response['create'][$k]['id'], $this->response['create'][$k]['stock_quantity'], getDateFromGMT($this->response['create'][$k]['date_modified_gmt']));
					
						if (!empty($this->batch_images[$this->batch_create_ids[$k]]))
						{
							$woo_image_id = $this->response['create'][$k]['image']['id'];
							$this->CI->Item->link_image_to_ecommerce($this->batch_images[$this->batch_create_ids[$k]], $woo_image_id);	
						}	
					}
				}
				
				if ($this->batch_update_ids > 0 && isset($this->response['update']) && count($this->response['update']) > 0)
				{
					for($k=0; $k < count($this->response['update']); $k++)
					{																		
						//variation_id, woo_variation_id, woo_quantity
						$this->woo->link_item_variation($this->batch_update_ids[$k],$this->response['update'][$k]['id'], $this->response['update'][$k]['stock_quantity'], getDateFromGMT($this->response['update'][$k]['date_modified_gmt']));
					
						if (!empty($this->batch_images[$this->batch_update_ids[$k]]))
						{
							$woo_image_id = $this->response['update'][$k]['image']['id'];
							$this->CI->Item->link_image_to_ecommerce($this->batch_images[$this->batch_update_ids[$k]], $woo_image_id);	
						}		
					}
				}
				if ($this->batch_delete_ids > 0 && isset($this->response['delete']) && count($this->response['delete']) > 0)
				{
					for($k=0;$k<count($this->response['delete']);$k++)
					{
						//variation_id
						$this->woo->unlink_item_variation($this->batch_delete_ids[$k]);
						if(isset($this->response['delete'][$k]['image']['id']))
						{
							$this->CI->Item->unlink_image_from_ecommerce($this->response['delete'][$k]['image']['id']);
						}
					}
				}
			}
		}
		catch(Exception $e)
		{
			$this->woo->log("*******".lang('common_EXCEPTION').": ".var_export($e->getMessage(),TRUE));
		}
	
		return TRUE;
	}
}
?>