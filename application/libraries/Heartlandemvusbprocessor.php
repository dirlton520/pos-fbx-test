<?php
require_once ("Datacapusbprocessor.php");
class Heartlandemvusbprocessor extends Datacapusbprocessor
{
	function __construct($controller)
	{		
		//Dev server for net-pay is testhost5.dsipscs.com
		parent::__construct($controller,'EMV_IPP320_HEARTLAND','INGENICOIPP320_HEARTLAND_VOLT');
	}	
}