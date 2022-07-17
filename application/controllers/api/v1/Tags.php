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
class Tags extends REST_Controller {
	
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
				$this->load->model('Tag');
    }
			
		private function _tags_result_to_array($tags)
		{
				$tags_return = array(
					'id' => (int)$tags['id'],
					'name' => $tags['name'],
					
				);
				return $tags_return;
		}
		
		function index_delete($tags_id)
		{
  		$tags = $this->Tag->get_info($tags_id);
  					
  		if ($tags->id && !$tags->deleted)
  		{
				$this->Tag->delete($tags->id);
		    $tags_return = $this->_tags_result_to_array((array)$tags);
				
				$this->response($tags_return, REST_Controller::HTTP_OK);
			}
			else
			{
				$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
			}			
			
		}
				
    public function index_get($tags_id = NULL)
    {
			$this->load->helper('url');
			$this->load->helper('date');
			
			if ($tags_id === NULL)
      {
      	$search = $this->input->get('search');
				$offset = $this->input->get('offset');
				$limit = $this->input->get('limit');
				
				if ($limit !== NULL && $limit > 100)
				{
					$limit = 100;
				}

				if ($search)
				{
					$sort_col = $this->input->get('sort_col') ? $this->input->get('sort_col') : 'name';
					$sort_dir = $this->input->get('sort_dir') ? $this->input->get('sort_dir') : 'asc';
					
					$tags = $this->Tag->search($search, 0, $limit!==NULL ? $limit : 20, $offset!==NULL ? $offset : 0,$sort_col,$sort_dir);
					$total_records = $this->Tag->search_count_all($search, 0,10000);
				}
				else
				{
					$sort_col = $this->input->get('sort_col') ? $this->input->get('sort_col') : 'id';
					$sort_dir = $this->input->get('sort_dir') ? $this->input->get('sort_dir') : 'desc';
					
					$tags = $this->Tag->get_all($limit!==NULL ? $limit : 20, $offset!==NULL ? $offset : 0,$sort_col,$sort_dir);
					$total_records = $this->Tag->count_all(0);
				}
				
				$tags_return = array();
				foreach($tags as $id=>$tags)
				{
						$tags_return[] = $this->_tags_result_to_array(array('id' => $id,'name' => $tags['name']));
				}
				
				header("x-total-records: $total_records");
				
				$this->response($tags_return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
      }
      else
      {    			
      		$tags = $this->Tag->get_info($tags_id);
      		if ($tags->id)
      		{
      			$tags_return = $this->_tags_result_to_array((array)$tags);
						$this->response($tags_return, REST_Controller::HTTP_OK);
					}
					else
					{
						$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
					}			
      }
    }
		
    public function index_post($tags_id = NULL)
    {
			$tags_request = json_decode(file_get_contents('php://input'),TRUE);
			
			if ($tags_id!== NULL)
			{
				$tags_id = $this->_update_tags($tags_id,$tags_request);
				$tag_return = $this->_tags_result_to_array((array)$this->Tag->get_info($tags_id));
				$this->response($tag_return, REST_Controller::HTTP_OK);
			}
			
			if ($tag_id = $this->_create_tags($tags_request))
			{
				$tag_return = $this->_tags_result_to_array((array)$this->Tag->get_info($tag_id));
				$this->response($tag_return, REST_Controller::HTTP_OK);
			}
			
			$this->response(NULL, REST_Controller::HTTP_METHOD_NOT_ALLOWED);
			
    }
		
		
    public function batch_post()
    {
       	$this->load->model('Tag');

    		$request = json_decode(file_get_contents('php://input'),TRUE);
    		$create = isset($request['create']) ? $request['create']:  array();
    		$update = isset($request['update']) ? $request['update'] : array();
    		$delete = isset($request['delete']) ? $request['delete'] : array();
    		
    		$response = array();
    		
    		if (!empty($create))
    		{
    			$response['create'] = array();
    			
    			foreach($create as $tags_request)
    			{
    				if ($id = $this->_create_tags($tags_request))
						{
							$tags_return = $this->_tags_result_to_array((array)$this->Tag->get_info($id));
						}
						else
						{
							$tags_return = array('error' => TRUE);
						}
						$response['create'][] = $tags_return;

    			}
    		}

    		if (!empty($update))
    		{
    			$response['update'] = array();
    			
    				foreach($update as $tags_request)
    				{
							if ($this->_update_tags($tags_request['id'],$tags_request))
							{
								$tags_return = $this->_tags_result_to_array((array)$this->Tag->get_info($tags_request['id']));
							}
							else
							{
								$tags_return = array('error' => TRUE);
							}
							$response['update'][] = $tags_return;
    				}

    		}

    		if (!empty($delete))
    		{
    			$response['delete'] = array();
    			
    			foreach($delete as $id)
    			{
							if ($id === NULL)
     				  {
								$response['delete'][] = array('error' => TRUE);
			      		break;
			      	}
			      	
			  			$tags = $this->Tag->get_info($id);
										
							if ($tags->id && !$tags->deleted)
							{	
									$this->Tag->delete($tags->id);
									$tags_return = $this->_tags_result_to_array((array)$tags);
									$response['delete'][] = $tags_return;
							}
							else
							{
								$response['delete'][] = array('error' => TRUE);
							}
    			}
    		}
    		
				$this->response($response, REST_Controller::HTTP_OK);
    }
		
    private function _create_tags($tags_request)
    {
    	$this->load->model('Tag');
			return $this->Tag->save($tags_request['name']);
    }
    
    private function _update_tags($tags_id,$tags_request)
    {			
			return $this->Tag->save($tags_request['name'],$tags_id);
    }
		
}