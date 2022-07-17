<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . 'libraries/REST_Controller.php';

/**
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array
 *
 * @package         CodeIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver
 */
class Employees extends REST_Controller {
	
		protected $methods = [
        'index_get' => ['level' => 1, 'limit' => 20],
        'index_post' => ['level' => 2, 'limit' => 20],
        'index_delete' => ['level' => 2, 'limit' => 20],
        'batch_post' => ['level' => 2, 'limit' => 20],

      ];

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
    }
			

		private function _employee_result_to_array($employee)
		{
			$this->load->model('Employee');
				
			$default_register = $this->Employee->getDefaultRegister($employee->person_id);
			$default_register_id = NULL;
			if (isset($default_register['register_id']))
			{
				$default_register_id = (int)$default_register['register_id'];
			}
			
				$employee_return = array(
					'person_id' => (int)$employee->person_id,
					'first_name' => $employee->first_name,
					'last_name' => $employee->last_name,
					'email' => $employee->email,
					'phone_number' => $employee->phone_number,
					'address_1' => $employee->address_1,
					'address_2' => $employee->address_2,
					'city' => $employee->city,
					'state' => $employee->state,
					'zip' => $employee->zip,
					'country' => $employee->country,
					'comments' => $employee->comments,
					'custom_fields' => array(),
					'username' => $employee->username,
					'inactive' => $employee->inactive ? true : false,
					'hire_date' => $employee->hire_date ? date(get_date_format(), strtotime($employee->hire_date)) : NULL,
					'employee_number' => $employee->employee_number,
					'birthday' => $employee->birthday ? date(get_date_format(), strtotime($employee->birthday)) : NULL,
					'login_start_time' => $employee->login_start_time ? date(get_time_format(), strtotime($employee->login_start_time)) : NULL,
					'login_end_time' => $employee->login_end_time ? date(get_time_format(), strtotime($employee->login_end_time)) : NULL,
					'termination_date' => $employee->termination_date ? date(get_date_format(), strtotime($employee->termination_date)) : NULL,
					'force_password_change' => $employee->force_password_change ? true : false,
					'default_register_id' => $default_register_id,
					'always_require_password' => $employee->always_require_password ? true : false,
					'not_required_to_clock_in' => $employee->not_required_to_clock_in ? true : false,
					'image_url' => $employee->image_id ? secure_app_file_url($employee->image_id) : '',
					'created_at' => $employee->create_date ? date(get_date_format().' '.get_time_format(), strtotime($employee->create_date)) : NULL,
					'dark_mode' => $employee->dark_mode ? true : false,
				);

				for($k=1; $k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS; $k++)
				{
					if($this->Employee->get_custom_field($k) !== false)
					{
						$field = array();
						$field['label']= $this->Employee->get_custom_field($k);
						if($this->Employee->get_custom_field($k,'type') == 'date')
						{
							$field['value'] = date_as_display_date($employee->{"custom_field_{$k}_value"});
						}
						else
						{
							$field['value'] = $employee->{"custom_field_{$k}_value"};
						}
						
						$employee_return['custom_fields'][$field['label']] = $field['value'];
					}
	
				}
				
				$this->load->model("Module_action");
				$allowed_modules_actions = $this->Module_action->get_allowed_module_actions($employee->person_id);
				
				$employee_return['permissions'] = array();
				$employee_return['permissions_location'] = array();
				//$employee_return['permissions_location2'] = array();
				
				foreach($allowed_modules_actions as $allowed_action){
					list($module,$action) = explode('|',$allowed_action);
					$employee_return['permissions'][$module][] = $action;
					//$employee_return['permissions_location'][$module]['locations'] = $this->Employee->get_employee_module_wise_location($employee->person_id, $module);
					//$employee_return['permissions_location'][$module]['actions'][$action] = $this->Employee->get_employee_module_action_wise_location($employee->person_id, $module, $action);
				}
				
				$permissions_location = array();
				
				$phppos_permissions_locations = $this->Employee->get_action_wise_employee_location($employee->person_id);
				$phppos_permissions_locations = $phppos_permissions_locations['permissions_locations'];
				
				foreach($phppos_permissions_locations as $k => $v){
					if(!array_key_exists($v->module_id, $permissions_location)){
						//$permissions_location[$v->module_id]['permission_name'] =  $v->module_id;
					}
					
					$permissions_location[$v->module_id]['locations'][] =  (int)$v->location_id;
					
					$action_location = array();
					
					$phppos_permissions_actions_locations = $this->Employee->get_action_wise_employee_location($employee->person_id, $v->module_id);
					$phppos_permissions_actions_locations = $phppos_permissions_actions_locations['permissions_actions_locations'];
					foreach($phppos_permissions_actions_locations as $kk => $vv){
						if(!array_key_exists($v->module_id, $action_location)){
							//$action_location[$vv->action_id]['action'] =  $vv->action_id;
						}
						$action_location[$vv->action_id]['locations'][] =  (int)$vv->location_id;
					}
					
					$permissions_location[$v->module_id]['actions'] = $action_location;
				}
				
				$employee_return['permissions_location'] = $permissions_location;
				
				return $employee_return;
		}

		public function index_delete($person_id)
		{
			$this->load->model('Employee');

			if ($person_id === NULL || !is_numeric($person_id))
      {
      		$this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
			}
			  $employee = $this->Employee->get_info($person_id, FALSE);
      	if ($employee->person_id && !$employee->deleted && $employee->person_id != 1)
				{	
						$this->Employee->delete($person_id);
				    $employee_return = $this->_employee_result_to_array($employee);
						$this->response($employee_return, REST_Controller::HTTP_OK);
				}
				else
				{
						$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
				}
			
		}
				
    public function index_get($person_id = NULL)
    {
			$this->load->model('Employee');
			$this->load->helper('url');
			$this->load->helper('date');
			
			if ($person_id === NULL)
      {
      	$search = $this->input->get('search');
      	$search_field = $this->input->get('search_field');
				$offset = $this->input->get('offset');
				$limit = $this->input->get('limit');
				$location_id = $this->input->get('location_id');
				
				if ($limit !== NULL && $limit > 100)
				{
					$limit = 100;
				}
				
				if ($search)
				{
					if ($search_field !== NULL)
					{
						$custom_fields_map = array();
			
						for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
						{
							if($this->Employee->get_custom_field($k) !== false)
							{
								$custom_fields_map[$this->Employee->get_custom_field($k)] = "custom_field_${k}_value";
							}
						}
						
						if (isset($custom_fields_map[$search_field]))
						{
							$search_field = $custom_fields_map[$search_field];
						}
						
					}

					$sort_col = $this->input->get('sort_col') ? $this->input->get('sort_col') : 'last_name';
					$sort_dir = $this->input->get('sort_dir') ? $this->input->get('sort_dir') : 'asc';

					$employees = $this->Employee->search($search, 0, $limit!==NULL ? $limit : 20, $offset!==NULL ? $offset : 0,$sort_col,$sort_dir,$search_field,$location_id)->result();
					$total_records = $this->Employee->search_count_all($search, 0,10000,$search_field,$location_id);
				}
				else
				{
					$sort_col = $this->input->get('sort_col') ? $this->input->get('sort_col') : 'pid';
					$sort_dir = $this->input->get('sort_dir') ? $this->input->get('sort_dir') : 'desc';
					
					$employees = $this->Employee->get_all(0,$limit!==NULL ? $limit : 20, $offset!==NULL ? $offset : 0,$sort_col,$sort_dir,false,$location_id)->result();
					$total_records = $this->Employee->count_all(0,$location_id);
				}
				
				$employees_return = array();
				foreach($employees as $employee)
				{
						$employees_return[] = $this->_employee_result_to_array($employee);
				}
				
				header("x-total-records: $total_records");
				
				$this->response($employees_return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
      }
      else
      {
      			if (!is_numeric($person_id))
      			{
							$this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
      			}
      			
        		$employee = $this->Employee->get_info($person_id, FALSE);
        		
        		if ($employee->person_id)
        		{
        			$employee_return = $this->_employee_result_to_array($employee);
							$this->response($employee_return, REST_Controller::HTTP_OK);
					}
					else
					{
							$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
					}			
      }
    }
    
    public function index_post($employee_id = NULL)
    {
			if ($employee_id!== NULL)
			{
				$this->_update($employee_id);
				return;
			}
			
    	$this->load->model('Employee');
			if (isset($_FILES['image']))
			{
				$employee_request = json_decode($_POST['employee'],TRUE);
			}
			else
			{
				$employee_request = json_decode(file_get_contents('php://input'),TRUE);
			}
			
			if ($employee_person_id = $this->_create_employee($employee_request))
			{
				$employee_return = $this->_employee_result_to_array($this->Employee->get_info($employee_person_id, FALSE));
				$this->response($employee_return, REST_Controller::HTTP_OK);
			}
			
			$this->response(NULL, REST_Controller::HTTP_METHOD_NOT_ALLOWED);
			
    }
    
    private function _save_and_populate_image_id($employee_request,&$person_data)
    {
    	if (isset($employee_request['image_url']) && $employee_request['image_url'])
    	{
				$this->load->model('Appfile');
				@$image_contents = file_get_contents_curl($employee_request['image_url']);
		
				if ($image_contents)
				{
					$image_file_id = $this->Appfile->save(basename($employee_request['image_url']), $image_contents);
					
					if ($image_file_id)
					{
						$person_data['image_id'] = $image_file_id;
					}
				}
			}
			elseif(isset($_FILES["image"]["tmp_name"]))
			{					
					$this->load->model('Appfile');
					$image_file_id = $this->Appfile->save(basename($_FILES["image"]["name"]), file_get_contents($_FILES["image"]["tmp_name"]));
					if ($image_file_id)
					{
						$person_data['image_id'] = $image_file_id;
					}
			}
    }
    private function _populate_custom_fields($employee_request,&$employee_data)
    {
    	$custom_fields_map = array();
			
			for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
			{
				if($this->Employee->get_custom_field($k) !== false)
				{
					$custom_fields_map[$this->Employee->get_custom_field($k)] = array('index' => $k, 'type' => $this->Employee->get_custom_field($k,'type'));
				}

			}
			if (isset($employee_request['custom_fields']))
			{
				foreach($employee_request['custom_fields'] as $custom_field => $custom_field_value)
				{
					if(isset($custom_fields_map[$custom_field]))
					{
						$key = $custom_fields_map[$custom_field]['index'];
						$type = $custom_fields_map[$custom_field]['type'];
					
						if ($type == 'date')
						{
							$employee_data["custom_field_{$key}_value"] = strtotime($custom_field_value);
						}
						else
						{
							$employee_data["custom_field_{$key}_value"] = $custom_field_value;
						}
					}
				}
			}
    }
    
    private function _create_employee($employee_request)
    {
    	 $this->load->model('Employee');

			$person_data = array(
			'first_name'=>isset($employee_request['first_name']) ? $employee_request['first_name'] : '',
			'last_name'=>isset($employee_request['last_name']) ? $employee_request['last_name'] : '',
			'email'=>isset($employee_request['email']) ? $employee_request['email'] : '',
			'phone_number'=>isset($employee_request['phone_number']) ? $employee_request['phone_number'] : '',
			'address_1'=>isset($employee_request['address_1']) ? $employee_request['address_1'] : '',
			'address_2'=>isset($employee_request['address_2']) ? $employee_request['address_2'] : '',
			'city'=>isset($employee_request['city']) ? $employee_request['city'] : '',
			'state'=>isset($employee_request['state']) ? $employee_request['state'] : '',
			'zip'=>isset($employee_request['zip']) ? $employee_request['zip'] : '',
			'country'=>isset($employee_request['country']) ? $employee_request['country'] : '',
			'comments'=>isset($employee_request['comments']) ? $employee_request['comments'] : '',
			);
		
			$employee_data=array(
			'username'=>isset($employee_request['username']) ? $employee_request['username'] : '',
			'password'=>isset($employee_request['password']) ? md5($employee_request['password']) : '',
			'inactive'=>isset($employee_request['inactive']) && $employee_request['inactive'] ? 1 : 0,
			'reason_inactive'=>isset($employee_request['reason_inactive']) ? $employee_request['reason_inactive'] : '',
			'employee_number'=>isset($employee_request['employee_number']) ? $employee_request['employee_number'] : NULL,
			'hire_date'=>isset($employee_request['hire_date']) ? date('Y-m-d',strtotime($employee_request['hire_date']) ): NULL,
			'birthday'=>isset($employee_request['birthday']) ? date('Y-m-d',strtotime($employee_request['birthday']) ): NULL,
			'login_start_time'=>isset($employee_request['login_start_time']) ? date('H:i:s',strtotime($employee_request['login_start_time']) ): NULL,
			'login_end_time'=>isset($employee_request['login_end_time']) ? date('H:i:s',strtotime($employee_request['login_end_time']) ): NULL,
			'termination_date'=>isset($employee_request['termination_date']) ? date('Y-m-d',strtotime($employee_request['termination_date']) ): NULL,
			'force_password_change'=>isset($employee_request['force_password_change']) && $employee_request['force_password_change'] ? 1 : 0,
			'always_require_password'=>isset($employee_request['always_require_password']) && $employee_request['always_require_password'] ? 1 : 0,
			'not_required_to_clock_in'=>isset($employee_request['not_required_to_clock_in']) && $employee_request['not_required_to_clock_in'] ? 1 : 0,
			'dark_mode'=>isset($employee_request['dark_mode']) && $employee_request['dark_mode'] ? 1 : 0,
			);
			
			
			$permissions_location = isset($employee_request['permissions_location']) ? $employee_request['permissions_location'] : array();
			
			$module_location = array();
			$action_location = array();
			
			$permissions_location = $this->_marge_permission_location($permissions_location);
			
			if(!empty($permissions_location)){
				$module_location = $permissions_location['module_location'];
				$action_location = $permissions_location['action_location'];
			}

			$this->_populate_custom_fields($employee_request,$employee_data);
			$this->_save_and_populate_image_id($employee_request,$person_data);
			
			$permissions = $this->_merge_permission_data(isset($employee_request['permissions']) ? $employee_request['permissions'] : array());
			$permission_actions = $this->_merge_permission_action_data(isset($employee_request['permissions']) ? $employee_request['permissions'] : array());
			$locations = $this->_merge_location_data(isset($employee_request['locations']) ? $employee_request['locations'] : array());
			
	
			$this->Employee->save_employee($person_data,$employee_data,$permissions,$permission_actions,$locations, $employee_id=false, $action_location, $module_location);
			
			if (isset($employee_request['default_register_id']))
			{
				$this->db->where('employee_id',$employee_data['person_id']);
				$this->db->delete('employee_registers');				
				$this->db->insert('employee_registers',array('employee_id' => $employee_data['person_id'], 'register_id' => $employee_request['default_register_id']));
			}
			
			return $employee_data['person_id'];
    }
    
    public function _merge_location_data($locations,$employee_person_id = FALSE)
    {
    	if ($employee_person_id === FALSE)
    	{
    		return $locations;
    	}
    	
    	$auth_locations = $this->Employee->get_authenticated_location_ids($employee_person_id);
    	
    	$return = array_unique(array_merge($locations,$auth_locations));
    	return $return;
    }
    
    
    private function _merge_permission_data($employee_request_permissions,$employee_person_id = FALSE)
    {
    	if ($employee_person_id === FALSE)
    	{
    		return array_keys($employee_request_permissions);
    	}
    	$this->load->model('Module');
    	
    	$allowed_modules = $this->Module->get_allowed_modules($employee_person_id)->result_array();
    	$allowed_modules = array_column($allowed_modules, 'module_id');
    	$new_modules = array_keys($employee_request_permissions);
    	$return = array_keys(array_merge(array_flip($allowed_modules),array_flip($new_modules)));
    	return $return;
    }

    private function _merge_permission_action_data($employee_request_permissions,$employee_person_id = FALSE)
    {
    	$api_allowed_actions = array();
    	$api_modules= array();
    	foreach($employee_request_permissions as $module => $actions)
    	{
    	  $api_modules[] = $module;
    	
    		foreach($actions as $action)
    		{
    			$api_allowed_actions[] = $module.'|'.$action;
    		}
    	}
    	
    	
    	$allowed_modules_actions = array();
    	if ($employee_person_id !== FALSE)
    	{
    	  $this->load->model('Module_action'); 
    	  $allowed_modules_actions = array();
    	  
	    	foreach($this->Module_action->get_allowed_module_actions($employee_person_id) as $allowed_action)
	    	{
	    	  list($module,$action) = explode('|',$allowed_action);
	    	  //We only want to save module actions for actions not in api request
	    	  if (!in_array($module,$api_modules))
	    	  {
	    			$allowed_modules_actions[] = $allowed_action;
	    		}
	    	}
	    	
    	}
    	
    	$return = array_keys(array_merge(array_flip($allowed_modules_actions),array_flip($api_allowed_actions)));

    	return $return;
    	
    }
    
    private function _update_employee($employee_person_id,$employee_request)
    {
   	  $this->load->model('Employee');

			$person_data = array();
			$employee_data = array();
			
    	$person_keys = array('first_name','last_name','email','phone_number','address_1','address_2','city','state','zip','country','comments');
    	$employee_keys = array('login_start_time','login_end_time','username','password','inactive','reason_inactive','employee_number','hire_date','birthday','termination_date','force_password_change','always_require_password','not_required_to_clock_in');
    	
    	foreach($employee_request as $key=>$value)
    	{
				if(in_array($key,$person_keys))
				{
					$person_data[$key] = $value;
				}
				elseif(in_array($key,$employee_keys))
				{
					if (in_array($key,array('login_start_time','login_end_time')))
					{
						$employee_data[$key] = date('H:i:s',strtotime($value));						
					}
					elseif (in_array($key,array('hire_date','birthday','termination_date')))
					{
						$employee_data[$key] = date('Y-m-d',strtotime($value));
					}
					elseif(in_array($key,array('inactive','force_password_change','always_require_password','not_required_to_clock_in')))
					{
						$employee_data[$key] = $value ? 1 : 0;
					}
					elseif($key == 'password')
					{
						$employee_data[$key] = md5($value);
					}
					else
					{
						$employee_data[$key] = $value;
					}
				}
    	}
			
			if (isset($employee_request['default_register_id']))
			{
				$this->db->where('employee_id',$employee_person_id);
				$this->db->delete('employee_registers');				
				$this->db->insert('employee_registers',array('employee_id' => $employee_person_id, 'register_id' => $employee_request['default_register_id']));
			}
			
    	
			$this->_populate_custom_fields($employee_request,$employee_data);
			$this->_save_and_populate_image_id($employee_request,$person_data);
			
			$permissions = $this->_merge_permission_data(isset($employee_request['permissions']) ? $employee_request['permissions'] : array(),$employee_person_id);
			$permission_actions = $this->_merge_permission_action_data(isset($employee_request['permissions']) ? $employee_request['permissions'] : array(),$employee_person_id);
			$locations = $this->_merge_location_data(isset($employee_request['locations']) ? $employee_request['locations'] : array(),$employee_person_id);
			
			$permissions_location = isset($employee_request['permissions_location']) ? $employee_request['permissions_location'] : array();
			
			$module_location = array();
			$action_location = array();
			
			$permissions_location = $this->_marge_permission_location($permissions_location, $employee_person_id);
			
			if(!empty($permissions_location)){
				$module_location = $permissions_location['module_location'];
				$action_location = $permissions_location['action_location'];
			}
			
			$this->load->helper('demo');
			if ( (is_on_demo_host()) && $employee_person_id == 1)
			{
				return FALSE;
			}
			else
			{
				return $this->Employee->save_employee($person_data,$employee_data,$permissions,$permission_actions,$locations,$employee_person_id, $action_location, $module_location);
			}
		}
    
    public function _update($employee_person_id)
    {
   		if (isset($_FILES['image']))
			{
				$employee_request = json_decode($_POST['employee'],TRUE);
			}
			else
			{
				$employee_request = json_decode(file_get_contents('php://input'),TRUE);
			}
			
      if ($this->_update_employee($employee_person_id, $employee_request))
			{
				$employee_return = $this->_employee_result_to_array($this->Employee->get_info($employee_person_id, FALSE));
				$this->response($employee_return, REST_Controller::HTTP_OK);
			}
			
			$this->response(NULL, REST_Controller::HTTP_METHOD_NOT_ALLOWED);
			
    }
    
        
    public function batch_post()
    {
       	$this->load->model('Employee');

    		$request = json_decode(file_get_contents('php://input'),TRUE);
    		$create = isset($request['create']) ? $request['create']:  array();
    		$update = isset($request['update']) ? $request['update'] : array();
    		$delete = isset($request['delete']) ? $request['delete'] : array();
    		
    		$response = array();
    		
    		if (!empty($create))
    		{
    			$response['create'] = array();
    			
    			foreach($create as $employee_request)
    			{
    				if ($employee_person_id = $this->_create_employee($employee_request))
						{
							$employee_return = $this->_employee_result_to_array($this->Employee->get_info($employee_person_id, FALSE));
						}
						else
						{
							$employee_return = array('error' => TRUE);
						}
						$response['create'][] = $employee_return;

    			}
    		}

    		if (!empty($update))
    		{
    			$response['update'] = array();
    			
    				foreach($update as $employee_request)
    				{
							if ($this->_update_employee($employee_request['person_id'],$employee_request))
							{
								$employee_return = $this->_employee_result_to_array($this->Employee->get_info($employee_request['person_id'], FALSE));
							}
							else
							{
								$employee_return = array('error' => TRUE);
							}
							$response['update'][] = $employee_return;
    				}

    		}

    		if (!empty($delete))
    		{
    			$response['delete'] = array();
    			
    			foreach($delete as $person_id)
    			{
							if ($person_id === NULL || !is_numeric($person_id))
     				  {
								$response['delete'][] = array('error' => TRUE);
			      		break;
			      	}
			      	
			  			$employee = $this->Employee->get_info($person_id, FALSE);
							if ($employee->person_id && !$employee->deleted && $employee->person_id != 1)
							{	
									$this->Employee->delete($person_id);
									$employee_return = $this->_employee_result_to_array($employee);
									$response['delete'][] = $employee_return;
							}
							else
							{
								$response['delete'][] = array('error' => TRUE);
							}
    			}
    		}
    		
				$this->response($response, REST_Controller::HTTP_OK);
    }
    
    
    function _marge_permission_location($permissions_location, $employee_id=false){
    	$return = array();
    	$api_module_location = array();
    	$api_action_location = array();
    	$api_modules = array();
    	$api_actions = array();
			foreach($permissions_location as $pk => $pv){
				$api_modules[] = $pk;
				if(isset($pv['locations'])){
					foreach($pv['locations'] as $spk => $spv){
						$api_module_location[] = $pk."|".$spv;
					}
				}
				
				if(isset($pv['actions'])){
					foreach($pv['actions'] as $aspk => $aspv){
						$api_actions[] = $pk."|".$aspk;
						if(isset($aspv['locations'])){
							foreach($aspv['locations'] as $lk => $lv){
								$api_action_location[] = $pk."|".$aspk."|".$lv;
							}
						}
					}
				}
			}
			
			if($employee_id==false){
				$return['module_location'] = $api_module_location;
				$return['action_location'] = $api_action_location;
				return $return;
			}
			
			$allowed_permissions_location = $this->Employee->get_action_wise_employee_location($employee_id);
			
			$allowed_module_location = array();
			$allowed_action_location = array();
			
			foreach( $allowed_permissions_location['permissions_locations'] as $plv){
				if(!in_array($plv->module_id, $api_modules)){
					$allowed_module_location[] = $plv->module_id."|".$plv->location_id;
				}
			}
			
			$return['module_location'] = array_keys(array_merge(array_flip($allowed_module_location), array_flip($api_module_location)));
			
			foreach( $allowed_permissions_location['permissions_actions_locations'] as $alv){
				if(!in_array($alv->module_id."|".$alv->action_id, $api_actions)){
					$allowed_action_location[] = $alv->module_id."|".$alv->action_id."|".$alv->location_id;
				}
			}
			
			$return['action_location'] = array_keys(array_merge(array_flip($allowed_action_location), array_flip($api_action_location)));
			return $return;
    }
}
