<?php
require_once (APPPATH."libraries/ip-lib-1.17.0/ip-lib.php");

class Secure_area extends MY_Controller 
{
	var $module_id;

	/*
	Controllers that are considered secure extend Secure_area, optionally a $module_id can
	be set to also check if a user can access a particular module in the system.
	*/
	function __construct($module_id=null)
	{
		parent::__construct();
		$this->module_id = $module_id;	
		$this->load->model('Employee');
		$this->load->model('Location');

		if(!$this->Employee->is_logged_in())
		{
			redirect('login?continue='.rawurlencode(uri_string().'?'.$_SERVER['QUERY_STRING']));
		}

		//check ip access allow
		if( count($this->Employee->get_logged_in_employee_info()->allowed_ip_address) > 0 )
		{
			$ip_range_list = $this->Employee->get_logged_in_employee_info()->allowed_ip_address;
			$ip_login = get_real_ip_address();
			$ip_type = "";
			$ip_allow = false;

			$matches = array();
			$login_address = \IPLib\Factory::parseAddressString($ip_login);

			foreach($ip_range_list as $ip_range){
				$range = \IPLib\Factory::parseRangeString($ip_range);
				if(!$range) continue;
				$contained = $login_address->matches($range);
				if($contained){
					$ip_allow = true; break;
				}
			}

			if(!$ip_allow){
				if($ip_login == "::1" || $ip_login == "127.0.0.1" ){
					// $this->Employee->logout();
				}else{
					$controller = $this->router->fetch_class();
					if($controller != "no_access"){
						redirect('no_access_ip/'.$login_address->toString());
					}
				}
			}
		}

		if(!$this->Employee->has_module_permission($this->module_id, $this->Employee->get_logged_in_employee_info()->person_id))
		{
			redirect('no_access/'.$this->module_id);
		}

		//load disable modules
		$data['disable_modules'] = unserialize($this->config->item('disable_modules'));
		if(!$data['disable_modules']) $data['disable_modules']= array();

		$controller = $this->router->fetch_class();
		if(array_search($controller, $data['disable_modules']) !== false){
			redirect('no_access/'.$this->module_id);
		}

		//load up global data
		$logged_in_employee_info=$this->Employee->get_logged_in_employee_info();
		$data['allowed_modules']=$this->Module->get_allowed_modules($logged_in_employee_info->person_id);
		$data['user_info']=$logged_in_employee_info;
		$data['new_message_count']=$this->Employee->get_unread_messages_count();;
		
		$all_locations_in_system = array();
		$locations_list=$this->Location->get_all();
		
		$authenticated_locations = $this->Employee->get_authenticated_location_ids($logged_in_employee_info->person_id,TRUE);
		
		
		
		$locations = array();
		$total_locations_in_system = 0;
		foreach($locations_list->result() as $row)
		{
			if(in_array($row->location_id, $authenticated_locations))
			{
				$locations[$row->location_id] =$row->name;
			}
			
			$all_locations_in_system[$row->location_id] =$row->name;
			
			$total_locations_in_system++;
		}
		
		$data['total_locations_in_system'] = $total_locations_in_system;
		$data['authenticated_locations'] = $locations;
		$data['all_locations_in_system'] = $all_locations_in_system;
		
		$location_id = $this->Employee->get_logged_in_employee_current_location_id();
		$loc_info = $this->Location->get_info($location_id);
		
		$data['current_logged_in_location_id'] = $location_id;
		$data['current_employee_location_info'] = $loc_info;
		$data['location_color'] = $loc_info->color;
		
		$this->load->helper('update');
		if (is_on_phppos_host())
		{
			$this->load->helper('cloud');
			
			$phppos_client_name = substr($_SERVER['HTTP_HOST'], 0, strpos($_SERVER['HTTP_HOST'], '.'));
			$site_db = $this->load->database('site', TRUE);
			
			if (is_trial_over($site_db) || (is_subscription_cancelled($site_db) && !is_subscription_cancelled_within_grace_period($site_db)))
			{
				$this->Employee->logout();
			}
			
			$site_db->from('subscriptions');	
			$site_db->where('username',$phppos_client_name);
			$query = $site_db->get();			
			$data['cloud_customer_info'] = $query->row_array();
			if (isset($site_db) && $site_db)
			{
				$site_db->close();
			}
		}
		
		$this->load->vars($data);
	}
	
	function check_action_permission($action_id)
	{
		if (!$this->Employee->has_module_action_permission($this->module_id, $action_id, $this->Employee->get_logged_in_employee_info()->person_id))
		{
			redirect('no_access/'.$this->module_id);
		}
	}

	function check_ip_validate(){
		$ip_range = $this->input->post('value');
		$range = \IPLib\Factory::parseRangeString($ip_range);
		$is_valid = false;
		if($range) $is_valid = true;

		$result = array(
			'isValid' => $is_valid,
			'ip_range' => $ip_range
		);
		echo json_encode($result);
		exit;
	}	
}
?>