<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once('PHPPOSCartPaymentBase.php');

class PHPPOSCartPaymentSale extends PHPPOSCartPaymentBase
{
	public $truncated_card;
	public $card_issuer;
	public $auth_code;
	public $ref_no;
	public $cc_token;
	public $acq_ref_data;
	public $process_data;
	public $entry_method;
	public $aid;
	public $tvr;
	public $iad;
	public $tsi;
	public $arc;
	public $cvm;
	public $tran_type;
	public $application_label;
	public $ebt_voucher_no;
	public $ebt_auth_code;
			
	public function __construct(array $params = array())
	{		 
		parent::__construct($params);
	}	
}