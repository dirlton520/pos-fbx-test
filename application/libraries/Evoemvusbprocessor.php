<?php
require_once ("Datacapusbprocessor.php");
class Evoemvusbprocessor extends Datacapusbprocessor
{
	function __construct($controller)
	{	
		//Net e pay dev is testhost3.dsipscs.com
		parent::__construct($controller,'EMV_VX805_TSYS','VX805XPI_CTLS');
	}	
}