<?php

defined('BASEPATH') or exit('No direct script access allowed');

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
class Expenses_categories extends REST_Controller
{

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
		$this->load->model('Expense_category');
	}

	private function _categories_result_to_array($categories)
	{
		$categories_return = array(
			'id' => (int)$categories['id'],
			'parent_id' => (int)$categories['parent_id'],
			'name' => $categories['name'],
		);
		return $categories_return;
	}

	function index_delete($categories_id)
	{
		$categories = $this->Expense_category->get_info($categories_id);

		if ($categories->id && !$categories->deleted) {
			$this->Expense_category->delete($categories->id);
			$categories_return = $this->_categories_result_to_array((array)$categories);

			$this->response($categories_return, REST_Controller::HTTP_OK);
		} else {
			$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public function index_get($categories_id = NULL)
	{
		$this->load->helper('url');
		$this->load->helper('date');

		if ($categories_id === NULL) {
			$search = $this->input->get('search');
			$offset = $this->input->get('offset');
			$limit = $this->input->get('limit');

			if ($limit !== NULL && $limit > 100) {
				$limit = 100;
			}

			if ($search) {
				$sort_col = $this->input->get('sort_col') ? $this->input->get('sort_col') : 'name';
				$sort_dir = $this->input->get('sort_dir') ? $this->input->get('sort_dir') : 'asc';

				$categories = $this->Expense_category->search($search, 0, $limit !== NULL ? $limit : 20, $offset !== NULL ? $offset : 0, $sort_col, $sort_dir);
				$total_records = $this->Expense_category->search_count_all($search, 0, 10000);
			} else {
				$sort_col = $this->input->get('sort_col') ? $this->input->get('sort_col') : 'id';
				$sort_dir = $this->input->get('sort_dir') ? $this->input->get('sort_dir') : 'desc';

				$categories = $this->Expense_category->get_all_categories_including_children(FALSE, $limit !== NULL ? $limit : 20, $offset !== NULL ? $offset : 0, $sort_col, $sort_dir);
				$total_records = $this->Expense_category->count_all(NULL, FALSE);
			}

			$categories_return = array();
			foreach ($categories as $id => $category) {
				$categories_return[] = $this->_categories_result_to_array(array(
					'id' => (int)$category['id'],
					'parent_id' => (int)$category['parent_id'],
					'name' => $category['name'],

				));
			}

			header("x-total-records: $total_records");

			$this->response($categories_return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
		} else {
			$categories = $this->Expense_category->get_info($categories_id);
			if ($categories->id) {
				$categories_return = $this->_categories_result_to_array((array)$categories);
				$this->response($categories_return, REST_Controller::HTTP_OK);
			} else {
				$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
			}
		}
	}

	public function index_post($categories_id = NULL)
	{
		$categories_request = json_decode(file_get_contents('php://input'), TRUE);

		if ($categories_id !== NULL) {
			$categories_id = $this->_update_categories($categories_id, $categories_request);
			$category_return = $this->_categories_result_to_array((array)$this->Expense_category->get_info($categories_id));
			$this->response($category_return, REST_Controller::HTTP_OK);
		}

		if ($category_id = $this->_create_categories($categories_request)) {
			$category_return = $this->_categories_result_to_array((array)$this->Expense_category->get_info($category_id));
			$this->response($category_return, REST_Controller::HTTP_OK);
		}

		$this->response(NULL, REST_Controller::HTTP_METHOD_NOT_ALLOWED);
	}


	public function batch_post()
	{
		$this->load->model('Expense_category');

		$request = json_decode(file_get_contents('php://input'), TRUE);
		$create = isset($request['create']) ? $request['create'] :  array();
		$update = isset($request['update']) ? $request['update'] : array();
		$delete = isset($request['delete']) ? $request['delete'] : array();

		$response = array();

		if (!empty($create)) {
			$response['create'] = array();

			foreach ($create as $categories_request) {
				if ($id = $this->_create_categories($categories_request)) {
					$categories_return = $this->_categories_result_to_array((array)$this->Expense_category->get_info($id));
				} else {
					$categories_return = array('error' => TRUE);
				}
				$response['create'][] = $categories_return;
			}
		}

		if (!empty($update)) {
			$response['update'] = array();

			foreach ($update as $categories_request) {
				if ($this->_update_categories($categories_request['id'], $categories_request)) {
					$categories_return = $this->_categories_result_to_array((array)$this->Expense_category->get_info($categories_request['id']));
				} else {
					$categories_return = array('error' => TRUE);
				}
				$response['update'][] = $categories_return;
			}
		}

		if (!empty($delete)) {
			$response['delete'] = array();

			foreach ($delete as $id) {
				if ($id === NULL) {
					$response['delete'][] = array('error' => TRUE);
					break;
				}

				$categories = $this->Expense_category->get_info($id);

				if ($categories->id && !$categories->deleted) {
					$this->Expense_category->delete($categories->id);
					$categories_return = $this->_categories_result_to_array((array)$categories);
					$response['delete'][] = $categories_return;
				} else {
					$response['delete'][] = array('error' => TRUE);
				}
			}
		}

		$this->response($response, REST_Controller::HTTP_OK);
	}

	private function _create_categories($categories_request)
	{
		$this->load->model('Expense_category');

		if (!isset($categories_request['hide_from_grid'])) {
			$categories_request['hide_from_grid'] = NULL;
		}

		if (!isset($categories_request['parent_id'])) {
			$categories_request['parent_id'] = NULL;
		}

		return $this->Expense_category->save($categories_request['name'], $categories_request['parent_id'], FALSE);
	}

	private function _update_categories($categories_id, $categories_request)
	{
		$this->load->model('Expense_category');

		if (!isset($categories_request['parent_id'])) {
			$categories_request['parent_id'] = NULL;
		}

		return $this->Expense_category->save($categories_request['name'], $categories_request['parent_id'], $categories_id);
	}
}
