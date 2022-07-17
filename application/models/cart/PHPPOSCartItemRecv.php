<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once('PHPPOSCartItem.php');

class PHPPOSCartItemRecv extends PHPPOSCartItem
{
	public $selling_price;
	public $location_selling_price;
	public $expire_date;
	public $cost_price_preview;
	
	public function __construct(array $params = array())
	{		
		$params['type'] = 'receiving';
		parent::__construct($params);
	}
	
}