<?php
class Login extends MY_Controller 
{
	function __construct()
	{
		parent::__construct();
		$this->lang->load('login');
		$this->load->helper('cloud');
	}
	
	function index()
	{
		$data = array();
		$this->load->helper('demo');
		$data['username'] = is_on_demo_host() ? 'admin' : '';
		$data['password'] = is_on_demo_host() ? 'pointofsale' : '';
		if ($this->agent->browser() == 'Internet Explorer' && $this->agent->version() < 11)
		{
			$data['ie_browser_warning'] = TRUE;
		}
		else
		{
			$data['ie_browser_warning'] = FALSE;
		}
		
		$this->load->helper('update');
		if(!is_on_phppos_host() && (APPLICATION_VERSION!=$this->config->item('version') || ($this->migration->get_migration_version() != $this->migration->get_version())))
		{
			redirect('migrate/start');
		}
		
		if($this->Employee->is_logged_in())
		{
			redirect('home');
		}
		else
		{
			$this->form_validation->set_rules('username', 'lang:login_username', 'required|callback_employee_location_check|callback_login_check');
			$this->form_validation->set_message('required', lang('login_invalid_username_and_password'));
    	   $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
			if($this->form_validation->run() == FALSE)
			{
				//Only set the username when we have a non false value (not '' or FALSE)
				if ($this->input->post('username'))
				{					
					$data['username'] = $this->input->post('username');
				}
				
				$this->load->helper('update');
				if (is_on_phppos_host())
				{
					$site_db = $this->load->database('site', TRUE);
					if (!is_on_demo_host())
					{
						$data['announcement'] = get_cloud_announcement($site_db);
					}
					
					if (is_subscription_cancelled($site_db) || is_subscription_failed($site_db) || is_in_trial($site_db))
					{
						$data['cloud_customer_info'] = get_cloud_customer_info($site_db);
						
						if (is_in_trial($site_db))
						{
							if (is_trial_over($site_db))
							{
								$data['trial_over']  = TRUE;
								$this->load->view('login/trial_over',$data);
							}
							else
							{
								$data['trial_on']  = TRUE;
								$this->load->view('login/login',$data);
							}
						}
						elseif (is_subscription_failed($site_db))
						{
							$data['subscription_payment_failed']  = TRUE;
							$this->load->view('login/login',$data);							
						}
						elseif (is_subscription_cancelled_within_grace_period($site_db))
						{
							$data['subscription_cancelled_within_5_days']  = TRUE;
							$this->load->view('login/login',$data);
						}
						else
						{
							$this->load->view('login/subscription_cancelled', $data);
						}
					}
					else
					{
						$this->load->view('login/login', $data);
					}
				}
				else
				{
					$this->load->view('login/login',$data);
				}
				
				if (isset($site_db) && $site_db)
				{
					$site_db->close();
				}			
			}
			else
			{
				
				$logged_in_employee_info=$this->Employee->get_logged_in_employee_info();
				
				if ($logged_in_employee_info->force_password_change)
				{
					$this->Employee->logout(false);
					$data['username'] = $logged_in_employee_info->username;
					//Create key on the fly
					$data['key'] = $this->generate_reset_key($logged_in_employee_info->person_id);
					$data['force_password_change'] = TRUE;
					$this->load->view('login/reset_password_enter_password', $data);			
				}
				else
				{
					$number_of_locations = count($this->Employee->get_authenticated_location_ids($logged_in_employee_info->person_id));
					
					if ($this->input->get('continue'))
					{
						$continue = rtrim($this->input->get('continue'),'?');
						redirect('/'.$continue);	
					}
					else
					{
						redirect('home/index/'.($number_of_locations > 1 ? '1' : '0'));
					}
				}
			}
		}
	}
		
	function login_check($username)
	{
		$this->load->helper('update');
		if (is_on_phppos_host())
		{
			$site_db = $this->load->database('site', TRUE);
		
			if (is_subscription_cancelled($site_db))
			{
				//If we are not cancelled within 5 days; block login
				if (!is_subscription_cancelled_within_grace_period($site_db))
				{
					$this->form_validation->set_message('login_check', lang('login_invalid_username_and_password'));
					return false;
				}
			}
			if (isset($site_db) && $site_db)
			{
				$site_db->close();
			}
		}
		$password = $this->input->post("password");	
		
		if(!$this->Employee->login($username,$password))
		{
			if ($this->Employee->login_failed_time_period($username,$password))
			{
				$this->form_validation->set_message('login_check', lang('login_you_are_not_allowed_to_login_at_this_time'));
			}
			else
			{
				$this->form_validation->set_message('login_check', lang('login_invalid_username_and_password'));
			}
			
			return false;
		}
		return true;		
	}
	
	function employee_location_check($username)
	{		
		$employee_id = $this->Employee->get_employee_id($username);
		
		if ($employee_id)
		{
			$employee_location_count = count($this->Employee->get_authenticated_location_ids($employee_id));

			if ($employee_location_count < 1)
			{
				$this->form_validation->set_message('employee_location_check', lang('login_employee_is_not_assigned_to_any_locations'));
				return false;
			}
		}
		
		//Didn't find an employee, we can pass validation
		return true;
	}
		
	function can_fast_switch()
	{
		$allowed = $this->_can_fast_switch_user($this->input->post('username'));
		
		echo json_encode(array('allowed' => $allowed));
	}
	
	function _can_fast_switch_user($username)
	{
		$emp_id = $this->Employee->get_employee_id($username);
		
		if ($emp_id)
		{
			$emp_info = $this->Employee->get_info($emp_id);
			return !$emp_info->always_require_password && $this->config->item('fast_user_switching');
		}
		
		return false;
	}
	
	private function reset_sold_by_employee($previous_logged_in_employee_id)
	{
		$cart = PHPPOSCartSale::get_instance('sale');
		
		if ($this->config->item('default_sales_person') != 'not_set' && $cart->sold_by_employee_id == $previous_logged_in_employee_id)
		{
			$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
			$cart->sold_by_employee_id = $employee_id;
			$cart->save();
		}
		elseif($this->config->item('default_sales_person') == 'not_set')
		{
			$cart->sold_by_employee_id = NULL;
			$cart->save();
		}
	}
	
	function switch_user($reload = 0)
	{
		require_once (APPPATH."models/cart/PHPPOSCartSale.php");
		
		$previous_logged_in_employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
		$username = $this->input->post('username_or_account_number');
		
		if (!$username)
		{
			$username = $this->input->post('username');
		}
		
		if($username && $this->_can_fast_switch_user($username))
		{
			if (!$this->Employee->login_no_password($username))
			{
				echo json_encode(array('success'=>false,'message'=>lang('login_invalid_username_and_password')));
			}
			else
			{
				if ($this->config->item('reset_location_when_switching_employee'))
				{
					//Unset location in case the user doesn't have access to currently set location
					$this->session->unset_userdata('employee_current_location_id');							
				}
				$this->reset_sold_by_employee($previous_logged_in_employee_id);
				$emp_info = $this->Employee->get_logged_in_employee_info();
				$name = $emp_info->first_name. ' '.$emp_info->last_name;
				$avatar = $emp_info->image_id ?  secure_app_file_url($emp_info->image_id) : base_url('assets/assets/images/avatar-default.jpg');
				$is_clocked_in_or_timeclock_disabled = $this->Employee->get_logged_in_employee_info()->not_required_to_clock_in || $this->Employee->is_clocked_in() || !$this->config->item('timeclock');
				echo json_encode(array('success'=>true,'reload' => $reload,'name' => $name,'avatar' => $avatar,'is_clocked_in_or_timeclock_disabled' => $is_clocked_in_or_timeclock_disabled));
			}
		}
		elseif($username)
		{
			if(!$this->Employee->login($username,$this->input->post('password')))
			{
				echo json_encode(array('success'=>false,'message'=>lang('login_invalid_username_and_password')));
			}
			else
			{
				if ($this->config->item('reset_location_when_switching_employee'))
				{
					//Unset location in case the user doesn't have access to currently set location
					$this->session->unset_userdata('employee_current_location_id');							
				}
				
				$this->reset_sold_by_employee($previous_logged_in_employee_id);
				$is_clocked_in_or_timeclock_disabled = $this->Employee->get_logged_in_employee_info()->not_required_to_clock_in || $this->Employee->is_clocked_in() || !$this->config->item('timeclock');
				
				$emp_info = $this->Employee->get_logged_in_employee_info();
				$name = $emp_info->first_name. ' '.$emp_info->last_name;
				$avatar = $emp_info->image_id ?  secure_app_file_url($emp_info->image_id) : base_url('assets/assets/images/avatar-default.jpg');

				
				echo json_encode(array('success'=>true,'reload' => $reload, 'name'=> $name,'avatar' => $avatar,'is_clocked_in_or_timeclock_disabled' => $is_clocked_in_or_timeclock_disabled));
			}
		}
		else
		{
			foreach($this->Employee->get_all()->result_array() as $row)
			{
				$employees[$row['username']] = $row['first_name'] .' '. $row['last_name'];
			}
			$data['employees']=$employees;
			$data['reload'] = $reload;
			$this->load->view('login/switch_user',$data);
		}
	}
			
	function reset_password()
	{
		$this->load->view('login/reset_password');
	}
	
	function do_reset_password_notify()
	{	
		if($this->input->post('username_or_email'))
		{
			$employee = $this->Employee->get_employee_by_username_or_email($this->input->post('username_or_email'));
			if ($employee)
			{
				$data = array();
				$data['employee'] = $employee;
			   $data['reset_key'] = $this->generate_reset_key($employee->person_id);
			
				$this->load->library('email');
				$config['mailtype'] = 'html';
				$this->email->initialize($config);
				$this->email->from('no-reply@mg.phppointofsale.com', $this->config->item('company'));
				$this->email->to($employee->email); 
				
				if($this->Location->get_info_for_key('cc_email'))
				{
					$this->email->cc($this->Location->get_info_for_key('cc_email'));
				}
				
				if($this->Location->get_info_for_key('bcc_email'))
				{
					$this->email->bcc($this->Location->get_info_for_key('bcc_email'));
				}
				
				$this->email->subject(lang('login_reset_password'));
				$this->email->message($this->load->view("login/reset_password_email",$data, true));	
				$this->email->send();
			
				$data['success']=lang('login_password_reset_has_been_sent');
				$this->load->view('login/reset_password',$data);
			}
			else 
			{
				$data['error']=lang('login_username_or_email_does_not_exist');
				$this->load->view('login/reset_password',$data);
			}
		}
		else
		{
			$data['error']= lang('common_field_cannot_be_empty');
			$this->load->view('login/reset_password',$data);
		}
	}
	
	function reset_password_enter_password($key=false)
	{
		if ($key)
		{
			$data = array();
			$reset_info = $this->get_reset_info($key);
			
			if ($reset_info)
			{
				$employee_id = $reset_info->employee_id;
				$expire = strtotime($reset_info->expire);
						 
				if ($employee_id && $expire && $expire > time())
				{
					$employee = $this->Employee->get_info($employee_id);
					$data['username'] = $employee->username;
					$data['key'] = $key;
					$this->load->view('login/reset_password_enter_password', $data);			
				}
			}
		}
	}
	
	function get_reset_info($key)
	{
		$this->db->from('employees_reset_password');
		$this->db->where('key',$key);
		$query = $this->db->get();

		if($query->num_rows()==1)
		{
			return $query->row();
		}
		
		return FALSE;
	}
	
	function generate_reset_key($employee_id)
	{
		if (function_exists('openssl_random_pseudo_bytes'))
		{
			$key = bin2hex(openssl_random_pseudo_bytes(16));
		}
		else
		{
			$key = md5(rand());
		}
		if($this->db->insert('employees_reset_password',
		array(
			'employee_id' => $employee_id, 
			'key' => $key, 
			'expire' => date('Y-m-d H:i:s', strtotime("+3 day")))))
		{
			return $key;
		}
		
		return FALSE;
	}
	
	function delete_reset_key($key)
	{
		return $this->db->delete('employees_reset_password', array('key' => $key)); 
	}
	
	function do_reset_password($key=false)
	{
		if ($key)
		{
			$reset_info = $this->get_reset_info($key);
			
			if ($reset_info)
			{
				$employee_id = $reset_info->employee_id;
				$expire = strtotime($reset_info->expire);
				
				if ($employee_id && $expire && $expire > time())
				{
					$password = $this->input->post('password');
					$confirm_password = $this->input->post('confirm_password');
			
					if (($password == $confirm_password) && strlen($password) >=8)
					{
						if ($this->Employee->update_employee_password($employee_id, md5($password)))
						{
							$this->delete_reset_key($key);
							$this->load->view('login/do_reset_password');	
						}
					}
					else
					{
						$data = array();
						$employee = $this->Employee->get_info($employee_id);
						$data['username'] = $employee->username;
						$data['key'] = $key;
						$data['force_password_change'] = $this->input->post('force_password_change') ? TRUE : FALSE;
						$data['error_message'] = lang('login_passwords_must_match_and_be_at_least_8_characters');
						$this->load->view('login/reset_password_enter_password', $data);
					}
				}
			}
		}
	}
	
	function is_update_available()
	{
		session_write_close();
		$this->load->helper('update');
		echo json_encode(is_phppos_update_available());
	}
}
?>