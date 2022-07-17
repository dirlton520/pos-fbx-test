<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class PHPPOSCartDelivery
{
	public $delivery_info;
	public $delivery_tax_group_id;
	public $delivery_person_info;
	
	function __construct()
	{
		$delivery_info = array();
		$delivery_tax_group_id = NULL;
		$delivery_person_info = NULL;
	}
	
	function to_array()
	{
		return array('delivery_person_info' => $this->delivery_person_info,'delivery_info' => $this->delivery_info, 'delivery_tax_group_id' => $this->delivery_tax_group_id);
	}
}

