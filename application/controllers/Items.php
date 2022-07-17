<?php
require_once ("Secure_area.php");
require_once ("interfaces/Idata_controller.php");
class Items extends Secure_area implements Idata_controller
{
	private $ecom_model;
	
	function __construct()
	{
		parent::__construct('items');
		$this->load->model('Inventory');
		$this->load->model('Additional_item_numbers');
		$this->lang->load('items');
		$this->lang->load('reports');
		$this->lang->load('module');
		$this->load->model('Item');
		$this->load->model('Category');
		$this->load->model('Tag');
		$this->load->model('Appconfig');
		$this->load->model('Item_modifier');
		
		if ($this->Appconfig->get_key_directly_from_database("ecommerce_platform"))
		{
			require_once (APPPATH."models/interfaces/Ecom.php");
			$this->ecom_model = Ecom::get_ecom_model();
		}
	}

	function custom_fields()
	{
		$this->lang->load('config');
		$fields_prefs = $this->config->item('item_custom_field_prefs') ? unserialize($this->config->item('item_custom_field_prefs')) : array();
		$data = array_merge(array('controller_name' => strtolower(get_class())),$fields_prefs);
		$locations_list = $this->Location->get_all()->result();
		$data['locations'] = $locations_list;
		$this->load->view('custom_fields',$data);
	}
	
	function save_custom_fields()
	{
		$this->load->model('Appconfig');
		$this->Appconfig->save('item_custom_field_prefs',serialize($this->input->post()));
	}
	
	function index($offset=0)
	{
		$params = $this->session->userdata('items_search_data') ? $this->session->userdata('items_search_data') : array('offset' => 0, 'order_col' => 'item_id', 'order_dir' => 'desc', 'search' => FALSE, 'category_id' => FALSE, 'fields' => 'all','deleted' => 0);
		
		if ($offset!=$params['offset'])
		{
		   redirect('items/index/'.$params['offset']);
		}

		$this->check_action_permission('search');
		$config['base_url'] = site_url('items/sorting');
		$config['per_page'] = $this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20; 
		
		$data['controller_name']=strtolower(get_class());
		$data['per_page'] = $config['per_page'];
		$data['search'] = $params['search'] ? $params['search'] : "";
		$data['category_id'] = $params['category_id'] ? $params['category_id'] : "";
		$data['categories'][''] = lang('common_all');
		$data['deleted'] = $params['deleted'];
		$categories = $this->Category->sort_categories_and_sub_categories($this->Category->get_all_categories_and_sub_categories());
		
		foreach($categories as $key=>$value)
		{
			$name = $this->config->item('show_full_category_path') ? str_repeat('&nbsp;&nbsp;', $value['depth']).$this->Category->get_full_path($key) : str_repeat('&nbsp;&nbsp;', $value['depth']).$value['name'];
			$data['categories'][$key] = $name;
		}
		
		$data['fields'] = $params['fields'] ? $params['fields'] : "all";
		
		if ($data['search'] || $data['category_id'])
		{
			$table_data = $this->Item->search($data['search'],$params['deleted'],$data['category_id'],$data['per_page'],$params['offset'],$params['order_col'],$params['order_dir'], $data['fields']);
		  $config['total_rows'] = $this->Item->count_last_query_results();
		}
		else
		{
			$table_data = $this->Item->get_all($params['deleted'],$data['per_page'],$params['offset'],$params['order_col'],$params['order_dir']);
			$config['total_rows'] = $this->Item->count_last_query_results();
		}
		
		$data['total_rows'] = $config['total_rows'];
		$this->load->library('pagination');
		$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		$data['order_col'] = $params['order_col'];
		$data['order_dir'] = $params['order_dir'];		
		$data['manage_table'] = get_items_manage_table($table_data,$this);
		
		$this->load->model('Employee_appconfig');
		$data['default_columns'] = $this->Item->get_default_columns();
		$data['selected_columns'] = $this->Employee->get_item_columns_to_display();
		$data['all_columns'] = array_merge($data['selected_columns'],$this->Item->get_displayable_columns());		
		$this->load->view('items/manage',$data); 
	}
	
	function reload_table()
	{
		$config['base_url'] = site_url('items/sorting');
		$config['per_page'] = $this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20; 
		$params = $this->session->userdata('items_search_data') ? $this->session->userdata('items_search_data') : array('offset' => 0, 'order_col' => 'item_id', 'order_dir' => 'desc', 'search' => FALSE, 'category_id' => FALSE, 'fields' => 'all','deleted' => 0);

		$data['per_page'] = $config['per_page'];
		$data['search'] = $params['search'] ? $params['search'] : "";
		$data['category_id'] = $params['category_id'] ? $params['category_id'] : "";
		
		$data['fields'] = $params['fields'] ? $params['fields'] : "all";
		
		if ($data['search'] || $data['category_id'])
		{
			$table_data = $this->Item->search($data['search'],$params['deleted'],$data['category_id'],$data['per_page'],$params['offset'],$params['order_col'],$params['order_dir'], $data['fields']);
			$config['total_rows'] = $this->Item->count_last_query_results();
		}
		else
		{
			$table_data = $this->Item->get_all($params['deleted'],$data['per_page'],$params['offset'],$params['order_col'],$params['order_dir']);
			$config['total_rows'] = $this->Item->count_last_query_results();
		}
		
		echo get_items_manage_table($table_data,$this);
	}
	
	function manage_modifiers()
	{		
		$this->load->model('Item_modifier');
		$data = array('controller_name' => strtolower(get_class()));
		$data['item_modifiers'] = $this->Item_modifier->get_all();
		
		$data['redirect'] = $this->input->get("redirect");
		
		$this->load->view('items/modifiers',$data);
	}
	
	function modifier($modifier_id = NULL)
	{
		$data['modifier_info'] = $this->Item_modifier->get_info($modifier_id);
		$data['modifier_items'] = $this->Item_modifier->get_modifier_items($modifier_id)->result_array();
		$this->load->view('items/modifier_form',$data);		
	}
	
	function save_modifier($modifier_id = NULL)
	{
		$this->load->model('Item_modifier');
		$this->Item_modifier->save($modifier_id,array('name' => $this->input->post('name')), $this->input->post('modifier_items'),$this->input->post('modifier_items_to_delete'));
		redirect(site_url('items/manage_modifiers'));
	}
	
	function delete_modifier()
	{
		$this->load->model('Item_modifier');
		$this->Item_modifier->delete($this->input->post('id'));
	}
	
	function manage_attributes()
	{
		$this->load->model('Item_attribute');
		$this->load->model('Item_attribute_value');
		
		$data = array('controller_name' => strtolower(get_class()));
		$data['item_attributes'] = $this->Item_attribute->get_all_global();
		
		$data['redirect'] = $this->input->get("redirect");
		
		$this->load->view('items/item_attributes',$data);
	}
	
	function save_attributes()
	{
		$attributes_to_save = $this->input->post('attributes');
		$attributes_to_delete = $this->input->post('attributes_to_delete');
		
		$this->load->model('Item_attribute');
		$this->load->model('Item_attribute_value');
		
		if ($attributes_to_save)
		{							
			foreach($attributes_to_save as $attribute_id => $data)
			{
				$attribute_name = $data['name'];
				$attribute_values = explode("|", $data['values']);
				
				
				if ($attribute_name)
				{
					$attribute_data = array('name' => $attribute_name);
					
					$this->Item_attribute->save($attribute_data, $attribute_id < 0 ? false : $attribute_id);
					
					if ($attribute_id > 0)
					{
						$attribute_values_previous_result = $this->Item_attribute_value->get_values_for_attribute($attribute_id)->result_array();
						$attribute_values_previous = array();
						foreach($attribute_values_previous_result as $attr_val_row)
						{
							$attribute_values_previous[] = $attr_val_row['name'];
						}
						
						$attribute_values_to_delete = array_values(array_diff($attribute_values_previous,$attribute_values));
						
						foreach($attribute_values_to_delete as $attr_value_to_delete)
						{
							$this->Item_attribute_value->delete($attribute_id, $attr_value_to_delete);
						}
					}
					
					if($attribute_values)
					{
						foreach($attribute_values as $value)
						{
							//if we couldn't save the attribute and the attribute is new skip saving values
							//prevents notice when saving non-unique attributes
							if($attribute_id < 0 && !isset($attribute_data['id']))
							{
								continue;
							}
							
							$this->Item_attribute_value->save($value, $attribute_id < 0 ? $attribute_data['id'] : $attribute_id);
						}
					}
				}				
			}				
		}
		
		if ($attributes_to_delete)
		{
			foreach($attributes_to_delete as $attribute_id)
			{
				$this->Item_attribute->delete($attribute_id);
			}
		}
		
		//Ecommerce
		if (isset($this->ecom_model))
		{
			$this->ecom_model->export_phppos_attributes_to_ecommerce();
		}

		echo json_encode(array('success'=>true,'message'=>lang('common_saved_successfully')));
		
	}
	
	function manage_categories()
	{
		$this->check_action_permission('manage_categories');
		$categories = $this->Category->get_all_categories_and_sub_categories_as_tree();
		$data = array('category_tree' => $this->_category_tree_list($categories));
		$data['categories']['0'] = lang('common_none');
		$categories = $this->Category->sort_categories_and_sub_categories($this->Category->get_all_categories_and_sub_categories());
		foreach($categories as $key=>$value)
		{
			$name = $this->config->item('show_full_category_path') ? str_repeat('&nbsp;&nbsp;', $value['depth']).$this->Category->get_full_path($key) : str_repeat('&nbsp;&nbsp;', $value['depth']).$value['name'];
			$data['categories'][$key] = $name;
		}
		
		$data['redirect'] = $this->input->get("redirect");
		
		$this->load->view('items/categories',$data);		
	}
	
	function save_category($category_id = FALSE)
	{	
		$this->check_action_permission('manage_categories');
		
		$update = $category_id ? true : false;
		
		$parent_id = $this->input->post('parent_id');
		$category_name = $this->input->post('category_name');
		$category_info_popup = $this->input->post('category_info_popup');
		$category_color = $this->input->post('category_color');
		$delete_image = $this->input->post('del_image');

		if ($this->input->post('hide_from_grid') !== NULL)
		{
			$hide_from_grid = $this->input->post('hide_from_grid') ? 1 : 0;
		}
		else
		{
			$hide_from_grid = NULL;
		}
		
		if ($this->input->post('exclude_from_e_commerce') !== NULL)
		{
			$exclude_from_e_commerce = $this->input->post('exclude_from_e_commerce') ? 1 : 0;
		}
		else
		{
			$exclude_from_e_commerce = NULL;
		}
			
		//Save Image File
		$category_image_id = NULL;
		if(!empty($_FILES["category_image"]) && $_FILES["category_image"]["error"] == UPLOAD_ERR_OK)
		{			    
		  $allowed_extensions = array('png', 'jpg', 'jpeg', 'gif');
			$extension = strtolower(pathinfo($_FILES["category_image"]["name"], PATHINFO_EXTENSION));
			$category_info = $this->Category->get_info($category_id);
			
		    if (in_array($extension, $allowed_extensions))
		    {
			    $config['image_library'] = 'gd2';
			    $config['source_image']	= $_FILES["category_image"]["tmp_name"];
			    $config['create_thumb'] = FALSE;
			    $config['maintain_ratio'] = TRUE;
			    $config['width']	 = 1200;
			    $config['height']	= 900;
			    $this->load->library('image_lib', $config); 
			    $this->image_lib->resize();
		   	 	$this->load->model('Appfile');
		
			    $category_image_id = $this->Appfile->save($_FILES["category_image"]["name"], file_get_contents($_FILES["category_image"]["tmp_name"]), NULL, $category_info->image_id);
		    }
		} 
		elseif($delete_image && $category_id !== FALSE)
		{
			$this->Category->delete_category_image($category_id);
		}
		
		if (!$parent_id)
		{
			$parent_id = NULL;
		}
		
		if ($category_id = $this->Category->save($category_name, $hide_from_grid, $parent_id, $category_id, $category_color, $category_image_id,0,$exclude_from_e_commerce,$category_info_popup))
		{
			if ($this->input->post('locations'))
			{
				foreach($this->input->post('locations') as $location_id => $category_location_data)
				{
					if (isset($category_location_data['hide_from_grid']) && $category_location_data['hide_from_grid'])
					{
						$this->Category->add_hidden_category($category_id,$location_id);
					}
					else
					{
						$this->Category->remove_hidden_category($category_id,$location_id);					
					}
				}
			}
			
			if (isset($this->ecom_model) && !$exclude_from_e_commerce)
			{
				if($update)
				{
					$this->ecom_model->update_category($category_id);
				}
				else
				{
					$this->ecom_model->save_category($category_id);
				}
			}
			
			$categories_data = $this->Category->sort_categories_and_sub_categories($this->Category->get_all_categories_and_sub_categories());
			$categories = array();
			foreach($categories_data as $key=>$value)
			{
				$name = $this->config->item('show_full_category_path') ? str_repeat('&nbsp;&nbsp;', $value['depth']).$this->Category->get_full_path($key) : str_repeat('&nbsp;&nbsp;', $value['depth']).$value['name'];
				$categories[] = array('value'=> H($key),'text'=> H($name));
			}
						
			echo json_encode(array('success'=>true,'message'=>lang('items_category_successful_adding').' '.H($category_name), 'categories' => $categories, 'selected' => $category_id));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('items_category_successful_error')));
		}
	}
	
	function get_hidden_locations_for_category($category_id)
	{
		$location_ids = array();
		foreach($this->Location->get_all()->result_array() as $location)
		{
			if ($this->Category->is_category_hidden($category_id,$location['location_id']))
			{
				$location_ids[] = $location['location_id'];
			}
		}
		echo json_encode($location_ids);
	}
	
	function get_hidden_locations_for_tag($tag_id)
	{
		$location_ids = array();
		foreach($this->Location->get_all()->result_array() as $location)
		{
			if ($this->Tag->is_tag_hidden($tag_id,$location['location_id']))
			{
				$location_ids[] = $location['location_id'];
			}
		}
		echo json_encode($location_ids);
	}
	
		
	function delete_category()
	{
		$this->check_action_permission('manage_categories');		
		$category_id = $this->input->post('category_id');
		if($this->Category->delete($category_id))
		{
			if (isset($this->ecom_model))
			{
				$this->ecom_model->delete_category($category_id);
			}
			
			echo json_encode(array('success'=>true,'message'=>lang('items_successful_deleted')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('items_cannot_be_deleted')));
		}
	}
	
	function get_category_tree_list()
	{
		$categories = $this->Category->get_all_categories_and_sub_categories_as_tree();
		echo $this->_category_tree_list($categories);
	}
	
	function manage_tags()
	{
		$this->check_action_permission('manage_tags');
		$tags = $this->Tag->get_all();
		$data = array('tags' => $tags, 'tag_list' => $this->_tag_list());
		$data['redirect'] = $this->input->get('redirect');

		$progression = $this->input->get('progression');
		$quick_edit = $this->input->get('quick_edit');
		$data['progression'] = !empty($progression);
		$data['quick_edit'] = !empty($quick_edit);
		$this->load->view('items/tags',$data);
	}
	
	function get_banned_locations_for_tag($tag_id)
	{
		echo json_encode($this->Tag->get_banned_locations($tag_id));
	}
	
	
	function save_tag($tag_id = FALSE)
	{		
		$this->check_action_permission('manage_tags');
		$tag_name = $this->input->post('tag_name');
		
		if ($this->Tag->save($tag_name, $tag_id,$this->input->post('ban_locations')))
		{
			if ($this->input->post('locations'))
			{
				foreach($this->input->post('locations') as $location_id => $tag_location_data)
				{
					if (isset($tag_location_data['hide_from_grid']) && $tag_location_data['hide_from_grid'])
					{
						$this->tag->add_hidden_tag($tag_id,$location_id);
					}
					else
					{
						$this->tag->remove_hidden_tag($tag_id,$location_id);					
					}
				}
			}
			if (isset($this->ecom_model))
			{
				$this->ecom_model->save_tag($tag_name);
			}
			//here
			echo json_encode(array('success'=>true,'message'=>lang('items_tag_successful_adding').' '.H($tag_name)));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('items_tag_successful_error')));
		}
	}
	
	function delete_tag()
	{
		$this->check_action_permission('manage_tags');		
		$tag_id = $this->input->post('tag_id');
		if($this->Tag->delete($tag_id))
		{
			if (isset($this->ecom_model))
			{	
				$this->ecom_model->delete_tag($tag_id);
			}
			
			echo json_encode(array('success'=>true,'message'=>lang('items_successful_deleted')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('items_cannot_be_deleted')));
		}
	}
	
	function tag_list()
	{
		echo $this->_tag_list();
	}
	
	function _tag_list()
	{
		$tags = $this->Tag->get_all();
     	$return = '<ul>';
		foreach($tags as $tag_id => $tag) 
		{
			$return .='<li>'.H($tag['name']).
					'<a href="javascript:void(0);" class="edit_tag" data-name = "'.H($tag['name']).'" data-tag_id="'.$tag_id.'">['.lang('common_edit').']</a> '.
					'<a href="javascript:void(0);" class="delete_tag" data-tag_id="'.$tag_id.'">['.lang('common_delete').']</a> ';
			 $return .='</li>';
		}
     	$return .='</ul>';
		
		return $return;
	}
	
	function _category_tree_list($tree) 
	{
		$this->load->model('Appfile');
		$return = '';
    if(!is_null($tree) && count($tree) > 0) 
		{
        $return = '<ul>';
        foreach($tree as $node) 
				{
            $return .='<li>'.H($node->name). ' <a href="javascript:void(0);" class="add_child_category" data-category_id="'.$node->id.'">['.lang('items_add_child_category').']</a> '.
						'<a href="javascript:void(0);" class="edit_category" data-exclude_from_e_commerce="'.($node->exclude_from_e_commerce ? 1 : 0).'" data-color="'.H($node->color).'" data-image_id="'.H($node->image_id).'" data-image_timestamp="'.$this->Appfile->get_file_timestamp($node->image_id).'" data-name = "'.H($node->name).'" data-info-popup = "'.H($node->category_info_popup).'" data-parent_id = "'.$node->parent_id.'" data-category_id="'.$node->id.'">['.lang('common_edit').']</a> '.
							'<a href="javascript:void(0);" class="delete_category" data-category_id="'.$node->id.'">['.lang('common_delete').']</a> '.
							'&nbsp;&nbsp;&nbsp;<label for="hide_from_grid_'.$node->id.'">'.lang('items_hide_from_item_grid').'</label> <input type="checkbox" '.($node->hide_from_grid ? 'checked="checked"' : '' ).' class="hide_from_grid" id="hide_from_grid_'.$node->id.'" value="1" name="hide_from_grid_'.$node->id.'" data-category_id="'.$node->id.'" /> <label for="hide_from_grid_'.$node->id.'"><span></span></label>';
						
							if ($this->config->item("ecommerce_platform"))
							{
								$return.='&nbsp;&nbsp;&nbsp;<label for="exclude_from_e_commerce_'.$node->id.'">'.lang('items_exclude_from_e_commerce').'</label> <input type="checkbox" '.($node->exclude_from_e_commerce ? 'checked="checked"' : '' ).' class="exclude_from_e_commerce" id="exclude_from_e_commerce_'.$node->id.'" value="1" name="exclude_from_e_commerce_'.$node->id.'" data-category_id="'.$node->id.'" /> <label for="exclude_from_e_commerce_'.$node->id.'"><span></span></label>';
							}
						$return .= $this->_category_tree_list($node->children);
	          $return .='</li>';
        }
        $return .='</ul>';
    }
		
		return $return;
	}
	
	function manage_manufacturers()
	{
		$this->check_action_permission('manage_manufacturers');
		$this->load->model('Manufacturer');
		$manufacturers = $this->Manufacturer->get_all();
		$data = array('manufacturers' => $manufacturers, 'manufacturers_list' => $this->_manufacturers_list());
		
		$progression = $this->input->get('progression');
		$quick_edit = $this->input->get('quick_edit');
		$data['progression'] = !empty($progression);
		$data['quick_edit'] = !empty($quick_edit);
		
		$data['redirect'] = $this->input->get('redirect');
		
		$this->load->view('items/manufacturers',$data);		
	
	}
	
	function save_manufacturer($manufacturer_id = FALSE)
	{
		$this->check_action_permission('manage_manufacturers');
		$this->load->model('Manufacturer');
		$manufacturer_name = $this->input->post('manufacturer_name');
		
		if ($this->Manufacturer->save($manufacturer_name, $manufacturer_id))
		{
			echo json_encode(array('success'=>true,'message'=>lang('items_manufacturer_successful_adding').' '.H($manufacturer_name)));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('items_manufacturer_successful_error')));
		}
	
	}
	
	function delete_manufacturer()
	{
		$this->check_action_permission('manage_manufacturers');
		$this->load->model('Manufacturer');
		$manufacturer_id = $this->input->post('manufacturer_id');
		if($this->Manufacturer->delete($manufacturer_id))
		{
			echo json_encode(array('success'=>true,'message'=>lang('items_successful_deleted')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('items_cannot_be_deleted')));
		}
		
	}
	
	function manufacturers_list()
	{
		echo $this->_manufacturers_list();
	}
	
	function _manufacturers_list()
	{
		$this->load->model('Manufacturer');
		$manufacturers = $this->Manufacturer->get_all();
     	$return = '<ul>';
		foreach($manufacturers as $manufacturers_id => $manufacturers) 
		{
			$return .='<li>'.H($manufacturers['name']).
					'<a href="javascript:void(0);" class="edit_manufacturer" data-name = "'.H($manufacturers['name']).'" data-manufacturer_id="'.$manufacturers_id.'">['.lang('common_edit').']</a> '.
					'<a href="javascript:void(0);" class="delete_manufacturer" data-manufacturer_id="'.$manufacturers_id.'">['.lang('common_delete').']</a> ';
			 $return .='</li>';
		}
     	$return .='</ul>';
		
		return $return;
	}		
	
	function sorting()
	{		
		$this->check_action_permission('search');
		$params = $this->session->userdata('items_search_data') ? $this->session->userdata('items_search_data') : array('order_col' => 'name', 'order_dir' => 'asc','deleted' => 0);
		$search=$this->input->post('search') ? $this->input->post('search') : "";
		$category_id = $this->input->post('category_id');
		$fields = $this->input->post('fields') ? $this->input->post('fields') : 'all';
		
		$per_page=$this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20;
		$offset = $this->input->post('offset') ? $this->input->post('offset') : 0;
		$order_col = $this->input->post('order_col') ? $this->input->post('order_col') : $params['order_col'];
		$order_dir = $this->input->post('order_dir') ? $this->input->post('order_dir'): $params['order_dir'];
		$deleted = $this->input->post('deleted') ? $this->input->post('deleted') : $params['deleted'];
		

		$items_search_data = array('offset' => $offset, 'order_col' => $order_col, 'order_dir' => $order_dir, 'search' => $search, 'category_id' => $category_id, 'fields' => $fields,'deleted' => $deleted);
		
		$this->session->set_userdata("items_search_data",$items_search_data);
		if ($search || $category_id)
		{
			$table_data = $this->Item->search($search,$deleted,$category_id, $per_page,$this->input->post('offset') ? $this->input->post('offset') : 0, $order_col ,$order_dir, $fields);
			$config['total_rows'] = $this->Item->count_last_query_results();
		}
		else
		{
			$table_data = $this->Item->get_all($deleted,$per_page,$this->input->post('offset') ? $this->input->post('offset') : 0, $order_col ,$order_dir);
			$config['total_rows'] = $this->Item->count_last_query_results();
		}
		$config['base_url'] = site_url('items/sorting');
		$config['per_page'] = $per_page; 
		$this->load->library('pagination');$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		$this->load->model('Employee_appconfig');
		$data['default_columns'] = $this->Item->get_default_columns();
		$data['manage_table']=get_items_manage_table_data_rows($table_data,$this);
		
		echo json_encode(array('manage_table' => $data['manage_table'], 'pagination' => $data['pagination'],'total_rows' => $config['total_rows']));	
	}

	
	function find_item_info()
	{
		$this->load->helper('items');
		$item_identifer=$this->input->post('scan_item_number');
		$result = parse_item_scan_data($item_identifer);
		echo json_encode(array('info' => $this->Item->get_info($result['item_id']),'link' =>site_url('items/view/'.$result['item_id'])));
	}
		
	function item_number_exists()
	{
		if($this->Item->account_number_exists($this->input->post('item_number')))
		echo 'false';
		else
		echo 'true';
		
	}

	function product_id_exists()
	{
		if($this->Item->product_id_exists($this->input->post('product_id')))
		echo 'false';
		else
		echo 'true';
	}
	
	function check_duplicate()
	{
		echo json_encode(array('duplicate'=>$this->Item->check_duplicate($this->input->post('term'))));
	}
		
	function search()
	{

		$this->check_action_permission('search');
		$params = $this->session->userdata('items_search_data');
		
		$search=$this->input->post('search');
		$category_id = $this->input->post('category_id');
		$offset = $this->input->post('offset') ? $this->input->post('offset') : 0;
		$order_col = $this->input->post('order_col') ? $this->input->post('order_col') : 'name';
		$order_dir = $this->input->post('order_dir') ? $this->input->post('order_dir'): 'asc';
		$fields = $this->input->post('fields') ? $this->input->post('fields') : 'all';
		$deleted = isset($params['deleted']) ? $params['deleted'] : 0;
		
		$items_search_data = array('offset' => $offset, 'order_col' => $order_col, 'order_dir' => $order_dir, 'search' => $search,  'category_id' => $category_id, 'fields' => $fields,'deleted' => $deleted);
		$this->session->set_userdata("items_search_data",$items_search_data);
		$per_page=$this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20;
		$search_data=$this->Item->search($search, $deleted, $category_id, $per_page, $this->input->post('offset') ? $this->input->post('offset') : 0, $this->input->post('order_col') ? $this->input->post('order_col') : 'name' ,$this->input->post('order_dir') ? $this->input->post('order_dir'): 'asc', $fields);

		$config['total_rows'] = $this->Item->count_last_query_results();
		$config['base_url'] = site_url('items/search');
		$config['per_page'] = $per_page ;
		
		$this->load->library('pagination');$this->pagination->initialize($config);				
		$data['pagination'] = $this->pagination->create_links();
		$this->load->model('Employee_appconfig');
		$data['default_columns'] = $this->Item->get_default_columns();
		$data['manage_table'] = get_items_manage_table_data_rows($search_data,$this);
		
		echo json_encode(array('manage_table' => $data['manage_table'], 'pagination' => $data['pagination'],'total_rows' => $config['total_rows']));
	}
	
	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		//allow parallel searches to improve performance.
		session_write_close();
		$params = $this->session->userdata('items_search_data') ? $this->session->userdata('items_search_data') : array('deleted' => 0);
		$suggestions = $this->Item->get_item_search_suggestions_without_variations($this->input->get('term'),$params['deleted'],$this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20);
		echo json_encode(H($suggestions));
	}

	function item_search()
	{
		//allow parallel searches to improve performance.
		session_write_close();
		$suggestions = $this->Item->get_item_search_suggestions($this->input->get('term'),0,'unit_price',$this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20); //milc reduced 100 to 25
		echo json_encode(H($suggestions));
	}

	function get_info($item_id=-1,$variation_id = false)
	{
		$info = $this->Item->get_info($item_id);
		
		if ($variation_id)
		{
			$this->load->model('Item_variations');
			$variation_info = $this->Item_variations->get_info($variation_id);
			
			
			if ($variation_info->cost_price)
			{
				$info->cost_price = $variation_info->cost_price;
			}
			
			if ($variation_info->unit_price)
			{
				$info->unit_price = $variation_info->unit_price;
			}
		
		}
		
		foreach($info as $key=>$value)
		{
			$info->$key = H($value);
		}
		echo json_encode($info);
	}

	function _get_item_data($item_id)
	{
    	$this->load->helper('report');
		$this->load->model('Item_serial_number');
		$this->load->model('Tax_class');
		$this->load->model('Item_taxes');
		$this->load->model('Tier');
		$this->load->model('Item_location');
		$this->load->model('Item_location_taxes');
		$this->load->model('Supplier');
		$this->load->model('Item_attribute');
		$this->load->model('Item_attribute_value');
		$this->load->model('Item_variations');
		$this->load->model('Item_variation_location');
		
		$data = array();
		$data['controller_name']=strtolower(get_class());

		$data['item_info']=$this->Item->get_info($item_id);

		$data['ecommerce_shipping_classes'] = array('' => lang('common_none'));
		
		if ($this->config->item('woo_shipping_classes'))
		{
			$woo_shipping_classes = unserialize($this->config->item('woo_shipping_classes'));
			
			foreach($woo_shipping_classes as $shipping_class)
			{
				$data['ecommerce_shipping_classes'][$shipping_class['slug']] = $shipping_class['name'];
			}
		}
		
		$data['tax_classes'] = array();
		$data['tax_classes'][''] = lang('common_none');
		
		foreach($this->Tax_class->get_all()->result_array() as $tax_class)
		{
			$data['tax_classes'][$tax_class['id']] = $tax_class['name'];
		}
		
		$data['item_images']=$this->Item->get_item_images($item_id);
		
		$data['categories'][''] = lang('common_select_category');
		
		$categories = $this->Category->sort_categories_and_sub_categories($this->Category->get_all_categories_and_sub_categories());
		foreach($categories as $key=>$value)
		{
			$name = $this->config->item('show_full_category_path') ? str_repeat('&nbsp;&nbsp;', $value['depth']).$this->Category->get_full_path($key) : str_repeat('&nbsp;&nbsp;', $value['depth']).$value['name'];
			$data['categories'][$key] = $name;
		}
		
		$this->load->model('Manufacturer');
		$manufacturers = array('-1' => lang('common_none'));
		
		foreach($this->Manufacturer->get_all() as $id => $row)
		{
			$manufacturers[$id] = $row['name'];
		}
		
		$data['manufacturers'] = $manufacturers;
		$data['selected_manufacturer'] = $this->Item->get_info($item_id)->manufacturer_id;
				
		$data['tags'] = implode(',',$this->Tag->get_tags_for_item($item_id));
		$data['item_tax_info']=$this->Item_taxes->get_info($item_id);
		$data['tiers']=$this->Tier->get_all()->result();
		$data['locations'] = array();
		$data['other_locations'] = array();
		$data['location_tier_prices'] = array();
		
		$available_attributes_for_item = array(
			'-1' => lang('items_select_an_attribute'),
			'0' => lang('items_custom_attribute'),
		);
		foreach($this->Item_attribute->get_available_attributes_for_item($item_id) as $id => $row)
		{
			$available_attributes_for_item[$id] = $row['name'];
		}
		
		$data['attribute_select_options'] = $available_attributes_for_item;
		$data['attributes'] = $this->Item_attribute->get_attributes_for_item_with_attribute_values($item_id);
		
		$data['additional_item_numbers'] = $this->Additional_item_numbers->get_item_numbers($item_id);
		
		$data['item_variations'] = $this->Item_variations->get_variations($item_id);
		
		foreach(array_keys($data['item_variations']) as $item_variation_id)
		{
			$var_additional_numbers =	$this->Additional_item_numbers->get_item_numbers_for_variation($item_id,$item_variation_id)->result_array();
			
			foreach($var_additional_numbers as $row)
			{
				$data['item_variations'][$item_variation_id]['item_number'].='|'.$row['item_number'];
			}
		}
		
		$data['serial_numbers'] = $this->Item_serial_number->get_all($item_id);
		
		if ($item_id != -1)
		{
			$data['next_item_id'] = $this->Item->get_next_id($item_id);
			$data['prev_item_id'] = $this->Item->get_prev_id($item_id);;
		}
			
		foreach($this->Location->get_all()->result() as $location)
		{
			if($this->Employee->is_location_authenticated($location->location_id))
			{				
				$data['locations'][] = $location;
				$data['location_items'][$location->location_id] = $this->Item_location->get_info($item_id,$location->location_id);
				$data['location_taxes'][$location->location_id] = $this->Item_location_taxes->get_info($item_id, $location->location_id);
				$data['location_variations'][$location->location_id] = $this->Item_variation_location->get_variations_with_quantity($item_id, $location->location_id);
				foreach($data['tiers'] as $tier)
				{					
					$tier_prices = $this->Item_location->get_tier_price_row($tier->id,$data['item_info']->item_id, $location->location_id);
					if (!empty($tier_prices))
					{
						$data['location_tier_prices'][$location->location_id][$tier->id] = $tier_prices;
					}
					else
					{
						$data['location_tier_prices'][$location->location_id][$tier->id] = FALSE;			
					}
				}
			}else{
				$data['other_locations'][] = $location;
			}
		}
				
		
		if ($item_id == -1)
		{
			$suppliers = array(''=> lang('common_not_set'), '-1' => lang('common_none'));
		}
		else
		{
			$suppliers = array('-1' => lang('common_none'));
		}
		foreach($this->Supplier->get_all()->result_array() as $row)
		{
			$suppliers[$row['person_id']] = $row['company_name'] .' ('.$row['first_name'] .' '. $row['last_name'].')';
		}
		
		$data['tier_prices'] = array();
		$data['tier_type_options'] = array('unit_price' => lang('common_fixed_price'), 'percent_off' => lang('common_percent_off'), 'cost_plus_percent' => lang('common_cost_plus_percent'),'cost_plus_fixed_amount' => lang('common_cost_plus_fixed_amount'));
		foreach($data['tiers'] as $tier)
		{
			$tier_prices = $this->Item->get_tier_price_row($tier->id,$data['item_info']->item_id);
			
			if (!empty($tier_prices))
			{
				$data['tier_prices'][$tier->id] = $tier_prices;
			}
			else
			{
				$data['tier_prices'][$tier->id] = FALSE;			
			}
		}

		$data['suppliers']=$suppliers;
		$data['selected_supplier'] = $this->Item->get_info($item_id)->supplier_id;
		
		$decimals = $this->Appconfig->get_raw_number_of_decimals();
		$decimals = $decimals !== NULL && $decimals!= '' ? $decimals : 2;
		$data['decimals'] = $decimals;
		
		$data['item_quantity_units'] = $this->Item->get_quantity_units($item_id);
		return $data;
	}
	
	function view($item_id=-1, $sale_or_receiving = 'sale')
	{
 	 	$this->load->model('Appfile');
		$this->load->model('Item_taxes');
		$this->load->model('Tier');
		$this->load->model('Item_location');
		$this->load->model('Item_location_taxes');
		$this->load->model('Supplier');
		$this->load->model('Category');
		
		$this->check_action_permission('add_update');
		
    	$this->load->helper('report');
		$data = $this->_get_item_data($item_id);
		
		$data['category'] = $this->Category->get_full_path($data['item_info']->category_id);
		
		$data['redirect'] = $this->input->get('redirect');
		
		$progression = $this->input->get('progression');
		$quick_edit = $this->input->get('quick_edit');
		$data['progression'] = !empty($progression);
		$data['quick_edit'] = !empty($quick_edit);
		$data['current_location'] = $this->Employee->get_logged_in_employee_current_location_id();
		$this->load->view("items/form", $data);
	}
	
	function images($item_id=-1)
	{			
		$this->check_action_permission('add_update');
				
		$data = $this->_get_item_data($item_id);
		
		$data['category'] = $this->Category->get_full_path($data['item_info']->category_id);
		$data['redirect'] = $this->input->get('redirect');
		
		$progression = $this->input->get('progression');
		$quick_edit = $this->input->get('quick_edit');
		$data['progression'] = !empty($progression);
		$data['quick_edit'] = !empty($quick_edit);
		
		$this->load->view("items/images",$data);
	}
	
	function location_settings($item_id=-1)
	{
		$this->load->model('Category');
		$this->load->model('Item_taxes');
		$this->load->model('Tier');
		$this->load->model('Supplier');
		
		$this->load->model('Item_location');
		$this->load->model('Item_location_taxes');
		
		$data = $this->_get_item_data($item_id);
		$data['category'] = $this->Category->get_full_path($data['item_info']->category_id);
		
		$data['redirect'] = $this->input->get('redirect');
		$progression = $this->input->get('progression');
		$quick_edit = $this->input->get('quick_edit');
		$data['progression'] = !empty($progression);
		$data['quick_edit'] = !empty($quick_edit);
		
		$this->load->view("items/locations",$data);
	}
	
	function clone_item($item_id)
	{		
		$this->load->model('Item_taxes');
		$this->load->model('Tier');
		$this->load->model('Item_location');
		$this->load->model('Item_location_taxes');
		$this->load->model('Supplier');
		$this->load->model('Item_taxes_finder');
		$this->load->model('Item_variations');
		
		$this->check_action_permission('add_update');
		
		$redirect = $this->input->get('redirect');
		
   	     	$this->load->helper('report');
		$item = $this->_get_item_data($item_id);
		
		$item_info = $item['item_info'];
		
		$item_data = array(
				'name'=>$item_info->name.' ('.lang('common_clone').')',
				'description'=>$item_info->description,
				'long_description'=>$item_info->long_description,
				'info_popup'=>$item_info->info_popup,
				'tax_included'=>$item_info->tax_included ? $item_info->tax_included : 0,
				'category_id'=>$item_info->category_id,
				'size'=>$item_info->size,
				'expire_days'=>$item_info->expire_days ?  $item_info->expire_days : NULL,
				'supplier_id'=>$item_info->supplier_id== -1 || $item_info->supplier_id == '' ? null:$item_info->supplier_id,
				'manufacturer_id'=>$item_info->manufacturer_id== -1 || $item_info->manufacturer_id == '' ? null:$item_info->manufacturer_id,
				'cost_price'=>$item_info->cost_price,
				'change_cost_price' => $item_info->change_cost_price ? $item_info->change_cost_price : 0,
		 		'unit_price'=>$item_info->unit_price,
				'promo_price'=>$item_info->promo_price ? $item_info->promo_price : NULL,
				'start_date'=>$item_info->start_date ? date('Y-m-d', strtotime($item_info->start_date)) : NULL,
				'end_date'=>$item_info->end_date ?date('Y-m-d', strtotime($item_info->end_date)) : NULL,
				'min_edit_price'=>$item_info->min_edit_price !== '' ? $item_info->min_edit_price : NULL,
				'max_edit_price'=>$item_info->max_edit_price !== '' ? $item_info->max_edit_price : NULL,
				'max_discount_percent'=>$item_info->max_discount_percent !== '' ? $item_info->max_discount_percent : NULL,
				'reorder_level'=>$item_info->reorder_level!='' ? $item_info->reorder_level : NULL,
				'replenish_level'=>$item_info->replenish_level!='' ? $item_info->replenish_level : NULL,
				'is_service'=>$item_info->is_service ? $item_info->is_service : 0 ,
				'allow_alt_description'=>$item_info->allow_alt_description ? $item_info->allow_alt_description : 0 ,
				'is_serialized'=>$item_info->is_serialized ? $item_info->is_serialized : 0,
				'override_default_tax'=> $item_info->override_default_tax ? $item_info->override_default_tax : 0,
				'tax_class_id'=> $item_info->tax_class_id ? $item_info->tax_class_id : NULL,
				'is_ebt_item'=> $item_info->is_ebt_item ? $item_info->is_ebt_item : 0,
				'is_ecommerce'=> $item_info->is_ecommerce ? $item_info->is_ecommerce : 0,
				'commission_fixed'=> $item_info->commission_fixed ? $item_info->commission_fixed : NULL,
				'commission_percent'=> $item_info->commission_percent ? $item_info->commission_percent : NULL,
				'commission_percent_type'=> $item_info->commission_percent_type ? $item_info->commission_percent_type : '',				
				'verify_age'=> $item_info->verify_age ? 1 : 0,
				'required_age'=> $item_info->required_age,
				'allow_price_override_regardless_of_permissions' => $item_info->allow_price_override_regardless_of_permissions ? 1 : 0,
				'only_integer' => $item_info->only_integer ? 1 : 0,
				'is_series_package' => $item_info->is_series_package ? 1 : 0,
				'series_quantity' => $item_info->series_quantity,
				'series_days_to_use_within' => $item_info->series_days_to_use_within,
				'is_barcoded' => $item_info->is_barcoded ? 1 : 0,
				'item_inactive' => $item_info->item_inactive ? 1 : 0,
				'is_favorite' => $item_info->is_favorite ? 1 : 0,
				'loyalty_multiplier' => $item_info->loyalty_multiplier ? $item_info->loyalty_multiplier : NULL,
		);
		
		$this->Item->save($item_data);
		
		foreach($this->Tier->get_all()->result() as $tier)
		{
			$tier_prices = $this->Item->get_tier_price_row($tier->id,$item_id);

			if (!empty($tier_prices))
			{
				$tier_data = array();
				$tier_data['tier_id'] = $tier_prices->tier_id;
				$tier_data['item_id'] = $item_data['item_id'];							
				$tier_data['percent_off'] = $tier_prices->percent_off;
				$tier_data['unit_price'] = $tier_prices->unit_price;
				$tier_data['cost_plus_percent'] = $tier_prices->cost_plus_percent;
				$tier_data['cost_plus_fixed_amount'] = $tier_prices->cost_plus_fixed_amount;
				$this->Item->save_item_tiers($tier_data,$item_data['item_id']);
			}
		}
		
		foreach($this->Location->get_all()->result_array() as $location)
		{
			$location_id = $location['location_id'];
			
			$item_location_data = $this->Item_location->get_info($item_id,$location_id);		
			$data = array(
				'location_id' => $location_id,
				'item_id' => $item_data['item_id'],
				'location' => $item_location_data->location,
				'cost_price' => $item_location_data->cost_price ? $item_location_data->cost_price : NULL,
				'unit_price' => $item_location_data->unit_price ? $item_location_data->unit_price : NULL,
				'promo_price' => $item_location_data->promo_price ? $item_location_data->promo_price : NULL,
				'start_date' => $item_location_data->promo_price!='' && $item_location_data->start_date != '' ? date('Y-m-d', strtotime($item_location_data->start_date)) : NULL,
				'end_date' => $item_location_data->promo_price != '' && $item_location_data->end_date != '' ? date('Y-m-d', strtotime($item_location_data->end_date)) : NULL,
				'reorder_level' => $item_location_data->reorder_level,
				'replenish_level' => $item_location_data->replenish_level,
				'override_default_tax'=> $item_location_data->override_default_tax,
				'tax_class_id'=> $item_location_data->tax_class_id ? $item_location_data->tax_class_id : NULL,
			);
	
			$this->Item_location->save($data, $item_data['item_id'], $location_id);
		}
		
		$this->clone_attributes($item, $item_data['item_id']);
		
		$this->clone_variations($item, $item_data['item_id']);
		
		$this->clone_item_quantity_units($item, $item_data['item_id']);
		
		redirect("items/view/".$item_data['item_id']."?redirect=$redirect");
	}
	
	function clone_attributes($parent_item, $cloned_item_id)
	{
		$this->load->model('Item_attribute');
		$this->load->model('Item_attribute_value');
		
		if(isset($parent_item['attributes']) && is_array($parent_item['attributes']) )
		{
			$attribute_ids = array();
			$attribute_value_ids = array();
			foreach($parent_item['attributes'] as $attribute_id => $attribute){
				$attribute_ids[] = $attribute_id;
				
				if(isset($attribute['attr_values']) && is_array($attribute['attr_values']) )
				{
					foreach($attribute['attr_values'] as $attribute_value_id => $attribute_value)
					{
						$attribute_value_ids[] = $attribute_value_id;
					}
				}
			}
			
			if(!$this->Item_attribute->save_item_attributes($attribute_ids, $cloned_item_id))
			{
				// through a message here
			}
			
			if(!$this->Item_attribute_value->save_item_attribute_values($cloned_item_id, $attribute_value_ids))
			{
				// through a message here
			}
			
		}
	}
	
	function clone_variations($parent_item, $cloned_item_id)
	{
		$this->load->model('Item');
		$this->load->model('Item_attribute');
		$this->load->model('Item_attribute_value');
		$this->load->model('Item_variations');
		$this->load->model('Additional_item_numbers');
		$this->load->model('Item_variation_location');
		
		if(isset($parent_item['item_variations']) && is_array($parent_item['item_variations']))
		{
			$variations_ids = array();
			
			foreach($parent_item['item_variations'] as $variations_id => $variation)
			{
				$data['name'] = $variation['name'];
				$data['item_number'] = $variation['item_number'];
				$data['is_ecommerce'] = $variation['is_ecommerce'];
				$data['cost_price'] = $variation['cost_price'];
				$data['unit_price'] = $variation['unit_price'];
				$data['promo_price'] = $variation['promo_price'];
				$data['start_date'] = $variation['start_date'];
				$data['end_date'] = $variation['end_date'];
				$data['reorder_level'] = $variation['reorder_level'];
				$data['replenish_level'] = $variation['replenish_level'];
				$data['item_id'] = $cloned_item_id;
				
				$variation_attribute_value_ids = array();
				
				if(isset($variation['attributes']) && is_array($variation['attributes']) )
				{
					
					foreach($variation['attributes'] as $variation_attribute_value_id => $variation_attribute_value)
					{
						$variation_attribute_value_ids[] = $variation_attribute_value['value'];
					}
				}

				$item_variation_id = $this->Item_variations->save($data, false, $variation_attribute_value_ids);

				//add location item variation based on this item variation
				
				if(isset($parent_item['location_variations']) && is_array($parent_item['location_variations']) ){
					foreach($parent_item['location_variations'] as $location_id => $location_variation){
						if(array_key_exists($variations_id, $location_variation)){
							$location_variation_data = $location_variation[$variations_id];
							unset($location_variation_data['name']);
							unset($location_variation_data['quantity']);
							unset($location_variation_data['item_number']);
							
							$this->Item_variation_location->save($location_variation_data, $item_variation_id, $location_id);
						}
					}
				}
				
				$all_item_numbers = explode('|',$variation['item_number']);
				
				if (count($all_item_numbers) > 1)
				{
					$var_additional_item_numbers = array_slice($all_item_numbers,1);
				}
				else
				{
					$var_additional_item_numbers = array();
				}
				
				$this->Additional_item_numbers->save_variation($cloned_item_id, $item_variation_id, $var_additional_item_numbers);				
				
			}
		}
	}
	
	function clone_item_quantity_units($parent_item, $cloned_item_id){
		if(isset($parent_item['item_quantity_units']) && is_array($parent_item['item_quantity_units'])){
			foreach($parent_item['item_quantity_units'] as $item_quantity_value){
				
					$unit_name = $item_quantity_value->unit_name;
					$unit_quantity = $item_quantity_value->unit_quantity;
					$unit_price = $item_quantity_value->unit_price;
					$cost_price = $item_quantity_value->cost_price;
					
					$quantity_unit_data = array(
						'item_id'=> $cloned_item_id,
						'unit_name' => $unit_name, 
						'unit_quantity' => $unit_quantity,
						'unit_price' => $unit_price !== '' ? $unit_price : NULL,
						'cost_price' => $cost_price !== '' ? $cost_price : NULL
					);
					
					$this->Item->save_unit_quantity($quantity_unit_data, false);
			}
		}
	}
	
	function inventory($item_id=-1,$offset=0)
	{
		$this->load->model('Item_location');
		$this->load->model('Item_variations');
		$this->load->model('Item_variation_location');
		$this->load->model('Category');
		
		$this->check_action_permission('edit_quantity');
		
		$data['item_info']=$this->Item->get_info($item_id);
		$data['item_location_info']=$this->Item_location->get_info($item_id);		
		
		if ($item_id != -1)
		{
			$data['next_item_id'] = $this->Item->get_next_id($item_id);
			$data['prev_item_id'] = $this->Item->get_prev_id($item_id);;
		}
		
		$data['item_variations'] = $this->Item_variations->get_variations($item_id);
		$data['item_variation_location_info'] = $this->Item_variation_location->get_variations_with_quantity($item_id);
		$config['base_url'] = site_url('items/inventory/'.$item_id);
		$config['per_page'] = $this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20; 
		$config['total_rows'] = $this->Inventory->count_all($item_id);
		$config['uri_segment'] = 4;
		$this->load->library('pagination');$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		$data['inventory_data'] = $this->Inventory->get_inventory_data_for_item($item_id, $config['per_page'],$offset);
		$data['category'] = $this->Category->get_full_path($data['item_info']->category_id);
		
		$data['redirect'] = $this->input->get('redirect');
		$progression = $this->input->get('progression');
		$quick_edit = $this->input->get('quick_edit');
		$data['progression'] = !empty($progression);
		$data['quick_edit'] = !empty($quick_edit);
		
		$this->load->view("items/inventory",$data);
	}
	
	function pricing($item_id=-1)
	{
		$this->check_action_permission('edit_prices');
		
		$this->load->model('Category');
		$this->load->model('Item_taxes');
		$this->load->model('Item_taxes_finder');
		$this->load->model('Tier');
		$this->load->model('Supplier');
		
		$this->load->model('Item_location');
		$this->load->model('Item_location_taxes');
		
		$data = $this->_get_item_data($item_id);
		$data['category'] = $this->Category->get_full_path($data['item_info']->category_id);
		$data['redirect'] = $this->input->get('redirect');
		
		$progression = $this->input->get('progression');
		$quick_edit = $this->input->get('quick_edit');
		$data['progression'] = !empty($progression);
		$data['quick_edit'] = !empty($quick_edit);
		
		
		$this->load->view("items/pricing",$data);
	}
	
	function auto_create_variations($item_id)
	{
		$this->load->model('Item_variations');
		$this->Item_variations->auto_create($item_id);
		$item_info = $this->Item->get_info($item_id);
		
		if (isset($this->ecom_model))
		{
			if ($item_info->is_ecommerce)
			{
				$this->ecom_model->save_item_from_phppos_to_ecommerce($item_id);
			}
		}
		
		redirect("items/variations/$item_id");
	}
	
	function variations($item_id=-1)
	{
		$this->load->model('Category');
		$this->load->model('Item_taxes');
		$this->load->model('Tier');
		$this->load->model('Supplier');
		
		$this->load->model('Item_location');
		$this->load->model('Item_location_taxes');
		
		$data = $this->_get_item_data($item_id);
		$data['category'] = $this->Category->get_full_path($data['item_info']->category_id);
		
		$data['redirect'] = $this->input->get('redirect');
		$progression = $this->input->get('progression');
		$quick_edit = $this->input->get('quick_edit');
		$data['progression'] = !empty($progression);
		$data['quick_edit'] = !empty($quick_edit);
		$this->load->view("items/variations",$data);
	}
	
		
	function barcodes($item_id=-1,$offset=0)
	{
		$this->load->model('Item_location');
		$this->load->model('Item_variations');
		$this->load->model('Item_variation_location');
		
		$data['item_info']=$this->Item->get_info($item_id);		
		$data['item_variations'] = $this->Item_variations->get_variations($item_id);
				
		
		$this->load->view("items/barcodes",$data);
	}
	
	function print_barcodes()
	{
		$item_id = $this->input->post('item_id');
		$quantity = $this->input->post('items_number_of_barcodes');
	
		$item_ids_to_make = array_fill(0, $quantity, $item_id);
		$item_ids = implode('~', $item_ids_to_make);
				
		$skip = 0;
		$item_variations_number_of_barcodes = $this->input->post('item_variations_number_of_barcodes');
		$variation_ids = false;
		
		if($item_variations_number_of_barcodes && count($item_variations_number_of_barcodes) > 0)
		{			
			foreach($item_variations_number_of_barcodes as $item_variation_id => $quantity)
			{
				for ($x = 1; $x <= $quantity; $x++)
				{
					$variation_ids .= $item_variation_id."~";
				}
			}
			if ($variation_ids[strlen($variation_ids)-1]==='~')
			  $variation_ids=substr($variation_ids, 0, -1);
		}
		
		if(null !== $this->input->post('barcode_labels_action'))
		{
			$this->generate_barcode_labels($item_ids, $variation_ids);
		}
		elseif(null !== $this->input->post('barcode_sheet_action'))
		{
			$skip = $this->input->post('skip');
			$this->generate_barcodes($item_ids, $skip, $variation_ids);
		}
	}
	
	
	function generate_barcodes($item_ids, $skip=0, $variation_ids = false)
	{				
		$select_all_inventory=$this->get_select_inventory();
		
		if ($select_all_inventory)
		{
			$item_ids = $this->Item->get_item_ids_for_search();
			$item_ids = implode('~',$item_ids);
		}
		
		
		$this->load->model('Item_taxes');
		$this->load->model('Item_location');
		$this->load->model('Item_location_taxes');
		$this->load->model('Item_taxes_finder');
		
		$this->load->helper('items');
		
		$data['items'] = $variation_ids ? get_item_variations_barcode_data($variation_ids) : get_items_barcode_data($item_ids);
		
		$data['scale'] = 1;
		$data['skip'] = $skip;
		
		$this->load->view("barcode_sheet", $data);
	}

	function generate_barcode_labels($item_ids, $variation_ids = false)
	{				
		$select_all_inventory=$this->get_select_inventory();
		
		if ($select_all_inventory)
		{
			$item_ids = $this->Item->get_item_ids_for_search();
			$item_ids = implode('~',$item_ids);
		}
		
		$this->load->model('Item_taxes');
		$this->load->model('Item_location');
		$this->load->model('Item_location_taxes');
		$this->load->model('Item_taxes_finder');
		
		
		$this->load->helper('items');
		$data['items'] = $variation_ids ? get_item_variations_barcode_data($variation_ids) : get_items_barcode_data($item_ids);		
		$data['excel_url'] = site_url('items/generate_barcode_labels_excel/'.($item_ids ? $item_ids : '-1').'/'.$variation_ids);
		$this->load->view("barcode_labels", $data);
	}
	
	function generate_barcode_labels_excel($item_ids, $variation_ids = false)
	{
		if ($this->input->post('item_id'))
		{
			$item_id = $this->input->post('item_id');
			$quantity = $this->input->post('items_number_of_barcodes');
	
			$item_ids_to_make = array_fill(0, $quantity, $item_id);
			$item_ids = implode('~', $item_ids_to_make);
				
			$skip = 0;
			$item_variations_number_of_barcodes = $this->input->post('item_variations_number_of_barcodes');
			$variation_ids = false;
		
			if($item_variations_number_of_barcodes && count($item_variations_number_of_barcodes) > 0)
			{			
				foreach($item_variations_number_of_barcodes as $item_variation_id => $quantity)
				{
					for ($x = 1; $x <= $quantity; $x++)
					{
						$variation_ids .= $item_variation_id."~";
					}
				}
				if ($variation_ids[strlen($variation_ids)-1]==='~')
				  $variation_ids=substr($variation_ids, 0, -1);
			}
		}
		else
		{
			$select_all_inventory=$this->get_select_inventory();
		
			if ($select_all_inventory)
			{
				$item_ids = $this->Item->get_item_ids_for_search();
				$item_ids = implode('~',$item_ids);
			}
		}
		
		$this->load->model('Item_taxes');
		$this->load->model('Item_location');
		$this->load->model('Item_location_taxes');
		$this->load->model('Item_taxes_finder');
		
		
		$this->load->helper('items');
		$data = $variation_ids ? get_item_variations_barcode_data($variation_ids) : get_items_barcode_data($item_ids);		
		
		$export_data[] = array(lang('common_item_number'),lang('common_name'), lang('common_description'),lang('common_unit_price'));
		foreach($data as $row)
		{
			$data = trim(strip_tags($row['name']));
			$price = substr($data,0,strpos($data,' '));
			$name = str_replace($price.' ','',$data);
			$description = $row['description'];
			$export_data[] = array($row['id'],$name,$description,$price);
		}
		
		$this->load->helper('spreadsheet');
		array_to_spreadsheet($export_data,'barcode_export.'.($this->config->item('spreadsheet_format') == 'XLSX' ? 'xlsx' : 'csv'));
		
	}
	
	
	function generate_barcodes_from_recv($recv_id, $skip=0)
	{
		$this->load->model('Item_taxes');
		$this->load->model('Item_location');
		$this->load->model('Item_location_taxes');
		$this->load->model('Item_taxes_finder');
		$this->load->model('Receiving');
		$item_ids = array();
		$variation_ids = array();
		$items_expire = array();
		
		foreach($this->Receiving->get_receiving_items($recv_id)->result() as $item)
		{
			for($k = 0; $k< abs((int)$item->quantity_purchased);$k++)
			{
				if ($item->item_variation_id)
				{
					$variation_ids[] = $item->item_variation_id;
				}
				else
				{
					$item_ids[] = $item->item_id;
				}
				$key = $item->receiving_id.'|'.$item->item_id;
				$items_expire[$key] = $item->expire_date ? date(get_date_format(), strtotime($item->expire_date)) : FALSE;
			}
		}
	
		$data = array();
		$this->load->helper('items');
		$items_barcodes = array();
		
		if (!empty($item_ids))
		{
			$items_barcodes = get_items_barcode_data(implode('~',$item_ids));
		}
		
		$variations_barcodes = array();
		if (!empty($variation_ids))
		{
			$variations_barcodes = get_item_variations_barcode_data(implode('~',$variation_ids));
		}
		$data['items'] = array_merge($items_barcodes, $variations_barcodes);
		$data['items_expire'] = $items_expire;
		$data['scale'] = 1;
		$data['from_recv'] = $recv_id;
		$data['skip'] = $skip;
		
		$this->load->view("barcode_sheet", $data);
	}
	
	
	function generate_barcodes_labels_from_recv($recv_id)
	{
		$this->load->model('Item_taxes');
		$this->load->model('Item_location');
		$this->load->model('Item_location_taxes');
		$this->load->model('Item_taxes_finder');
		$this->load->model('Receiving');
		
		$item_ids = array();
		$items_expire = array();
		$variation_ids = array();
		
		foreach($this->Receiving->get_receiving_items($recv_id)->result() as $item)
		{
			for($k = 0; $k< abs((int)$item->quantity_purchased);$k++)
			{
				if ($item->item_variation_id)
				{
					$variation_ids[] = $item->item_variation_id;
				}
				else
				{
					$item_ids[] = $item->item_id;
				}
				$key = $item->receiving_id.'|'.$item->item_id;
				$items_expire[$key] = $item->expire_date ? date(get_date_format(), strtotime($item->expire_date)) : FALSE;
			}
		}
		
		$data = array();
		$this->load->helper('items');
		
		$items_barcodes = array();
		
		if (!empty($item_ids))
		{
			$items_barcodes = get_items_barcode_data(implode('~',$item_ids));
		}
		
		$variations_barcodes = array();
		if (!empty($variation_ids))
		{
			$variations_barcodes = get_item_variations_barcode_data(implode('~',$variation_ids));
		}
		$data['items'] = array_merge($items_barcodes, $variations_barcodes);
		$data['items_expire'] = $items_expire;
		$data['from_recv'] = $recv_id;
		$data['excel_url'] = site_url('items/generate_barcodes_labels_from_recv_excel/'.$recv_id);
		$this->load->view("barcode_labels", $data);
	}
	
	function generate_barcodes_labels_from_recv_excel($recv_id)
	{
		$this->load->model('Item_taxes');
		$this->load->model('Item_location');
		$this->load->model('Item_location_taxes');
		$this->load->model('Item_taxes_finder');
		$this->load->model('Receiving');
		
		$item_ids = array();
		$items_expire = array();
		$variation_ids = array();
		
		foreach($this->Receiving->get_receiving_items($recv_id)->result() as $item)
		{
			for($k = 0; $k< abs((int)$item->quantity_purchased);$k++)
			{
				if ($item->item_variation_id)
				{
					$variation_ids[] = $item->item_variation_id;
				}
				else
				{
					$item_ids[] = $item->item_id;
				}
				$key = $item->receiving_id.'|'.$item->item_id;
				$items_expire[$key] = $item->expire_date ? date(get_date_format(), strtotime($item->expire_date)) : FALSE;
			}
		}
		
		$data = array();
		$this->load->helper('items');
		
		$items_barcodes = array();
		
		if (!empty($item_ids))
		{
			$items_barcodes = get_items_barcode_data(implode('~',$item_ids));
		}
		
		$variations_barcodes = array();
		if (!empty($variation_ids))
		{
			$variations_barcodes = get_item_variations_barcode_data(implode('~',$variation_ids));
		}
		$data = array_merge($items_barcodes, $variations_barcodes);
		
		
		$export_data[] = array(lang('common_item_number'),lang('common_name'), lang('common_description'),lang('common_unit_price'));
		foreach($data as $row)
		{
			$data = trim(strip_tags($row['name']));
			$price = substr($data,0,strpos($data,' '));
			$name = str_replace($price.' ','',$data);
			$description = $row['description'];
			$export_data[] = array($row['id'],$name,$description,$price);
		}
		
		$this->load->helper('spreadsheet');
		array_to_spreadsheet($export_data,'barcode_export.'.($this->config->item('spreadsheet_format') == 'XLSX' ? 'xlsx' : 'csv'));
		
	}
	
	function generate_barcodes_from_count($count_id, $skip=0)
	{
		$this->load->model('Item_taxes');
		$this->load->model('Item_location');
		$this->load->model('Item_location_taxes');
		$this->load->model('Item_taxes_finder');
		$this->load->model('Item');
		$this->load->model('Inventory');
		$item_ids = array();
		$variation_ids = array();
		
		foreach($this->Inventory->get_items_counted($count_id,10000) as $item)
		{
			
			for($k = 0; $k< abs((int)$item['count']);$k++)
			{
				if ($item['item_variation_id'])
				{
					$variation_ids[] = $item['item_variation_id'];
				}
				else
				{
					$item_ids[] = $item['item_id'];
				}
			}
		}
	
		$data = array();
		$this->load->helper('items');
		$items_barcodes = array();
		
		if (!empty($item_ids))
		{
			$items_barcodes = get_items_barcode_data(implode('~',$item_ids));
		}
		
		$variations_barcodes = array();
		if (!empty($variation_ids))
		{
			$variations_barcodes = get_item_variations_barcode_data(implode('~',$variation_ids));
		}
		$data['items'] = array_merge($items_barcodes, $variations_barcodes);
		$data['scale'] = 1;
		$data['from_count'] = $count_id;
		$data['skip'] = $skip;
		
		$this->load->view("barcode_sheet", $data);
	}
	
	
	function generate_barcodes_labels_from_count($count_id)
	{
		$this->load->model('Item_taxes');
		$this->load->model('Item_location');
		$this->load->model('Item_location_taxes');
		$this->load->model('Item_taxes_finder');
		$this->load->model('Item');
		$this->load->model('Inventory');
		$item_ids = array();
		$variation_ids = array();
		
		foreach($this->Inventory->get_items_counted($count_id,10000) as $item)
		{
			
			for($k = 0; $k< abs((int)$item['count']);$k++)
			{
				if ($item['item_variation_id'])
				{
					$variation_ids[] = $item['item_variation_id'];
				}
				else
				{
					$item_ids[] = $item['item_id'];
				}
			}
		}
	
		
		$data = array();
		$this->load->helper('items');
		
		$items_barcodes = array();
		
		if (!empty($item_ids))
		{
			$items_barcodes = get_items_barcode_data(implode('~',$item_ids));
		}
		
		$variations_barcodes = array();
		if (!empty($variation_ids))
		{
			$variations_barcodes = get_item_variations_barcode_data(implode('~',$variation_ids));
		}
		$data['items'] = array_merge($items_barcodes, $variations_barcodes);
		$data['from_count'] = $count_id;
		$this->load->view("barcode_labels", $data);
	}
	

	function bulk_edit()
	{
		$this->load->model('Supplier');
		$this->load->model('Tier');
		$this->load->model('Tax_class');
		$this->check_action_permission('add_update');		
		$this->load->helper('report');
        $data = array();
		
		$data['tax_classes'] = array();
		$data['tax_classes'][''] = lang('common_do_nothing');
		
		foreach($this->Tax_class->get_all()->result_array() as $tax_class)
		{
			$data['tax_classes'][$tax_class['id']] = $tax_class['name'];
		}
		
		
		$suppliers = array('' => lang('common_do_nothing'), '-1' => lang('common_none'));
		foreach($this->Supplier->get_all()->result_array() as $row)
		{
			$suppliers[$row['person_id']] = $row['company_name']. ' ('.$row['first_name'] .' '. $row['last_name'].')';
		}
		$data['suppliers'] = $suppliers;
		$data['categories'][''] = lang('common_do_nothing');
		$data['manufacturers'][''] = lang('common_do_nothing');

		$this->load->model('Manufacturer');
		$manufacturers = array('' => lang('common_do_nothing'), '-1' => lang('common_none'));
		foreach($this->Manufacturer->get_all() as $id => $row)
		{
			$manufacturers[$id] = $row['name'];
		}
		$data['manufacturers'] = $manufacturers;
		
		
		$categories = $this->Category->sort_categories_and_sub_categories($this->Category->get_all_categories_and_sub_categories());
		foreach($categories as $key=>$value)
		{
			$name = $this->config->item('show_full_category_path') ? str_repeat('&nbsp;&nbsp;', $value['depth']).$this->Category->get_full_path($key) : str_repeat('&nbsp;&nbsp;', $value['depth']).$value['name'];
			$data['categories'][$key] = $name;
		}
				
		$data['item_cost_price_choices'] = array(
			''=>lang('common_do_nothing'), 
			'fixed'=>lang('common_fixed_price'), 
			'percent'=>lang('items_increase_decrease_percent'),			
		);
		
		
		$data['disable_loyalty_choices'] = array(			
			''=>lang('common_do_nothing'), 
			'0' => lang('common_no'), 
			'1' => lang('common_yes')
		);
		
		
		$data['change_cost_price_during_sale_choices'] = array(
			''=>lang('common_do_nothing'), 
			'0' => lang('common_no'), 
			'1' => lang('common_yes'));
	
		$data['change_is_ebt_item_during_sale_choices'] = array(
			''=>lang('common_do_nothing'), 
			'0' => lang('common_no'), 
			'1' => lang('common_yes'));	

			$data['item_unit_price_choices'] = array(
				''=>lang('common_do_nothing'), 
				'fixed'=>lang('common_fixed_price'), 
				'percent'=>lang('items_increase_decrease_percent'),			
			);
		
			$data['verify_age_choices'] = array(
				''=>lang('common_do_nothing'), 
				'0' => lang('common_no'), 
				'1' => lang('common_yes')	
			);

			$data['is_barcoded_choices'] = array(
				''=>lang('common_do_nothing'), 
				'0' => lang('common_no'), 
				'1' => lang('common_yes')	
			);
		
		
		$data['item_promo_price_choices'] = array(
			''=>lang('common_do_nothing'), 
			'fixed'=>lang('common_fixed_price'), 
			'percent'=>lang('items_increase_decrease_percent'),			
			'remove_promo'=>lang('items_remove_promo_price'),			
		);
				
		$data['override_default_commission_choices'] = array(			
			''=>lang('common_do_nothing'), 
			'0' => lang('common_no'), 
			'1' => lang('common_yes'));
		
		$data['override_default_tax_choices'] = array(
			''=>lang('common_do_nothing'), 
			'0' => lang('common_no'), 
			'1' => lang('common_yes'));
			
		$data['allow_alt_desciption_choices'] = array(
			''=>lang('common_do_nothing'),
			1 =>lang('items_change_all_to_allow_alt_desc'),
			0 =>lang('items_change_all_to_not_allow_allow_desc'));
	 
       
		$data['serialization_choices'] = array(
			''=>lang('common_do_nothing'),
			1 =>lang('items_change_all_to_serialized'),
			0 =>lang('items_change_all_to_unserialized'));

		$data['tax_included_choices'] = array(
				''=>lang('common_do_nothing'),
				'0' => lang('common_no'), 
				'1' => lang('common_yes'));
			
		$data['is_ecommerce_choices'] = array(
				''=>lang('common_do_nothing'),
				'0' => lang('common_no'), 
				'1' => lang('common_yes'));
		
		$data['is_service_choices'] = array(
			''=>lang('common_do_nothing'),
			'0' => lang('common_no'), 
			'1' => lang('common_yes'));
		
			$data['disable_from_price_rules_choices']= array(
			''=>lang('common_do_nothing'),
			'0' => lang('common_no'), 
			'1' => lang('common_yes'));
		
			$data['inactive_choices'] = array(
				''=>lang('common_do_nothing'),
				'0' => lang('common_no'), 
				'1' => lang('common_yes'));
			
			$data['favorite_choices'] = array(
				''=>lang('common_do_nothing'),
				'0' => lang('common_no'), 
				'1' => lang('common_yes'));
			
			$data['ecommerce_shipping_classes'] = array('' => lang('common_none'));
		
			if ($this->config->item('woo_shipping_classes'))
			{
				$woo_shipping_classes = unserialize($this->config->item('woo_shipping_classes'));
			
				foreach($woo_shipping_classes as $shipping_class)
				{
					$data['ecommerce_shipping_classes'][$shipping_class['slug']] = $shipping_class['name'];
				}
			}
			
		$this->load->view("items/form_bulk", $data);
	}
	
	function save_item_location($item_id=-1)
	{
		$this->check_action_permission('add_update');
		
		$redirect=$this->input->post('redirect');
		$progression_post = $this->input->post('progression');
		$quick_edit_post = $this->input->post('quick_edit_post');
		$progression= !empty($progression_post) ? 1 : null;
		$quick_edit= !empty($quick_edit_post) ? 1 : null;
		
		$this->load->model('Item');
		$this->load->model('Item_location');
		$this->load->model('Item_location_taxes');
		$this->load->model('Item_variation_location');
		$item_info = $this->Item->get_info($item_id);
		
		if(!$item_info)
		{
			echo json_encode(array('success'=>false,'message'=>lang('common_error_adding_updating'),'item_id'=>-1));
		}
		
		if ($this->input->post('locations'))
		{
			foreach($this->input->post('locations') as $location_id => $item_location_data)
			{	
				if($this->Employee->is_location_authenticated($location_id)){

					$override_prices = isset($item_location_data['override_prices']) && $item_location_data['override_prices'];

					$data = array(
						'location_id' => $location_id,
						'item_id' => $item_id,
						'location' => $item_location_data['location'],
						'cost_price' => $override_prices && $item_location_data['cost_price'] != '' ? $item_location_data['cost_price'] : NULL,
						'unit_price' => $override_prices && $item_location_data['unit_price'] != '' ? $item_location_data['unit_price'] : NULL,
						'promo_price' => $override_prices && $item_location_data['promo_price'] != '' ? $item_location_data['promo_price'] : NULL,
						'start_date' => $override_prices && $item_location_data['promo_price']!='' && $item_location_data['start_date'] != '' ? date('Y-m-d', strtotime($item_location_data['start_date'])) : NULL,
						'end_date' => $override_prices && $item_location_data['promo_price'] != '' && $item_location_data['end_date'] != '' ? date('Y-m-d', strtotime($item_location_data['end_date'])) : NULL,
						'reorder_level' => isset($item_location_data['reorder_level']) && $item_location_data['reorder_level'] != '' ? $item_location_data['reorder_level'] : NULL,
						'replenish_level' => isset($item_location_data['replenish_level']) && $item_location_data['replenish_level'] != '' ? $item_location_data['replenish_level'] : NULL,
						'override_default_tax'=> isset($item_location_data['override_default_tax'] ) && $item_location_data['override_default_tax'] != '' ? $item_location_data['override_default_tax'] : 0,
						'tax_class_id'=> isset($item_location_data['tax_class']) && $item_location_data['tax_class'] ? $item_location_data['tax_class'] : NULL,
					);
					
					$this->Item_location->save($data, $item_id,$location_id);
					
					if (isset($item_location_data['ban_from_location']) && $item_location_data['ban_from_location'])
					{
						$this->Item->add_ban_location($item_id,$location_id);
					}
					else
					{
						$this->Item->remove_ban_location($item_id,$location_id);					
					}

					if (isset($item_location_data['hide_from_grid']) && $item_location_data['hide_from_grid'])
					{
						$this->Item->add_hidden_item($item_id,$location_id);
					}
					else
					{
						$this->Item->remove_hidden_item($item_id,$location_id);					
					}
					
					if (isset($item_location_data['item_tier']))
					{
						$tier_type = $item_location_data['tier_type'];

						foreach($item_location_data['item_tier'] as $tier_id => $price_or_percent)
						{
							//If we are overriding prices and we have a price/percent, add..otherwise delete
							if ($override_prices && $price_or_percent !== '')
							{				
								$tier_data=array('tier_id'=>$tier_id);
								$tier_data['item_id'] = isset($item_data['item_id']) ? $item_data['item_id'] : $item_id;
								$tier_data['location_id'] = $location_id;
							
								if ($tier_type[$tier_id] == 'unit_price')
								{
									$tier_data['unit_price'] = $price_or_percent;
									$tier_data['percent_off'] = NULL;
									$tier_data['cost_plus_percent'] = NULL;
									$tier_data['cost_plus_fixed_amount'] = NULL;
								}
								elseif($tier_type[$tier_id] == 'percent_off')
								{
									$tier_data['percent_off'] = (float)$price_or_percent;
									$tier_data['unit_price'] = NULL;
									$tier_data['cost_plus_percent'] = NULL;
									$tier_data['cost_plus_fixed_amount'] = NULL;
								}
								elseif($tier_type[$tier_id] == 'cost_plus_percent')
								{
									$tier_data['percent_off'] = NULL;
									$tier_data['unit_price'] = NULL;
									$tier_data['cost_plus_percent'] = (float)$price_or_percent;
									$tier_data['cost_plus_fixed_amount'] = NULL;
								}
								elseif($tier_type[$tier_id] == 'cost_plus_fixed_amount')
								{
									$tier_data['percent_off'] = NULL;
									$tier_data['unit_price'] = NULL;
									$tier_data['cost_plus_percent'] = NULL;
									$tier_data['cost_plus_fixed_amount'] = (float)$price_or_percent;
								}
								

								$this->Item_location->save_item_tiers($tier_data,$item_id, $location_id);
							}
							else
							{
								$this->Item_location->delete_tier_price($tier_id, $item_id, $location_id);
							}

						}
					}
					
					
					if (isset($item_location_data['item_variations']))
					{
						foreach($item_location_data['item_variations'] as $item_variation_id=>$item_variation_location_data)
						{
							
							if ($item_variation_location_data['reorder_level']==='')
							{
								$item_variation_location_data['reorder_level'] = NULL;
							}
							
							if ($item_variation_location_data['replenish_level']==='')
							{
								$item_variation_location_data['replenish_level'] = NULL;
							}
							
							if ($item_variation_location_data['cost_price']==='')
							{
								$item_variation_location_data['cost_price'] = NULL;
							}
							
							if ($item_variation_location_data['unit_price']==='')
							{
								$item_variation_location_data['unit_price'] = NULL;
							}
							
							
							$this->Item_variation_location->save($item_variation_location_data, $item_variation_id, $location_id);
						}
					}
				
					if (isset($item_location_data['tax_names']))
					{
						$location_items_taxes_data = array();
						$tax_names = $item_location_data['tax_names'];
						$tax_percents = $item_location_data['tax_percents'];
						$tax_cumulatives = $item_location_data['tax_cumulatives'];
						for($k=0;$k<count($tax_percents);$k++)
						{
							if (is_numeric($tax_percents[$k]))
							{
								$location_items_taxes_data[] = array('name'=>$tax_names[$k], 'percent'=>$tax_percents[$k], 'cumulative' => isset($tax_cumulatives[$k]) ? $tax_cumulatives[$k] : '0' );
							}
						}
						$this->Item_location_taxes->save($location_items_taxes_data, $item_id, $location_id);
					}
				}else{

					if (isset($item_location_data['ban_from_location']) && $item_location_data['ban_from_location'])
					{
						$this->Item->add_ban_location($item_id,$location_id);
					}
					else
					{
						$this->Item->remove_ban_location($item_id,$location_id);					
					}
				}
			}
		}	
		
		if (isset($this->ecom_model))
		{			
			if ($item_info->is_ecommerce)
			{
				$this->ecom_model->save_item_from_phppos_to_ecommerce($item_id);
			}
		}
		
		$success_message = lang('common_items_successful_updating').' '.H($item_info->name);
		echo json_encode(array('success'=>true,'message'=>$success_message,'item_id'=>$item_id, 'redirect' => $redirect, 'progression' => $progression));
		
	}
	
	function save_item_pricing($item_id=-1)
	{
		$this->check_action_permission('add_update');
		$this->check_action_permission('edit_prices');
		
		$redirect=$this->input->post('redirect');
		$progression_post = $this->input->post('progression');
		$quick_edit_post = $this->input->post('quick_edit_post');
		$progression= !empty($progression_post) ? 1 : null;
		$quick_edit= !empty($quick_edit_post) ? 1 : null;
		
		$attributes = $this->input->post('attributes') ? $this->input->post('attributes') : array();
		$variations = $this->input->post('variations') ? $this->input->post('variations') : array();
		
		$this->load->model('Item');
		$this->load->model('Item_taxes');
		
		$item_info = $this->Item->get_info($item_id);
		
		if(!$item_info)
		{
			echo json_encode(array('success'=>false,'message'=>lang('common_error_adding_updating'),'item_id'=>-1));
		}
				
		$item_data = array(
			'cost_price'=>$this->input->post('cost_price'),
			'change_cost_price' => $this->input->post('change_cost_price') ? $this->input->post('change_cost_price') : 0,
	 		'unit_price'=>$this->input->post('unit_price'),
			'tax_included'=>$this->input->post('tax_included') ? $this->input->post('tax_included') : 0,
			'promo_price'=>$this->input->post('promo_price') ? $this->input->post('promo_price') : NULL,
			'start_date'=>$this->input->post('start_date') ? date('Y-m-d', strtotime($this->input->post('start_date'))) : NULL,
			'end_date'=>$this->input->post('end_date') ?date('Y-m-d', strtotime($this->input->post('end_date'))) : NULL,
			'min_edit_price'=>$this->input->post('min_edit_price') !== '' ? $this->input->post('min_edit_price') : NULL,
			'max_edit_price'=>$this->input->post('max_edit_price') !== '' ? $this->input->post('max_edit_price') : NULL,
			'max_discount_percent'=>$this->input->post('max_discount_percent') !== '' ? $this->input->post('max_discount_percent') : NULL,		
			'override_default_tax'=> $this->input->post('override_default_tax') ? $this->input->post('override_default_tax') : 0,
			'tax_class_id'=> $this->input->post('tax_class') ? $this->input->post('tax_class') : NULL,
			'allow_price_override_regardless_of_permissions' => $this->input->post('allow_price_override_regardless_of_permissions') ? 1 : 0,
			'only_integer' => $this->input->post('only_integer') ? 1 : 0,
			'disable_from_price_rules' => $this->input->post('disable_from_price_rules') ? 1 : 0,
		);
		
		if ($this->input->post('override_default_commission'))
		{
			if ($this->input->post('commission_type') == 'fixed')
			{
				$item_data['commission_fixed'] = (float)$this->input->post('commission_value');
				$item_data['commission_percent_type'] = '';
				$item_data['commission_percent'] = NULL;
			}
			else
			{
				$item_data['commission_percent'] = (float)$this->input->post('commission_value');
				$item_data['commission_percent_type'] = $this->input->post('commission_percent_type');
				$item_data['commission_fixed'] = NULL;
			}
		}
		else
		{
			$item_data['commission_percent'] = NULL;
			$item_data['commission_fixed'] = NULL;
			$item_data['commission_percent_type'] = '';
		}
		
		if($this->Item->save($item_data,$item_id))
		{
			$this->Item->set_last_edited($item_id);	
			$this->load->model("Item_variations");
			
			foreach($variations as $variation_id => $data)
			{
				$data['unit_price'] = $data['unit_price'] ? $data['unit_price'] : NULL;
				$data['cost_price'] = $data['cost_price'] ? $data['cost_price'] : NULL;
				$data['promo_price'] = $data['promo_price'] ? $data['promo_price'] : NULL;
				
				$data['start_date'] = $data['start_date'] ? date('Y-m-d', strtotime($data['start_date'])) : NULL;
				$data['end_date'] = $data['end_date'] ? date('Y-m-d', strtotime($data['end_date'])) : NULL;
				
				$this->Item_variations->save($data, $variation_id);
			}
				
			$tier_type = $this->input->post('tier_type');
			
			if ($this->input->post('item_tier'))
			{
				foreach($this->input->post('item_tier') as $tier_id => $price_or_percent)
				{
					if ($price_or_percent !== '')
					{				
						$tier_data=array('tier_id'=>$tier_id);
						$tier_data['item_id'] = isset($item_data['item_id']) ? $item_data['item_id'] : $item_id;

						if ($tier_type[$tier_id] == 'unit_price')
						{
							$tier_data['unit_price'] = $price_or_percent;
							$tier_data['percent_off'] = NULL;
							$tier_data['cost_plus_percent'] = NULL;
							$tier_data['cost_plus_fixed_amount'] = NULL;
						}
						elseif($tier_type[$tier_id] == 'percent_off')
						{
							$tier_data['percent_off'] = (float)$price_or_percent;
							$tier_data['unit_price'] = NULL;
							$tier_data['cost_plus_percent'] = NULL;
							$tier_data['cost_plus_fixed_amount'] = NULL;
						}
						elseif($tier_type[$tier_id] == 'cost_plus_percent')
						{
							$tier_data['percent_off'] = NULL;
							$tier_data['unit_price'] = NULL;
							$tier_data['cost_plus_percent'] = (float)$price_or_percent;
							$tier_data['cost_plus_fixed_amount'] = NULL;
						}
						elseif($tier_type[$tier_id] == 'cost_plus_fixed_amount')
						{
							$tier_data['percent_off'] = NULL;
							$tier_data['unit_price'] = NULL;
							$tier_data['cost_plus_percent'] = NULL;
							$tier_data['cost_plus_fixed_amount'] = (float)$price_or_percent;
						}
					
						$this->Item->save_item_tiers($tier_data,$item_id);
					}
					else
					{
						$this->Item->delete_tier_price($tier_id, $item_id);
					}
				
				}
			}
			
			$items_taxes_data = array();
			$tax_names = $this->input->post('tax_names');
			$tax_percents = $this->input->post('tax_percents');
			$tax_cumulatives = $this->input->post('tax_cumulatives');
			for($k=0;$k<count($tax_percents);$k++)
			{
				if (is_numeric($tax_percents[$k]))
				{
					$items_taxes_data[] = array('name'=>$tax_names[$k], 'percent'=>$tax_percents[$k], 'cumulative' => isset($tax_cumulatives[$k]) ? $tax_cumulatives[$k] : '0' );
				}
			}
			
			$this->Item_taxes->save($items_taxes_data, $item_id);
			
			//Ecommerce
			if (isset($this->ecom_model))
			{			
				if ($item_info->is_ecommerce)
				{
					$this->ecom_model->save_item_from_phppos_to_ecommerce($item_id);
				}
			}
			
			$success_message = lang('common_items_successful_updating').' '.H($item_info->name);
			
			echo json_encode(array('success'=>true,'message'=>$success_message,'item_id'=>$item_id, 'redirect' => $redirect, 'progression' => $progression, 'quick_edit' => $quick_edit));
		}
	}
	
	function get_values_for_attribute($attribute_id)
	{
		$this->load->model('Item_attribute_value');
		
		$values_data = $this->Item_attribute_value->get_values_for_attribute($attribute_id)->result_array();
			
		$values = array();
		foreach($values_data as $value)
		{
			$values[] = array('label'=> $value['name']);
		}
			
		echo json_encode($values);
	}
	

	
	function save_variations($item_id=-1)
	{
		
		$this->check_action_permission('add_update');
		
		$redirect=$this->input->post('redirect');
		$progression_post = $this->input->post('progression');
		$quick_edit_post = $this->input->post('quick_edit_post');
		$progression= !empty($progression_post) ? 1 : null;
		$quick_edit= !empty($quick_edit_post) ? 1 : null;
		
		$this->load->model('Item');
		$this->load->model('Item_attribute');
		$this->load->model('Item_attribute_value');
		$this->load->model('Item_variations');
		
		$item_info = $this->Item->get_info($item_id);
		$location_id = $this->Employee->get_logged_in_employee_current_location_id();
		
		if(!$item_info)
		{
			echo json_encode(array('success'=>false,'message'=>lang('common_error_adding_updating'),'item_id'=>-1));
		}
		
		$attributes_and_attr_values = $this->input->post('attributes') ? $this->input->post('attributes') : array();

		$attr_ids = array_keys($attributes_and_attr_values);
		
		$item_attributes_to_delete = array();
		$item_attributes_previous_result = $this->Item_attribute->get_attributes_for_item(isset($item_data['item_id']) ? $item_data['item_id'] : $item_id);
		
		$attributes_previous = array();
		
		foreach($item_attributes_previous_result as $item_attr_row)
		{
			$attributes_previous[] = $item_attr_row['id'];
		}
		
		$item_attributes_to_delete = array_diff($attributes_previous,$attr_ids);
						
		foreach($item_attributes_to_delete as $item_attr_to_delete)
		{
			$this->Item_attribute->delete_item_attribute(isset($item_data['item_id']) ? $item_data['item_id'] : $item_id, $item_attr_to_delete);
		}
		
	 	$this->Item_attribute->save_item_attributes($attr_ids, isset($item_data['item_id']) ? $item_data['item_id'] : $item_id);
		
		$save_item_attribute_values = array();
		
		foreach($attributes_and_attr_values as $attr_id => $attr_values)
		{
			$item_attribute_values_to_delete = array();
			$item_attribute_values_previous_result = $this->Item_attribute_value->get_attribute_values_for_item(isset($item_data['item_id']) ? $item_data['item_id'] : $item_id, $attr_id)->result_array();
			$attribute_values_previous = array();
			
			foreach($item_attribute_values_previous_result as $item_attr_val_row)
			{
				$attribute_values_previous[$item_attr_val_row['attribute_value_id']] = $item_attr_val_row['attribute_value_name'];
			}
			$attr_values_array = explode('|',$attr_values);
		
			$item_attribute_values_to_delete = array_keys(array_diff($attribute_values_previous,$attr_values_array));

			foreach($item_attribute_values_to_delete as $item_attr_value_to_delete)
			{
				$this->Item_attribute_value->delete_item_attribute_value(isset($item_data['item_id']) ? $item_data['item_id'] : $item_id, $item_attr_value_to_delete);
			}
						
			foreach($attr_values_array as $attr_value)
			{
				if ($attr_value)
				{
					//use save incase we want to allow term creation on variation page in future
					$attrbute_value_id = $this->Item_attribute_value->save($attr_value, $attr_id);
					$save_item_attribute_values[] = $attrbute_value_id;
				}
			}
		}
		
		$this->Item_attribute_value->save_item_attribute_values(isset($item_data['item_id']) ? $item_data['item_id'] : $item_id, $save_item_attribute_values);
		
		//variations
		if($this->input->post('item_variations_to_delete') && is_array($this->input->post('item_variations_to_delete')))
		{
			$item_variations_to_delete = $this->input->post('item_variations_to_delete');
							
			foreach($item_variations_to_delete as $item_variation_id)
			{
				$this->Item_variations->delete($item_variation_id);
			}
		}
		
		if ($this->input->post('item_variations') && is_array($this->input->post('item_variations')))
		{
			$item_variations = $this->input->post('item_variations');
			
			$names = $item_variations['name'];
			$attribute_values = $item_variations['attributes'];
			$item_numbers = $item_variations['item_number'];
			$is_ecommerce = isset($item_variations['is_ecommerce']) ? $item_variations['is_ecommerce'] : 1;
			$item_variation_ids = $item_variations['item_variation_id'];
			
			$data = array();
					
			foreach($item_variation_ids as $key => $item_variation_id)
			{
				$attribute_ids = array();
				$attribute_value_ids = array();
				
				if ($attribute_values[$key])
				{
					$attribute_value_ids = explode("|",$attribute_values[$key]);
				}
				
				$item_variation_id = isset($item_variation_id) && $item_variation_id ? $item_variation_id : NULL;
				
				
				$all_item_numbers = explode('|',$item_numbers[$key]);
				
				$data = array(
		 			'item_id' => isset($item_data['item_id']) ? $item_data['item_id'] : $item_id,
					'name' => $names[$key] == '' ? null : $names[$key],
					'item_number' => $item_numbers[$key] == '' ? null : $all_item_numbers[0],
					'is_ecommerce' => isset($is_ecommerce[$key]) && $is_ecommerce[$key] ? 1 : 0,
				);
								
				$item_variation_id = $this->Item_variations->save($data, $item_variation_id, $attribute_value_ids);
				
				if (count($all_item_numbers) > 1)
				{
					$var_additional_item_numbers = array_slice($all_item_numbers,1);
				}
				else
				{
					$var_additional_item_numbers = array();
				}
				$this->Additional_item_numbers->save_variation(isset($item_data['item_id']) ? $item_data['item_id'] : $item_id,$item_variation_id,$var_additional_item_numbers);				
			}								
		}
		
		
		$this->save_quantity_units($this->input->post('quantity_units_to_edit'), $this->input->post('quantity_units_to_delete'),$item_id);
		
		$modifier_ids = $this->input->post('modifiers') ? $this->input->post('modifiers') : array();
		$this->Item_modifier->item_save_modifiers(isset($item_data['item_id']) ? $item_data['item_id'] : $item_id,$modifier_ids);
			
		//Ecommerce
		if (isset($this->ecom_model))
		{
			if ($item_info->is_ecommerce)
			{
				$this->ecom_model->save_item_from_phppos_to_ecommerce($item_id);
			}
		}
		
		$success_message = lang('common_items_successful_updating').' '.H($item_info->name);
		
		$this->Item->set_last_edited($item_id);
		echo json_encode(array('success'=>true, 'reload' => true, 'message'=>$success_message,'item_id'=>$item_id, 'redirect' => $redirect, 'progression' => $progression, 'quick_edit' => $quick_edit));
		
	}
	
	function save_images($item_id=-1)
	{
		$this->check_action_permission('add_update');
		
		$redirect= $this->input->post('redirect');
		$progression_post = $this->input->post('progression');
		$quick_edit_post = $this->input->post('quick_edit_post');
		$progression= !empty($progression_post) ? 1 : null;
		$quick_edit= !empty($quick_edit_post) ? 1 : null;
		
		$this->load->model('Item');
		
		$item_info = $this->Item->get_info($item_id);
		
		//Delete Image
		if($this->input->post('del_images') && $item_id != -1)
		{
			foreach(array_keys($this->input->post('del_images')) as $image_id)
			{
				$this->Item->delete_image($image_id);
			}
		}
		
    $this->load->library('image_lib');
		
		if (isset($_FILES['image_files']))
		{
			$ignore = $this->input->post('ignore');
			
			for($k=0; $k<count($_FILES['image_files']['name']); $k++)
			{
				if(!empty($ignore) && in_array($k, $ignore))
				{
					continue;
				}
				
				$allowed_extensions = array('png', 'jpg', 'jpeg', 'gif');
				$extension = strtolower(pathinfo($_FILES['image_files']['name'][$k], PATHINFO_EXTENSION));
		    if (in_array($extension, $allowed_extensions))
		    {
					
			    $config['image_library'] = 'gd2';
			    $config['source_image']	= $_FILES['image_files']['tmp_name'][$k];
			    $config['create_thumb'] = FALSE;
			    $config['maintain_ratio'] = TRUE;
			    $config['width']	 = 1200;
			    $config['height']	= 900;
					$this->image_lib->initialize($config);
			    $this->image_lib->resize();
		   	 	$this->load->model('Appfile');
			    $image_file_id = $this->Appfile->save($_FILES['image_files']['name'][$k], file_get_contents($_FILES['image_files']['tmp_name'][$k]));
		  		$this->Item->add_image($item_id, $image_file_id);
					$last_image_id = $image_file_id;
				}
			}
		}
		
		$titles = $this->input->post('titles');
		$alt_texts = $this->input->post('alt_texts');
		$variations = $this->input->post('variations');
		$main_images = $this->input->post('main_image');
		
		if ($titles)
		{
			foreach(array_keys($titles) as $image_id)
			{
				$title = $titles[$image_id];
				$alt_text = $alt_texts[$image_id];
				$variation = $variations[$image_id] ? $variations[$image_id] : NULL;
				$main_image = isset($main_images[$image_id]) ? TRUE : FALSE;
  			$this->Item->save_image_metadata($image_id, $title, $alt_text, $variation);
				
				if ($main_image)
				{
					$item_image_data = array('main_image_id' => $image_id);
					$this->Item->set_last_edited($item_id);	
					
					$this->Item->save($item_image_data,$item_id);
				}
			}
		}
		else
		{
			if ($last_image_id)
			{
				$item_image_data = array('main_image_id' => $last_image_id);
				$this->Item->set_last_edited($item_id);	
				
				$this->Item->save($item_image_data,$item_id);
			}
		}
		
		//Ecommerce
		if (isset($this->ecom_model))
		{
			if ($item_info->is_ecommerce)
			{
				$this->ecom_model->save_item_from_phppos_to_ecommerce($item_id);
			}
		}
		
		$success_message = lang('common_items_successful_updating');
		echo json_encode(array('reload' => isset($_FILES['image_files']) || $this->input->post('del_images'),'success'=>true,'message'=>$success_message,'item_id'=>$item_id,'redirect' => $redirect, 'progression' => $progression));
		
	}

	function save($item_id=-1)
	{
		$this->check_action_permission('add_update');
		if($this->input->post('add_to_inventory')){
			$this->check_action_permission('edit_quantity');
		}
		$redirect= $this->input->post('redirect');
		$progression_post = $this->input->post('progression');
		$quick_edit_post = $this->input->post('quick_edit_post');
		$progression= !empty($progression_post) ? 1 : null;
		$quick_edit= !empty($quick_edit_post) ? 1 : null;
						
		$this->load->model('Item_taxes');
		
		if (!$this->Category->exists($this->input->post('category_id')))
		{
			if (!$category_id = $this->Category->get_category_id($this->input->post('category_id')))
			{
				$category_id = $this->Category->save($this->input->post('category_id'));
			}
		}	
		else
		{
			$category_id = $this->input->post('category_id');
		}
				
		$item_data = array(
			'name'=>$this->input->post('name'),
			'barcode_name'=>$this->input->post('barcode_name'),
			'description'=>$this->input->post('description'),
			'long_description'=>$this->input->post('long_description'),
			'info_popup'=>$this->input->post('info_popup') ? $this->input->post('info_popup') : NULL,
			'category_id'=>$category_id,
			'size'=>$this->input->post('size'),
			'supplier_id'=>$this->input->post('supplier_id')== -1 || $this->input->post('supplier_id') == '' ? null:$this->input->post('supplier_id'),
			'manufacturer_id'=>$this->input->post('manufacturer_id')== -1 || $this->input->post('manufacturer_id') == '' ? null:$this->input->post('manufacturer_id'),
			'item_number'=>$this->input->post('item_number')=='' ? null:$this->input->post('item_number'),
			'product_id'=>$this->input->post('product_id')=='' ? null:$this->input->post('product_id'),
			'ecommerce_product_id'=>$this->input->post('ecommerce_product_id') ? $this->input->post('ecommerce_product_id') : NULL,
			'is_service'=>$this->input->post('is_service') ? $this->input->post('is_service') : 0 ,
			'allow_alt_description'=>$this->input->post('allow_alt_description') ? $this->input->post('allow_alt_description') : 0 ,
			'is_serialized'=>$this->input->post('is_serialized') ? $this->input->post('is_serialized') : 0,
			'is_ebt_item'=> $this->input->post('is_ebt_item') ? $this->input->post('is_ebt_item') : 0,
			'is_ecommerce'=> $this->input->post('is_ecommerce') ? $this->input->post('is_ecommerce') : 0,
			'verify_age'=> $this->input->post('verify_age') ? 1 : 0,
			'required_age'=> $this->input->post('verify_age') ? $this->input->post('required_age') : NULL,
			'weight'=>$this->input->post('weight')=='' ? null:$this->input->post('weight'),
			'weight_unit'=>$this->input->post('weight')=='' ? null:$this->input->post('weight_unit'),
			'length'=>$this->input->post('length')=='' ? null:$this->input->post('length'),
			'width'=>$this->input->post('width')=='' ? null:$this->input->post('width'),
			'height'=>$this->input->post('height')=='' ? null:$this->input->post('height'),
			'ecommerce_shipping_class_id'=>$this->input->post('ecommerce_shipping_class_id') ? $this->input->post('ecommerce_shipping_class_id') : NULL,			
			'is_series_package'=> $this->input->post('is_series_package') ? $this->input->post('is_series_package') : 0,
			'is_barcoded'=> $this->input->post('is_barcoded') ? $this->input->post('is_barcoded') : 0,
			'item_inactive'=> $this->input->post('item_inactive') ? $this->input->post('item_inactive') : 0,
			'series_quantity'=>$this->input->post('series_quantity') ? $this->input->post('series_quantity') : NULL,		
			'series_days_to_use_within' => $this->input->post('series_days_to_use_within') ? $this->input->post('series_days_to_use_within') : NULL,	
			'is_favorite'=> $this->input->post('is_favorite') ? $this->input->post('is_favorite') : 0,
			'loyalty_multiplier'=> $this->input->post('loyalty_multiplier') ? $this->input->post('loyalty_multiplier') : NULL,
		);
		
		if ($this->input->post('default_quantity') !== '')
		{
			$item_data['default_quantity'] = $this->input->post('default_quantity');
		}
		else
		{
			$item_data['default_quantity'] = NULL;
		}
				
		for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
		{
			if ($this->Item->get_custom_field($k) !== FALSE)
			{			
				if ($this->Item->get_custom_field($k,'type') == 'checkbox')
				{
					$item_data["custom_field_{$k}_value"] = $this->input->post("custom_field_{$k}_value");
				}
				elseif($this->Item->get_custom_field($k,'type') == 'date')
				{
					$item_data["custom_field_{$k}_value"] = $this->input->post("custom_field_{$k}_value") !== '' ? strtotime($this->input->post("custom_field_{$k}_value")) : NULL;
				}
				elseif(isset($_FILES["custom_field_{$k}_value"]['tmp_name']) && $_FILES["custom_field_{$k}_value"]['tmp_name'])
				{
					
					if ($this->Item->get_custom_field($k,'type') == 'image')
					{
				    $this->load->library('image_lib');
					
						$allowed_extensions = array('png', 'jpg', 'jpeg', 'gif');
						$extension = strtolower(pathinfo($_FILES["custom_field_{$k}_value"]['name'], PATHINFO_EXTENSION));
				    if (in_array($extension, $allowed_extensions))
				    {
					    $config['image_library'] = 'gd2';
					    $config['source_image']	= $_FILES["custom_field_{$k}_value"]['tmp_name'];
					    $config['create_thumb'] = FALSE;
					    $config['maintain_ratio'] = TRUE;
					    $config['width']	 = 1200;
					    $config['height']	= 900;
							$this->image_lib->initialize($config);
					    $this->image_lib->resize();
				   	 	$this->load->model('Appfile');
					    $image_file_id = $this->Appfile->save($_FILES["custom_field_{$k}_value"]['name'], file_get_contents($_FILES["custom_field_{$k}_value"]['tmp_name']));
							$item_data["custom_field_{$k}_value"] = $image_file_id;
						}
					}
					else
					{
			   	 	$this->load->model('Appfile');
						
				    $custom_file_id = $this->Appfile->save($_FILES["custom_field_{$k}_value"]['name'], file_get_contents($_FILES["custom_field_{$k}_value"]['tmp_name']));
						$item_data["custom_field_{$k}_value"] = $custom_file_id;
					}
				}
				elseif($this->Item->get_custom_field($k,'type') != 'image' && $this->Item->get_custom_field($k,'type') != 'file')
				{
					$item_data["custom_field_{$k}_value"] = $this->input->post("custom_field_{$k}_value");
				}
			}
		}
		
		if ($this->config->item('enable_customer_loyalty_system') && $this->config->item('loyalty_option') == 'advanced')
		{
			$item_data['disable_loyalty'] = $this->input->post('disable_loyalty') ? $this->input->post('disable_loyalty') : 0;
		}
		
		if ($this->config->item('loyalty_option') == 'advanced')
		{
			$item_data['loyalty_multiplier'] = $this->input->post('loyalty_multiplier') ? $this->input->post('loyalty_multiplier') : NULL;
		}
		
		
		$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
		$cur_item_info = $this->Item->get_info($item_id);
		
		if ($cur_item_info->is_ecommerce)
		{
			$was_ecommerce_item = TRUE;
		}
			
			
		//New item commission and prices include tax default values need to be set as database doesn't do this for us
		if ($item_id == -1)
		{
			$item_data['commission_percent'] = NULL;
			$item_data['commission_fixed'] = NULL;
			$item_data['commission_percent_type'] = '';
			$item_data['tax_included'] = $this->config->item('prices_include_tax') ? 1 : 0;
			$item_data['reorder_level'] = $this->config->item('default_reorder_level_when_creating_items') ? $this->config->item('default_reorder_level_when_creating_items') : NULL;
			$item_data['expire_days'] = $this->config->item('default_days_to_expire_when_creating_items') ? $this->config->item('default_days_to_expire_when_creating_items') : NULL;
			
		}
		if($this->Item->save($item_data,$item_id))
		{
			$this->Item->set_last_edited($item_id);	
			
			$this->Tag->save_tags_for_item(isset($item_data['item_id']) ? $item_data['item_id'] : $item_id, $this->input->post('tags'));
			
			$success_message = '';
			
			//New item
			if($item_id==-1)
			{	
				$success_message = lang('common_successful_adding').' '.H($item_data['name']);
				$this->session->set_flashdata('manage_success_message', $success_message);
				$this->Appconfig->save('wizard_add_inventory',1);
				echo json_encode(array('reload' => false, 'success'=>true, 'message'=>$success_message,'item_id'=>$item_data['item_id'], 'redirect' => $redirect, 'progression' => $progression, 'quick_edit' => $quick_edit));
				$item_id = $item_data['item_id'];
			}
			else //previous item
			{
				$this->Appconfig->save('wizard_add_inventory',1);
				$success_message = lang('common_items_successful_updating').' '.H($item_data['name']);
				echo json_encode(array('reload' => true, 'success'=>true, 'message'=>$success_message,'item_id'=>$item_id,'redirect' => $redirect, 'progression' => $progression, 'quick_edit' => $quick_edit));
			}
			
			if ($this->input->post('additional_item_numbers') && is_array($this->input->post('additional_item_numbers')))
			{
				$this->Additional_item_numbers->save($item_id, $this->input->post('additional_item_numbers'));
			}
			else
			{
				$this->Additional_item_numbers->delete($item_id);
			}
			
			$this->load->model('Item_serial_number');
			if ($this->input->post('serial_numbers') && is_array($this->input->post('serial_numbers')))
			{
				$this->Item_serial_number->save($item_id, $this->input->post('serial_numbers'), $this->input->post('serial_number_cost_prices'), $this->input->post('serial_number_prices'),$this->input->post('serial_number_prices_variations'),$this->input->post('serials_to_delete'), $this->input->post('add_to_inventory'));
			}
			else
			{
				$this->Item_serial_number->delete($item_id);
			}
				
			if ($this->input->post('secondary_categories'))
			{
				foreach($this->input->post('secondary_categories') as $sec_category_id=>$category_id)
				{
					$this->Item->save_secondory_category(isset($item_data['item_id']) ? $item_data['item_id'] : $item_id,$category_id,$sec_category_id);
				}
			}
			
			if ($this->input->post('secondary_categories_to_delete'))
			{
				foreach($this->input->post('secondary_categories_to_delete') as $sec_category_id_to_delete)
				{
					$this->Item->delete_secondory_category($sec_category_id_to_delete);
				}
			}
						
			//Ecommerce
			if (isset($this->ecom_model))
			{					
				if ($item_data['is_ecommerce'])
				{
					$this->ecom_model->save_item_from_phppos_to_ecommerce($item_id);
				}
				elseif(isset($was_ecommerce_item) && $was_ecommerce_item)
				{
					$this->ecom_model->delete_item(isset($item_data['item_id']) ? $item_data['item_id'] : $item_id);
				}
			}
		}
		else //failure
		{
			echo json_encode(array('success'=>false,'message'=>lang('common_error_adding_updating').' '.
			$item_data['name'],'item_id'=>-1));
		}
	}

	function save_inventory($item_id=-1)
	{
		$this->load->model('Item_variations');
		$error = false;
		$this->check_action_permission('edit_quantity');		
		
		$redirect=$this->input->post('redirect');
		$progression_post = $this->input->post('progression');
		$quick_edit_post = $this->input->post('quick_edit_post');
		$progression= !empty($progression_post) ? 1 : null;
		$quick_edit= !empty($quick_edit_post) ? 1 : null;
		
		$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
		$cur_item_info = $this->Item->get_info($item_id);

		$location_id = $this->Employee->get_logged_in_employee_current_location_id();
		
		$item_variations = $this->input->post('item_variations');
		
		$item_data = array(
		'expire_days'=>$this->input->post('expire_days') ?  $this->input->post('expire_days') : NULL,
		'reorder_level'=>$this->input->post('reorder_level')!='' ? $this->input->post('reorder_level') : NULL,
		'replenish_level'=>$this->input->post('replenish_level')!='' ? $this->input->post('replenish_level') : NULL,
		);
		
		$this->Item->save($item_data,$item_id);
				
		if ($this->input->post("damaged_qty") && empty($item_variations))
		{
			$this->Item->save_damaged_qty(date('Y-m-d H:i:s'),$this->input->post("damaged_qty"),$this->input->post("damaged_reason") ? $this->input->post("damaged_reason") : NULL,$item_id,NULL,$location_id,NULL, $this->input->post('trans_comment'));	
		}
		
		if(!empty($item_variations))
		{
			$this->load->model('Item_variation_location');
						
			$ecom_item_variation_data = array();
			
			foreach($item_variations as $item_variation_id => $item_variation)
			{
				
				$variation_data = array(
					'reorder_level' => $item_variation['reorder_level'] == '' ? null : $item_variation['reorder_level'],
					'replenish_level' => $item_variation['replenish_level'] == '' ? null : $item_variation['replenish_level']);
				
				$this->Item_variations->save($variation_data, $item_variation_id);
								
				if ($damaged_qty = $item_variation['damaged_qty'])
				{
					$damaged_reason = $item_variation['damaged_reason'];
					
					$this->Item->save_damaged_qty(date('Y-m-d H:i:s'),$damaged_qty,$damaged_reason,$item_id,$item_variation_id,$location_id, NULL, $item_variation['comments']);	
				}
				
				$cur_item_variation_location_info = $this->Item_variation_location->get_info($item_variation_id);
				
				if ($item_variation['add_subtract']!=='')
				{			
					$inv_data = array
					(
						'trans_date'=>date('Y-m-d H:i:s'),
						'trans_items'=>$item_id,
						'item_variation_id'=>$item_variation_id,
						'trans_user'=>$employee_id,
						'trans_comment'=>$item_variation['comments'],
						'trans_inventory'=>$item_variation['add_subtract'],
						'location_id'=> $location_id,
						'trans_current_quantity' => $item_variation['add_subtract'] ? ($cur_item_variation_location_info->quantity ? $cur_item_variation_location_info->quantity : 0) + $item_variation['add_subtract'] : 0,
					);
		
					$this->Inventory->insert($inv_data);
				
					//Update stock quantity
					$quantity = $item_variation['add_subtract'] ? ($cur_item_variation_location_info->quantity ? $cur_item_variation_location_info->quantity : 0) + $item_variation['add_subtract'] : 0;
					if(!$this->Item_variation_location->save_quantity($quantity,$item_variation_id))
					{		
						$error = true;
					} 
				}
			}
			
			//Ecommerce							
			if (isset($this->ecom_model))
			{
				$total_locations_ecom_sync = count($this->Appconfig->get_ecommerce_locations());
				
				if ($cur_item_info->is_ecommerce && $location_id  == $this->ecom_model->ecommerce_store_location && $total_locations_ecom_sync == 1)
				{		
					if (strtolower(get_class($this->ecom_model)) == 'shopify')		
					{
						foreach(array_keys($item_variations) as $item_variation_id)
						{
							$cur_item_variation_location_info = $this->Item_variation_location->get_info($item_variation_id);
							$cur_item_variation_info = $this->Item_variations->get_info($item_variation_id);
							$stock_quantity  = $cur_item_variation_location_info->quantity;
							
							if ($cur_item_variation_info->is_ecommerce)
							{
								$ecommerce_inventory_item_id = $cur_item_variation_info->ecommerce_inventory_item_id;
								$ecom_item_data = array(
									'stock_quantity' => $stock_quantity,
									'ecommerce_inventory_item_id' => $ecommerce_inventory_item_id,
									'manage_stock' => true,
								);
						
								$this->ecom_model->update_item_from_phppos_to_ecommerce($item_id, $ecom_item_data);
							}
						}
					}
					else
					{
						$ecom_item_data = array('manage_stock' => false);
						$this->ecom_model->update_item_from_phppos_to_ecommerce($item_id, $ecom_item_data);
						$this->ecom_model->save_item_variations($item_id);
					}
				}
			}			
		} 
		else
		{
			$this->load->model('Item_location');
			$cur_item_location_info = $this->Item_location->get_info($item_id);
				
			$inv_data = array
			(
				'trans_date'=>date('Y-m-d H:i:s'),
				'trans_items'=>$item_id,
				'trans_user'=>$employee_id,
				'trans_comment'=>$this->input->post('trans_comment'),
				'trans_inventory'=>$this->input->post('add_subtract'),
				'location_id'=>$location_id,
				'trans_current_quantity' => ($cur_item_location_info->quantity ? $cur_item_location_info->quantity : 0) + ($this->input->post('add_subtract') ? $this->input->post('add_subtract') : 0),
			);
		
			$this->Inventory->insert($inv_data);
			
			//Update stock quantity
			if($this->Item_location->save_quantity(($cur_item_location_info->quantity ? $cur_item_location_info->quantity : 0) + ($this->input->post('add_subtract') ? $this->input->post('add_subtract') : 0),$item_id))
			{
				//Ecommerce
				if (isset($this->ecom_model))
				{
					$total_locations_ecom_sync = count($this->Appconfig->get_ecommerce_locations());
					
					if ($cur_item_info->is_ecommerce && $location_id  == $this->ecom_model->ecommerce_store_location && $total_locations_ecom_sync == 1)
					{
						$ecom_item_data = array(
							'stock_quantity' => ($cur_item_location_info->quantity ? $cur_item_location_info->quantity : 0) + ($this->input->post('add_subtract') ? $this->input->post('add_subtract') : 0),
							'manage_stock' => true,
						);
						
						$this->ecom_model->update_item_from_phppos_to_ecommerce($item_id, $ecom_item_data);
					}
				}
			}
			else//failure
			{
				$error = true;
			}
		}
		
		if($error)
		{
			echo json_encode(array('success'=>false,'message'=>lang('common_error_adding_updating').' '.H($cur_item_info->name),'item_id'=>-1));
		} else {			
			echo json_encode(array('success'=>true,'message'=>lang('common_items_successful_updating').' '.H($cur_item_info->name),'item_id'=>$item_id, 'redirect' => $redirect, 'progression' => $progression, 'quick_edit' => $quick_edit,'reload' => true));
		}
	}

	function clear_state()
	{
		$params = $this->session->userdata('items_search_data');
		$this->session->set_userdata('items_search_data', array('offset' => 0, 'order_col' => 'item_id', 'order_dir' => 'desc', 'search' => FALSE, 'category_id' => FALSE, 'fields' => 'all','deleted' => $params['deleted']));
		redirect('items');
	}

	function bulk_update()
	{
		$this->load->model('Item_location');
		$this->load->model('Item_taxes');

		$cost_price_percent = FALSE;
		$unit_price_percent = FALSE;
		$promo_price_percent = FALSE;
		$promo_price_use_selling_price = FALSE;
		
		$this->db->trans_start();
		
		$this->check_action_permission('add_update');
		$items_to_update=$this->input->post('item_ids');
		$select_inventory=$this->get_select_inventory();

		//clears the total inventory selection
		$this->clear_select_inventory();

		$item_data = array('last_modified' => date('Y-m-d H:i:s'));

		
		foreach($_POST as $key=>$value)
		{
			if ($key == 'submit' || $key == 'tags' || $key == 'tier_types' || $key == 'tier_values' )
			{
				continue;
			}
			
			//This field is nullable, so treat it differently
			if ($key == 'supplier_id' || $key =='manufacturer_id')
			{
				if ($value!='')
				{
					$item_data["$key"]=$value == '-1' ? null : $value;
				}
			}
			elseif($value != '' && ($key == 'start_date' || $key == 'end_date'))
			{
				$item_data["$key"]=date('Y-m-d', strtotime($value));
			}
			elseif(($value != '' || !empty($_POST['is_service'])) && $key == 'quantity')
			{				
				$this->Item_location->update_multiple(array('quantity' => empty($_POST['is_service']) ? $value : NULL), $items_to_update,$select_inventory);		
			}
			elseif($value!='' && $key =='item_cost_price_method' && $this->input->post('cost_price'))
			{
				if ($value == 'fixed')
				{
					$item_data["cost_price"]=$this->input->post('cost_price');
				}
				elseif($value == 'percent')
				{
					$cost_price_percent = (float)$this->input->post('cost_price');
				}
			}
			elseif($value!='' && $key =='item_unit_price_method' && $this->input->post('unit_price'))
			{
				if ($value == 'fixed')
				{
					$item_data["unit_price"]=$this->input->post('unit_price');
				}
				elseif($value == 'percent')
				{
					$unit_price_percent = (float)$this->input->post('unit_price');
				}
			}
			elseif($value!='' && $key =='item_promo_price_method' && $this->input->post('promo_price'))
			{
				if ($value == 'fixed')
				{
					$item_data["promo_price"]=$this->input->post('promo_price');
				}
				elseif($value == 'percent')
				{
					$promo_price_percent = (float)$this->input->post('promo_price');
					$promo_price_use_selling_price = $this->input->post('use_selling_price');
				}
			}
			elseif($value!='' && $key =='item_promo_price_method' && $this->input->post('item_promo_price_method')  == 'remove_promo')
			{
				$item_data["promo_price"] = NULL;
				$item_data["start_date"] = NULL;
				$item_data["end_date"] = NULL;
			}
			elseif($value!='' and !(in_array($key, array('cost_price', 'unit_price','promo_price','item_cost_price_method','item_unit_price_method','item_promo_price_method','item_ids', 'tax_names', 'tax_percents', 'tax_cumulatives', 'select_inventory', 'commission_value', 'commission_type', 'commission_percent_type', 'override_default_commission','use_selling_price'))))
			{
				$item_data["$key"]=$value;
			}
		}
		
		//If we have any of the percents to update then we will update them (one or more)
		if ($cost_price_percent || $unit_price_percent || $promo_price_percent)
		{			
			$this->Item->update_multiple_percent($items_to_update,$select_inventory,$cost_price_percent, $unit_price_percent, $promo_price_percent, $promo_price_use_selling_price);
		}
		
		$this->Item->update_tiers($items_to_update,$select_inventory, $this->input->post('tier_types'), $this->input->post('tier_values'));
		
		if ($this->input->post('override_default_commission')!= '')
		{
			if ($this->input->post('override_default_commission') == 1)
			{
				if ($this->input->post('commission_type') == 'fixed')
				{
					$item_data['commission_fixed'] = (float)$this->input->post('commission_value');
					$item_data['commission_percent_type'] = '';
					$item_data['commission_percent'] = NULL;
				}
				else
				{
					$item_data['commission_percent'] = (float)$this->input->post('commission_value');
					$item_data['commission_percent_type'] = $this->input->post('commission_percent_type');
					$item_data['commission_fixed'] = NULL;
				}
			}
			else
			{
				$item_data['commission_percent'] = NULL;
				$item_data['commission_fixed'] = NULL;
				$item_data['commission_percent_type'] = '';
				
			}
		}
	
		//Item data could be empty if tax information is being updated
		if(empty($item_data) || $this->Item->update_multiple($item_data,$items_to_update,$select_inventory))
		{
			//Only update tax data of we are override taxes
			if (isset($item_data['override_default_tax']) && $item_data['override_default_tax'])
			{
				$items_taxes_data = array();
				$tax_names = $this->input->post('tax_names');
				$tax_percents = $this->input->post('tax_percents');
				$tax_cumulatives = $this->input->post('tax_cumulatives');

				for($k=0;$k<count($tax_percents);$k++)
				{
					if (is_numeric($tax_percents[$k]))
					{
						$items_taxes_data[] = array('name'=>$tax_names[$k], 'percent'=>$tax_percents[$k], 'cumulative' => isset($tax_cumulatives[$k]) ? $tax_cumulatives[$k] : '0' );
					}
				}

				if (!empty($items_taxes_data))
				{
					$this->Item_taxes->save_multiple($items_taxes_data, $items_to_update,$select_inventory);
				}
			}
						
			//Update all items with tags
			if ($this->input->post('tags'))
			{
				if ($select_inventory == 0)
				{
					foreach($items_to_update as $item_id)
					{
						$this->Tag->save_tags_for_item($item_id, $this->input->post('tags'));
					}
				}
				else
				{
					$params = $this->session->userdata('items_search_data') ? $this->session->userdata('items_search_data') : array('offset' => 0, 'order_col' => 'name', 'order_dir' => 'asc', 'search' => FALSE, 'category_id' => FALSE, 'fields' => 'all','deleted' => 0);
					$total_items = $this->Item->count_all($params['deleted']);
					$result = $this->Item->search(isset($params['search']) ? $params['search'] : '',isset($params['deleted']) ? $params['deleted'] : 0, isset($params['category_id']) ? $params['category_id'] : '',$total_items,0,'name','asc', isset($params['fields']) ? $params['fields']: 'all');
					foreach($result->result() as $item)
					{
						$this->Tag->save_tags_for_item($item->item_id, $this->input->post('tags'));
					}
				}
			}
			echo json_encode(array('success'=>true,'message'=>lang('items_successful_bulk_edit')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('items_error_updating_multiple')));
		}
		
		$this->db->trans_complete();
	}

	function delete()
	{
		$this->check_action_permission('delete');
		$items_to_delete=$this->input->post('ids');
		$select_inventory=$this->get_select_inventory();
		$params = $this->session->userdata('items_search_data') ? $this->session->userdata('items_search_data') : array('offset' => 0, 'order_col' => 'name', 'order_dir' => 'asc', 'search' => FALSE, 'category_id' => FALSE, 'fields' => 'all','deleted' => 0);
		$total_rows= $select_inventory ? $this->Item->search_count_all(isset($params['search']) ? $params['search'] : '',isset($params['deleted']) ? $params['deleted'] : 0,isset($params['category_id']) ? $params['category_id'] : '',$this->Item->count_all(),isset($params['fields']) ? $params['fields']: 'all') : count($items_to_delete);
		//clears the total inventory selection
		$this->clear_select_inventory();
		
		$deleted_item_ids = $this->Item->delete_list($items_to_delete,$select_inventory);
		if($deleted_item_ids)
		{
			$new_count = $this->Item->search_count_all(isset($params['search']) ? $params['search'] : '', isset($params['deleted']) ? $params['deleted'] : 0,isset($params['category_id']) ? $params['category_id'] : '',$this->Item->count_all(), isset($params['fields']));
			
			echo json_encode(array('success'=>true,'message'=>lang('items_successful_deleted').' '.
			$total_rows.' '.lang('items_one_or_multiple'), 'total_rows'=> $new_count));
			
			//ecommerce 
			session_write_close();
			
			if ($this->config->item("ecommerce_platform"))
			{
				require_once (APPPATH."models/interfaces/Ecom.php");
				$ecom_model = Ecom::get_ecom_model();
				$this->Appconfig->save('ecommerce_cron_running',0);
				if(is_array($deleted_item_ids))
				{
					$ecom_model->delete_items($deleted_item_ids);
				} 
				elseif($select_inventory)
				{
					$ecom_model->delete_all();
				}
			}		
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('items_cannot_be_deleted')));
		}
	}
	
	function undelete()
	{
		//ecommerce 
		session_write_close();
		
		$this->check_action_permission('delete');		
		$items_to_undelete=$this->input->post('ids');
		$select_inventory=$this->get_select_inventory();
		$params = $this->session->userdata('items_search_data') ? $this->session->userdata('items_search_data') : array('offset' => 0, 'order_col' => 'name', 'order_dir' => 'asc', 'search' => FALSE, 'category_id' => FALSE, 'fields' => 'all','deleted' => 0);
		$total_rows= $select_inventory ? $this->Item->search_count_all(isset($params['search']) ? $params['search'] : '',isset($params['deleted']) ? $params['deleted'] : 0,isset($params['deleted']) ? $params['deleted'] : 0,isset($params['category_id']) ? $params['category_id'] : '',$this->Item->count_all(),isset($params['fields']) ? $params['fields']: 'all') : count($items_to_undelete);
		//clears the total inventory selection
		$this->clear_select_inventory();
		
		$undeleted_item_ids = $this->Item->undelete_list($items_to_undelete,$select_inventory);
		if($undeleted_item_ids)
		{
			$new_count = $this->Item->search_count_all(isset($params['search']) ? $params['search'] : '', isset($params['deleted']) ? $params['deleted'] : 0,isset($params['category_id']) ? $params['category_id'] : '',$this->Item->count_all(), isset($params['fields']));
			
			echo json_encode(array('success'=>true,'message'=>lang('items_successful_undeleted').' '.
			$total_rows.' '.lang('items_one_or_multiple'), 'total_rows'=> $new_count));
			
			if ($this->config->item("ecommerce_platform"))
			{
				require_once (APPPATH."models/interfaces/Ecom.php");
				$ecom_model = Ecom::get_ecom_model();
				if(is_array($undeleted_item_ids))
				{
					$ecom_model->undelete_items($undeleted_item_ids);
				} 
				elseif($select_inventory)
				{
					$ecom_model->undelete_all();
				}
			}
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('items_cannot_be_undeleted')));
		}
	}
	

	function _excel_get_header_row($include_location_columns = 0)
	{
		$logged_in_employee_info=$this->Employee->get_logged_in_employee_info();
		$authed_locations = $this->Employee->get_authenticated_location_ids($logged_in_employee_info->person_id);
		$has_cost_price_permission = $this->Employee->has_module_action_permission('items','see_cost_price', $this->Employee->get_logged_in_employee_info()->person_id);
		
		$this->load->model('Tier');
		
		$header_row = array();
	
		$header_row[] = lang('common_item_number');
		$header_row[] = lang('common_product_id');
		$header_row[] = lang('common_item_name');
		$header_row[] = lang('common_barcode_name');
		$header_row[] = lang('common_variation');
		$header_row[] = lang('items_quantity_unit_quantity');
		$header_row[] = lang('common_category');
		$header_row[] = lang('common_supplier_id');
		$header_row[] = lang('common_allow_price_override_regardless_of_permissions');
		$header_row[] = lang('common_disable_from_price_rules');
		$header_row[] = lang('common_only_integer');
		$header_row[] = lang('items_sold_in_a_series');
		$header_row[] = lang('common_series_quantity');
		$header_row[] = lang('common_series_days_to_use_within');
		$header_row[] = lang('common_is_barcoded');
		$header_row[] = lang('common_inactive');
		$header_row[] = lang('common_default_quantity');
		if ($has_cost_price_permission)
		{
			$header_row[] = lang('common_cost_price');
		}
		$header_row[] = lang('common_unit_price');
		
		if ($this->config->item('limit_manual_price_adj'))
		{
			$header_row[] = lang('common_min_edit_price');
			$header_row[] = lang('common_max_edit_price');
			$header_row[] = lang('common_max_discount_percent');
		}
		
		$header_row[] = lang('items_promo_price');
		$header_row[] = lang('items_promo_start_date');
		$header_row[] = lang('items_promo_end_date');
		
		foreach($this->Tier->get_all()->result() as $tier)
		{
			$header_row[] =$tier->name;
		}
	
		$header_row[] = lang('items_price_includes_tax');
		$header_row[] = lang('items_is_service');
		$header_row[] = lang('common_is_favorite');
		if (count($authed_locations) ==1)
		{
			$header_row[] = lang('items_quantity');
		}
		
		$header_row[] = lang('items_reorder_level');
		$header_row[] = lang('common_replenish_level');
		$header_row[] = lang('common_description');
		$header_row[] = lang('common_long_description');
		$header_row[] = lang('common_info_popup');
		$header_row[] = lang('items_weight');
		$header_row[] = lang('items_weight_unit');
		$header_row[] = lang('items_length');
		$header_row[] = lang('items_width');
		$header_row[] = lang('items_height');
		$header_row[] = lang('items_allow_alt_desciption');
		$header_row[] = lang('items_is_serialized');
		
		if (!$this->config->item('hide_size_field'))
		{
			$header_row[] = lang('common_size');
		}
		$header_row[] = lang('reports_commission');
		$header_row[] = lang('items_commission_percent_based_on_profit');
		$header_row[] = lang('common_tax_class');
		$header_row[] = lang('common_tags');
		$header_row[] = lang('items_days_to_expiration');
		$header_row[] = lang('common_change_cost_price_during_sale');
		$header_row[] = lang('common_manufacturer');
		
		if (count($authed_locations) ==1)
		{
			$header_row[] = lang('items_location_at_store');
		}
		
		if ($this->config->item('enable_customer_loyalty_system') && $this->config->item('loyalty_option') == 'advanced')
		{
			$header_row[] = lang('common_disable_loyalty');
		}

		if ($this->config->item('loyalty_option') == 'advanced')
		{
			$header_row[] = lang('common_loyalty_multiplier');
		}
		
		if ($this->config->item('enable_ebt_payments'))
		{
			$header_row[] = lang('common_ebt');			
		}
		
		if($this->config->item("ecommerce_platform"))
		{
			$header_row[] = lang('items_is_ecommerce');
		}
		
		if($this->config->item("verify_age_for_products"))
		{		
			$header_row[] = lang('common_requires_age_verification');
			$header_row[] = lang('common_required_age');
		}
		
		for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
		{
			if ($this->Item->get_custom_field($k) !== FALSE)
			{
				$header_row[] = $this->Item->get_custom_field($k);
			}
		}
		
		
		if ($include_location_columns)
		{
			foreach($authed_locations as $location_id)
			{
				$location_info = $this->Location->get_info($location_id);
				
				if (count($authed_locations) !=1)
				{
					$header_row[] = $location_info->name.' '.lang('items_quantity');
					$header_row[] = $location_info->name.' '.lang('items_location_at_store');
				}
				$header_row[] = $location_info->name.' '.lang('items_reorder_level');
				$header_row[] = $location_info->name.' '.lang('common_replenish_level');
				$header_row[] = $location_info->name.' '.lang('common_cost_price');
				$header_row[] = $location_info->name.' '.lang('common_unit_price');
				$header_row[] = $location_info->name.' '.lang('items_promo_price');
				$header_row[] = $location_info->name.' '.lang('items_promo_start_date');
				$header_row[] = $location_info->name.' '.lang('items_promo_end_date');
				
			}
		}
		
		return $header_row;
	}
	
	function excel($include_location_columns = 0)
	{
		$this->load->helper('report');
		$header_row = $this->_excel_get_header_row($include_location_columns);
		$this->load->helper('spreadsheet');
		array_to_spreadsheet(array($header_row),'items_import.'.($this->config->item('spreadsheet_format') == 'XLSX' ? 'xlsx' : 'csv'));
	}
	
	function inventory_print_list($summary_only = FALSE,$export_excel = 0)
	{
		$this->check_action_permission('view_inventory_print_list');
		$params = $this->session->userdata('items_search_data') ? $this->session->userdata('items_search_data') : array('offset' => 0, 'order_col' => 'name', 'order_dir' => 'asc', 'search' => FALSE, 'category_id' => FALSE, 'fields' => 'all','deleted' => 0);
		
		$search = $params['search'] ? $params['search'] : "";
		$category_id = $params['category_id'] ? $params['category_id'] : "";
		
		
		if ($search || $category_id)
		{
			$result_data = $this->Item->search($search,$params['deleted'],$category_id,$this->Item->search_count_all($search, $params['deleted'],$category_id,30000, $params['fields']),0,$params['order_col'],$params['order_dir'], $params['fields'])->result_object();
		}
		else
		{
			$result_data = $this->Item->get_all($params['deleted'],$this->Item->count_all($params['deleted']),0,$params['order_col'],$params['order_dir'])->result_object();
		}
		
		$data = array();
		
		$this->load->model('Item_variation_location');
		foreach($result_data as $row)
		{
			$data[] = array(
				'item_id' => $row->item_id,
				'is_variation' => FALSE,
				'name' => $row->name,
				'product_id' => $row->product_id,
				'category_id' => $row->category_id,
				'item_number' => $row->item_number,
				'supplier' => $row->supplier_company_name,
				'quantity' => $row->quantity,
			);
			
			$variations = $this->Item_variation_location->get_variations_with_quantity($row->item_id);
			
			if (count($variations) > 0 && $summary_only == FALSE)
			{
				foreach($variations as $var_id=>$var_info)
				{
					$data[] = array(
						'item_id' => $row->item_id.'#'.$var_id,
						'is_variation' => TRUE,
						'name' => $var_info['name'],
						'product_id' => '',
						'category_id' => $row->category_id,
						'item_number' => $var_info['item_number'],
						'supplier' => '',
						'quantity' => $var_info['quantity'],
					);
				}
			}
		}
		
		if ($export_excel)
		{
			$this->load->helper('spreadsheet');
			
			for($k=0;$k<count($data);$k++)
			{
				$data[$k]['category_id'] = $this->Category->get_full_path($data[$k]['category_id']);
				$data[$k]['quantity'] = to_quantity($data[$k]['quantity']);
				unset($data[$k]['is_variation']);
			}
			
			
			$header_row= array(
				'item_id' => lang('common_item_id'),
				'name' => lang('common_item_name'),
				'product_id' => lang('common_product_id'),
				'category_id' => lang('common_category'),
				'item_number' => lang('common_item_number'),
				'supplier' => lang('common_supplier'),
				'quantity' => lang('common_quantity'),
			);
			
			array_unshift($data,$header_row);
			
			array_to_spreadsheet($data,'inventory_list.'.($this->config->item('spreadsheet_format') == 'XLSX' ? 'xlsx' : 'csv'));
			
		}
		else
		{
			$this->load->view("items/inventory_print_list", array('items' => $data,'summary_only' => $summary_only));
		}
	}

	function excel_export($include_location_columns = 0) 
	{			
		$this->check_action_permission('excel_export');
		$to_export = array();
		
		foreach($this->_excel_get_header_row($include_location_columns) as $row)
		{
			$to_export[$row] = array();
		}
		
		$this->load->model('Item_location');
		
		$logged_in_employee_info=$this->Employee->get_logged_in_employee_info();
		$authed_locations = $this->Employee->get_authenticated_location_ids($logged_in_employee_info->person_id);
		
		$has_cost_price_permission = $this->Employee->has_module_action_permission('items','see_cost_price', $this->Employee->get_logged_in_employee_info()->person_id);
		ini_set('memory_limit','1024M');
		$this->load->model('Tier');
		$this->load->model('Manufacturer');
		$this->load->model('Tax_class');
		$this->load->model('Additional_item_numbers');
		$this->load->model('Item_attribute');
		$this->load->model('Item_attribute_value');
		$this->load->model('Item_variations');
		$this->load->model('Item_variation_location');
		
		set_time_limit(0);
		ini_set('max_input_time','-1');
		$params = $this->session->userdata('items_search_data') ? $this->session->userdata('items_search_data') : array('offset' => 0, 'order_col' => 'name', 'order_dir' => 'asc', 'search' => FALSE, 'category_id' => FALSE, 'fields' => 'all','deleted' => 0);
		
		$search = $params['search'] ? $params['search'] : "";
		$category_id = $params['category_id'] ? $params['category_id'] : "";
		
		//Filter based on search
		if ($search || $category_id)
		{
			$data = $this->Item->search($search,$params['deleted'],$category_id,$this->Item->search_count_all($search, $params['deleted'],$category_id,30000, $params['fields']),0,$params['order_col'],$params['order_dir'], $params['fields'])->result_object();
		}
		else
		{
			$data = $this->Item->get_all($params['deleted'],$this->Item->count_all($params['deleted']),0,$params['order_col'],$params['order_dir'])->result_object();
		}
		
		$this->load->model('Tax_class');
		
		$tax_classes_indexed_by_id = $this->Tax_class->get_tax_classes_indexed_by_id();
		$tier_prices = $this->Item->get_all_tiers_prices();
		$this->load->helper('report');
		
		$header_row = $this->_excel_get_header_row($include_location_columns);
		$header_row[] = lang('common_item_id');
		
		foreach($header_row as $hr)
		{
			$to_export[$hr][] = $hr;
		}
		
		$tiers = $this->Tier->get_all()->result();
		$categories = $this->Category->get_all_categories_and_sub_categories_as_indexed_by_category_id();
		
		$manufacturers = array();
		
		foreach($this->Manufacturer->get_all() as $id => $row)
		{
		 	$manufacturers[$id] = $row['name'];

		}
		
		$additional_item_numbers = $this->Additional_item_numbers->get_all(false);
		
		$all_item_ids = array();
		
		if ($include_location_columns)
		{
			foreach ($data as $r) 
			{
				$all_item_ids[] = $r->item_id;
			}
			
			foreach($authed_locations as $location_id)
			{
				//call this to force cache so when we export it is fast
				$this->Item_location->get_info($all_item_ids,$location_id, TRUE);
			}
		}
		foreach ($data as $r) 
		{
			if(isset($additional_item_numbers[$r->item_id]) && count($additional_item_numbers[$r->item_id]) > 0)
			{
				foreach($additional_item_numbers[$r->item_id] as $item_num)
				{
					if($r->item_number)
					{
						$r->item_number .= "|";
					}
					$r->item_number .= $item_num;
				}
				
			}
			
			$to_export[lang('common_item_number')][] = $r->item_number;
			$to_export[lang('common_product_id')][] = $r->product_id;
			$to_export[lang('common_item_name')][] = $r->name;
			$to_export[lang('common_barcode_name')][] = $r->barcode_name;
			$to_export[lang('common_variation')][] = '';
			$to_export[lang('items_quantity_unit_quantity')][] = '';
			$to_export[lang('common_category')][] = isset($categories[$r->category_id]) ? $categories[$r->category_id] : '';
			$to_export[lang('common_supplier_id')][] = $r->supplier_company_name;
			$to_export[lang('common_allow_price_override_regardless_of_permissions')][] = $r->allow_price_override_regardless_of_permissions ?  'y' : '';
			$to_export[lang('common_disable_from_price_rules')][] = $r->disable_from_price_rules ?  'y' : '';
			$to_export[lang('common_only_integer')][] = $r->only_integer ?  'y' : '';
			$to_export[lang('items_sold_in_a_series')][] = $r->is_series_package ?  'y' : '';
			$to_export[lang('common_series_quantity')][] = $r->series_quantity;
			$to_export[lang('common_series_days_to_use_within')][] = $r->series_days_to_use_within;
			$to_export[lang('common_is_barcoded')][] = $r->is_barcoded ? 'y' : '';
			$to_export[lang('common_inactive')][] = $r->item_inactive ? 'y' : '';
			$to_export[lang('common_default_quantity')][] = $r->default_quantity !== NULL ? to_quantity($r->default_quantity) : '';
					
			if ($has_cost_price_permission)
			{
				$to_export[lang('common_cost_price')][] = to_currency_no_money($r->cost_price, 10,TRUE);
			}
			
			$to_export[lang('common_unit_price')][] = to_currency_no_money($r->unit_price,2,TRUE);
						
			if ($this->config->item('limit_manual_price_adj'))
			{
				$to_export[lang('common_min_edit_price')][] = $r->min_edit_price !== NULL ? to_currency_no_money($r->min_edit_price,2,TRUE) : '';
				$to_export[lang('common_max_edit_price')][] = $r->max_edit_price !== NULL ? to_currency_no_money($r->max_edit_price,2,TRUE) : '';
				$to_export[lang('common_max_discount_percent')][] = $r->max_discount_percent !== NULL ? to_quantity($r->max_discount_percent,FALSE) : '';				
			}
			
			$to_export[lang('items_promo_price')][]= $r->promo_price!=0 ? to_currency_no_money($r->promo_price,2,TRUE) : '';
			$to_export[lang('items_promo_start_date')][] = $r->start_date ? date(get_date_format(), strtotime($r->start_date)) : '';
			$to_export[lang('items_promo_end_date')][] = $r->end_date ? date(get_date_format(), strtotime($r->end_date)) : '';
			foreach($tiers as $tier)
			{
				$tier_id = $tier->id;
				$value = '';
				
				if (isset($tier_prices[$r->item_id][$tier->id]))
				{
					$percent_value = '';
					if ($this->config->item('default_tier_percent_type_for_excel_import') == 'cost_plus_percent')
					{
						if ( $tier_prices[$r->item_id][$tier->id]['cost_plus_percent'])
						{
							$percent_value = $tier_prices[$r->item_id][$tier->id]['cost_plus_percent'].'%';
						}
					}
					else
					{
						if ($tier_prices[$r->item_id][$tier->id]['percent_off'])
						{
							$percent_value = $tier_prices[$r->item_id][$tier->id]['percent_off'].'%';						
						}
					}
					
					$fixed_value ='';
					if ($this->config->item('default_tier_fixed_type_for_excel_import') == 'cost_plus_fixed_amount')
					{
						if ( $tier_prices[$r->item_id][$tier->id]['cost_plus_fixed_amount'])
						{
							$fixed_value = to_currency_no_money($tier_prices[$r->item_id][$tier->id]['cost_plus_fixed_amount'],2,TRUE);
						}
					}
					else
					{
						if ( $tier_prices[$r->item_id][$tier->id]['unit_price'])
						{
							$fixed_value = to_currency_no_money($tier_prices[$r->item_id][$tier->id]['unit_price'],2,TRUE);
						}
					}
					$value = $fixed_value !== '' ? $fixed_value : $percent_value;
				}

				$to_export[$tier->name][] = $value;
				
			}
			
			$to_export[lang('items_price_includes_tax')][] = $r->tax_included ? 'y' : '';
			$to_export[lang('items_is_service')][] = $r->is_service ? 'y' : '';
			$to_export[lang('common_is_favorite')][] = $r->is_favorite ? 'y' : 'n';
			if (count($authed_locations) ==1)
			{
				$to_export[lang('items_quantity')][] = to_quantity($r->quantity, FALSE);
			}
			
			$to_export[lang('items_reorder_level')][]= to_quantity($r->reorder_level, FALSE);
			$to_export[lang('common_replenish_level')][]= to_quantity($r->replenish_level, FALSE);
			$to_export[lang('common_description')][]= $r->description;
			$to_export[lang('common_long_description')][]= $r->long_description;
			$to_export[lang('common_info_popup')][]= $r->info_popup;
			$to_export[lang('items_weight')][]= to_quantity($r->weight, FALSE);
			$to_export[lang('items_weight_unit')][]= $r->weight_unit;
			$to_export[lang('items_length')][]= to_quantity($r->length, FALSE);
			$to_export[lang('items_width')][]= to_quantity($r->width, FALSE);
			$to_export[lang('items_height')][]= to_quantity($r->height, FALSE);
			$to_export[lang('items_allow_alt_desciption')][]= $r->allow_alt_description ? 'y' : '';
			$to_export[lang('items_is_serialized')][]= $r->is_serialized ? 'y' : '';
			if (!$this->config->item('hide_size_field'))
			{
				$to_export[lang('common_size')][] = $r->size;
			}
			
			$commission = '';
			
			if ($r->commission_fixed)
			{
				$commission = to_currency_no_money($r->commission_fixed,2,TRUE);
			}
			elseif($r->commission_percent)
			{
				$commission = to_currency_no_money($r->commission_percent,2,TRUE).'%';
			}
			
			
			$to_export[lang('reports_commission')][] = $commission;
			$to_export[lang('items_commission_percent_based_on_profit')][] = $r->commission_percent_type == 'profit' ? 'y':'';
			$to_export[lang('common_tax_class')][] = isset($tax_classes_indexed_by_id[$r->tax_class_id]) ? $tax_classes_indexed_by_id[$r->tax_class_id] : '';
			$to_export[lang('common_tags')][] = $r->tags;
			$to_export[lang('items_days_to_expiration')][] = $r->expire_days ? $r->expire_days : '';
			$to_export[lang('common_change_cost_price_during_sale')][] = $r->change_cost_price ? 'y' : '';
			$to_export[lang('common_manufacturer')][] = isset($manufacturers[$r->manufacturer_id]) ? $manufacturers[$r->manufacturer_id] : '';
			if (count($authed_locations) ==1)
			{
				$to_export[lang('items_location_at_store')][] = $r->location;
			}
			
			if ($this->config->item('enable_customer_loyalty_system') && $this->config->item('loyalty_option') == 'advanced')
			{
				$to_export[lang('common_disable_loyalty')][] = $r->disable_loyalty ? 'y' : '';			
					
			}
			
			if ($this->config->item('loyalty_option') == 'advanced')
			{
				$to_export[lang('common_loyalty_multiplier')][] = $r->loyalty_multiplier ? to_quantity($r->loyalty_multiplier) : NULL;			
					
			}
			
			if ($this->config->item('enable_ebt_payments'))
			{
				$to_export[lang('common_ebt')][] = $r->is_ebt_item ? 'y' : '';				
				
			}
			
			if($this->config->item("ecommerce_platform"))
			{
				$to_export[lang('items_is_ecommerce')][] = $r->is_ecommerce ? 'y' : '';
				
			}

			if($this->config->item("verify_age_for_products"))
			{
				$to_export[lang('common_requires_age_verification')][] = $r->verify_age ? 'y' : '';
				$to_export[lang('common_required_age')][] = $r->required_age;
				
			}
			for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
			{
				$type = $this->Item->get_custom_field($k,'type');
				$name = $this->Item->get_custom_field($k,'name');
				
				if ($name !== FALSE)
				{					
					if ($type == 'date')
					{
						$to_export[$name][] = date(get_date_format(),$r->{"custom_field_{$k}_value"});
					}
					elseif($type=='checkbox')
					{
						$to_export[$name][] = $r->{"custom_field_{$k}_value"} ? '1' : '0';					
					}
					else
					{
						$to_export[$name][] = $r->{"custom_field_{$k}_value"};				
					}
				}
			}
						
			if ($include_location_columns)
			{
				foreach($authed_locations as $location_id)
				{
					$location_info = $this->Location->get_info($location_id,TRUE);
					$item_location_info = $this->Item_location->get_info($r->item_id,$location_id, TRUE);
					
					if (count($authed_locations) !=1)
					{
						$to_export[$location_info->name.' '.lang('items_quantity')][] = to_quantity($item_location_info->quantity, false);
						$to_export[$location_info->name.' '.lang('items_location_at_store')][] = $item_location_info->location;
					}
					
					$to_export[$location_info->name.' '.lang('items_reorder_level')][] = $item_location_info->reorder_level ? to_quantity($item_location_info->reorder_level, false) : '';
					$to_export[$location_info->name.' '.lang('common_replenish_level')][] = $item_location_info->replenish_level ? to_quantity($item_location_info->replenish_level,false ) : '';
					$to_export[$location_info->name.' '.lang('common_cost_price')][] = $item_location_info->cost_price ? to_currency_no_money($item_location_info->cost_price,2,TRUE) : '';
					$to_export[$location_info->name.' '.lang('common_unit_price')][] = $item_location_info->unit_price ? to_currency_no_money($item_location_info->unit_price,2,TRUE) : '';
					$to_export[$location_info->name.' '.lang('items_promo_price')][] = $item_location_info->promo_price!=0 ? to_currency_no_money($item_location_info->promo_price,2,TRUE) : '';
					$to_export[$location_info->name.' '.lang('items_promo_start_date')][] = $item_location_info->start_date ? date(get_date_format(), strtotime($item_location_info->start_date)) : '';
					$to_export[$location_info->name.' '.lang('items_promo_end_date')][] = $item_location_info->end_date ? date(get_date_format(), strtotime($item_location_info->end_date)) : '';
				}
			}

			
			$to_export[lang('common_item_id')][] = $r->item_id;
					
			
			if ($this->Item_attribute->has_attributes($r->item_id))
			{
				foreach($this->Item_variations->get_variations($r->item_id) as $variation_id => $variation)
				{
					$var_add_item_numbers = $this->Additional_item_numbers->get_item_numbers_for_variation($r->item_id,$variation_id)->result_array();
					
					foreach($var_add_item_numbers as $v_row)
					{
						$v_item_num = $v_row['item_number'];
						if($variation['item_number'])
						{
							$variation['item_number'] .= "|";
						}
						$variation['item_number'] .= $v_item_num;
					}
					
					
					$variation_export_row = array();
					
					$variation_export_row[lang('common_item_id')] = $r->item_id.'#'.$variation_id;
					$variation_export_row[lang('common_item_name')] = $variation['name'];
					$variation_export_row[lang('common_item_number')] = $variation['item_number'];
					$variation_export_row[lang('common_cost_price')] = $variation['cost_price'] ? to_currency_no_money($variation['cost_price'],2,TRUE) : '';
					$variation_export_row[lang('common_unit_price')] = $variation['unit_price'] ? to_currency_no_money($variation['unit_price'],2,TRUE) : '';
					$variation_export_row[lang('items_promo_price')] = $variation['promo_price'] ? to_currency_no_money($variation['promo_price'],2,TRUE) : '';
					$variation_export_row[lang('items_promo_start_date')] = $variation['start_date'] ? date(get_date_format(), strtotime($variation['start_date'])) : '';
					$variation_export_row[lang('items_promo_end_date')] = $variation['end_date'] ? date(get_date_format(), strtotime($variation['end_date'])) : '';

					$variation_label = '';
					$variation_quantity = $this->Item_variation_location->get_location_quantity($variation_id);
					$variation_reorder = to_quantity($variation['reorder_level'],false);
					$variation_replenish = to_quantity($variation['replenish_level'],false);
					
					foreach($variation['attributes'] as $attribute)
					{
						$variation_label.=$attribute['label'].', ';
					}
					
					$variation_label= rtrim($variation_label,', ');
					
					$variation_export_row[lang('common_variation')] = $variation_label;
					

					if (count($authed_locations) ==1)
					{
						$variation_export_row[lang('items_quantity')] = $variation_quantity;
					}
										
					$variation_export_row[lang('items_reorder_level')] = $variation_reorder;
					$variation_export_row[lang('common_replenish_level')] = $variation_replenish;
					
					if ($include_location_columns)
					{
						foreach($authed_locations as $location_id)
						{
							$item_variation_location_info = $this->Item_variation_location->get_info($variation_id,$location_id);
							$location_info = $this->Location->get_info($location_id,TRUE);
					
							if (count($authed_locations) !=1)
							{
								$variation_export_row[$location_info->name.' '.lang('items_quantity')] = $item_variation_location_info->quantity ? to_quantity($item_variation_location_info->quantity, false) : '';
							}
							$variation_export_row[$location_info->name.' '.lang('common_cost_price')] =  $item_variation_location_info->cost_price ? to_currency_no_money($item_variation_location_info->cost_price) : '';
							$variation_export_row[$location_info->name.' '.lang('common_unit_price')] =  $item_variation_location_info->unit_price ? to_currency_no_money($item_variation_location_info->unit_price) : '';
							
							$variation_export_row[$location_info->name.' '.lang('items_reorder_level')] = $item_variation_location_info->reorder_level ? to_quantity($item_variation_location_info->reorder_level, false) : '';
							$variation_export_row[$location_info->name.' '.lang('common_replenish_level')] = $item_variation_location_info->replenish_level ? to_quantity($item_variation_location_info->replenish_level,false) : '';
						}
					}
										
					//Look at all headers and export correctly so we have parallel array
					foreach($this->_excel_get_header_row($include_location_columns) as $hrow)
					{
						if (isset($variation_export_row[$hrow]))
						{
							$to_export[$hrow][] = $variation_export_row[$hrow];						
						}
						else
						{
							$to_export[$hrow][] = '';
						}
					}
					
					$to_export[lang('common_item_id')][] = $variation_export_row[lang('common_item_id')];						
					
				}						
			}
			
			foreach($this->Item->get_quantity_units($r->item_id) as $qu)
			{
				$variation_export_row = array();
				
				$variation_export_row[lang('common_variation')] = lang('items_quantity_unit');
				
				$variation_export_row[lang('common_item_id')] = $r->item_id.'@'.$qu->id;
				$variation_export_row[lang('common_item_name')] = $qu->unit_name;
				$variation_export_row[lang('common_item_number')] = $qu->quantity_unit_item_number;
				$variation_export_row[lang('common_cost_price')] = $qu->cost_price ? to_currency_no_money($qu->cost_price,2,TRUE) : '';
				$variation_export_row[lang('common_unit_price')] = $qu->unit_price ? to_currency_no_money($qu->unit_price,2,TRUE) : '';
				
				$variation_export_row[lang('items_quantity_unit_quantity')] = to_quantity($qu->unit_quantity);
				
				
				//Look at all headers and export correctly so we have parallel array
				foreach($this->_excel_get_header_row($include_location_columns) as $hrow)
				{
					if (isset($variation_export_row[$hrow]))
					{
						$to_export[$hrow][] = $variation_export_row[$hrow];						
					}
					else
					{
						$to_export[$hrow][] = '';
					}
				}
				
				$to_export[lang('common_item_id')][] = $variation_export_row[lang('common_item_id')];						
				
			}
		}
		
		$rows = array();
		$header_row = array_keys($to_export);
		$row_count = count($to_export[lang('common_item_id')]);
		for($k=0;$k<$row_count;$k++)
		{
			$row = array();
			
			foreach(array_keys($to_export) as $key)
			{
				if ($to_export[$key][$k] !== NULL)
				{
					$row[] = $to_export[$key][$k];
				}
				else
				{
					$row[] = '';
				}
			}
			
			$rows[] = $row;
		}
		
		$this->load->helper('spreadsheet');
		$extension = ($this->config->item('spreadsheet_format') == 'XLSX' ? 'xlsx' : 'csv');
		array_to_spreadsheet($rows,'items_export.'.$extension, FALSE);
	}

	function excel_import()
	{
		ini_set('memory_limit','1024M');
		$this->check_action_permission('add_update');
		
		$data = array();
		$data['redirect'] = $this->input->get("redirect");
		$this->load->view("items/excel_import", $data);
	}
	
	function do_excel_upload()
	{
		ini_set('memory_limit','1024M');
		$this->load->helper('demo');
		
		//Write to app files
 	 	$this->load->model('Appfile');
    	$app_file_file_id = $this->Appfile->save($_FILES["file"]["name"], file_get_contents($_FILES["file"]["tmp_name"]),'+3 hours');
		//Store file_id from app files in session so we can reference later
		$this->session->set_userdata("excel_import_file_id",$app_file_file_id);
		
		$file_info = pathinfo($_FILES["file"]["name"]);		
		$file = $this->Appfile->get($this->session->userdata('excel_import_file_id'));
		$tmpFilename = tempnam(ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir(), 'iexcel');
		file_put_contents($tmpFilename,$file->file_data);
		$this->load->helper('spreadsheet');
		
		$first_row = get_spreadsheet_first_row($tmpFilename,$file_info['extension']);
		unlink($tmpFilename);
		
		$fields = $this->_get_database_fields_for_import_as_array();
		
		$k=0;
		foreach($first_row as $col_name)
		{
			$column =  array('Spreadsheet Column' => $col_name, 'Index' => $k);
			
			if($column['Spreadsheet Column'] == '')
			{
				echo json_encode(array('success'=>false,'message'=>lang('common_all_spreadsheet_columns_must_have_labels')));
				return;
			}
				
			$cols = array_column($fields, 'Name');
			$cols = array_map('strtolower', $cols);
			$search = strtolower($column['Spreadsheet Column']);
			$matchIndex = array_search($search, $cols);

			if (is_numeric($matchIndex))
			{
				$column['Database Field'] = $fields[$matchIndex]['Id'];
			}
			
			$columns[] = $column;
			$k++;
		}
		
		$this->session->set_userdata("items_excel_import_column_map", $columns);
		echo json_encode(array('success'=>true,'message'=>lang('common_import_successful')));
	}
	
	function do_excel_import_map()
	{
		ini_set('memory_limit','1024M');
		$this->load->helper('text');
 	 	$this->load->model('Appfile');
		
		$file = $this->Appfile->get($this->session->userdata('excel_import_file_id'));

		$tmpFilename = tempnam(ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir(), 'iexcel');
		file_put_contents($tmpFilename,$file->file_data);
		$this->load->helper('spreadsheet');
		$file_info = pathinfo($file->file_name);
		$sheet = file_to_spreadsheet($tmpFilename,$file_info['extension']);
		unlink($tmpFilename);

		$this->sheet_data = array();

		$columns = array();
		$k=0;

		$fields = $this->_get_database_fields_for_import_as_array();
		$numRows = $sheet->getNumberOfRows();

		while($col_name = $sheet->getCellByColumnAndRow($k,1))
		{
			$column =  array('Spreadsheet Column' => $col_name, 'Index' => $k);
			
			$cols = array_column($fields, 'Name');
			$cols = array_map('strtolower', $cols);
			$search = strtolower($column['Spreadsheet Column']);
			$matchIndex = array_search($search, $cols);

			if (is_numeric($matchIndex))
			{
				$column['Database Field'] = $fields[$matchIndex]['Id'];
			}

	    $col_data = array();
			for ($i = 2; $i <= $numRows; $i++) 
			{
	  		$col_data[] = clean_string(trim($sheet->getCellByColumnAndRow($k,$i)));
			}

			$column["data"] = $col_data;

			$columns[] = $column;
			$k++;
		}
		
		$this->session->set_userdata("items_excel_import_num_rows", $numRows);
		$this->session->set_userdata("items_excel_import_column_map", $columns);
	}
	
	function get_database_fields_for_import()
	{
		$fields = $this->_get_database_fields_for_import_as_array();
		array_unshift($fields , array('Name' => '', 'Id' => -1));
		echo json_encode($fields);
	}
	
	private function _get_database_fields_for_import_as_array()
	{		
		$this->load->model('Tier');
		$fields = array();

		$fields[] = array('Name' => lang('common_item_number'), 'key' => 'item_number');
		$fields[] = array('Name' => lang('common_product_id'), 'key' => 'product_id');
		$fields[] = array('Name' => lang('common_item_name'), 'key' => 'name');
		$fields[] = array('Name' => lang('common_barcode_name'), 'key' => 'barcode_name');
		$fields[] = array('Name' => lang('common_variation'), 'key' => 'variation');
		$fields[] = array('Name' => lang('items_quantity_unit_quantity'), 'key' => 'quantity_unit_quantity');
		$fields[] = array('Name' => lang('common_category'), 'key' => 'category_id');
		$fields[] = array('Name' => lang('common_supplier_id'), 'key' => 'supplier_id');
		$fields[] = array('Name' => lang('common_allow_price_override_regardless_of_permissions'), 'key' => 'allow_price_override_regardless_of_permissions');
		$fields[] = array('Name' => lang('common_disable_from_price_rules'), 'key' => 'disable_from_price_rules');
		$fields[] = array('Name' => lang('common_only_integer'), 'key' => 'only_integer');
		$fields[] = array('Name' => lang('items_sold_in_a_series'), 'key' => 'is_series_package');
		$fields[] = array('Name' => lang('common_is_barcoded'), 'key' => 'is_barcoded');
		$fields[] = array('Name' => lang('common_inactive'), 'key' => 'item_inactive');
		$fields[] = array('Name' => lang('common_default_quantity'), 'key' => 'default_quantity');
		$fields[] = array('Name' => lang('common_series_quantity'), 'key' => 'series_quantity');
		$fields[] = array('Name' => lang('common_series_days_to_use_within'), 'key' => 'series_days_to_use_within');		
		
		$fields[] = array('Name' => lang('common_cost_price'), 'key' => 'cost_price');
		$fields[] = array('Name' => lang('common_unit_price'), 'key' => 'unit_price');
		$fields[] = array('Name' => lang('common_min_edit_price'), 'key' => 'min_edit_price');
		$fields[] = array('Name' => lang('common_max_edit_price'), 'key' => 'max_edit_price');
		$fields[] = array('Name' => lang('common_max_discount_percent'), 'key' => 'max_discount_percent');
		$fields[] = array('Name' => lang('items_promo_price'), 'key' => 'promo_price');
		$fields[] = array('Name' => lang('items_promo_start_date'), 'key' => 'start_date');
		$fields[] = array('Name' => lang('items_promo_end_date'), 'key' => 'end_date');
		
		foreach($this->Tier->get_all()->result() as $tier)
		{
			$fields[] = array('Name' => $tier->name, 'key' => 'tier');
		}
		
		$fields[] = array('Name' => lang('items_price_includes_tax'), 'key' => 'tax_included');
		$fields[] = array('Name' => lang('items_is_service'), 'key' => 'is_service');
		$fields[] = array('Name' => lang('common_is_favorite'), 'key' => 'is_favorite');
		$fields[] = array('Name' => lang('items_quantity'), 'key' => 'quantity');
		$fields[] = array('Name' => lang('items_reorder_level'), 'key' => 'reorder_level');
		$fields[] = array('Name' => lang('common_replenish_level'), 'key' => 'replenish_level');
		$fields[] = array('Name' => lang('common_description'), 'key' => 'description');
		$fields[] = array('Name' => lang('common_long_description'), 'key' => 'long_description');
		$fields[] = array('Name' => lang('common_info_popup'), 'key' => 'info_popup');
		$fields[] = array('Name' => lang('items_weight'), 'key' => 'weight');
		$fields[] = array('Name' => lang('items_weight_unit'), 'key' => 'weight_unit');
		$fields[] = array('Name' => lang('items_length'), 'key' => 'length');
		$fields[] = array('Name' => lang('items_width'), 'key' => 'width');
		$fields[] = array('Name' => lang('items_height'), 'key' => 'height');
		$fields[] = array('Name' => lang('items_allow_alt_desciption'), 'key' => 'allow_alt_description');
		$fields[] = array('Name' => lang('items_is_serialized'), 'key' => 'is_serialized');
		
		if (!$this->config->item('hide_size_field'))
		{
			$fields[] = array('Name' => lang('common_size'), 'key' => 'size');
		}
		$fields[] = array('Name' => lang('reports_commission'), 'key' => 'commission');
		$fields[] = array('Name' => lang('items_commission_percent_based_on_profit'), 'key' => 'commission_percent_type');
		$fields[] = array('Name' => lang('common_tax_class'), 'key' => 'tax_class_id');
		$fields[] = array('Name' => lang('common_tags'), 'key' => 'tags');
		$fields[] = array('Name' => lang('items_days_to_expiration'), 'key' => 'expire_days');
		$fields[] = array('Name' => lang('common_change_cost_price_during_sale'), 'key' => 'change_cost_price');
		$fields[] = array('Name' => lang('common_manufacturer'), 'key' => 'manufacturer_id');
		$fields[] = array('Name' => lang('items_location_at_store'), 'key' => 'location');
		if ($this->config->item('enable_customer_loyalty_system') && $this->config->item('loyalty_option') == 'advanced')
		{
			$fields[] = array('Name' => lang('common_disable_loyalty'), 'key' => 'disable_loyalty');
		}
		
		if ($this->config->item('loyalty_option') == 'advanced')
		{
			$fields[] = array('Name' => lang('common_loyalty_multiplier'), 'key' => 'loyalty_multiplier');
		}

		
		if ($this->config->item('enable_ebt_payments'))
		{
			$fields[] = array('Name' => lang('common_ebt'), 'key' => 'is_ebt_item');
		}
		
		if($this->config->item("ecommerce_platform"))
		{
			$fields[] = array('Name' => lang('items_is_ecommerce'), 'key' => 'is_ecommerce');
		}
		
		$fields[] = array('Name' => lang('common_requires_age_verification'), 'key' => 'verify_age');
		$fields[] = array('Name' => lang('common_required_age'), 'key' => 'required_age');
		
		for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
		{
			if ($this->Item->get_custom_field($k) !== FALSE)
			{
				$fields[] = array('Name' => $this->Item->get_custom_field($k), 'key' => 'custom_field_'.$k.'_value');			
			}	
		}
		
		
		$logged_in_employee_info=$this->Employee->get_logged_in_employee_info();
		$authed_locations = $this->Employee->get_authenticated_location_ids($logged_in_employee_info->person_id);
		foreach($authed_locations as $location_id)
		{
			$location_info = $this->Location->get_info($location_id);

			if (count($authed_locations) !=1)
			{
				$fields[] = array('Name' => $location_info->name.' '.lang('items_quantity'), 'key' => $location_id.'|quantity');
				$fields[] = array('Name' => $location_info->name.' '.lang('items_location_at_store'), 'key' => $location_id.'|location');
			}

			$fields[] = array('Name' => $location_info->name.' '.lang('items_reorder_level'), 'key' => $location_id.'|reorder_level');
			$fields[] = array('Name' => $location_info->name.' '.lang('common_replenish_level'), 'key' => $location_id.'|replenish_level');
			$fields[] = array('Name' => $location_info->name.' '.lang('common_cost_price'), 'key' => $location_id.'|cost_price');
			$fields[] = array('Name' => $location_info->name.' '.lang('common_unit_price'), 'key' => $location_id.'|unit_price');
			$fields[] = array('Name' => $location_info->name.' '.lang('items_promo_price'), 'key' => $location_id.'|promo_price');
			$fields[] = array('Name' => $location_info->name.' '.lang('items_promo_start_date'), 'key' => $location_id.'|start_date');
			$fields[] = array('Name' => $location_info->name.' '.lang('items_promo_end_date'), 'key' => $location_id.'|end_date');
		}
		
		$fields[] = array('Name' => lang('common_item_id'), 'key' => 'item_id');
		
		$id = 0;
		foreach($fields as &$field)
		{
			$field['Id'] = $id;
			$id++;
		}
		unset($field);
		
		return $fields;
	}
	
	function get_uploaded_excel_columns()
	{
		$data = $this->session->userdata("items_excel_import_column_map");
		
		foreach($data as &$col)
		{
			unset($col["data"]);
		}
		
		echo json_encode($data);
	}
	
	public function set_excel_columns_map()
	{	
		ini_set('memory_limit','1024M');
		$data = $this->session->userdata("items_excel_import_column_map");
		
		$mapKeys = json_decode($this->input->post('mapKeys'), true);
		
		foreach($mapKeys as $mapKey)
		{
			foreach ($data as $key => $col) 
			{
	       if ($col['Index'] == $mapKey["Index"])
				 {
					 $data[$key]["Database Field"] = $mapKey["Database Field"];
	       }
			}
		}	
		
		$this->session->set_userdata("items_excel_import_column_map", $data);
	}
	
	private function _indexColumnArray($n)
	{
		if (isset($n['Database Field']))
		{
			return $n['Database Field'];
		}
		
		return 'N/A';
	}
	
	//dedup
	function dedup_excel_import_data()
	{
		ini_set('memory_limit','1024M');
		$this->session->set_userdata('items_excel_import_error_log', NULL);
		$columns_with_data = $this->session->userdata("items_excel_import_column_map");
		
		$fieldId_to_colIndex = array_flip(array_map(array($this, '_indexColumnArray'), $columns_with_data));
		unset($fieldId_to_colIndex['N/A']);
		unset($fieldId_to_colIndex[-1]);
		
		$item_number_dups = array();
		if (isset($fieldId_to_colIndex[0]))
		{
			$item_number_index = $fieldId_to_colIndex[0];
			$item_numbers = $columns_with_data[$item_number_index]['data'] ? $columns_with_data[$item_number_index]['data'] : array();
			
			$all_item_numbers = array();
			
			foreach($item_numbers as $item_number)
			{
				$all_item_numbers[] = $item_number;
			}
			
			$item_number_dups = $this->_get_keys_for_duplicate_values($all_item_numbers);

			foreach($item_number_dups as $key => $val)
			{
				foreach($val as $v)
				{
					$row = $v+2;
					$message = lang('items_duplicate_item_number').' "'. $key .'" ' .lang('items_in_spreadsheet');
					$this->_log_validation_error($row, $message, 'Error');
				}
			}
		}
		
		$product_id_dups = array();
		
		if (isset($fieldId_to_colIndex[1]))
		{
			$product_id_index = $fieldId_to_colIndex[1];
			$product_ids = $columns_with_data[$product_id_index]['data'] ? $columns_with_data[$product_id_index]['data'] : array();
			
			$product_id_dups = $this->_get_keys_for_duplicate_values($product_ids);
				
		
			foreach($product_id_dups as $key => $val)
			{
				foreach($val as $v)
				{
					$row = $v+2;
					$message = lang('items_duplicate_product_id').' "'. $key .'" ' .lang('items_in_spreadsheet');
					$this->_log_validation_error($row, $message, 'Error');
				}
			}
		}		
		if(count($item_number_dups) > 0 || count($product_id_dups) > 0)
		{
			echo json_encode(array('type'=> 'error','message'=>lang('items_duplicate_item_numbers_product_ids'), 'title' =>  lang('common_error')));
		} else {
			echo json_encode(array('type'=> 'success','message'=>lang('items_no_duplicate_item_numbers_product_ids'), 'title' =>  lang('common_success')));
		}
	}
	
	private function _get_keys_for_duplicate_values($my_arr) 
	{
    $dups = array();;
		$new_arr = array();
		
    foreach ($my_arr as $key => $val) {
			if(!$val)
			{
				continue;
			}
			
      if (!isset($new_arr[$val])) {
         $new_arr[$val] = $key;
      } else {
        if (isset($dups[$val])) {
           $dups[$val][] = $key;
        } else {
           // include the initial key in the dups array.
           $dups[$val] = array($new_arr[$val], $key);
        }
      }
    }
    return $dups;
	}
	
	//new function
	function complete_excel_import()
	{
		ini_set('memory_limit','1024M');
		set_time_limit(0);
		ini_set('max_input_time','-1');
		$this->check_action_permission('add_update');
		
		$this->session->set_userdata('items_excel_import_error_log', NULL);
		
		$numRows = $this->session->userdata("items_excel_import_num_rows");
		$columns_with_data = $this->session->userdata("items_excel_import_column_map");
		$current_location_id= $this->Employee->get_logged_in_employee_current_location_id();
		
		$this->load->model('Tier');
		$this->load->model('Item_taxes');
		$this->load->model('Item_location');
		$this->load->model('Supplier');
		$this->load->model('Manufacturer');
		$this->load->model('Item_attribute');
		$this->load->model('Item_attribute_value');
		$this->load->model('Item_variations');
		$this->load->model('Item_variation_location');
		
		$fields = $this->_get_database_fields_for_import_as_array();
		
		$tiers = $this->Tier->get_all()->result_array();
		
		$this->categories_indexed_by_name = $this->Category->get_all_categories_and_sub_categories_as_indexed_by_name_key();
		
		$this->manufacturers_map = array();
		
		foreach($this->Manufacturer->get_all() as $id => $row)
		{
		 	$this->manufacturers_map[strtoupper($row['name'])] = $id;
		}
				
		$fieldId_to_colIndex = array_flip(array_map(array($this, '_indexColumnArray'), $columns_with_data));
		unset($fieldId_to_colIndex['N/A']);
		
		$can_commit = TRUE;
		$last_saved_item_id = NULL;
		$this->db->trans_begin();
		
		for ($i = 0; $i < $numRows -1; $i++)
		{
			$item_id = NULL;
			$variation = FALSE;
			$item_numbers = array();
			$quantity = NULL;
			$quantity_unit_quantity = NULL;
			$item_data = array();
			$item_location_data = array();
			$tier_datas = array();
			
			foreach($fields as $field)
			{
				if(array_key_exists($field['Id'], $fieldId_to_colIndex))
				{
					$key = $fieldId_to_colIndex[$field['Id']];
				}
				else
				{
					continue;
				}
				
				if($field['key'] !== "")
				{						
					if($field['key'] == 'commission')
					{
						if (strpos($columns_with_data[$key]['data'][$i], '%') === FALSE)
						{
							$field['key'] = 'commission_fixed';
						}
						else
						{
							$field['key'] = 'commission_percent';
						}
						
						//hotfix for data
						if($columns_with_data[$key]['data'][$i] === '')
						{
							$columns_with_data[$key]['data'][$i] = NULL;
						}
						
						
					}
					
					if (strpos($field['key'], '|') !== false)
					{
						//Location fields
						list($location_id,$location_field) = explode('|',$field['key']);
						$item_location_data[$location_id][$location_field] = $this->_clean_location_field($location_field, $columns_with_data[$key]['data'][$i]);
					}
					elseif($field['key'] == 'item_id')
					{
						$item_id = $this->_clean($field['key'], $columns_with_data[$key]['data'][$i]);
					} 
					elseif($field['key'] == 'variation')
					{
						$variation = $this->_clean($field['key'], $columns_with_data[$key]['data'][$i]);
					}
					elseif($field['key'] == 'quantity_unit_quantity')
					{
						$quantity_unit_quantity = $this->_clean($field['key'], $columns_with_data[$key]['data'][$i]);						
					}
					elseif($field['key'] == 'quantity')
					{
						$quantity = $this->_clean($field['key'], $columns_with_data[$key]['data'][$i]);
					}
					elseif($field['key'] == 'quantity_unit_quantity')
					{
						$quantity = $this->_clean($field['key'], $columns_with_data[$key]['data'][$i]);
					}
					elseif($field['key'] == 'location')
					{
						$location_at_store = $columns_with_data[$key]['data'][$i];
					}
					elseif($field['key'] == 'tier')
					{
						$tier_data = array();
						$cols = array_column($tiers, 'name');
						$tier_data['tier_id'] = $tiers[array_search($field['Name'], $cols)]['id'];
						
						$tier_value = $columns_with_data[$key]['data'][$i];
						
						if ($tier_value)
						{
							if (strpos($tier_value, '%') === FALSE)
							{
								
									
								if ($this->config->item('default_tier_fixed_type_for_excel_import') == 'cost_plus_fixed_amount')
								{
									$tier_data['unit_price'] = NULL;									
									$tier_data['cost_plus_fixed_amount'] =  $this->_clean('cost_plus_fixed_amount',$tier_value);
								}
								else
								{
									$tier_data['unit_price'] = $this->_clean('unit_price',$tier_value);									
									$tier_data['cost_plus_fixed_amount'] = NULL;
								}
									
								$tier_data['percent_off'] = NULL;
								$tier_data['cost_plus_percent'] = NULL;
								
							}
							else
							{
								$tier_data['unit_price'] = NULL;
								$tier_data['cost_plus_fixed_amount'] = NULL;
								
								if ($this->config->item('default_tier_percent_type_for_excel_import') == 'cost_plus_percent')
								{
									$tier_data['cost_plus_percent'] = $this->_clean('cost_plus_percent', $tier_value);
									$tier_data['percent_off'] = NULL;
								}
								else
								{
									$tier_data['percent_off'] =  $this->_clean('percent_off', $tier_value);
									$tier_data['cost_plus_percent'] = NULL;
								}
							}
						}
						$tier_datas[] = $tier_data;
					}
					elseif($field['key'] == 'tags')
					{
						$tags = $this->_clean($field['key'], $columns_with_data[$key]['data'][$i]);
					}
					elseif($field['key'] == 'item_number')
					{
						$item_numbers = explode('|', $columns_with_data[$key]['data'][$i], 2);
						
						$item_data[$field['key']]  = $this->_clean($field['key'], $item_numbers[0]);
							
						if(isset($item_numbers[1]))
						{
							$additional_item_numbers = explode('|', $item_numbers[1]);
						}
						else
						{
							if (isset($additional_item_numbers))
							{
								unset($additional_item_numbers);
							}
						}
						
					}
					else 
					{
						$item_data[$field['key']] =  $this->_clean($field['key'], $columns_with_data[$key]['data'][$i]);
					}
				}
			}
			
			$item_data['deleted'] = 0;
			
			if(!isset($item_data['commission_fixed']) && !isset($item_data['commission_percent']) && !$item_id)
			{
				$item_data['commission_fixed'] = NULL;
				$item_data['commission_percent'] = NULL;
			}
			
			if(isset($item_data['commission_fixed']))
			{
				$item_data['commission_percent'] = NULL;
			}
			
			if(isset($item_data['commission_percent']))
			{
				$item_data['commission_fixed'] = NULL;
			}

			if (isset($item_data['is_service']) && $item_data['is_service'])
			{
				$quantity = NULL;
			}
			
			if (isset($item_data['tax_class_id']) && $item_data['tax_class_id'])
			{
				$item_data['override_default_tax'] = 1;
			}			
			
			//We have a variation id and we don't have a variation we want to skip this row
			if (!$variation && strpos($columns_with_data[$key]['data'][$i],'#') !== FALSE)
			{
				continue;
			}
			
			//We have a variation id and we don't have a variation we want to skip this row
			if (!$variation && strpos($columns_with_data[$key]['data'][$i],'@') !== FALSE)
			{
				continue;
			}
			
			if(!$variation && !$this->Item->save($item_data, $item_id))
			{
				if($item_id === NULL)
				{					
					if(!isset($item_data['item_number']) || !$item_id = $this->Item->get_item_id($item_data['item_number']))
					{			
						if(!isset($item_data['product_id']) || !$item_id = $this->Item->get_item_id($item_data['product_id']))
						{
							//couldnt find Item id to make second attempt
							$this->_logDbError($i+2);
							$can_commit = FALSE;
							continue;
						}
					}
										
					$item_data['deleted'] = 0;
					//second attempt
					if($this->config->item('overwrite_existing_items_on_excel_import') && $this->Item->save($item_data, $item_id))
					{
						//second attempt Succeeded
						$this->_log_validation_error($i+2, lang('items_item_existed_warning'));
					}
					else
					{
						
						if ($this->config->item('overwrite_existing_items_on_excel_import'))
						{
							//second attempt failed
							$this->_logDbError($i+2);
							$can_commit = FALSE;
							continue;
						}
						else
						{
							$this->_log_validation_error($i+2, lang('items_item_existed_warning'),'Error');
							$can_commit = FALSE;
							continue;
						}
					}
					
				}
				else
				{ //first attempt failed even with item id
					$this->_logDbError($i+2);
					$can_commit = FALSE;
					continue;
				}	
				
			}
			elseif($variation && $last_saved_item_id) //If we have a variation and then try to save a variation
			{
				//This is a quantity unit variation we will process differntly
				if ($variation == lang('items_quantity_unit'))
				{
					$item_id_to_use_for_variation = $item_id ? $item_id : $last_saved_item_id;
					$quantity_unit_id = FALSE;
					
					if (strpos($item_id_to_use_for_variation,'@') !== FALSE)
					{
						list($item_id_to_use_for_variation,$quantity_unit_id) = explode('@',$item_id_to_use_for_variation);
					}
					
					$quantity_unit_cost_price = $item_data['cost_price'] ? $item_data['cost_price'] : NULL;
					$quantity_unit_selling_price = $item_data['unit_price'] ? $item_data['unit_price'] : NULL;
					$quantity_unit_item_number = $item_data['item_number'] ? $item_data['item_number'] : NULL;
					$quantity_unit_name = $item_data['name'] ? $item_data['name'] : NULL;
										
					//We must have quantity and name to continue
					if (isset($quantity_unit_quantity) && $quantity_unit_quantity !== '' && $quantity_unit_quantity !== NULL && $quantity_unit_name)
					{
						$quantity_unit_data = array('item_id'=> $item_id_to_use_for_variation,'unit_name' => $quantity_unit_name, 'unit_quantity' => $quantity_unit_quantity,'unit_price' => $quantity_unit_selling_price !== '' ? $quantity_unit_selling_price : NULL,'cost_price' => $quantity_unit_cost_price !== '' ? $quantity_unit_cost_price : NULL,'quantity_unit_item_number' => $quantity_unit_item_number !== '' ? $quantity_unit_item_number : NULL);
						$this->Item->save_unit_quantity($quantity_unit_data, $quantity_unit_id);
						
					}					
				}
				else
				{
					$attribute_value_pairs = explode(',',$variation);
				
					if (count($attribute_value_pairs) > 0)
					{
					$variation_attribute_value_ids = array();
					foreach($attribute_value_pairs as $attribute_value)
					{
						$attribute_value_pair = explode(':',$attribute_value);
						
						//We need exactly 2 for the pair
						if (count($attribute_value_pair) == 2)
						{
							$item_id_to_use_for_variation = $item_id ? $item_id : $last_saved_item_id;
							
							//Remove extra whitespace in case typed in like Color: Red
							$attribute = trim($attribute_value_pair[0]);
							$value = trim($attribute_value_pair[1]);
													
							$attribute_info = $this->Item_attribute->get_info($attribute, TRUE, $item_id_to_use_for_variation);
						
							$attribute_id = $attribute_info->id;

							if (!$attribute_id)
							{
								$attribute_info = $this->Item_attribute->get_info($attribute);
								$attribute_id = $attribute_info->id;
							}
							
							if (!$attribute_id)
							{
								//Make attribute
								$attribute_data = array();
								$attribute_data['name'] = $attribute;
								$this->Item_attribute->save($attribute_data);
								$attribute_id = $attribute_data['id'];
							}
							
							//Link item to attribute
							if ($attribute_id)
							{
								$this->Item_attribute->save_item_attributes(array($attribute_id),$item_id_to_use_for_variation, FALSE);							
							}
							
							$item_attribute_value_info = $this->Item_attribute_value->lookup($value,$attribute_id);
							$item_attribute_value_id = $item_attribute_value_info->id;
							
							if (!$item_attribute_value_id)
							{
								//Make attribute value
								$item_attribute_value_id = $this->Item_attribute_value->save($value,$attribute_id);
							}
							
							//Link attribute value to item
							if ($item_attribute_value_id)
							{
								$variation_attribute_value_ids[] = $item_attribute_value_id;
								$this->Item_attribute_value->save_item_attribute_values($item_id_to_use_for_variation,array($item_attribute_value_id));							
							}
							
						}
					}
					
					//Variation creation
					if (count($variation_attribute_value_ids) > 0)
					{
						$item_number_to_save_for_variation = isset($item_numbers[0]) && $item_numbers[0] ? $item_numbers[0] : NULL;
						
						$variation_name = $item_data['name'] ? $item_data['name'] : NULL;
						$reorder_level = $item_data['reorder_level'] ? $item_data['reorder_level'] : NULL;
						$replenish_level = $item_data['replenish_level'] ? $item_data['replenish_level'] : NULL;
						
						$cost_price = $item_data['cost_price'] ? $item_data['cost_price'] : NULL;
						$selling_price = $item_data['unit_price'] ? $item_data['unit_price'] : NULL;
						$promo_price = $item_data['promo_price'] ? $item_data['promo_price'] : NULL;
						$promo_start = $item_data['start_date'] ? date('Y-m-d', strtotime($item_data['start_date'])) : NULL;
						$promo_end = $item_data['end_date'] ? date('Y-m-d', strtotime($item_data['end_date'])) : NULL;
						
						$data = array(
							'name' => $variation_name,
				 			'item_id' => $item_id_to_use_for_variation,
							'reorder_level' => $reorder_level,
							'replenish_level' => $replenish_level,
							'item_number' => $item_number_to_save_for_variation,
							'cost_price' => $cost_price,
							'unit_price' => $selling_price,
							'promo_price' => $promo_price,
							'start_date' => $promo_start,
							'end_date' => $promo_end,
							'deleted' => 0,
						);
						
						
						$item_variation_id = $this->Item_variations->lookup($item_id_to_use_for_variation, $variation_attribute_value_ids);
						$item_variation_id = $this->Item_variations->save($data,$item_variation_id, $variation_attribute_value_ids); 
						
						if($item_variation_id && isset($additional_item_numbers))
						{
							if(!$this->Additional_item_numbers->save_variation($item_id_to_use_for_variation,$item_variation_id, $additional_item_numbers))
							{
								$this->_logDbError($i+2);
							}
						}
						
						
						if(isset($quantity) && $quantity !== '' && $quantity !== NULL)
						{
							$cur_item_variation_location_info_before_save = $this->Item_variation_location->get_info($item_variation_id);
							
							$this->Item_variation_location->save_quantity($quantity, $item_variation_id);
							$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
							
							$cur_item_variation_location_info = $this->Item_variation_location->get_info($item_variation_id);
							$inv_data = array
							(
								'trans_date'=>date('Y-m-d H:i:s'),
								'trans_items'=>$item_id_to_use_for_variation,
								'item_variation_id'=>$item_variation_id,
								'trans_user'=>$employee_id,
								'trans_comment'=>lang('items_csv_import'),
								'trans_inventory'=>$quantity - (float)$cur_item_variation_location_info_before_save->quantity,
								'location_id'=>$this->Employee->get_logged_in_employee_current_location_id(),
								'trans_current_quantity' => $quantity,
							);
							
							if ($quantity - (float)$cur_item_variation_location_info_before_save->quantity!=0)
							{
								$this->Inventory->insert($inv_data);
							}
						}
					}
				}
				}
				
				
				if (!empty($item_location_data))
				{
					foreach($item_location_data as $cur_location_id=>$cur_item_location_data)
					{
						$item_variation_location_info_before_save = $this->Item_variation_location->get_info($item_variation_id,$cur_location_id);
			
						if ($item_variation_id && (isset($cur_item_location_data['quantity'])))
						{
							if (!$this->Item_variation_location->save_quantity($cur_item_location_data['quantity'], $item_variation_id,$cur_location_id))
							{
								$this->_logDbError($i+2);
								$can_commit = FALSE;
								continue;
							}
				
							$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
							$emp_info=$this->Employee->get_info($employee_id);
							$comment =lang('items_csv_import');
			
							//Only log inventory if quantity changes
							if ($cur_item_location_data['quantity'] != (float)$item_variation_location_info_before_save->quantity)
							{
								$inv_data = array
								(
									'trans_date'=>date('Y-m-d H:i:s'),
									'trans_items'=>$item_id_to_use_for_variation,
									'item_variation_id'=>$item_variation_id,
									'trans_user'=>$employee_id,
									'trans_comment'=>$comment,
									'trans_inventory'=>(float)$cur_item_location_data['quantity'] - (float)$item_variation_location_info_before_save->quantity,
									'location_id'=>$cur_location_id,
									'trans_current_quantity' => $cur_item_location_data['quantity'],
								);
								if(!$this->Inventory->insert($inv_data))
								{
									//ERROR updating quantity
									$this->_logDbError($i+2);
									$can_commit = FALSE;
									continue;
								}
							}
						
							unset($cur_item_location_data['quantity']);
						}
						
						//unset vars that we don't have for variations
						unset($cur_item_location_data['location']);
						unset($cur_item_location_data['promo_price']);
						unset($cur_item_location_data['start_date']);
						unset($cur_item_location_data['end_date']);
						
						//save non quantity fields
						$this->Item_variation_location->save($cur_item_location_data, $item_variation_id,$cur_location_id);
					}
				}
				
				//Don't do the other item stuff below as we are focused on just variation for this row
				continue;
				
			}
		
			$item_id = isset($item_data['item_id']) ? $item_data['item_id'] :  $item_id;
			$last_saved_item_id = $item_id;
			
			if(isset($tags))
			{
				if(!$this->Tag->save_tags_for_item(isset($item_data['item_id']) ? $item_data['item_id'] :  $item_id, $tags))
				{
					$this->_logDbError($i+2);
				}
			}
			
			foreach($tier_datas as $tier_data)
			{
				$tier_data['item_id'] = $item_id;
				
				if(array_key_exists("unit_price", $tier_data) || array_key_exists("cost_plus_percent", $tier_data) || array_key_exists("percent_off", $tier_data))
				{
					if(!$this->Item->save_item_tiers($tier_data, $tier_data['item_id']))
					{
						$this->_logDbError($i+2);
					}
				}
				else 
				{
					if(!$this->Item->delete_tier_price($tier_data['tier_id'], $tier_data['item_id']))
					{
						$this->_logDbError($i+2);
					}
				}
			}
			
			if($item_id && isset($additional_item_numbers))
			{
				if(!$this->Additional_item_numbers->save($item_id, $additional_item_numbers))
				{
					$this->_logDbError($i+2);
				}
			}
			
			if (!empty($item_location_data))
			{
				foreach($item_location_data as $cur_location_id=>$cur_item_location_data)
				{
					$item_location_before_save = $this->Item_location->get_info($item_id,$cur_location_id);
			
					if ($item_id && (isset($cur_item_location_data['quantity'])) || (isset($item_data['is_service']) && $item_data['is_service']))
					{
						if (!$this->Item_location->save_quantity($cur_item_location_data['quantity'], $item_id,$cur_location_id))
						{
							$this->_logDbError($i+2);
							$can_commit = FALSE;
							continue;
						}
				
						$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
						$emp_info=$this->Employee->get_info($employee_id);
						$comment =lang('items_csv_import');
			
						//Only log inventory if quantity changes
						if ((!isset($item_data['is_sevice']) || !$item_data['is_service']) && $cur_item_location_data['quantity'] != (float)$item_location_before_save->quantity)
						{
							$inv_data = array
							(
								'trans_date'=>date('Y-m-d H:i:s'),
								'trans_items'=>isset($item_data['item_id']) ? $item_data['item_id'] :  $item_id,
								'trans_user'=>$employee_id,
								'trans_comment'=>$comment,
								'trans_inventory'=>(float)$cur_item_location_data['quantity'] - (float)$item_location_before_save->quantity,
								'location_id'=>$cur_location_id,
								'trans_current_quantity' => $cur_item_location_data['quantity'],
							);
							if(!$this->Inventory->insert($inv_data))
							{
								//ERROR updating quantity
								$this->_logDbError($i+2);
								$can_commit = FALSE;
								continue;
							}
						}
						
						unset($cur_item_location_data['quantity']);
					}
					
					//save non quantity fields
					$this->Item_location->save($cur_item_location_data, $item_id,$cur_location_id);
				}
			}
			
			if (isset($location_at_store))
			{
				$this->Item_location->save(array('location' => $location_at_store), $item_id,$current_location_id);
			}
			
			$item_location_before_save = $this->Item_location->get_info($item_id,$this->Employee->get_logged_in_employee_current_location_id());
			
			if ($item_id && (isset($quantity) && $quantity !== '') || (isset($item_data['is_service']) && $item_data['is_service']))
			{
				if (!$this->Item_location->save_quantity($quantity, $item_id))
				{
					$this->_logDbError($i+2);
					$can_commit = FALSE;
					continue;
				}
				
				$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
				$emp_info=$this->Employee->get_info($employee_id);
				$comment =lang('items_csv_import');
			
				//Only log inventory if quantity changes
				if ((!isset($item_data['is_sevice']) || !$item_data['is_service']) && $quantity != (float)$item_location_before_save->quantity)
				{
					$inv_data = array
					(
						'trans_date'=>date('Y-m-d H:i:s'),
						'trans_items'=>isset($item_data['item_id']) ? $item_data['item_id'] :  $item_id,
						'trans_user'=>$employee_id,
						'trans_comment'=>$comment,
						'trans_inventory'=>$quantity - (float)$item_location_before_save->quantity,
						'location_id'=>$this->Employee->get_logged_in_employee_current_location_id(),
						'trans_current_quantity' => $quantity,
					);
					if(!$this->Inventory->insert($inv_data))
					{
						//ERROR updating quantity
						$this->_logDbError($i+2);
						$can_commit = FALSE;
						continue;
					}
				}
			}
			
			$tax_class_id = NULL;
			if(array_key_exists('tax_class_id', $item_data))
			{
				$tax_class_id = $item_data['tax_class_id'];
			}
			
			if ($tax_class_id)
			{
				if(!$this->Item_taxes->delete($item_id))
				{
					$this->_logDbError($i+2);
					$can_commit = FALSE;
					continue;
				}
			}
		} //loop done for items
		
		if ($can_commit)
		{
			$this->db->trans_commit();
		}
		else
		{
			$this->db->trans_rollback();
		}
		
		//if there were any errors or warnings
		if ($this->db->trans_status() === FALSE && !$can_commit)
		{
			echo json_encode(array('type'=> 'error','message'=> lang('common_errors_occured_durring_import'), 'title' => lang('common_error')));
		}
		elseif ($this->db->trans_status() === FALSE && $can_commit)
		{	
			echo json_encode(array('type'=> 'warning','message'=> lang('common_warnings_occured_durring_import'), 'title' => lang('common_warning')));
		}
		else
		{
			//Clear out session data used for import
			$this->session->unset_userdata('excel_import_file_id');
			$this->session->unset_userdata('items_excel_import_column_map');
			$this->session->unset_userdata('items_excel_import_num_rows');
			
			echo json_encode(array('type'=> 'success','message'=>lang('common_import_successful'), 'title' =>  lang('common_success')));			
		}
	}
	
	private function _clean_location_field($key,$value,$row = NULL)
	{
		if ($key == 'cost_price' || $key == 'unit_price' || $key == 'promo_price')
		{
			if ($value !== "")
			{
				return make_currency_no_money($value);
			}
			return NULL;			
		}
		
		return $this->_clean($key,$value,$row);
	}
	
	private function _clean($key, $value, $row = NULL)
	{	//$row added for logging warnings if we decide to
		
		//Location specific keys use a location_id|key; we just want key for this functions purpose
		if (strpos($key, '|') !== false)
		{
			$key = substr($key, strpos($key, "|") + 1);    
		}
		
		if ($key == 'default_quantity' || $key == 'quantity_unit_quantity')
		{
			if ($value === '')
			{
				return NULL;
			}
			
			return to_quantity($value);
			
		}
		if ($key == 'location')
		{
			if($value === '')
			{
				 return NULL;
			}
			return $value;
		}
		if ($key == 'item_number'){
			if($value === '')
			{
				 return NULL;
			}
			return $value;
		}
		if ($key == 'variation'){
			if($value === '')
			{
				 return NULL;
			}
			return $value;
		}
		if ($key == 'product_id'){
			if($value === '')
			{
				 return NULL;
			}
			return $value;
		}
		if ($key == 'name')
		{
			if(!$value)
			{
				 return '';
			}
			return $value;
		}
		
		if ($key == 'barcode_name')
		{
			if(!$value)
			{
				 return '';
			}
			return $value;
		}
		
		if ($key == 'category_id')
		{
			if($value)
			{	
				
				//remove false or empty values
				$category_list = explode('|', $value);
				$category_list = array_values(array_filter($category_list, function($value) { return $value !== ''; }));
				$value = implode("|", $category_list);
				
				if (!isset($this->categories_indexed_by_name[strtoupper($value)]))
				{
					$this->Category->create_categories_as_needed($value, $this->categories_indexed_by_name);
				}
			
				return $this->categories_indexed_by_name[strtoupper($value)];
			}
			
			return NULL;
		}
		if ($key == 'supplier_id'){
			if ($value)
			{
				$supplier_name_before_searching = $value;
				$value = $this->Supplier->exists($value) ? $value : $this->Supplier->find_supplier_id($value);

				if (!$value)
				{
					$person_data = array('first_name' => '', 'last_name' => '');
					$supplier_data = array('company_name' => $supplier_name_before_searching);
					$this->Supplier->save_supplier($person_data, $supplier_data);
					$value = $supplier_data['person_id'];
				}
				return $value;
				
			}
			
			return NULL;
		}
		if ($key == 'cost_price'){
			if ($value !== "")
			{
				return make_currency_no_money($value);
			}
			return 0;
		}
		if ($key == 'unit_price'){
			if ($value !== "")
			{
				return make_currency_no_money($value);
			}
			return 0;
		}
		if ($key == 'min_edit_price') {
			
			if ($value !== "")
			{
				return make_currency_no_money($value);
			}
			return NULL;
		}
		if ($key == 'max_edit_price') {
			
			if ($value !== "")
			{
				return make_currency_no_money($value);
			}
			return NULL;
		}
		if ($key == 'max_discount_percent') {
			
			if ($value !== "")
			{
				return floatval($value);
			}
			return NULL;
		}
		if ($key == 'promo_price'){
			
			if ($value!=='')
			{
				return make_currency_no_money($value);
			}
			return NULL;
		}
		if ($key == 'start_date'){
			if($value)
			{
				return date('Y-m-d',strtotime($value));
			}
			return NULL;
		}
		if ($key == 'end_date'){
			if($value)
			{
				return date('Y-m-d',strtotime($value));
			}
			return NULL;
		}
		if ($key == 'tax_included') {
			$true_values = array("true", "yes", "y", "1");
			if (in_array(strtolower($value), $true_values)) {
			    return 1;
			}
		
			return 0;
		}
		
		if ($key == 'allow_price_override_regardless_of_permissions') {
			$true_values = array("true", "yes", "y", "1");
			if (in_array(strtolower($value), $true_values)) {
			    return 1;
			}
		
			return 0;
		}

		if ($key == 'disable_from_price_rules') {
			$true_values = array("true", "yes", "y", "1");
			if (in_array(strtolower($value), $true_values)) {
			    return 1;
			}
		
			return 0;
		}


		if ($key == 'only_integer') {
			$true_values = array("true", "yes", "y", "1");
			if (in_array(strtolower($value), $true_values)) {
			    return 1;
			}
		
			return 0;
		}
		
		if ($key == 'is_series_package') {
			$true_values = array("true", "yes", "y", "1");
			if (in_array(strtolower($value), $true_values)) {
			    return 1;
			}
		
			return 0;
		}

		if ($key == 'is_barcoded') {
			$false_values = array("false", "no", "n", "0");
			if (in_array(strtolower($value), $false_values)) {
			    return 0;
			}
		
			return 1;
		}
		
		if ($key == 'is_favorite') {
			$false_values = array("false", "no", "n", "0");
			if (in_array(strtolower($value), $false_values)) {
			    return 0;
			}
		
			return 1;
		}
		
		
		if ($key == 'item_inactive') {
			$true_values = array("true", "yes", "y", "1");
			if (in_array(strtolower($value), $true_values)) {
			    return 1;
			}
		
			return 0;
		}
		
		
		if ($key == 'series_quantity' || $key == 'series_days_to_use_within'){
			if(is_numeric($value))
			{
				return $value;
			}
			return NULL;
		}		
		
		if ($key == 'is_service') {
			$true_values = array("true", "yes", "y", "1");
			if (in_array(strtolower($value), $true_values)) {
			    return 1;
			}
			
			return 0;
		}
		if ($key == 'reorder_level' || $key == 'replenish_level'){
			if(is_numeric($value))
			{
				return $value;
			}
			return NULL;
		}
		if ($key == 'description' || $key == 'long_description'){
			if(!$value)
			{
				 return '';
			}
			return $value;
		}
		
		if ($key == 'info_popup')
		{
			if($value)
			{
				return $value;
			}
			return NULL;
		}		
		
		if ($key == 'allow_alt_description'){
			$true_values = array("true", "yes", "y", "1");
			if (in_array(strtolower($value), $true_values)) {
			    return 1;
			}
			
			return 0;
		}
		if ($key == 'is_serialized'){
			$true_values = array("true", "yes", "y", "1");
			if (in_array(strtolower($value), $true_values)) {
			    return 1;
			}
			
			return 0;
		}
		if ($key == 'size'){
			if(!$value)
			{
				 return '';
			}
			return $value;
		}
		if ($key == 'commission_fixed'){
			
			if($value === '' || $value === NULL)
			{
				return NULL;  
			}
				
			return make_currency_no_money($value);
		}
		if ($key == 'commission_percent'){
			
			if($value === '' || $value === NULL)
			{
				return NULL;  
			}
			
			return strval((float) $value);
		
		}
		if ($key == 'commission_percent_type')
		{
			$true_values = array("true", "yes", "y", "1");
			if (in_array(strtolower($value), $true_values)) {
			    return 'profit';
			}
			
			return 'selling_price';
		}
		if ($key == 'tax_class_id'){
			if ($value)
			{
				$this->load->model('Tax_class');
				
				$value = $this->Tax_class->exists($value) ? $value : $this->Tax_class->find_tax_class_id($value);
				return $value;
			}
			
			return NULL;
			
		}
		if ($key == 'expire_days'){
			
			if($value !='' && $value == (int) $value)
			{
				return (int)$value;
			}
			
			return null;
		}
		if ($key == 'change_cost_price'){
			$true_values = array("true", "yes", "y", "1");
			if (in_array(strtolower($value), $true_values)) {
			    return 1;
			}
			
			return 0;
		}
		if ($key == 'manufacturer_id')
		{
			if ($value)
			{
				$manufacturer_id = NULL;
				
				if (isset($this->manufacturers_map[strtoupper($value)]))
				{
					$manufacturer_id = $this->manufacturers_map[strtoupper($value)];
				}	
				else
				{
					$manufacturer_id = $this->Manufacturer->save($value);
					$this->manufacturers_map[strtoupper($value)] = $manufacturer_id;
				}
				return $manufacturer_id;
			}	
		}
		if ($key == 'disable_loyalty'){
			$true_values = array("true", "yes", "y", "1");
			if (in_array(strtolower($value), $true_values)) {
			    return 1;
			}
			
			return 0;
			
		}
		
		if ($key == 'loyalty_multiplier'){
			if($value !== '' && $value == (float) $value)
			{
				return strval((float) $value);
			}
			return NULL;
			
		}
		
		
		if ($key == 'is_ebt_item'){
			$true_values = array("true", "yes", "y", "1");
			if (in_array(strtolower($value), $true_values)) {
			    return 1;
			}
			
			return 0;

		}
		if ($key == 'item_id'){
			
			//Quantity unit variation
			if (strpos($value,'@') !== FALSE)
			{
				return $value;
			}
			
			if($value == NULL)
			{
				return NULL;
			}
			if($value == (int) $value)
			{
				return strval((int) $value);
			}
			return NULL;
		}
		if ($key == 'quantity'){
			if($value !== '' && $value == (float) $value)
			{
				return $value;
			}
			return '';
		}
		if ($key == 'unit_price'){
			return make_currency_no_money($value);
		}
		
		if ($key == 'cost_plus_fixed_amount')
		{
			return make_currency_no_money($value);			
		}
		
		if ($key == 'cost_plus_percent'){
			if($value == (float) $value)
			{
				return strval((float) $value);
			}
			return NULL;
		}
		if ($key == 'percent_off'){
			if($value == (float) $value)
			{
				return strval((float) $value);
			}
			return NULL;
		}
		if ($key == 'tags'){
			if($value)
			{
				return $value;
			}
			return '';
		}
		if($key == 'quantity')
		{
			if($value !== '' && $value == (float) $value)
			{
				return strval((float) $value);
			}
			return ;
		}
		if($key == 'is_ecommerce')
		{
			$true_values = array("true", "yes", "y", "1");
			
			if ($this->config->item('new_items_are_ecommerce_by_default'))
			{
				$true_values[] = '';
			}
			
			if (in_array(strtolower($value), $true_values)) {
			    return 1;
			}
			
			return 0;
		}

		if($key == 'verify_age')
		{
			$true_values = array("true", "yes", "y", "1");
						
			if (in_array(strtolower($value), $true_values)) {
			    return 1;
			}
			
			return 0;
		}

		if($key == 'required_age')
		{			
			if($value == (int) $value)
			{
				return strval((float) $value);
			}
			return NULL;
		}
		
		if ($key == 'weight'){
			
			if($value !='')
			{
				return (float)$value;
			}
			
			return null;
		}
		
		if ($key == 'weight_unit'){
			
			if($value !='')
			{
				return $value;
			}
			
			return null;
		}
		
		
		if ($key == 'length'){
			
			if($value !='')
			{
				return (float)$value;
			}
			
			return null;
		}
		
		
		if ($key == 'width'){
			
			if($value !='')
			{
				return (float)$value;
			}
			
			return null;
		}
		
		if ($key == 'height'){
			
			if($value !='')
			{
				return (float)$value;
			}
			
			return null;
		}
		
		
		$custom_fields = array();
		for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
		{
			if ($this->Item->get_custom_field($k) !== FALSE)
			{
				$custom_fields[] = "custom_field_${k}_value";
			}
		}
		
		if (in_array($key, $custom_fields))
		{
			if(!$value)
			{
				 return '';
			}
			
			$k = substr($key, strlen('custom_field_'),1);
			$type = $this->Item->get_custom_field($k,'type');
			
			if ($type == 'date')
			{
				$value = strtotime($value);
			}
			
			return $value;
		}
		
	}
	
	private function _logDbError($index)
	{
		$error = $this->db->error();
		$matches = array();
		preg_match('/for key \'(.+)\'/', $error['message'], $matches);

		if (isset($matches[1]))
		{
			$col_name = $matches[1];
			$data = $this->_get_database_fields_for_import_as_array();
			$cols = array_column($data, 'key');
			$match_index = array_search($col_name, $cols);

			if ($match_index !== FALSE)
			{
				$column_human_name = $data[$match_index]['Name'];
				$error['message'] = str_replace($col_name,$column_human_name,$error['message']);
			}

		}
		$this->_log_validation_error($index, $error['message'], "Error");
	}
	
	private function _log_validation_error($row, $message, $type = "Warning")
	{
		//log errors and warnings for import
		if(!$log = $this->session->userdata('items_excel_import_error_log'))
		{
			$log = array();
		}
		
		$log[] = array("row" => $row, "message" => $message, "type" => $type);
		
		$this->session->set_userdata('items_excel_import_error_log', $log);
	}
	
	public function get_import_errors()
	{
		echo json_encode(array_slice($this->session->userdata('items_excel_import_error_log'),0,min(count($this->session->userdata('items_excel_import_error_log')),100)));
	}
	
	function cleanup()
	{
		$this->Item->cleanup();
		echo json_encode(array('success'=>true,'message'=>lang('items_cleanup_sucessful')));
	}
	
	
	function select_inventory() 
	{
		$this->session->set_userdata('select_inventory', 1);
	}
	
	function get_select_inventory() 
	{
		return $this->session->userdata('select_inventory') ? $this->session->userdata('select_inventory') : 0;
	}

	function clear_select_inventory() 	
	{
		$this->session->unset_userdata('select_inventory');
		
	}
	
	function tags()
	{
		//allow parallel searchs to improve performance.
		session_write_close();
		$suggestions = $this->Tag->get_tag_suggestions($this->input->get('term'),$this->config->item('items_per_search_suggestions') ? (int)$this->config->item('items_per_search_suggestions') : 20);
		echo json_encode($suggestions);
	}
	
	function add_custom_attribute_to_item($item_id)
	{
		$name = $this->input->post('name');
		
		$this->load->model('Item_attribute');
		
		$custom_attribute_data = array('item_id'=> $item_id, 'name' => $name);
		$attr_id = $this->Item_attribute->save($custom_attribute_data);
		
		if($this->Item_attribute->save_item_attributes(array($attr_id),$item_id, FALSE))
		{
			echo json_encode(array('success'=>true,'message'=>lang('items_custom_attribute_successful_added').' '.H($name), 'attribute_id' => $attr_id));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('items_custom_attribute_error_added').' '.H($name)));
		}		
	}
	
	function delete_custom_attribute($item_id)
	{
		$this->load->model('Item_attribute');
		
		$attr_id = $this->input->post('attr_id');
		
		if($this->Item_attribute->delete($attr_id))
		{
			echo json_encode(array('success'=>true,'message'=>lang('items_custom_attribute_successful_deleted')));
		}
		else
		{
				echo json_encode(array('success'=>false,'message'=>lang('items_custom_attribute_error_deleted')));
		}
	}
	
	function add_attribute_to_item($item_id)
	{
		$this->load->model('Item_attribute');

		$attr_id = $this->input->post('attr_id');

		if($this->Item_attribute->save_item_attributes(array($attr_id),$item_id,FALSE))
		{
			echo json_encode(array('success'=>true,'message'=>lang('items_add_attribute_to_item_successful')));
		}	else {
			echo json_encode(array('success'=>false,'message'=>lang('items_add_attribute_to_item_failed')));
		}
	}
	
	function add_attribute_value_to_item($item_id)
	{
		$this->load->model('Item_attribute');
		$this->load->model('Item_attribute_value');
		
		if(!$item_id)
		{
			return;
		}
		
		$attr_id = $this->input->post('attr_id');
		$value_added = $this->input->post('value_added');
		
		$this->Item_attribute->save_item_attributes(array($attr_id),$item_id, FALSE);
		$attr_value_id = $this->Item_attribute_value->save($value_added,$attr_id);
		$this->Item_attribute_value->save_item_attribute_values($item_id, array($attr_value_id));
		
		echo $attr_value_id;
	}
	
	function remove_attribute_for_item($item_id)
	{
		$this->load->model('Item_attribute');
		
		$attr_id = $this->input->post('attr_id');
		
		$this->Item_attribute->delete_item_attribute($item_id, $attr_id);
		
		//check if custom
		$is_custom = $this->Item_attribute->get_info((int)$attr_id,true,$item_id)->item_id ? true : false;
				
		if($is_custom)
		{
			$this->Item_attribute->delete($attr_id);
		}
		
		echo $attr_id;
	}
	
	function remove_attribute_value_for_item($item_id)
	{
		$this->load->model('Item_attribute');
		$this->load->model('Item_attribute_value');
		
		if(!$item_id)
		{
			return;
		}
		
		$attr_id = $this->input->post('attr_id');
		
		$attr_value_id = $this->Item_attribute_value->lookup($this->input->post('value_removed'),$attr_id)->id;
		
		$this->Item_attribute_value->delete_item_attribute_value($item_id, $attr_value_id);
		
		//check if custom
		$is_custom = $this->Item_attribute->get_info((int)$attr_id)->item_id ? true : false;
		if($is_custom)
		{
			$this->Item_attribute_value->delete_attribute_value($attr_value_id);
		}
		
		echo $attr_value_id;
	}
	
	function attribute_values_for_item_variations($item_id)
	{
		$this->load->model('Item_attribute_value');
		//allow parallel searchs to improve performance.
		session_write_close();
		$term = $this->input->get('term') ? $this->input->get('term') : false;
		$suggestions = $this->Item_attribute_value->get_attribute_value_suggestions_for_item_variations($item_id, $term, 25);
		echo json_encode($suggestions);
	}
	
	function attribute_values($attribute_id)
	{
		$this->load->model('Item_attribute_value');
		//allow parallel searchs to improve performance.
		session_write_close();
		$suggestions = $this->Item_attribute_value->get_attribute_value_suggestions($attribute_id, $this->input->get('term'),$this->config->item('items_per_search_suggestions') ? (int)$this->config->item('items_per_search_suggestions') : 20);
		echo json_encode($suggestions);
	}
	
	function count($status = 'open', $offset = 0)
	{
		$this->check_action_permission('count_inventory');
		$data = array();
		$config = array();
		$config['base_url'] = site_url("items/count/$status");
		$config['per_page'] = $this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20; 
		$config['total_rows'] = $this->Inventory->get_count_by_status($status);
		$config['uri_segment'] = 4;
		$data['per_page'] = $config['per_page'];
	

		$data['total_rows'] = $config['total_rows'];
		$this->load->library('pagination');$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
	
		$counts = $this->Inventory->get_counts_by_status($status,$config['per_page'], $offset)->result_array();
		
		$data['counts'] = $counts;
		$data['status'] = $status;
		$this->load->view('items/count', $data);
	}
	
	function new_count()
	{
		$this->check_action_permission('count_inventory');
		$count_id = $this->Inventory->create_count();
	  redirect('items/do_count/'.$count_id);
	}
	
	function count_not_counted($in_stock = 0,$count_id=null,$offset = 0)
	{
		$this->check_action_permission('count_inventory');
		
		$count_info = $this->Inventory->get_count_info($count_id);
		
		$this->load->model('Item_variations');
		$this->load->model('Item_location');
		$this->load->model('Item_variation_location');
		$data = array();
		
		$headers = array();
		$data = array();
		$config = array();
		if (count($_GET) > 0) $config['suffix'] = '?' . http_build_query($_GET, '', "&");
		$config['base_url'] = site_url("items/count_not_counted/$in_stock/$count_id");
		$config['first_url'] = $config['base_url'].'?'.http_build_query($_GET);
		$config['per_page'] = $this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20;
		$config['total_rows'] = $this->Inventory->get_items_not_counted_count($count_id,$in_stock);
		$config['uri_segment'] = 5;
		$this->load->library('pagination');
		$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		
		if ($this->input->get('category'))
		{
			if ($this->config->item('include_child_categories_when_searching_or_reporting'))
			{	
				$category_ids = $this->Category->get_category_id_and_children_category_ids_for_category_id($this->input->get('category'));			
			}
			else
			{
				$category_ids = array($this->input->get('category'));
			}
		}
		else
		{
			$category_ids = '';
		}
		
		$items_not_counted = $this->Inventory->get_items_not_counted($count_id,$category_ids,$in_stock,$this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20,$offset);
		$headers = array(
			array('data' =>lang('common_item_name') ,'align' => 'center'),
			array('data' =>lang('common_category') ,'align' => 'center'),
			array('data' =>lang('common_item_number') ,'align' => 'center'),
			array('data' =>lang('common_product_id') ,'align' => 'center'),
			array('data' =>lang('common_cost_price') ,'align' => 'center'),
			array('data' =>lang('common_unit_price') ,'align' => 'center'),
			array('data' =>lang('items_actual_on_hand') ,'align' => 'center'),
			array('data' =>lang('common_count') ,'align' => 'center'),
		);
		
		if (!$this->Employee->has_module_action_permission('items', 'see_count_when_count_inventory', $this->Employee->get_logged_in_employee_info()->person_id)) 
		{
			unset($headers[5]);
		}
		
		$tab_data = array();
		$index = 0;
		foreach($items_not_counted as $item)
		{
			$tab_data_row = array();
			if ($item['item_variation_id'])
			{
				$name = $item['name'].' '.$this->Item_variations->get_variation_name($item['item_variation_id']);
				$item['unit_price'] = $item['item_variation_unit_price'];
				$item['cost_price'] = $item['item_variation_cost_price'];
			}
			else
			{
				$name = $item['name'];
			}
			$items_not_counted[$index]['item_name'] = H($name);
			$items_not_counted[$index]['cost_price'] = to_currency($item['cost_price']);
			$items_not_counted[$index]['unit_price'] = to_currency($item['unit_price']);
			$items_not_counted[$index]['promo_price'] = to_currency($item['promo_price']);
			$items_not_counted[$index]['location_cost_price'] = to_currency($item['location_cost_price']);
			$items_not_counted[$index]['location_unit_price'] = to_currency($item['location_unit_price']);
			$items_not_counted[$index]['commission_fixed'] = to_currency($item['commission_fixed']);
			$items_not_counted[$index]['is_favorite'] = boolean_as_string($item['is_favorite']);
			$items_not_counted[$index]['is_barcoded'] = boolean_as_string($item['is_barcoded']);
			$items_not_counted[$index]['series_quantity'] = to_quantity($item['series_quantity']);
			$items_not_counted[$index]['loyalty_multiplier'] = to_quantity($item['loyalty_multiplier']);
			$items_not_counted[$index]['start_date'] = date_as_display_date($item['start_date']);
			$items_not_counted[$index]['override_default_tax'] = boolean_as_string($item['override_default_tax']);
			$items_not_counted[$index]['disable_from_price_rules'] = boolean_as_string($item['disable_from_price_rules']);
			$items_not_counted[$index]['tax_included'] = boolean_as_string($item['tax_included']);
			$items_not_counted[$index]['allow_alt_description'] = boolean_as_string($item['allow_alt_description']);
			$items_not_counted[$index]['is_ecommerce'] = boolean_as_string($item['is_ecommerce']);
			$items_not_counted[$index]['only_integer'] = boolean_as_string($item['only_integer']);
			$items_not_counted[$index]['is_series_package'] = boolean_as_string($item['is_series_package']);
			$items_not_counted[$index]['allow_price_override_regardless_of_permissions'] = boolean_as_string($item['allow_price_override_regardless_of_permissions']);
			$items_not_counted[$index]['change_cost_price'] = boolean_as_string($item['change_cost_price']);
			$items_not_counted[$index]['has_variations'] = boolean_as_string($item['has_variations']);
			$items_not_counted[$index]['disable_loyalty'] = boolean_as_string($item['disable_loyalty']);
			$items_not_counted[$index]['is_service'] = boolean_as_string($item['is_service']);
			$items_not_counted[$index]['is_ebt_item'] = boolean_as_string($item['is_ebt_item']);
			$items_not_counted[$index]['is_serialized'] = boolean_as_string($item['is_serialized']);
			$items_not_counted[$index]['item_inactive'] = boolean_as_string($item['item_inactive']);
			$items_not_counted[$index]['commission_percent'] = to_quantity($item['commission_percent']);
			$items_not_counted[$index]['variation_count'] = to_quantity($item['variation_count']);
			$items_not_counted[$index]['default_quantity'] = to_quantity($item['default_quantity']);
			$items_not_counted[$index]['commission_amount'] = to_currency($item['tax_included']);
			$dataArr = ['length'=>$item['length'],'width'=>$item['width'],'height'=>$item['height']];
			$items_not_counted[$index]['dimensions'] = dimensions_format('',$dataArr);
			$items_not_counted[$index]['weight'] = to_quantity($item['weight']);

			$items_not_counted[$index]['category_id'] = $this->Category->get_full_path($item['category_id']);

			$tab_data_row[] = array('data' => H($name),'align' => 'center');
			$tab_data_row[] = array('data' => $this->Category->get_full_path($item['category_id']),'align' => 'center');
			$tab_data_row[] = array('data' => H($item['item_number']),'align' => 'center');
			$tab_data_row[] = array('data' => H($item['product_id']),'align' => 'center');
			$tab_data_row[] = array('data' => to_currency($item['cost_price']),'align' => 'center');
			$tab_data_row[] = array('data' => to_currency($item['unit_price']),'align' => 'center');

			if ($this->Employee->has_module_action_permission('items', 'see_count_when_count_inventory', $this->Employee->get_logged_in_employee_info()->person_id)) 
			{
				$tab_data_row[] = array('data' => to_quantity($item['item_variation_id'] ? $this->Item_variation_location->get_location_quantity($item['item_variation_id'],$count_info->location_id) : $this->Item_location->get_location_quantity($item['item_id'],$count_info->location_id)),'align' => 'center');
				$items_not_counted[$index]['actual_quantity'] = $actual_quantity = to_quantity($item['item_variation_id'] ? $this->Item_variation_location->get_location_quantity($item['item_variation_id'],$count_info->location_id) : $this->Item_location->get_location_quantity($item['item_id'],$count_info->location_id));

				if(is_numeric($actual_quantity)){
					$total_value_by_cost_price = $item['cost_price'] * $actual_quantity;
					$total_value_by_selling_price = $item['unit_price'] * $actual_quantity;

					$items_not_counted[$index]['total_value_by_cost_price'] = to_currency($total_value_by_cost_price);
					$items_not_counted[$index]['total_value_by_selling_price'] = to_currency($total_value_by_selling_price);
				}else{
					$items_not_counted[$index]['total_value_by_cost_price'] = 0;
					$items_not_counted[$index]['total_value_by_selling_price'] = 0;
				}
			}
			$tab_data_row[] = array('data' => anchor("items/prompt_count_save/$count_id", lang('common_count'), 
					"onclick='return do_prompt_count(".json_encode(lang('common_count')).",".json_encode($item['item_id']).",".json_encode($item['item_variation_id']).", this)'"),'align' => 'center');
			$items_not_counted[$index]['count'] = anchor("items/prompt_count_save/$count_id", lang('common_count'), 
					"onclick='return do_prompt_count(".json_encode(lang('common_count')).",".json_encode($item['item_id']).",".json_encode($item['item_variation_id']).", this)'");
			
			$tab_data[] = $tab_data_row;
			$index++;
		}

		//total calculation
		if(isset($items_not_counted[0])){
			$ret = $this->Inventory->get_items_not_counted_total($count_id, $category_ids, $in_stock);
			foreach($items_not_counted[0] as $key => $value){
				if($key == 'item_name'){
					$items_not_counted[$index][$key] = lang('common_total');
				}else if($key == 'total_value_by_cost_price'){
					$items_not_counted[$index][$key] = to_currency($ret ['grand_total_value_by_cost_price']);
				}else if ($key == 'total_value_by_selling_price'){
					$items_not_counted[$index][$key] = to_currency($ret ['grand_total_value_by_selling_price']);
				}else{
					$items_not_counted[$index][$key] = '';
				}
			}
		}

		$data['headers'] = $headers;
		$data['items_not_counted'] = $items_not_counted;
		$data['data'] = $tab_data;
		$data['count_id'] = $count_id;
		//Configuration changes
		$data['controller_name']=strtolower(get_class());
		$this->load->model('Employee');
		$data['default_columns'] = $this->Inventory->get_item_not_count_default_columns();
		$data['selected_columns'] = $this->Employee->get_item_not_count_columns_to_display();
		$data['all_columns'] = array_merge($data['selected_columns'],$this->Inventory->get_item_not_count_displayable_columns());
		//END
		
		$this->load->view('items/not_counted',$data);
	}
	
	function prompt_count_save($count_id)
	{
		$this->check_action_permission('count_inventory');
		
		$this->load->model('Item_location');
		$this->load->model('Item_variation_location');
		$item_id = $this->input->post('item_id');
		$item_variation_id = $this->input->post('variation_id');
		$current_count = $this->input->post('quantity');
		$count_info = $this->Inventory->get_count_info($count_id);
		
		if ($item_variation_id)
		{
			$current_inventory_value = $this->Item_variation_location->get_location_quantity($item_variation_id,$count_info->location_id);
		}
		else
		{
			$current_inventory_value = $this->Item_location->get_location_quantity($item_id,$count_info->location_id);
		}
		$this->Inventory->set_count_item($count_id, $item_id, $item_variation_id, $current_count, $current_inventory_value);
		
		echo json_encode(array('message' => lang('items_count_item_saved'),'success' => TRUE));
	}
	
	function do_count($count_id, $offset = 0)
	{
		$this->check_action_permission('count_inventory');		
		$this->session->set_userdata('current_count_id',$count_id);
		
		$data = array();
		$data['count_id'] = $count_id;
		$config = array();
		$config['base_url'] = site_url("items/do_count/$count_id");
		$config['per_page'] = $this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20;
		$config['total_rows'] = $this->Inventory->get_number_of_items_counted($count_id);
		$config['uri_segment'] = 4;
		$data['controller_name']=strtolower(get_class());
		$data['per_page'] = $config['per_page'];
	
		$search = $this->input->get('search') ? $this->input->get('search') : "";
		
		$data['total_rows'] = $config['total_rows'];
		$this->load->library('pagination');$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		$data['count_info'] = $this->Inventory->get_count_info($count_id);

		$data['items_counted'] = $this->Inventory->get_items_counted($count_id,$config['per_page'], $offset,$search);
		$ret = $this->Inventory->get_items_counted_total($count_id);
		$data['grand_total_value_by_selling_price'] = $ret['grand_total_value_by_selling_price'];
		$data['grand_total_value_by_cost_price'] = $ret['grand_total_value_by_cost_price'];

		$index = 0;

		foreach($data['items_counted'] as $item)
		{
			$data['items_counted'][$index]['category_id'] = $this->Category->get_full_path($item['category_id']);
			$data['items_counted'][$index]['cost_price'] = to_currency($item['cost_price']);
			$data['items_counted'][$index]['unit_price'] = to_currency($item['unit_price']);
			$data['items_counted'][$index]['raw_cost_price'] = $item['cost_price'];
			$data['items_counted'][$index]['raw_unit_price'] = $item['unit_price'];
			$index++;
		}

		$data['mode'] = $this->session->userdata('count_mode') ? $this->session->userdata('count_mode') : 'scan_and_set';
		$data['modes'] = array('scan_and_set' => lang('items_scan_and_set'), 'scan_and_add' => lang('items_scan_and_add') ); 
		
		$this->load->model('Employee_appconfig');
		$data['default_columns'] = $this->Inventory->get_default_columns();
		$data['selected_columns'] = $this->Employee->get_inventory_count_columns_to_display();
		$data['all_columns'] = array_merge($data['selected_columns'],$this->Inventory->get_displayable_columns());	
		
		$this->load->view('items/do_count', $data);
	}

	private function can_add_item_to_inventory_count($item_id, $item_variation_id)
	{
		$count_id = $this->session->userdata('current_count_id');		
		
		$item_info = $this->Item->get_info($item_id);
		$item_variation_ids = array_keys($this->Item_variations->get_variations($item_id));
		
		if(!$item_info || !$count_id || $item_info->is_service)
		{
			return false;
		}
		
		if(count($item_variation_ids) > 0)
		{
			$counted_variations = array_map('intval', array_column($this->Inventory->get_counted_variations_for_item($count_id, $item_id), 'item_variation_id'));
			
			if($item_variation_id)
			{
				if(!in_array($item_variation_id, $item_variation_ids))
				{
					return false;
				}				
			}
	
			if(!$item_variation_id && count(array_diff($item_variation_ids,$counted_variations)) == 0)
			{
				return false;
			}
		}
	
		return true;
	}
	
	function add_item_to_inventory_count()
	{
		$this->check_action_permission('count_inventory');
		
		$this->load->model('Item');
		$this->load->model('Item_variations');
		$this->load->model('Item_location');
		$this->load->model('Item_variation_location');
		$this->load->helper('items');
		
		$item_identifer = $this->input->post('item');
				
		$count_id = $this->session->userdata('current_count_id');		
		$mode = $this->session->userdata('count_mode') ? $this->session->userdata('count_mode') : 'scan_and_set';
		
		$data = array();
		
		$result = parse_item_scan_data($item_identifer);
		if(!$result)
		{
			$data['error'] = true;
		}
		else
		{
			$item_id = $result['item_id'];
			$item_variation_id = $result['variation_id'];
			if ($this->can_add_item_to_inventory_count($item_id, $item_variation_id))
			{
					$current_count = $this->Inventory->get_count_item_current_quantity($count_id, $item_id, $item_variation_id);
					$actual_quantity = $this->Inventory->get_count_item_actual_quantity($count_id, $item_id, $item_variation_id);
				
					if ($actual_quantity !== NULL)
					{
						$current_inventory_value = $actual_quantity;
					}
					else
					{
						$count_info = $this->Inventory->get_count_info($count_id);
						
						if($item_variation_id)
						{
							$current_inventory_value = $this->Item_variation_location->get_location_quantity($item_variation_id,$count_info->location_id);
						} else {
							$current_inventory_value = $this->Item_location->get_location_quantity($item_id,$count_info->location_id);
						}
					}
					if ($mode == 'scan_and_add')
					{	
						$this->Inventory->set_count_item($count_id, $item_id, $item_variation_id, $current_count + 1, $current_inventory_value);
					}
					else
					{
						$this->Inventory->set_count_item($count_id, $item_id, $item_variation_id, $current_count, $current_inventory_value);
					}
			} 
			else 
			{
				$data['error'] = true;
			} 

		}

		
		$this->_reload_inventory_counts($data);
	}
	
	function edit_count()
	{
		$this->check_action_permission('count_inventory');
		$name = $this->input->post('name');
		$count_id = $this->input->post('pk');
		$$name = $this->input->post('value');
		
		$this->Inventory->set_count($count_id, isset($status) ? $status : FALSE, isset($comment) ? $comment : FALSE);
	}
	
	function excel_import_count()
	{		
		$this->check_action_permission('count_inventory');
		$this->load->view("items/excel_import_count", null);	
	}
	
	function _excel_get_header_row_count()
	{
		return array(lang('common_item_id').'/'.lang('common_item_number').'/'.lang('common_product_id'),lang('items_count'));
	}
	
	function excel_count()
	{
		$this->load->helper('report');
		$header_row = $this->_excel_get_header_row_count();
		$this->load->helper('spreadsheet');
		array_to_spreadsheet(array($header_row),'items_count.'.($this->config->item('spreadsheet_format') == 'XLSX' ? 'xlsx' : 'csv'));
	}
	
	
	function do_excel_import_count()
	{
		$this->check_action_permission('count_inventory');
		$this->load->model('Item_location');
		$this->load->model('Item_variations');
		$this->load->model('Item_variation_location');
		$count_id = $this->session->userdata('current_count_id');	
		$this->load->helper('demo');

		$file_info = pathinfo($_FILES['file_path']['name']);
		if($file_info['extension'] != 'xlsx' && $file_info['extension'] != 'csv')
		{
			echo json_encode(array('success'=>false,'message'=>lang('common_upload_file_not_supported_format')));
			return;
		}
		
		set_time_limit(0);
		ini_set('max_input_time','-1');
		$this->db->trans_start();
		$msg = 'do_excel_import';
		
		//$category_map = array();
		//$failCodes = array();
		if ($_FILES['file_path']['error']!=UPLOAD_ERR_OK)
		{
			$msg = lang('common_excel_import_failed');
			echo json_encode( array('success'=>false,'message'=>H($msg)) );
			$this->db->trans_complete();
			return;
		}
		else
		{	
			if (($handle = fopen($_FILES['file_path']['tmp_name'], "r")) !== FALSE)
			{
				$this->load->helper('spreadsheet');
				$file_info = pathinfo($_FILES['file_path']['name']);
				$sheet = file_to_spreadsheet($_FILES['file_path']['tmp_name'],$file_info['extension']);
				$num_rows = $sheet->getNumberOfRows();
				
				//Loop through rows, skip header row
				for($k = 2;$k<=$num_rows; $k++)
				{
					$item_identifer = $sheet->getCellByColumnAndRow(0, $k);
					if (!$item_identifer)
					{
						continue;
					}
									
					$quantity = $sheet->getCellByColumnAndRow(1, $k);
					if ($quantity === NULL)
					{
						continue;
					}
				
					if ($item_identifer && $quantity!== NULL)
					{
						if(!$this->Item->exists(does_contain_only_digits($item_identifer) ? (int)$item_identifer : -1))	
						{
							//try to get item id given an item_number
							$item_id = $this->Item->get_item_id($item_identifer);
						}
			
						$item_id = $this->Item->lookup_item_id($item_identifer);
						$item_variation_id = $this->Item_variations->lookup_item_variation_id($item_identifer);
			
						if ($item_id)
						{
							$count_info = $this->Inventory->get_count_info($count_id);
							
							$current_inventory_value = $item_variation_id ? $this->Item_variation_location->get_location_quantity($item_variation_id,$count_info->location_id) :  $this->Item_location->get_location_quantity($item_id,$count_info->location_id);
							$this->Inventory->set_count_item($count_id, $item_id,$item_variation_id, $quantity, $current_inventory_value);
						}
					}
					
				}
				
				$this->db->trans_complete();
				echo json_encode(array('success'=>true,'message'=>lang('common_import_successful')));
				
			}
			else 
			{
				echo json_encode( array('success'=>false,'message'=>lang('common_upload_file_not_supported_format')));
				$this->db->trans_complete();
				return;
			}
		}
	}
	
	function count_import_success()
	{
		$count_id = $this->session->userdata('current_count_id');	
		redirect('items/do_count/'.$count_id);
	}
	
	function finish_count($update_inventory = 0)
	{
		$this->check_action_permission('count_inventory');
		
		$count_id = $this->session->userdata('current_count_id');	
		
		if($this->Inventory->validate_count($count_id))
		{
			if ($update_inventory && $this->Employee->has_module_action_permission('items','edit_quantity', $this->Employee->get_logged_in_employee_info()->person_id))
			{	
				$this->Inventory->update_inventory_from_count($count_id);
			}
		
			$this->Inventory->set_count($count_id, 'closed');
		  echo json_encode(array('success'=>true,'message'=>lang('common_success_closing_count')));
		} else {
			echo json_encode( array('success'=>false,'message'=>lang('common_error_closing_count_check_variations')));
		}
	}
	
	function edit_count_item()
	{		
		$this->check_action_permission('count_inventory');
		$this->load->model('Item_location');
		$count_item_id = $this->input->post('pk');
		
		$name = $this->input->post('name');
		$$name = $this->input->post('value');
		
		if (isset($variation))
		{
			$count_info = $this->Inventory->get_count_info_from_count_item_id($count_item_id);
			$item_id = $count_info->item_id;
			
			if ($this->input->post('value'))
			{			
				$this->load->model('Item_variation_location');
				$actual_quantity = $this->Item_variation_location->get_location_quantity($variation,$count_info->location_id);
				
				echo json_encode(array('actual_quantity' => $actual_quantity,'delete_href' => site_url('items/delete_inventory_count_item/'.$item_id.rawurlencode('#').$variation)));
			}
			else
			{
				echo json_encode(array('actual_quantity' => NULL,'delete_href' => site_url('items/delete_inventory_count_item/'.$item_id)));
				$variation = -1;
			}
		}
		$this->Inventory->update_count_item($count_item_id, isset($variation) ? $variation : false, isset($quantity) ? $quantity : false, isset($comment) ? $comment : FALSE, isset($actual_quantity) ? $actual_quantity : FALSE);
		
		//Variation sends back json (above)
		if (!isset($variation))
		{
			$this->_reload_inventory_counts();
		}
	}
	
	function delete_inventory_count_item($identifier, $redirect = true)
	{
		$this->check_action_permission('count_inventory');
		$identifier = explode('#', rawurldecode($identifier));
		$item_id = $identifier[0];
		$item_variation_id = isset($identifier[1]) ? $identifier[1] : false;
		
		$count_id = $this->session->userdata('current_count_id');
		$this->Inventory->delete_count_item($count_id, $item_id, $item_variation_id);
		
		if($redirect)
		{
		  redirect('items/do_count/'.$count_id);		
		}
	}
	
	function delete_inventory_count($count_id, $go_back_to_status = 'open')
	{
		$this->check_action_permission('count_inventory');
		
		$this->Inventory->delete_inventory_count($count_id);
	   redirect("items/count/$go_back_to_status");		
	}
		
	function reload_inventory_counts()
	{	
		$this->check_action_permission('count_inventory');
			
		$this->_reload_inventory_counts();
	}
	
	function change_count_mode()
	{
		$this->check_action_permission('count_inventory');
		
		$this->session->set_userdata('count_mode', $this->input->post('mode'));
			
		$this->_reload_inventory_counts();
	}
	
	function _reload_inventory_counts($data = array())
	{
		$this->check_action_permission('count_inventory');
		
		$count_id = $this->session->userdata('current_count_id');
		$config = array();
		
		$data['controller_name']=strtolower(get_class());
		$config['base_url'] = site_url("items/do_count/$count_id");
		$config['per_page'] = $this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20; 
		$config['total_rows'] = $this->Inventory->get_number_of_items_counted($count_id);
		$config['uri_segment'] = 4;
		$data['per_page'] = $config['per_page'];		
		$data['count_info'] = $this->Inventory->get_count_info($count_id);

		$data['total_rows'] = $config['total_rows'];
		$this->load->library('pagination');$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		
		$data['items_counted'] = $this->Inventory->get_items_counted($count_id,	$config['per_page']);
		$ret = $this->Inventory->get_items_counted_total($count_id);
		$data['grand_total_value_by_selling_price'] = $ret['grand_total_value_by_selling_price'];
		$data['grand_total_value_by_cost_price'] = $ret['grand_total_value_by_cost_price'];

		$index = 0;

		foreach($data['items_counted'] as $item)
		{
			$data['items_counted'][$index]['category_id'] = $this->Category->get_full_path($item['category_id']);
			$data['items_counted'][$index]['cost_price'] = to_currency($item['cost_price']);
			$data['items_counted'][$index]['unit_price'] = to_currency($item['unit_price']);
			$data['items_counted'][$index]['raw_cost_price'] = $item['cost_price'];
			$data['items_counted'][$index]['raw_unit_price'] = $item['unit_price'];
			$index++;
		}
		
		$data['mode'] = $this->session->userdata('count_mode') ? $this->session->userdata('count_mode') : 'scan_and_set';
		$data['modes'] = array('scan_and_set' => lang('items_scan_and_set'), 'scan_and_add' => lang('items_scan_and_add') ); 
		
		$this->load->model('Employee_appconfig');
		$data['default_columns'] = $this->Inventory->get_default_columns();
		$data['selected_columns'] = $this->Employee->get_inventory_count_columns_to_display();
		$data['all_columns'] = array_merge($data['selected_columns'],$this->Inventory->get_displayable_columns());
		if(isset($data['error']))
		{
			$this->output->set_status_header(400);
		}
		
		$this->load->view("items/do_count_data",$data);
	}
	
	function save_column_prefs()
	{
		$this->load->model('Employee_appconfig');
		
		if ($this->input->post('columns'))
		{
			$this->Employee_appconfig->save('item_column_prefs',serialize($this->input->post('columns')));
		}
		else
		{
			$this->Employee_appconfig->delete('item_column_prefs');			
		}
	}
	
	function toggle_show_deleted($deleted=0)
	{
		$this->check_action_permission('search');
		
		$params = $this->session->userdata('items_search_data') ? $this->session->userdata('items_search_data') : array('offset' => 0, 'order_col' => 'item_id', 'order_dir' => 'desc', 'search' => FALSE, 'category_id' => FALSE, 'fields' => 'all','deleted' => 0);
		$params['deleted'] = $deleted;
		$params['offset'] = 0;
		
		$this->session->set_userdata("items_search_data",$params);
	}
	
	function inventory_comment_edit($trans_id)
	{
		$comment = $this->input->post('value');
		$this->load->model('Inventory');
		$this->Inventory->set_comment_for_inventory_log($trans_id,$comment);
	}
	
	function delete_custom_field_value($item_id,$k)
	{
		$item_info = $this->Item->get_info($item_id);
		$file_id = $item_info->{"custom_field_{$k}_value"};
		$this->load->model('Appfile');
		$this->Appfile->delete($file_id);
		$item_data = array();
		$item_data["custom_field_{$k}_value"] = NULL;
		$this->Item->save($item_data,$item_id);
	}
	
	function does_quantity_unit_exist()
	{
		$number = $this->input->post('number');
		
		echo json_encode(array('exists' => $this->Item->quantity_unit_item_number_exists($number)));
	}
	
	function save_quantity_units($quantity_units_to_edit, $quantity_units_to_delete,$item_id)
	{		
		if ($quantity_units_to_edit)
		{
			$order = 1;			
			foreach($quantity_units_to_edit as $quantity_unit_id => $data)
			{
								
				$unit_name = $data['unit_name'];
				$unit_quantity = $data['unit_quantity'];
				$unit_price = $data['unit_price'];
				$cost_price = $data['cost_price'];
				$quantity_unit_item_number = $data['quantity_unit_item_number'];
				
				if ($unit_name)
				{
					$quantity_unit_data = array('item_id'=> $item_id,'unit_name' => $unit_name, 'unit_quantity' => $unit_quantity,'unit_price' => $unit_price !== '' ? $unit_price : NULL,'cost_price' => $cost_price !== '' ? $cost_price : NULL,'quantity_unit_item_number' => $quantity_unit_item_number !== '' ? $quantity_unit_item_number : NULL);
					$this->Item->save_unit_quantity($quantity_unit_data, $quantity_unit_id < 0 ? false : $quantity_unit_id);
				}
			}
		}
		
		if ($quantity_units_to_delete)
		{
			foreach($quantity_units_to_delete as $quantity_unit_id)
			{
				$this->Item->delete_quantity_unit($quantity_unit_id);
			}
		}
		return TRUE;
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

	function save_inventory_column_prefs()
	{
		$this->load->model('Employee_appconfig');
		
		if ($this->input->post('columns'))
		{
			$this->Employee_appconfig->save('item_count_column_prefs',serialize($this->input->post('columns')));
		}
		else
		{
			$this->Employee_appconfig->delete('item_count_column_prefs');			
		}
	}

	function save_item_not_count_column_prefs()
	{
		$this->load->model('Employee_appconfig');
		
		if ($this->input->post('columns'))
		{
			$this->Employee_appconfig->save('item_not_count_column_prefs',serialize($this->input->post('columns')));
		}
		else
		{
			$this->Employee_appconfig->delete('item_not_count_column_prefs');			
		}
	}
}
?>
