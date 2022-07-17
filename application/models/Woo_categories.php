<?php
require_once(APPPATH.'models/MY_Woo.php');

class Woo_categories extends MY_Woo
{
	private $categories;
	private $batch_update_ids_for_parent_ids;
			
	const get_endpoint = "products/categories";
	const post_endpoint = "products/categories";
	const put_endpoint = "products/categories/<id>";
	const delete_endpoint = "products/categories/<id>";
	const batch_endpoint="products/categories/batch";
	
	public function __construct($woo)
	{
		parent::__construct($woo);
	}
	
	protected function reset($exclude = array())
	{
		if (!in_array('categories', $exclude))
		{	
			unset($this->categories);
		}
		
		if(!in_array('batch_update_ids_for_parent_ids', $exclude))
		{
			unset($this->batch_update_ids_for_parent_ids);
		}
		
		unset($this->woo->categories_result);
		parent::reset();
	}
	
	private static function put_endpoint($woo_category_id)
	{
		return str_replace("<id>", $woo_category_id, self::put_endpoint);
	}
	
	private static function delete_endpoint($woo_category_id)
	{
		return str_replace("<id>", $woo_category_id, self::delete_endpoint);
	}
		
	public function get_categories() 
	{
		$this->reset();
		
		$this->response = parent::do_get(self::get_endpoint);
		
		return $this->response;
	}
	
	public function delete_category($category_id)
	{
		$this->reset();
		
		try
		{
			$this->CI->load->model('Category');
			$woo_category_id = $this->CI->Category->get_ecommerce_category_id($category_id);
			
			$this->parameters['force'] = true;
			$this->response = parent::do_delete(self::delete_endpoint($woo_category_id), $this->parameters);
			
			if ($this->response)
			{
				$this->woo->unlink_category($category_id);
			}
			
			return $this->response['id'];
		}
		catch(Exception $e)
		{
			$this->woo->log("*******".lang('common_EXCEPTION').": ".var_export($e->getMessage(),TRUE));
		}
		
		return NULL;
	}
	
	public function update_category($category_id)
	{
		$this->reset();
		
		$this->CI->load->model('Category');
		$category_info = $this->CI->Category->get_info($category_id);
				
		$this->data['id'] = $category_info->ecommerce_category_id;
		$this->data['name'] = $category_info->name;
		
		$parent = $this->CI->Category->get_ecommerce_category_id($category_info->parent_id);
		
		if($parent)
		{
			$this->data['parent'] = $parent;
		}
		
		$image_url = $category_info->image_id ? app_file_url_with_extension($category_info->image_id) : null;
		
		if($image_url)
		{
			$this->data['image'] = array('src' => $image_url);
		}
		
		try
		{	
			$this->response = parent::do_put(self::put_endpoint($category_info->ecommerce_category_id));
		
			if ($this->response && isset($this->response['id']))
			{
				$this->woo->link_category($category_id, $this->response['id']);
				return $this->response['id'];
			}			
		}
		catch(Exception $e)
		{
			$this->woo->log("*******".lang('common_EXCEPTION').": ".var_export($e->getMessage(),TRUE));
		}
		
		return NULL;		
	}
	
	public function save_category($category_id)
	{
		$this->reset();
		
		$this->CI->load->model('Category');
		$category_info = $this->CI->Category->get_info($category_id);
			
		$this->data['name'] = $category_info->name;
		$this->data['slug'] = substr(str_replace(' ',  '-', $category_id.'-'.$category_info->name), 0, 28);
		$parent = $this->CI->Category->get_ecommerce_category_id($category_info->parent_id);
		if($parent)
		{
			$this->data['parent'] = $parent;
		}
		
		$image_url = $category_info->image_id ? app_file_url_with_extension($category_info->image_id) : null;
		
		if($image_url)
		{
			$this->data['image'] = array('src' => $image_url);
		}
				
		try
		{
			$this->response = parent::do_post(self::post_endpoint);
	
			if ($this->response && isset($this->response['id']))
			{
				$this->woo->link_category($category_id, $this->response['id']);
				return $this->response['id'];
			}			
		}
		catch(Exception $e)
		{
			$this->woo->log("*******".lang('common_EXCEPTION').": ".var_export($e->getMessage(),TRUE));
		}
		
		return NULL;
	}
	
	private function make_category_data($category)
	{	
		$data = array(
			'name' => $category['name'],
			'slug' => substr(str_replace(' ',  '-', $category['id'].'-'.$category['name']), 0, 28),
		);
		
		if($category['ecommerce_category_id'])
		{
			$data['id'] = $category['ecommerce_category_id'];
		}
		
		if($category['image_id'] && !$this->CI->config->item('do_not_upload_images_to_ecommerce'))
		{
			$this->CI->load->model('Appfile');
			$data['image'] = array('src' => $this->CI->Appfile->get_url_for_file_with_extension($category['image_id']));
		}
		
		if(isset($this->categories[$category['parent_id']]))
		{
			if($this->categories[$category['parent_id']]['ecommerce_category_id'])
			{
				$data['parent'] = $this->categories[$category['parent_id']]['ecommerce_category_id'];
			}
			else
			{
				//add to todo to update parent once parent is created
				$this->batch_update_ids_for_parent_ids[] = $category['id'];
			}
		}
		
		return $data;
	}

	public function batch_categories($root_category_id = NULL)
	{		
		$this->reset();
		
		$this->CI->load->model('Category');
		
		$this->batch_update_ids_for_parent_ids = array();
		
		$this->categories = $this->CI->Category->get_all_for_ecommerce($root_category_id);
						
		foreach($this->categories as $category_id => $category)
		{
			if($category['deleted'] == 0)
			{
				$data = $this->make_category_data($category);					
				
				if(!$category['ecommerce_category_id'])
				{
					//create
					$this->data['create'][] = $data;
					$this->batch_create_ids[] = $category_id;
				}
			}
			else
			{
				//delete
				$this->data['delete'][] = $category['ecommerce_category_id'];
				$this->batch_delete_ids[] = $category_id;
			}
		}
		
		$this->woo->log(lang("save_categories_to_woocommerce"));
				
		try
		{
			$this->response = parent::do_batch(self::batch_endpoint);
			
			if ($this->response)
			{				
				if ($this->batch_create_ids > 0 && (isset($this->response['create']) && count($this->response['create']) > 0))
				{
					for($k=0; $k < count($this->response['create']); $k++)
					{
						$this->woo->link_category($this->batch_create_ids[$k], $this->response['create'][$k]['id']);
					}
				}
		
				if ($this->batch_delete_ids > 0 && (isset($this->response['delete']) && count($this->response['delete']) > 0))
				{
					for($k=0;$k<count($this->response['delete']);$k++)
					{
						$this->woo->unlink_category($this->batch_delete_ids[$k]);
					}
				}
			}
		}
		catch(Exception $e)
		{
			$this->woo->log("*******".lang('common_EXCEPTION').": ".var_export($e->getMessage(),TRUE));
		}
		
		if(count($this->batch_update_ids_for_parent_ids) > 0)
		{
			$this->categories = $this->CI->Category->get_all_for_ecommerce($root_category_id);
			
			return $this->batch_update_parent_ids();
		}
		
		return true;
	}
	
	private function batch_update_parent_ids()
	{		
		$this->reset(array("categories", "batch_update_ids_for_parent_ids"));
				
		foreach($this->batch_update_ids_for_parent_ids as $id)
		{
			$category = $this->categories[$id];
						
			if(isset($this->categories[$category['parent_id']]) && $this->categories[$category['parent_id']]['ecommerce_category_id'])
			{
				$data = array(
					'id' => $category['ecommerce_category_id'],
					'parent' => $this->categories[$category['parent_id']]['ecommerce_category_id']
				);
				
				$this->data['update'][] = $data;
			}		
		}
					
		try
		{
			$this->response = parent::do_batch(self::batch_endpoint);
				
			if ($this->response && count($this->response) == $this->batch_update_ids_for_parent_ids)
			{
				return true;
			}
		}
		catch(Exception $e)
		{
			$this->woo->log("*******".lang('common_EXCEPTION').": ".var_export($e->getMessage(),TRUE));
		}
		
		return false;	
	}
}
?>