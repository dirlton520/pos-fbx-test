<?php

require_once ("Secure_area.php");
require_once ("interfaces/Idata_controller.php");

class Expenses extends Secure_area implements Idata_controller {

    function __construct() {

        parent::__construct('expenses');
		  $this->load->model('Expense');
		  $this->load->model('Expense_category');
  			$this->lang->load('expenses');
  			$this->lang->load('module');		
		  
    }

    function index($offset = 0) {
        $params = $this->session->userdata('expenses_search_data') ? $this->session->userdata('expenses_search_data') : array('offset' => 0, 'order_col' => 'id', 'order_dir' => 'desc', 'search' => FALSE,'deleted' => 0);

        if ($offset != $params['offset']) {
            redirect('expenses/index/' . $params['offset']);
        }

        $this->check_action_permission('search');
        $config['base_url'] = site_url('expenses/sorting');
        $config['total_rows'] = $this->Expense->count_all();
        $config['per_page'] = $this->config->item('number_of_items_per_page') ? (int) $this->config->item('number_of_items_per_page') : 20;
        $data['controller_name'] = strtolower(get_class());
        $data['per_page'] = $config['per_page'];
				$data['deleted'] = $params['deleted'];
        $data['search'] = $params['search'] ? $params['search'] : "";
        if ($data['search']) {
            $config['total_rows'] = $this->Expense->search_count_all($data['search'],$params['deleted']);
            $table_data = $this->Expense->search($data['search'],$params['deleted'], $data['per_page'], $params['offset'], $params['order_col'], $params['order_dir']);
        } else {
            $config['total_rows'] = $this->Expense->count_all($params['deleted']);
            $table_data = $this->Expense->get_all($params['deleted'],$data['per_page'], $params['offset'], $params['order_col'], $params['order_dir']);
        }
        $this->load->library('pagination');$this->pagination->initialize($config);
        $data['pagination'] = $this->pagination->create_links();
        $data['order_col'] = $params['order_col'];
        $data['order_dir'] = $params['order_dir'];
        $data['total_rows'] = $config['total_rows'];
        $data['manage_table'] = get_expenses_manage_table($table_data, $this);
        $this->load->view('expenses/manage', $data);
    }

    function sorting() {
        $this->check_action_permission('search');
				$params = $this->session->userdata('expenses_search_data');

        $search = $this->input->post('search') ? $this->input->post('search') : "";
        $per_page = $this->config->item('number_of_items_per_page') ? (int) $this->config->item('number_of_items_per_page') : 20;

        $offset = $this->input->post('offset') ? $this->input->post('offset') : 0;
        $order_col = $this->input->post('order_col') ? $this->input->post('order_col') : 'id';
        $order_dir = $this->input->post('order_dir') ? $this->input->post('order_dir') : 'asc';
				$deleted = $this->input->post('deleted') ? $this->input->post('deleted') : $params['deleted'];

        $expenses_search_data = array('offset' => $offset, 'order_col' => $order_col, 'order_dir' => $order_dir, 'search' => $search,'deleted' => $deleted);
        $this->session->set_userdata("expenses_search_data", $expenses_search_data);

        if ($search) {
            $config['total_rows'] = $this->Expense->search_count_all($search,$deleted);
            $table_data = $this->Expense->search($search, $deleted,$per_page, $this->input->post('offset') ? $this->input->post('offset') : 0, $this->input->post('order_col') ? $this->input->post('order_col') : 'id', $this->input->post('order_dir') ? $this->input->post('order_dir') : 'asc');
        } else {
            $config['total_rows'] = $this->Expense->count_all($deleted);
            $table_data = $this->Expense->get_all($deleted,$per_page, $this->input->post('offset') ? $this->input->post('offset') : 0, $this->input->post('order_col') ? $this->input->post('order_col') : 'id', $this->input->post('order_dir') ? $this->input->post('order_dir') : 'asc');
        }
        $config['base_url'] = site_url('expenses/sorting');
        $config['per_page'] = $per_page;
        $this->load->library('pagination');$this->pagination->initialize($config);
        $data['pagination'] = $this->pagination->create_links();
        $data['manage_table'] = get_expenses_manage_table_data_rows($table_data, $this);
        echo json_encode(array('manage_table' => $data['manage_table'], 'pagination' => $data['pagination'],'total_rows' => $config['total_rows']));
    }

    function search() {
			
				$params = $this->session->userdata('expenses_search_data');
        $this->check_action_permission('search');

        $search = $this->input->post('search');
        $offset = $this->input->post('offset') ? $this->input->post('offset') : 0;
        $order_col = $this->input->post('order_col') ? $this->input->post('order_col') : 'id';
        $order_dir = $this->input->post('order_dir') ? $this->input->post('order_dir') : 'asc';
				$deleted = isset($params['deleted']) ? $params['deleted'] : 0;

        $expenses_search_data = array('offset' => $offset, 'order_col' => $order_col, 'order_dir' => $order_dir, 'search' => $search,'deleted' => $deleted);
        $this->session->set_userdata("expenses_search_data", $expenses_search_data);
        $per_page = $this->config->item('number_of_items_per_page') ? (int) $this->config->item('number_of_items_per_page') : 20;
        $search_data = $this->Expense->search($search, $deleted,$per_page, $this->input->post('offset') ? $this->input->post('offset') : 0, $this->input->post('order_col') ? $this->input->post('order_col') : 'id', $this->input->post('order_dir') ? $this->input->post('order_dir') : 'asc');
        $config['base_url'] = site_url('expenses/search');
        $config['total_rows'] = $this->Expense->search_count_all($search,$deleted);
        $config['per_page'] = $per_page;
        $this->load->library('pagination');$this->pagination->initialize($config);
        $data['pagination'] = $this->pagination->create_links();
        $data['manage_table'] = get_expenses_manage_table_data_rows($search_data, $this);
        echo json_encode(array('manage_table' => $data['manage_table'], 'pagination' => $data['pagination'],'total_rows' => $config['total_rows']));
    }

    function clear_state() {
			$params = $this->session->userdata('expenses_search_data');
			$this->session->set_userdata('expenses_search_data',array('offset' => 0, 'order_col' => 'id', 'order_dir' => 'desc', 'search' => FALSE,'deleted' => $params['deleted']));
			redirect('expenses');
    }

    /*
      Gives search suggestions based on what is being searched for
     */

    function suggest() {
			//allow parallel searchs to improve performance.
			session_write_close();
			$params = $this->session->userdata('expenses_search_data') ? $this->session->userdata('expenses_search_data') : array('deleted' => 0);
			$suggestions = $this->Expense->get_search_suggestions($this->input->get('term'),$params['deleted'], 100);
			echo json_encode(H($suggestions));
    }

    function view($expense_id = -1, $redirect_code = 0) {
        $this->check_action_permission('add_update');
        $logged_employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
        $data['expense_info'] = $this->Expense->get_info($expense_id);
        $data['logged_in_employee_id'] = $logged_employee_id;
        $data['all_modules'] = $this->Module->get_all_modules();
        $data['controller_name'] = strtolower(get_class());

        $data['redirect_code'] = $redirect_code;
        $data['categories'][''] = lang('common_select_category');
        $data['files'] = $this->Expense->get_files($expense_id)->result();

        if ($this->config->item('track_payment_types'))
        {
            $data['registers'] = array();
            $data['registers'][''] = lang('common_none');
			  
            foreach($this->Register->get_all_open()->result() as $register)
            {
                $data['registers'][$register->register_id] = $register->name;
            }
        }
		
        $categories = $this->Expense_category->sort_categories_and_sub_categories($this->Expense_category->get_all_categories_and_sub_categories());
        foreach($categories as $key=>$value)
        {
            $name = $this->config->item('show_full_category_path') ? str_repeat('&nbsp;&nbsp;', $value['depth']).$this->Expense_category->get_full_path($key) : str_repeat('&nbsp;&nbsp;', $value['depth']).$value['name'];
            $data['categories'][$key] = $name;
        }
				
        $employees = array();
			
        foreach($this->Employee->get_all()->result() as $employee)
        {
            $employees[$employee->person_id] = $employee->first_name .' '.$employee->last_name;
        }
        
        $data['employees'] = $employees;
        
        $this->load->model('Sale');
        $payment_types = array_keys($this->Sale->get_payment_options_with_language_keys());
        
        foreach($payment_types as $payment_type)
        {
            $data['payment_types'][$payment_type] = $payment_type;
        }
			
        $this->load->view("expenses/form", $data);
    }

    function save($id = -1) 
    {
        $this->check_action_permission('add_update');		 

        if (!$this->Expense_category->exists($this->input->post('category_id')))
        {
            if (!$category_id = $this->Expense_category->get_category_id($this->input->post('category_id')))
            {
                $category_id = $this->Expense_category->save($this->input->post('category_id'));
            }
        }	
        else
        {
            $category_id = $this->input->post('category_id');
        }
		  
        $expense_data = array(
            'expense_type' => $this->input->post('expenses_type'),
            'expense_payment_type' => $this->input->post('expense_payment_type'),
            'expense_description' => $this->input->post('expenses_description'),
            'expense_reason' => $this->input->post('expense_reason'),
            'expense_date' => date('Y-m-d',  strtotime($this->input->post('expenses_date'))),
            'expense_amount' => $this->input->post('expenses_amount'),
            'expense_tax' => $this->input->post('expenses_tax'),
            'expense_note' => $this->input->post('expenses_note'),
            'employee_id' => $this->input->post('employee_id'),
            'approved_employee_id' => $this->input->post('approved_employee_id') ? $this->input->post('approved_employee_id') : NULL,
            'category_id' => $category_id,
            'location_id' => $this->Employee->get_logged_in_employee_current_location_id(),
        );

        if ($this->Expense->save($expense_data, $id)) 
        {
            if ($this->input->post('cash_register_id'))
            {
                $cash_register = $this->Register->get_register_log_by_id($this->input->post('cash_register_id'));
                $register_log_id = $cash_register->register_log_id;
                $amount = to_currency_no_money($this->input->post('expenses_amount') + $this->input->post('expenses_tax'));
                $this->Register->add_expense_amount_to_register_log($register_log_id,'common_cash',$amount);
                $employee_id_audit = $this->Employee->get_logged_in_employee_info()->person_id;
            
                $register_audit_log_data = array(
                    'register_log_id'=> $cash_register->register_log_id,
                    'employee_id'=> $employee_id_audit,
                        'payment_type'=> 'common_cash',
                    'date' => date('Y-m-d H:i:s'),
                    'amount' => -$amount,
                    'note' => lang('common_expenses'). ' - '.$this->input->post('expenses_note'),
                );
        
                $this->Register->insert_audit_log($register_audit_log_data);
			}

            if($id==-1)
            {
                $expense_info = $this->Expense->get_info($expense_data['id']);
            }
            else
            {
                $expense_info = $this->Expense->get_info($id);
            }
			
			//Delete Image
			if($this->input->post('del_image') && $id != -1)
			{
			    if($expense_info->expense_image_id != null)
			    {
					$this->Expense->update_image(NULL,$id);
					$this->load->model('Appfile');
					$this->Appfile->delete($expense_info->expense_image_id);
			    }
			}

			//Save Image File
			if(!empty($_FILES["expense_image_id"]) && $_FILES["expense_image_id"]["error"] == UPLOAD_ERR_OK)
			{			    
			    $allowed_extensions = array('png', 'jpg', 'jpeg', 'gif');
				$extension = strtolower(pathinfo($_FILES["expense_image_id"]["name"], PATHINFO_EXTENSION));

			    if (in_array($extension, $allowed_extensions))
			    {
				    $config['image_library'] = 'gd2';
				    $config['source_image']	= $_FILES["expense_image_id"]["tmp_name"];
				    $config['create_thumb'] = FALSE;
				    $config['maintain_ratio'] = TRUE;
				    $config['width']	 = 1200;
				    $config['height']	= 900;
				    $this->load->library('image_lib', $config); 
				    $this->image_lib->resize();
					 $this->load->model('Appfile');
				    $image_file_id = $this->Appfile->save($_FILES["expense_image_id"]["name"], file_get_contents($_FILES["expense_image_id"]["tmp_name"]), NULL, $expense_info->expense_image_id);
			    }

				if($id==-1)
				{
	    			$this->Expense->update_image($image_file_id,$expense_data['id']);
				}
				else
				{
					$this->Expense->update_image($image_file_id,$id);
				}
			}
			
			if (isset($_FILES['files']))
			{	$this->load->model('Appfile');
				for($k=0; $k<count($_FILES['files']['name']); $k++)
				{
		   	 		if($_FILES['files']['tmp_name'][$k])
						{
					$file_id = $this->Appfile->save($_FILES['files']['name'][$k], file_get_contents($_FILES['files']['tmp_name'][$k]));
					$this->Expense->add_file($id==-1 ? $expense_data['id'] : $id, $file_id);
					}
				}
			}
            
            $redirect = $this->input->post('redirect');
			
            $success_message = '';
            //New item
            if ($id == -1) 
            {
                $success_message = H(lang('expenses_successful_adding').' '.$expense_data['expense_type'].' - '.to_currency($this->input->post('expenses_amount')));
                echo json_encode(array('success' => true, 'message' => $success_message, 'id' => $expense_data['id'], 'redirect' => $redirect));
            } else { //previous item
                $success_message = H(lang('common_items_successful_updating') . ' ' . $expense_data['expense_type'].' - '.to_currency($this->input->post('expenses_amount')));
                $this->session->set_flashdata('manage_success_message', $success_message);
                echo json_encode(array('success' => true, 'message' => $success_message, 'id' => $id, 'redirect' => $redirect));
            }
        } 
        else 
        {//failure
            echo json_encode(array('success' => false, 'message' => lang('expenses_error_adding_updating')));
        }
    }

    function delete() {
        $this->check_action_permission('delete');
        $expenses_to_delete = $this->input->post('ids');
        if ($this->Expense->delete_list($expenses_to_delete)) {

            echo json_encode(array('success' => true, 'message' => lang('expenses_successful_deleted') . ' ' . lang('expenses_one_or_multiple')));
        } else {
            echo json_encode(array('success' => false, 'message' => lang('expenses_cannot_be_deleted')));
        }
    }
    function undelete() {
        $this->check_action_permission('delete');
        $expenses_to_delete = $this->input->post('ids');
        if ($this->Expense->undelete_list($expenses_to_delete)) {

            echo json_encode(array('success' => true, 'message' => lang('expenses_successful_undeleted') . ' ' . lang('expenses_one_or_multiple')));
        } else {
            echo json_encode(array('success' => false, 'message' => lang('expenses_cannot_be_undeleted')));
        }
    }
		
		
		function toggle_show_deleted($deleted=0)
		{
			$this->check_action_permission('search');
		
			$params = $this->session->userdata('expenses_search_data') ? $this->session->userdata('expenses_search_data') : array('offset' => 0, 'order_col' => 'id', 'order_dir' => 'desc', 'search' => FALSE,'deleted' => 0);
			$params['deleted'] = $deleted;
			$params['offset'] = 0;
			
			$this->session->set_userdata("expenses_search_data",$params);
        }
        
        function manage_categories()
        {
            $this->check_action_permission('manage_categories');
            $categories = $this->Expense_category->get_all_categories_and_sub_categories_as_tree();
            $data = array('category_tree' => $this->_category_tree_list($categories));
            $data['categories']['0'] = lang('common_none');
            $categories = $this->Expense_category->sort_categories_and_sub_categories($this->Expense_category->get_all_categories_and_sub_categories());
            foreach($categories as $key=>$value)
            {
                $name = $this->config->item('show_full_category_path') ? str_repeat('&nbsp;&nbsp;', $value['depth']).$this->Expense_category->get_full_path($key) : str_repeat('&nbsp;&nbsp;', $value['depth']).$value['name'];
                $data['categories'][$key] = $name;
            }
            
            $data['redirect'] = $this->input->get("redirect");
            
            $this->load->view('expenses/categories',$data);		
        }

        function _category_tree_list($tree) 
        {
            $return = '';
            if(!is_null($tree) && count($tree) > 0) 
            {
                $return = '<ul>';
                foreach($tree as $node) 
                        {
                    $return .='<li>'.H($node->name). ' <a href="javascript:void(0);" class="add_child_category" data-category_id="'.$node->id.'">['.lang('items_add_child_category').']</a> '.
                                '<a href="javascript:void(0);" class="edit_category" data-name = "'.H($node->name).'" data-parent_id = "'.$node->parent_id.'" data-category_id="'.$node->id.'">['.lang('common_edit').']</a> '.
                                    '<a href="javascript:void(0);" class="delete_category" data-category_id="'.$node->id.'">['.lang('common_delete').']</a>';
                                    $return .= $this->_category_tree_list($node->children);
                    $return .='</li>';
                }
                $return .='</ul>';
            }

            return $return;
        }

        function save_category($category_id = FALSE)
        {	
            $this->check_action_permission('manage_categories');
            
            $update = $category_id ? true : false;
            
            $parent_id = $this->input->post('parent_id');
            $category_name = $this->input->post('category_name');
    
            if (!$parent_id)
            {
                $parent_id = NULL;
            }
            
            if ($category_id = $this->Expense_category->save($category_name, $parent_id, $category_id))
            {
                $categories_data = $this->Expense_category->sort_categories_and_sub_categories($this->Expense_category->get_all_categories_and_sub_categories());
                $categories = array();
                foreach($categories_data as $key=>$value)
                {
                    $name = $this->config->item('show_full_category_path') ? str_repeat('&nbsp;&nbsp;', $value['depth']).$this->Expense_category->get_full_path($key) : str_repeat('&nbsp;&nbsp;', $value['depth']).$value['name'];
                    $categories[] = array('value'=> H($key),'text'=> H($name));
                }
                            
                echo json_encode(array('success'=>true,'message'=>lang('items_category_successful_adding').' '.H($category_name), 'categories' => $categories, 'selected' => $category_id));
            }
            else
            {
                echo json_encode(array('success'=>false,'message'=>lang('items_category_successful_error')));
            }
        }

        function get_category_tree_list()
        {
            $categories = $this->Expense_category->get_all_categories_and_sub_categories_as_tree();
            echo $this->_category_tree_list($categories);
        }

        function delete_category()
        {
            $this->check_action_permission('manage_categories');		
            $category_id = $this->input->post('category_id');
            if($this->Expense_category->delete($category_id))
            {
                echo json_encode(array('success'=>true,'message'=>lang('items_successful_deleted')));
            }
            else
            {
                echo json_encode(array('success'=>false,'message'=>lang('items_cannot_be_deleted')));
            }
        }

    function download($file_id)
    {
        //Don't allow images to cause hangups with session
        session_write_close();
        $this->load->model('Appfile');
        $file = $this->Appfile->get($file_id);
        $this->load->helper('file');
        $this->load->helper('download');
        force_download($file->file_name,$file->file_data);
    }
    
    function delete_file($file_id){
        $this->check_action_permission('add_update');
        $this->Expense->delete_file($file_id);
    }
}

?>
