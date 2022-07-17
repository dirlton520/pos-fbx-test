<?php
require_once ("Secure_area.php");
class No_access extends  Secure_area 
{
	function __construct()
	{
		parent::__construct();
	}
	
	function index($module_id='')
	{
		$this->lang->load('error');
		$this->lang->load('module');
		$data['module_name']=$this->Module->get_module_name($module_id);
		$this->load->view('no_access',$data);
	}

	function ip_restriction($ip='')
	{
		$this->lang->load('error');
		$this->lang->load('module');
		$data['ip']=$ip;
		$this->load->view('ip_restriction',$data);
	}
}
?>
