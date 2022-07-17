<?php
require_once(APPPATH.'models/MY_Woo.php');

class Woo_tax_classes extends MY_Woo
{
	const get_endpoint = "taxes/classes";
	const post_endpoint = "taxes/classes";
	const delete_endpoint = "taxes/classes/<slug>";
	
	public function __construct($woo)
	{
		parent::__construct($woo);
	}
	
	protected function reset()
	{
		unset($this->woo->tax_classes_result);
		parent::reset();
	}
	
	private static function delete_endpoint($woo_tax_slug)
	{
		return str_replace("<slug>", $woo_tax_slug, self::delete_endpoint);
	}
		
	public function get_tax_classes() 
	{
		$this->reset();
		$this->response = parent::do_get(self::get_endpoint);
		
		return $this->response;		
	}
	
	public function delete_tax_class($tax_class_id)
	{
		$this->reset();
		
		try
		{
			$this->CI->load->model('Tax_class');
			
			$woo_tax_id = $this->CI->Tax_class->get_ecommerce_tax_id($tax_class_id);
			$this->parameters['force'] = true;
			$this->response = parent::do_delete(self::delete_endpoint($woo_tax_id),$this->parameters);
			
			if ($this->response)
			{
				$this->woo->unlink_tax_class($tax_class_id);
			}
			
			return $this->response['slug'];
		}
		catch(Exception $e)
		{
			$this->woo->log("*******".lang('common_EXCEPTION').": ".var_export($e->getMessage(),TRUE));
		}
		
		return NULL;
	}
	
	public function save_tax_class($phppos_tax_class_id)
	{
		$this->reset();
		
		try
		{
			$this->CI->load->model('Tax_class');
			$tax_class_info = $this->CI->Tax_class->get_info($phppos_tax_class_id);
			$this->data['name'] = $tax_class_info->name;
			
			if (!$tax_class_info->ecommerce_tax_class_id)
			{
				$this->response = parent::do_post(self::post_endpoint);
				if ($phppos_tax_class_id !== FALSE)
				{
					$this->woo->link_tax_class($phppos_tax_class_id, $this->response['slug']);
				}
				$return = $this->response['slug'];
			}
			else
			{
				$return = $tax_class_info->ecommerce_tax_class_id;
			}
			
			$woo_tax_rates = new Woo_tax_rates($this->woo);
			$woo_tax_rates->save_tax_rates($phppos_tax_class_id);
			return $return;
		}
		catch(Exception $e)
		{
			$this->woo->log("*******".lang('common_EXCEPTION').": ".var_export($e->getMessage(),TRUE));
		}
		
		return NULL;
	}
	
	function batch_tax_classes()
	{
		$this->reset();
		
		$create = array();
		$update = array();
		$delete = array();
		
		$this->CI->load->model('Tax_class');
		
		$woo_tax_classes = $this->woo->get_tax_classes();
		$ecommerce_taxes = $this->CI->Tax_class->get_all_for_ecommerce();
		
		foreach($ecommerce_taxes as $phppos_tax_class_id => $phppos_tax_class_data)
		{
			if($phppos_tax_class_data['deleted'] == 0)
			{
				if(!isset($woo_tax_classes[strtoupper($phppos_tax_class_data['name'])]))
				{
					//create
					$create[] = $phppos_tax_class_id;
				}
				else
				{
					//update
					$update[] = $phppos_tax_class_id;
				}
			}
			else
			{
				if($phppos_tax_class_data['ecommerce_tax_class_id'])
				{
					//delete
					$delete[] = $phppos_tax_class_id;
				}
			}
		}
		
		$this->woo->log(lang("save_tax_classes_to_woocommerce"));

		try
		{
			foreach($create as $cr)
			{
				$this->save_tax_class($cr);
			}
			
			foreach($update as $up)
			{
				$this->save_tax_class($up);
			}
			
			foreach($delete as $dl)
			{
				$this->delete_tax_class($dl);
			}
		}
		catch(Exception $e)
		{
			$this->woo->log("*******".lang('common_EXCEPTION').": ".var_export($e->getMessage(),TRUE));
		}
	}
}
?>