<?php
require_once ("Datacapusbprocessor.php");
class Worldpayemvusbprocessor extends Datacapusbprocessor
{
	function __construct($controller)
	{	
		
		// URL/IP:                 testhost6.dsipscs.com
		// Credit Port:            9000
		// Credit/Debit MID:       542929803118207
		// Credit/Debit TID:       00008118
		//
		//
		// GIFTePay  Test Credentials:
		// Gift Port:              9100
		// Gift MID:               000008043127
		// Gift TID:               1797
		
		parent::__construct($controller,'EMV_VX805_WORLDPAY', 'VX805XPI_CTLS');
	}	
}