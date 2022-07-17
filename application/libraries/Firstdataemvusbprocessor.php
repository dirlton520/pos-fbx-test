<?php
require_once ("Datacapusbprocessor.php");
class Firstdataemvusbprocessor extends Datacapusbprocessor
{
	// URL/IP:                 testhost4.dsipscs.com
	// MID:                      939650001885
	// TID:                        01352621
		
	function __construct($controller)
	{		
		parent::__construct($controller,'EMV_VX805_RAPIDCONNECT','VX805XPI_CTLS');
	}	
}