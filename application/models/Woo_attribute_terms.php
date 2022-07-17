<?php
require_once(APPPATH.'models/MY_Woo.php');

class Woo_attribute_terms extends MY_Woo
{
	const get_endpoint = "products/attributes/<woo_attribute_id>/terms";
	const batch_endpoint="products/attributes/<woo_attribute_id>/terms/batch";
	
	public function __construct($woo)
	{
		parent::__construct($woo);
	}
	
	protected function reset()
	{
		unset($this->woo->attribute_terms_result);
		parent::reset();
	}
	
	private static function get_endpoint($woo_attribute_id)
	{
		return str_replace("<woo_attribute_id>", $woo_attribute_id, self::get_endpoint);
	}
	
	private static function batch_endpoint($woo_attribute_id)
	{
		return str_replace("<woo_attribute_id>", $woo_attribute_id, self::batch_endpoint);
	}
		
	public function get_attribute_terms($woo_attribute_id) 
	{
		$this->reset();
		
		return parent::do_get(self::get_endpoint($woo_attribute_id));	
	}

	public function batch_attribute_terms()
	{	
		$this->CI->load->model('Item_attribute_value');
		$attribute_values = $this->CI->Item_attribute_value->get_all_attribute_values_for_ecommerce();
							
		foreach($attribute_values as $ecommerce_attribute_id => $attr_values)
		{
			$this->reset();
						
			foreach($attr_values as $attr_value)
			{
				if($attr_value['deleted'] == 0)
				{
					if(!$attr_value['ecommerce_attribute_term_id'])
					{
						//create
						$this->data['create'][] = array('name' => $attr_value['name']);
						$this->batch_create_ids[] = $attr_value['id'];
					}
				}
				else
				{
					if($attr_value['ecommerce_attribute_term_id'])
					{
						//delete
						$this->data['delete'][] = $attr_value['ecommerce_attribute_term_id'];
						$this->batch_delete_ids[] = $attr_value['id'];
					}
				}
			}
						
			$this->woo->log(lang("save_attribute_values_to_woocommerce"));
			
			try
			{
				$this->response = parent::do_batch(self::batch_endpoint($ecommerce_attribute_id));
						
				if ($this->response)
				{
					if ($this->batch_create_ids > 0 && (isset($this->response['create']) && count($this->response['create']) > 0))
					{
						for($k=0; $k < count($this->response['create']); $k++)
						{
							$this->woo->link_attribute_value($this->batch_create_ids[$k], $this->response['create'][$k]['id']);
						}
					}
		
					if ($this->batch_delete_ids > 0 && (isset($this->response['delete']) && count($this->response['delete']) > 0))
					{
						for($k=0;$k<count($this->response['delete']);$k++)
						{
							$this->woo->unlink_attribute_value($this->batch_delete_ids[$k]);
						}
					}
				}
			}
			catch(Exception $e)
			{
				$this->woo->log("*******".lang('common_EXCEPTION').": ".var_export($e->getMessage(),TRUE));
			}
		}	
	}
	
}
?>