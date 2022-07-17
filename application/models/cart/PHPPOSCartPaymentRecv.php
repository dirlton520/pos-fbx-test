<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once('PHPPOSCartPaymentBase.php');

class PHPPOSCartPaymentRecv extends PHPPOSCartPaymentBase
{
	public function __construct(array $params = array())
	{		 
		parent::__construct($params);
	}	
}