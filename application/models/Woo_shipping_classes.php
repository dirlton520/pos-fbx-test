<?php
require_once(APPPATH.'models/MY_Woo.php');

class Woo_shipping_classes extends MY_Woo
{
	const get_endpoint = "products/shipping_classes";
	
	public function __construct($woo)
	{
		parent::__construct($woo);
	}
	
	public function get_shipping_classes() 
	{
		$this->reset();
		$this->response = parent::do_get(self::get_endpoint);
		return $this->response;		
	}	
}
?>