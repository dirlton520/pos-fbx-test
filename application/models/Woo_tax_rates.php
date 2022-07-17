<?php
require_once(APPPATH.'models/MY_Woo.php');

class Woo_tax_rates extends MY_Woo
{
	private $taxes;
	private $batch_update_ids_for_parent_ids;
			
	const get_endpoint = "taxes";
	const post_endpoint = "taxes";
	const put_endpoint = "taxes/<id>";
	const delete_endpoint = "taxes/<id>";
	const batch_endpoint="taxes/batch";
	
	public function __construct($woo)
	{
		parent::__construct($woo);
		$this->CI->load->model('Tax_class');
	}
	
	protected function reset($exclude = array())
	{
		parent::reset();
	}
	
	private static function put_endpoint($woo_tax_id)
	{
		return str_replace("<id>", $woo_tax_id, self::put_endpoint);
	}
	
	private static function delete_endpoint($woo_tax_id)
	{
		return str_replace("<id>", $woo_tax_id, self::delete_endpoint);
	}
		
	public function get_tax_rates($phppos_tax_class_id) 
	{
		$this->reset();
		$tax_class_info = $this->CI->Tax_class->get_info($phppos_tax_class_id);
		$class_slug = $tax_class_info->ecommerce_tax_class_id;
		
		if ($class_slug)
		{
			$this->response = parent::do_get(self::get_endpoint,array('class' => $class_slug));
		
			return $this->response;
		}
		
		return array();
	}
	
	public function save_tax_rates($phppos_tax_class_id)
	{
		$this->CI->load->model('Tax_class');
		$tax_class_info = $this->CI->Tax_class->get_info($phppos_tax_class_id);
		$slug = $tax_class_info->ecommerce_tax_class_id;
		
		//If we have created a tax class in woo then we can update/insert rates
		if ($slug)
		{
			$phppos_tax_rates = $this->CI->Tax_class->get_taxes($phppos_tax_class_id, false);
			
			$woo_tax_ids_to_keep = array();
			
			foreach($phppos_tax_rates as $phppos_tax_rate)
			{
				$this->reset();
				$this->data['rate'] = $phppos_tax_rate['percent'];
				$this->data['name'] = $phppos_tax_rate['name'];
				$this->data['class'] = $this->CI->Tax_class->get_ecommerce_tax_id($phppos_tax_class_id);
				if ($phppos_tax_rate['ecommerce_tax_class_tax_rate_id'])
				{
					parent::do_put(self::put_endpoint($phppos_tax_rate['ecommerce_tax_class_tax_rate_id']));
					$woo_tax_ids_to_keep[] = $phppos_tax_rate['ecommerce_tax_class_tax_rate_id'];
				}
				else
				{
					$response =  parent::do_post(self::post_endpoint);
					$tax_class_tax_data = array('ecommerce_tax_class_tax_rate_id' => $response['id']);
					$woo_tax_ids_to_keep[] = $response['id'];
					$this->CI->Tax_class->save_tax($tax_class_tax_data, $phppos_tax_rate['id']);
				}
			}
			
			$woo_tax_rates = $this->get_tax_rates($phppos_tax_class_id);
			
			
			foreach($woo_tax_rates as $woo_tax_rate)
			{
				$this->reset();
				
				if (!in_array($woo_tax_rate['id'],$woo_tax_ids_to_keep))
				{
					$this->parameters['force'] = TRUE;
					parent::do_delete(self::delete_endpoint($woo_tax_rate['id']),$this->parameters);
				}
			}
		
		}
		
	}
}
?>