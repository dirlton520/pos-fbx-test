<?php
require_once ("Secure_area.php");
require_once ("interfaces/Idata_controller.php");

class Price_rules extends Secure_area implements Idata_controller
{
	function __construct()
	{
		parent::__construct('price_rules');	
		$this->lang->load('price_rules');
		$this->load->model('Price_rule');
		$this->load->model('Tier');
		$this->load->helper('price_rule');
		
		$this->load->model('Item');
		$this->load->model('Category');
		$this->load->model('Tag');
		$this->load->model('Item_kit');
		$this->lang->load('module');	
		
		$this->lang->load('item_kits');
		$this->lang->load('messages');
	}

	function index($offset=0)
	{
		$params = $this->session->userdata('price_rules_search_data') ? $this->session->userdata('price_rules_search_data') : array('offset' => 0, 'order_col' => 'name', 'order_dir' => 'asc', 'search' => FALSE,'deleted' => 0);
		
		if ($offset!=$params['offset'])
		{
		   redirect('price_rules/index/'.$params['offset']);
		}
		
		$this->check_action_permission('search');
		$config['base_url'] = site_url('price_rules/sorting');
		$config['per_page'] = $this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20; 
		
		$data['controller_name']=strtolower(get_class());
		$data['per_page'] = $config['per_page'];
		$data['search'] = $params['search'] ? $params['search'] : "";
		$data['deleted'] = $params['deleted'];
		if ($data['search'])
		{
			$config['total_rows'] = $this->Price_rule->search_count_all($data['search'],$params['deleted']);
			$table_data = $this->Price_rule->search($data['search'],$params['deleted'],$data['per_page'],$params['offset'],$params['order_col'],$params['order_dir']);
		}
		else
		{	
			$config['total_rows'] =$trows= $this->Price_rule->count_all($params['deleted']);
			$table_data = $this->Price_rule->get_all($params['deleted'],$data['per_page'],$params['offset'],$params['order_col'],$params['order_dir']);
		}
		
		$data['total_rows'] = $config['total_rows'];
		$this->load->library('pagination');$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		$data['order_col'] = $params['order_col'];
		$data['order_dir'] = $params['order_dir'];
		$data['manage_table']=$dataset=get_price_rules_manage_table($table_data,$this);

		$this->load->view('price_rules/manage',$data);
	}
	
	function clear_state()
	{
		$params = $this->session->userdata('price_rules_search_data');
		$this->session->set_userdata('price_rules_search_data', array('offset' => 0, 'order_col' => 'name', 'order_dir' => 'asc', 'search' => FALSE,'deleted' => $params['deleted']));
		redirect('price_rules');
	}
	
	
	function search()
	{
		$this->check_action_permission('search');
		$params = $this->session->userdata('price_rules_search_data');
		
		$search=$this->input->post('search');
		$offset = $this->input->post('offset') ? $this->input->post('offset') : 0;
		$order_col = $this->input->post('order_col') ? $this->input->post('order_col') : 'name';
		$order_dir = $this->input->post('order_dir') ? $this->input->post('order_dir'): 'asc';
		$deleted = isset($params['deleted']) ? $params['deleted'] : 0;

		$price_rules_search_data = array('offset' => $offset, 'order_col' => $order_col, 'order_dir' => $order_dir, 'search' => $search,'deleted' => $deleted);
		$this->session->set_userdata("price_rules_search_data",$price_rules_search_data);
		$per_page=$this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20;
		$search_data=$this->Price_rule->search($search,$deleted,$per_page,$this->input->post('offset') ? $this->input->post('offset') : 0, $this->input->post('order_col') ? $this->input->post('order_col') : 'name' ,$this->input->post('order_dir') ? $this->input->post('order_dir'): 'asc');
		$config['base_url'] = site_url('price_rules/search');
		$config['total_rows'] = $this->Price_rule->search_count_all($search,$deleted);
		$config['per_page'] = $per_page ;
		
		$this->load->library('pagination');$this->pagination->initialize($config);				
		$data['pagination'] = $this->pagination->create_links();
		$data['manage_table']=get_price_rules_manage_table_data_rows($search_data,$this);
		echo json_encode(array('manage_table' => $data['manage_table'], 'pagination' => $data['pagination'],'total_rows' => $config['total_rows']));
		
	}
	
	function sorting()
	{
		$this->check_action_permission('search');
		$params = $this->session->userdata('price_rules_search_data');
		
		$search=$this->input->post('search') ? $this->input->post('search') : "";
		$per_page=$this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20;
		$offset = $this->input->post('offset') ? $this->input->post('offset') : 0;
		$order_col = $this->input->post('order_col') ? $this->input->post('order_col') : 'name';
		$order_dir = $this->input->post('order_dir') ? $this->input->post('order_dir'): 'asc';
		$deleted = $this->input->post('deleted') ? $this->input->post('deleted') : $params['deleted'];
		

		$price_rules_search_data = array('offset' => $offset, 'order_col' => $order_col, 'order_dir' => $order_dir, 'search' => $search,'deleted' => $deleted);
		$this->session->set_userdata("price_rules_search_data",$price_rules_search_data);
		if ($search)
		{
			$config['total_rows'] = $this->Price_rule->search_count_all($search,$deleted);
			$table_data = $this->Price_rule->search($search,$deleted,$per_page,$this->input->post('offset') ? $this->input->post('offset') : 0, $this->input->post('order_col') ? $this->input->post('order_col') : 'name' ,$this->input->post('order_dir') ? $this->input->post('order_dir'): 'asc');
		}
		else
		{
			$config['total_rows'] = $this->Price_rule->count_all($deleted);
			$table_data = $this->Price_rule->get_all($deleted,$per_page,$this->input->post('offset') ? $this->input->post('offset') : 0, $this->input->post('order_col') ? $this->input->post('order_col') : 'name' ,$this->input->post('order_dir') ? $this->input->post('order_dir'): 'asc');
		}
		$config['base_url'] = site_url('price_rules/sorting');
		$config['per_page'] = $per_page; 
		$this->load->library('pagination');$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		$data['manage_table']=get_price_rules_manage_table_data_rows($table_data,$this);
		echo json_encode(array('manage_table' => $data['manage_table'], 'pagination' => $data['pagination'],'total_rows' => $config['total_rows']));
	}	

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		//allow parallel searchs to improve performance.
		session_write_close();
		$params = $this->session->userdata('price_rules_search_data') ? $this->session->userdata('price_rules_search_data') : array('deleted' => 0);
		$suggestions = $this->Price_rule->get_search_suggestions($this->input->get('term'),$params['deleted'],100);
		echo json_encode(H($suggestions));
	}
	
	/*
	Loads the price rule edit form
	*/
	function view($rule_id=-1,$redirect_code=0)
	{
		$this->load->model('Module_action');
		$this->check_action_permission('add_update');
		$data = $this->_get_pricerule_data($rule_id);
		$data['redirect']= $redirect_code;
		$data['rule_id'] = $rule_id;

		$data['redirect_code']=$redirect_code;

		$this->load->view("price_rules/price_rule_form",$data);
	}
	
	function search_term()
	{
		if ($this->input->get('act') == 'autocomplete') { // Must return a json string
			if ($this->input->get('w') != '') { // From where should we return data
				if ($this->input->get('term') != '') { // What exactly are we searchin
					
					//allow parallel searchs to improve performance.
					session_write_close();
					
					switch($this->input->get('w')) {
						
						case 'itemsCategory':
							$this->load->model('Category');
							$t = $this->Category->get_search_suggestions($this->input->get('term'));
							$tmp = array();
							$this->load->model('Category');
							foreach ($t as $k=>$v) { $tmp[$k] = array('id'=>$v['id'], 'name'=>$this->Category->get_full_path($v['id'])); }
							die(json_encode(H($tmp)));
						break;
						case 'itemsTag':
							$this->load->model('Tag');
							$t = $this->Tag->get_tag_suggestions($this->input->get('term'));
							$tmp = array();
							foreach ($t as $k=>$v) { $tmp[$k] = array('id'=>$v['id'], 'name'=>$v['label']); }
							die(json_encode(H($tmp)));
						break;
						
						case 'itemsKitName':
							$this->load->model('Item_kit');
							$t = $this->Item_kit->search($this->input->get('term'), 0,FALSE, 100, 0, 'name', 'asc')->result_object();
							$tmp = array();
							foreach ($t as $k=>$v) { $tmp[$k] = array('id'=>$v->item_kit_id, 'name'=>$v->name." / #".$v->item_kit_number); }
							die(json_encode(H($tmp)));
						break;
						case 'itemsName':
							$this->load->model('Item');
							$t = $this->Item->get_item_search_suggestions_without_variations($this->input->get('term'));
							$tmp = array();
							foreach ($t as $v) { $tmp[] = array('id'=>$v['value'], 'name'=>$v['label']); }
							die(json_encode(H($tmp)));
						break;
						case 'itemsManufacturers':
						$this->load->model('Manufacturer');
						$t = $this->Manufacturer->get_manufacturer_suggestions($this->input->get('term'));
						$tmp = array();
						foreach ($t as $v) { $tmp[] = array('id'=>$v['value'], 'name'=>$v['label']); }
						die(json_encode(H($tmp)));
					break;
								
					}
				} else {
					die;	
				}
			} else {
				die(json_encode(array('value' => 'No such data found!')));
			}
		}	
	}
	
	function save($rule_id=-1)
	{
		$this->check_action_permission('add_update');
		
		$rule_data = array(
		'name' => $this->input->post('name'),
		'start_date' => $this->input->post('start_date') ? date('Y-m-d 00:00:00', strtotime($this->input->post('start_date'))) : NULL,
		'end_date' => $this->input->post('end_date') ?date('Y-m-d 23:59:59', strtotime($this->input->post('end_date'))) : NULL,
		'added_on' => date('Y-m-d h:i:s'),
		'type'=>$this->input->post('type'),
		'active'=>$this->input->post('active'),
		'items_to_buy'=>$this->input->post('items_to_buy') !== '' ? $this->input->post('items_to_buy') : NULL,
		'items_to_get'=>$this->input->post('items_get_free') !== '' ? $this->input->post('items_to_get'): NULL,
		'percent_off'=>$this->input->post('percent_off') !== '' ? $this->input->post('percent_off') : NULL,
		'fixed_off'=>$this->input->post('fixed_off') !== '' ? $this->input->post('fixed_off') : NULL,
		'spend_amount'=>$this->input->post('spend_amount') !== '' ? $this->input->post('spend_amount') : NULL,
		'num_times_to_apply'=>$this->input->post('num_times_to_apply'),
		'coupon_code' => $this->input->post('coupon_code') !== '' ? $this->input->post('coupon_code') : NULL,
		'mix_and_match'=>$this->input->post('mix_and_match') ? 1 : 0,
		'description' => $this->input->post('description'),
		'show_on_receipt' => $this->input->post('show_on_receipt') ? 1 : 0,
		'coupon_spend_amount' => $this->input->post('coupon_spend_amount') ? $this->input->post('coupon_spend_amount') : NULL,
		'disable_loyalty_for_rule' => $this->input->post('disable_loyalty_for_rule') ? 1 : 0,
		);
		
		$items_post = $this->input->post('items');
		$items = explode(',', $items_post[0]);
		
		$item_kits_post = $this->input->post('itemkits');
		$item_kits = explode(',', $item_kits_post[0]);
		
		$categories_post = $this->input->post('categories');
		$categories = explode(',', $categories_post[0]);
		
		$tags_post = $this->input->post('tags');
		$tags = explode(',', $tags_post[0]);

		
		$manus_post = $this->input->post('manufacturers');
		$manus = explode(',', $manus_post[0]);

		$locations = $this->input->post('locations');
		$excluded_tiers = $this->input->post('excluded_tiers');
		/*Custom Price Break Rule Data*/
		$qty_to_buy = $this->input->post('qty_to_buy');
		$flat_unit_discount = $this->input->post('flat_unit_discount');
		$percent_unit_discount = $this->input->post('percent_unit_discount');
		
		$price_breaks = array();
		
		foreach($qty_to_buy as $key=>$val)
		{
			if($flat_unit_discount[$key] === '')
			{
				$flat_unit_discount[$key] = NULL;
			}
			
			if($percent_unit_discount[$key] === '')
			{
				$percent_unit_discount[$key] = NULL;
			}
			
		  $price_breaks[] = array(
			  'item_qty_to_buy'=>$qty_to_buy[$key],
			  'discount_per_unit_fixed'=>$flat_unit_discount[$key],
			  'discount_per_unit_percent'=>$percent_unit_discount[$key],
			);
		}
		
		if($this->Price_rule->save_price_rule($rule_id,$rule_data, $items, $item_kits, $categories, $tags, $manus,$price_breaks,$locations,$excluded_tiers))
		{
			$success=H(lang('price_rules_success').' '.$rule_data['name']);
			$this->session->set_flashdata('success', $success);
			redirect('price_rules');
		}
		else
		{
			$error=H(lang('price_rules_error').' '.$rule_data['name']);
			$this->session->set_flashdata('error', $error);
			redirect('price_rules');
		}
	}
	
	
	function _get_pricerule_data($rule_id)
	{
		$data = $rule_items = $rule_item_kits = $rule_cats = $rule_tags = $rule_manus = $price_breaks = array();		
		
		$data['rule_info']=$rule_info=$this->Price_rule->get_rule_info($rule_id);
		$data['price_rule_excluded_tiers'] = $this->Price_rule->get_excluded_tier_ids($rule_id);
		$ruleitems=$this->Price_rule->get_rule_items($rule_id);

		foreach($ruleitems as $key=>$val)
		{	
			if($rule_info['type'] !== 'spend_x_get_discount')
			{
				$rule_items[]=array('id'=>$val['item_id'],'name'=>$val['name']);
			}
		}
		
		$rule_itemkits=$this->Price_rule->get_rule_item_kits($rule_id);
		foreach($rule_itemkits as $key=>$val)
		{
			if($rule_info['type'] !== 'spend_x_get_discount')
			{
				$rule_item_kits[]=array('id'=>$val['item_kit_id'],'name'=>$val['name']);
			}
		}
		
		$ruletags=$this->Price_rule->get_rule_tags($rule_id);
		foreach($ruletags as $key=>$val)
		{
			if($rule_info['type'] !== 'spend_x_get_discount')
			{
				$rule_tags[$key]=array('id'=>$val['tag_id'],'name'=>$val['name']);
			}
		}
		
		$rulecats=$this->Price_rule->get_rule_categories($rule_id);
		$this->load->model('Category');
		foreach($rulecats as $key=>$val)
		{
			if($rule_info['type'] !== 'spend_x_get_discount')
			{
				$rule_cats[$key]=array('id'=>$val['category_id'],'name'=>$this->Category->get_full_path($val['category_id']));	
			}
		}
		
		
		$rulemanus=$this->Price_rule->get_rule_manus($rule_id);
		foreach($rulemanus as $key=>$val)
		{
			if($rule_info['type'] !== 'spend_x_get_discount')
			{
				$rule_manus[$key]=array('id'=>$val['manufacturer_id'],'name'=>$val['manufacturer_name']);	
			}
		}
		
		
		$price_breaks = $this->Price_rule->get_price_breaks($rule_id);

		$data['rule_items']=$rule_items;
		$data['rule_item_kits']=$rule_item_kits;
		$data['rule_tags']=$rule_tags;
		$data['rule_cats']=$rule_cats;
		$data['rule_manus']=$rule_manus;
		$data['rule_price_breaks']=$price_breaks;

		$data['price_rule_locations'] = array();
		
		foreach($this->Price_rule->get_all_price_rule_locations($rule_id)->result_array() as $prl)
		{
			$data['price_rule_locations'][] = $prl['location_id'];
		}
		return $data;
		
	}	

	
	function delete()
	{
		$this->check_action_permission('delete');		
		$rule_ids = $this->input->post('ids');
		if($this->Price_rule->delete($rule_ids))
		{
			echo json_encode(array('success'=>true,'message'=>lang('price_rules_rule_deleted')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('price_rules_cannot_be_deleted')));
		}
	}
	
	function undelete()
	{
		$this->check_action_permission('delete');		
		$rule_ids = $this->input->post('ids');
		if($this->Price_rule->undelete($rule_ids))
		{
			echo json_encode(array('success'=>true,'message'=>lang('price_rules_rule_undeleted')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('price_rules_cannot_be_undeleted')));
		}
	}
		
	function get_info_item_price_break($item_id)
	{
		$match_value=$this->input->post('matchvalue');
		$data['item_info']=$this->Item->get_info($item_id);
		print_r(json_encode($data['item_info']));
	}
	
	function toggle_show_deleted($deleted=0)
	{
		$this->check_action_permission('search');
		
		$params = $this->session->userdata('price_rules_search_data') ? $this->session->userdata('price_rules_search_data') : array('offset' => 0, 'order_col' => 'name', 'order_dir' => 'asc', 'search' => FALSE,'deleted' => 0);
		$params['deleted'] = $deleted;
		$params['offset'] = 0;
		
		$this->session->set_userdata("price_rules_search_data",$params);
	}
	

}
?>