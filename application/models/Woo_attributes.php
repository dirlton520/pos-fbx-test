<?php
require_once(APPPATH.'models/MY_Woo.php');

class Woo_attributes extends MY_Woo
{
	const get_endpoint = "products/attributes";
	const batch_endpoint="products/attributes/batch";
	
	
	public function __construct($woo)
	{
		parent::__construct($woo);
	}
	
	protected function reset()
	{
		unset($this->woo->attributes_result);
		parent::reset();
	}
		
	public function get_attributes() 
	{
		$this->reset();
		return parent::do_get(self::get_endpoint);
	}
	
	public function save_attribute($attribute_id)
	{
		$this->reset();
		
		try
		{
			$this->CI->load->model('Item_attribute');
			$attribute = $this->CI->Item_attribute->get_attributes_for_ecommerce($attribute_id);
			
			$this->data = $this->make_attribute_data($attribute);
			
			$this->response = parent::do_post(self::post_endpoint);
			
			$phppos_tag_id = $this->CI->Tag->get_tag_id_by_name($tag_name);
			
			if ($phppos_tag_id !== FALSE)
			{
				$this->woo->link_tag($phppos_tag_id, $this->response['id']);
			}
			
			return $this->response['id'];
		}
		catch(Exception $e)
		{
			$this->woo->log("*******".lang('common_EXCEPTION').": ".var_export($e->getMessage(),TRUE));
		}
		return NULL;
	}
	
	private function make_attribute_data($attribute)
	{	
		$data = array(
			'name' => $attribute['name'],
			'slug' => substr(str_replace(' ',  '-', $attribute['name']), 0, 28),
		);
		
		if($attribute['ecommerce_attribute_id'])
		{
			$data['id'] = $attribute['ecommerce_attribute_id'];
		}
		
		return $data;
	}

	public function batch_attributes($attribute_ids = array())
	{
		$this->reset();
				
		$this->CI->load->model('Item_attribute');
		$attributes = $this->CI->Item_attribute->get_attributes_for_ecommerce($attribute_ids);
		
		foreach($attributes as $attribute_id => $attribute)
		{
			if($attribute['deleted'] == 0)
			{
				$data = $this->make_attribute_data($attribute);
				
				if(!isset($data['id']))
				{
					//create
					$this->data['create'][] = $data;
					$this->batch_create_ids[] = $attribute_id;
				}
				else
				{
					//update
					$this->data['update'][] = $data;
					$this->batch_update_ids[] = $attribute_id;	
				}
			}
			else
			{
				//delete
				$this->data['delete'][] = $attribute['ecommerce_attribute_id'];
				$this->batch_delete_ids[] = $attribute_id;
			}	
		}
		
		$this->woo->log(lang("save_attributes_to_woocommerce"));
		
		try
		{
			$this->response = parent::do_batch(self::batch_endpoint);
			
			if ($this->response)
			{
				if (count($this->batch_create_ids) > 0 && (isset($this->response['create']) && count($this->response['create']) > 0))
				{
					for($k=0; $k < count($this->response['create']); $k++)
					{
						$this->woo->link_attribute($this->batch_create_ids[$k], $this->response['create'][$k]['id']);
					}
				}
		
				if (count($this->batch_delete_ids) > 0 && (isset($this->response['delete']) && count($this->response['delete']) > 0))
				{
					for($k=0;$k<count($this->response['delete']);$k++)
					{
						$this->woo->unlink_attribute($this->batch_delete_ids[$k]);
					}
				}
				
				//save terms
				if(count($this->batch_create_ids) > 0 || count($this->batch_update_ids) > 0)
				{
					$woo_attribute_terms	=	new Woo_attribute_terms($this->woo);
					$woo_attribute_terms->batch_attribute_terms();
				}
			}
		}
		catch(Exception $e)
		{
			$this->woo->log("*******".lang('common_EXCEPTION').": ".var_export($e->getMessage(),TRUE));
		}
	}
}
?>