<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once('PHPPOSCartItemKit.php');

class PHPPOSCartItemKitRecv extends PHPPOSCartItemKit
{
	public $item_kit_id;
	public function __construct(array $params = array())
	{		
		$params['type'] = 'receiving';
		parent::__construct($params);
	}
	
	public function get_id()
	{
		return $this->item_kit_id;
	}
}