<?php
class Testing extends CI_Controller
{
	function index()
	{
		echo '1';
	}
	
	function server_side_https()
	{
		$testing_url = site_url('testing','https');
		
		//TEST HTTPS connection by sending https request to keep_alive in home controller
		$ch = curl_init(); 
		//Don't verify ssl...just in case a server doesn't have the ability to verify
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_URL, $testing_url); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,3); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);
		$testing_response = curl_exec($ch); 
		curl_close($ch);		
		if ($testing_response)
		{
			echo json_encode(array('success' => TRUE));
		
		}
		else
		{
			echo json_encode(array('success' => FALSE));
		}
	}
}