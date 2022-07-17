<?php
require_once(APPPATH.'models/MY_Woo.php');

class Woo_tags extends MY_Woo
{
	const get_endpoint = "products/tags";
	const post_endpoint = "products/tags";
	const delete_endpoint = "products/tags/<id>";
	const batch_endpoint="products/tags/batch";
	
	public function __construct($woo)
	{
		parent::__construct($woo);
	}
	
	protected function reset()
	{
		unset($this->woo->tags_result);
		parent::reset();
	}
	
	private static function delete_endpoint($woo_tag_id)
	{
		return str_replace("<id>", $woo_tag_id, self::delete_endpoint);
	}
		
	public function get_tags() 
	{
		$this->reset();
		$this->response = parent::do_get(self::get_endpoint);
		
		return $this->response;		
	}
	
	public function delete_tag($tag_id)
	{
		$this->reset();
		
		try
		{
			$this->CI->load->model('Tag');
			
			$woo_tag_id = $this->CI->Tag->get_ecommerce_tag_id($tag_id);
			
			$this->parameters['force'] = true;
			$this->response = parent::do_delete(self::delete_endpoint($woo_tag_id), $this->parameters);
			
			if ($this->response)
			{
				$this->woo->unlink_tag($tag_id);
			}
			
			return $this->response['id'];
		}
		catch(Exception $e)
		{
			$this->woo->log("*******".lang('common_EXCEPTION').": ".var_export($e->getMessage(),TRUE));
		}
		
		return NULL;
	}
	
	public function save_tag($tag_name)
	{
		$this->reset();
		
		try
		{
			$this->CI->load->model('Tag');
			
			$this->data['name'] = $tag_name;
						
			$this->response = parent::do_post(self::post_endpoint);
			$phppos_tag_id = $this->CI->Tag->get_tag_id_by_name($tag_name);
			
			if ($phppos_tag_id !== FALSE)
			{
				$this->woo->link_tag($phppos_tag_id, $this->response['id']);
			}
			
			return $this->response['id'];
		}
		catch(Exception $e)
		{
			$this->woo->log("*******".lang('common_EXCEPTION').": ".var_export($e->getMessage(),TRUE));
		}
		
		return NULL;
	}

	public function batch_tags()
	{
		$this->reset();

		$this->CI->load->model('Tag');
		
		$woo_tags = $this->woo->get_tags();
		
		foreach($this->CI->Tag->get_all_for_ecommerce() as $phppos_tag_id => $phppos_tag_data)
		{
			if($phppos_tag_data['deleted'] == 0)
			{
				if(!isset($woo_tags[strtoupper($phppos_tag_data['name'])]))
				{
					//create
					$this->data['create'][] = array('name' => $phppos_tag_data['name']);
					$this->batch_create_ids[] = $phppos_tag_id;
				}
				else
				{
					//update
					$this->data['update'][] = array('name' => $phppos_tag_data['name'], 'id' => $woo_tags[strtoupper($phppos_tag_data['name'])]);
					$this->batch_update_ids[] = $phppos_tag_id;
				}
			}
			else
			{
				if($phppos_tag_data['ecommerce_tag_id'])
				{
					//delete
					$this->data['delete'][] = $phppos_tag_data['ecommerce_tag_id'];
					$this->batch_delete_ids[] = $phppos_tag_id;
				}
			}
		}
		
		$this->woo->log(lang("save_tags_to_woocommerce"));

		try
		{
			$this->response = parent::do_batch(self::batch_endpoint);

			if ($this->response)
			{
				if ($this->batch_create_ids > 0 && (isset($this->response['create']) && count($this->response['create']) > 0))
				{
					for($k=0; $k < count($this->response['create']); $k++)
					{
						$this->woo->link_tag($this->batch_create_ids[$k], $this->response['create'][$k]['id']);
					}
				}

				if ($this->batch_delete_ids > 0 && (isset($this->response['delete']) && count($this->response['delete']) > 0))
				{
					for($k=0;$k<count($this->response['delete']);$k++)
					{
						$this->woo->unlink_tag($this->batch_delete_ids[$k]);
					}
				}
			}
		}
		catch(Exception $e)
		{
			$this->woo->log("*******".lang('common_EXCEPTION').": ".var_export($e->getMessage(),TRUE));
		}
	}
	
}
?>