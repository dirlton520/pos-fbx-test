<?php
/*
This interface is implemented by any controller that keeps track of people, such
as customers and employees.
*/
require_once ("Idata_controller.php");
interface Iperson_controller extends Idata_controller
{
	public function mailto();
}
?>