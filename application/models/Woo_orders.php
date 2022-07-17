<?php
require_once(APPPATH.'models/MY_Woo.php');

class Woo_orders extends MY_Woo
{	
	const get_endpoint = "orders";
	
	public function __construct($woo)
	{
		parent::__construct($woo);
	}
		
	public function get_orders($parameters) 
	{
		$this->reset();
		return parent::do_get(self::get_endpoint,$parameters);
	}
}
?>