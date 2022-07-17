<?php
require_once ("Datacapusbprocessor.php");
class Otheremvusbprocessor extends Datacapusbprocessor
{
	function __construct($controller)
	{		
		parent::__construct($controller,$controller->Location->get_info_for_key('secure_device_override_emv'),$controller->Location->get_info_for_key('secure_device_override_non_emv'));
	}	
}