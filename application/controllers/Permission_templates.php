<?php
require_once ("Secure_area.php");
require_once ("interfaces/Idata_controller.php");
class Permission_templates extends Secure_area implements Idata_controller
{
	function __construct()
	{
		parent::__construct('employees');
		$this->lang->load('employees');
		$this->lang->load('module');
		$this->lang->load('permission_templates');
		$this->load->model('Permission_template');
	}
	
	function index($offset=0)
	{
		//templates_search_data
		$params = $this->session->userdata('permission_template_search_data') ? $this->session->userdata('permission_template_search_data') : array('offset' => 0, 'order_col' => 'name', 'order_dir' => 'asc', 'search' => FALSE,'deleted' => 0);
		if ($offset!=$params['offset'])
		{
		   redirect('permission_templates/index/'.$params['offset']);
		}
		$this->check_action_permission('search');
		$config['base_url'] = site_url('permission_templates/sorting');
		$config['per_page'] = $this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20; 
		$data['deleted'] = $params['deleted'];
		$data['controller_name']=strtolower(get_class());
		$data['per_page'] = $config['per_page'];
		$data['search'] = $params['search'] ? $params['search'] : "";

		if ($data['search'])
		{
			$config['total_rows'] = $this->Permission_template->search_count_all($data['search'],$params['deleted']);
			$table_data = $this->Permission_template->search($data['search'],$params['deleted'],$data['per_page'],$params['offset'],$params['order_col'],$params['order_dir']);
		}
		else
		{
			$config['total_rows'] = $this->Permission_template->count_all($params['deleted']);
			$table_data = $this->Permission_template->get_all($params['deleted'],$data['per_page'],$params['offset'],$params['order_col'],$params['order_dir']);
		}
		
		$data['total_rows'] = $config['total_rows'];
		$this->load->library('pagination');
		$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		$data['order_col'] = $params['order_col'];
		$data['order_dir'] = $params['order_dir'];
		$data['manage_table']= get_permission_template_manage_table($table_data,$this);
		
		$this->load->view('permission_templates/manage',$data);
	}
	
	
	function sorting()
	{
		$this->check_action_permission('search');
		$params = $this->session->userdata('permission_template_search_data') ? $this->session->userdata('permission_template_search_data') : array('offset' => 0, 'order_col' => 'name', 'order_dir' => 'asc', 'search' => FALSE,'deleted' => 0);
		
		$search = $this->input->post('search') ? $this->input->post('search') : "";
		$per_page = $this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20;
		$offset = $this->input->post('offset') ? $this->input->post('offset') : 0;
		$order_col = $this->input->post('order_col') ? $this->input->post('order_col') : $params['order_col'];
		$order_dir = $this->input->post('order_dir') ? $this->input->post('order_dir'): $params['order_dir'];
		$deleted = $this->input->post('deleted') ? $this->input->post('deleted') : $params['deleted'];

		$permission_template_search_data = array('offset' => $offset, 'order_col' => $order_col, 'order_dir' => $order_dir, 'search' => $search,'deleted' => $deleted);
		$this->session->set_userdata("permission_template_search_data",$permission_template_search_data);
		if ($search)
		{
			$config['total_rows'] = $this->Permission_template->search_count_all($search,$deleted);
			$table_data = $this->Permission_template->search($search,$deleted,$per_page,$this->input->post('offset') ? $this->input->post('offset') : 0, $order_col ,$order_dir);
		}
		else
		{
			$config['total_rows'] = $this->Permission_template->count_all($deleted);
			$table_data = $this->Permission_template->get_all($deleted,$per_page,$this->input->post('offset') ? $this->input->post('offset') : 0, $order_col ,$order_dir);
		}
		$config['base_url'] = site_url('permission_templates/sorting');
		$config['per_page'] = $per_page; 
		$this->load->library('pagination');$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		$data['manage_table']=get_permission_template_manage_table_data_rows($table_data,$this);
		echo json_encode(array('manage_table' => $data['manage_table'], 'pagination' => $data['pagination'],'total_rows' => $config['total_rows']));
	}
	
	function clear_state()
	{
		$params = $this->session->userdata('permission_template_search_data');
		$this->session->set_userdata('permission_template_search_data', array('offset' => 0, 'order_col' => 'name', 'order_dir' => 'asc', 'search' => FALSE, 'deleted' => $params['deleted']));
		redirect('permission_templates');
	}

	function check_duplicate()
	{
		echo json_encode(array('duplicate' => $this->Permission_template->check_duplicate($this->input->post('term'))));
		exit;
	}
	
	/*
	Returns template table data rows. This will be called with AJAX.
	*/

	function search()
	{
		$this->check_action_permission('search');
		$params = $this->session->userdata('permission_template_search_data');
		
		$search=$this->input->post('search');
		$offset = $this->input->post('offset') ? $this->input->post('offset') : 0;
		$order_col = $this->input->post('order_col') ? $this->input->post('order_col') : 'name';
		$order_dir = $this->input->post('order_dir') ? $this->input->post('order_dir'): 'asc';
		$deleted = isset($params['deleted']) ? $params['deleted'] : 0;
		
		$permission_template_search_data = array('offset' => $offset, 'order_col' => $order_col, 'order_dir' => $order_dir, 'search' => $search,'deleted' => $deleted);
		$this->session->set_userdata("permission_template_search_data",$permission_template_search_data);
		$per_page=$this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20;
		$search_data = $this->Permission_template->search($search,$deleted,$per_page,$this->input->post('offset') ? $this->input->post('offset') : 0, $this->input->post('order_col') ? $this->input->post('order_col') : 'name' ,$this->input->post('order_dir') ? $this->input->post('order_dir'): 'asc');
		$config['base_url'] = site_url('permission_templates/search');
		$config['total_rows'] = $this->Permission_template->search_count_all($search,$deleted);
		$config['per_page'] = $per_page ;
		
		$this->load->library('pagination');$this->pagination->initialize($config);				
		$data['pagination'] = $this->pagination->create_links();
		$data['manage_table']=get_permission_template_manage_table_data_rows($search_data,$this);
		echo json_encode(array('manage_table' => $data['manage_table'], 'pagination' => $data['pagination'],'total_rows' => $config['total_rows']));
		
	}
	
	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		//allow parallel searchs to improve performance.
		session_write_close();
		$params = $this->session->userdata('permission_template_search_data') ? $this->session->userdata('permission_template_search_data') : array('deleted' => 0);
		
		$suggestions = $this->Permission_template->get_search_suggestions($this->input->get('term'),$params['deleted'],100);
		echo json_encode(H($suggestions));
	}
	
	function _get_template_data($template_id)
	{
		$data = array();
		
		$data['template_info']=$this->Permission_template->get_info($template_id);
		$data['all_modules']=$this->Module->get_all_modules();
		$data['controller_name'] = strtolower(get_class());

		$locations_list = $this->Location->get_all()->result();
		
		$locations = array();
		foreach($locations_list as $row)
		{
			$locations[$row->location_id] = array('name' => $row->name);
		}
		
		$data['locations'] = $locations;
		
		return $data;
	}
	
	/*
	Loads the template edit form
	*/
	function view($permission_template_id=-1,$redirect_code=0)
	{
 	 	$this->load->model('Appfile');
		
		$this->load->model('Module_action');
		$this->check_action_permission('add_update');
		$data = $this->_get_template_data($permission_template_id);
		$data['redirect_code']=$redirect_code;
		$data['action_locations'] = $this->Permission_template->get_action_wise_template_location($permission_template_id);
		$data['template_id'] = $permission_template_id;
		$this->load->view("permission_templates/form",$data);
	}
	
	/*
	Inserts/updates an template
	*/
	function save($permission_template_id=-1)
	{
		$this->check_action_permission('add_update');
		
		//Catch an error if our first name is NOT set. This can happen if logo uploaded is larger than post size
		if ($this->input->post('permission_template_name') === NULL)
		{
			echo json_encode(array('success'=>false, 'message'=>lang('permission_template_error_adding_updating'), 'template_id' => -1));
			exit;
		}
		
		$template_data = array(
			'name' => $this->input->post('permission_template_name')
		);

		$permission_data = $this->input->post("permissions") != false ? $this->input->post("permissions"): array();
		$permission_action_data = $this->input->post("permissions_actions")!=false ? $this->input->post("permissions_actions"): array();

		
		$action_location = $this->input->post("action-location") != false ? $this->input->post("action-location") : array();
		$module_location = $this->input->post("module_location") != false ? $this->input->post("module_location") : array();
		
		$this->load->helper('directory');
		$update_employee_permission = false;
		$update_all_employees_with_template_assinged = $this->input->post("update_all_employees_with_template_assinged");
		
		if( isset($update_all_employees_with_template_assinged) && $this->input->post("update_all_employees_with_template_assinged") == 1){
			$update_employee_permission = true;
		}

		if($this->Permission_template->save($template_data, $permission_data, $permission_action_data, $permission_template_id, $action_location, $module_location, $update_employee_permission))
		{
			$success_message = '';
			
			//New template
			if($permission_template_id==-1)
			{
				$success_message = H(lang('permission_template_successful_adding'));
				echo json_encode(array('success'=>true,'message'=>$success_message));
				exit;
			}
			else //previous template
			{
				$success_message = H(lang('permission_template_successful_updating'));
				$this->session->set_flashdata('manage_success_message', $success_message);
				echo json_encode(array('success'=>true, 'message' => $success_message));
				exit;
			}
			
		}
		else//failure
		{	
			echo json_encode(array('success'=>false,'message'=>lang('permission_template_error_adding_updating')));
			exit;
		}
	}

	/*
	This deletes templates from the templates table
	*/
	function delete()
	{
		$this->check_action_permission('delete');
		$templates_to_delete=$this->input->post('ids');
		
		if($this->Permission_template->delete_list($templates_to_delete))
		{
			echo json_encode(array('success'=>true,'message'=>lang('templates_successful_deleted').' '.
			count($templates_to_delete).' '.lang('templates_one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('templates_cannot_be_deleted')));
		}
	}
	
	/*
	This undeletes templates from the templates table
	*/
	function undelete()
	{
		$this->check_action_permission('delete');
		$templates_to_undelete=$this->input->post('ids');
		
		if($this->Permission_template->undelete_list($templates_to_undelete))
		{
			echo json_encode(array('success'=>true,'message'=>lang('templates_successful_undeleted')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('templates_cannot_be_undeleted')));
		}
	}
	
	function reload_table()
	{
		$params = $this->session->userdata('permission_template_search_data') ? $this->session->userdata('permission_template_search_data') : array('offset' => 0, 'order_col' => 'name', 'order_dir' => 'asc', 'search' => FALSE,'deleted' => 0);
		$config['base_url'] = site_url('permission_templates/sorting');
		$config['per_page'] = $this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20; 
		
		$data['controller_name'] = strtolower(get_class());
		$data['per_page'] = $config['per_page'];
		$data['search'] = $params['search'] ? $params['search'] : "";
		if ($data['search'])
		{
			$config['total_rows'] = $this->Permission_template->search_count_all($data['search'],$params['deleted']);
			$table_data = $this->Permission_template->search($data['search'],$params['deleted'],$data['per_page'],$params['offset'],$params['order_col'],$params['order_dir']);
		}
		else
		{
			$config['total_rows'] = $this->Permission_template->count_all($params['deleted']);
			$table_data = $this->Permission_template->get_all($params['deleted'],$data['per_page'],$params['offset'],$params['order_col'],$params['order_dir']);
		}
		$this->load->library('pagination');
		$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		$data['order_col'] = $params['order_col'];
		$data['order_dir'] = $params['order_dir'];
		
		echo get_permission_template_manage_table($table_data,$this);
	}
	
	function toggle_show_deleted($deleted=0)
	{
		$this->check_action_permission('search');
		
		$params = $this->session->userdata('permission_template_search_data') ? $this->session->userdata('permission_template_search_data') : array('offset' => 0, 'order_col' => 'name', 'order_dir' => 'asc', 'search' => FALSE, 'deleted' => 0);
		$params['deleted'] = $deleted;
		$params['offset'] = 0;
		
		$this->session->set_userdata("permission_template_search_data",$params);
	}




}
