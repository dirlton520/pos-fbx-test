<?php
require_once("Report.php");
class Cheapest_supplier extends Report
{
	function __construct()
	{
		parent::__construct();
	}

	public function getDataColumns()
	{
		$columns = array();
		$columns[] = array('data' => lang('reports_category'), 'align' => 'left');
		//$columns[] = array('data' => lang('common_item_id'), 'align' => 'left');
		$columns[] = array('data' => lang('common_item'), 'align' => 'left');
		//$columns[] = array('data' => lang('common_item_variations'), 'align' => 'left');
		$columns[] = array('data' => lang('common_item_variations'), 'align' => 'left');
		$columns[] = array('data' => lang('common_supplier'), 'align' => 'left');
		$columns[] = array('data' => lang('common_cost_price'), 'align' => 'left');

		return $columns;
	}

	public function getInputData()
	{
		$this->load->model('Category');
		$input_params = array();

		$specific_entity_data['specific_input_name'] = 'item_id';
		$specific_entity_data['specific_input_label'] = lang('common_item');
		$specific_entity_data['search_suggestion_url'] = site_url('reports/item_search');
		$specific_entity_data['view'] = 'specific_entity';

		$category_entity_data = array();
		$category_entity_data['specific_input_name'] = 'category_id';
		$category_entity_data['specific_input_label'] = lang('reports_category');
		$category_entity_data['view'] = 'specific_entity';

		$categories = array();
		$categories[''] = lang('common_all');

		$categories_phppos = $this->Category->sort_categories_and_sub_categories($this->Category->get_all_categories_and_sub_categories());

		foreach ($categories_phppos as $key => $value) {
			$name = $this->config->item('show_full_category_path') ? str_repeat('&nbsp;&nbsp;', $value['depth']) . $this->Category->get_full_path($key) : str_repeat('&nbsp;&nbsp;', $value['depth']) . $value['name'];
			$categories[$key] = $name;
		}

		$category_entity_data['specific_input_data'] = $categories;


		$input_data = Report::get_common_report_input_data(TRUE);


		if ($this->settings['display'] == 'tabular') {
			$input_params = array();
			$input_params[] = array('view' => 'date_range', 'with_time' => TRUE);
			$input_params[] = $category_entity_data;
			$input_params[] = $specific_entity_data;
			$input_params[] = array('view' => 'excel_export');
			$input_params[] = array('view' => 'locations');
			$input_params[] = array('view' => 'submit');
		} else {
			$input_params[] = array('view' => 'date_range', 'with_time' => TRUE);
			$input_params[] = $category_entity_data;
			$input_params[] = $specific_entity_data;
			$input_params[] = array('view' => 'locations');
			$input_params[] = array('view' => 'submit');
		}
		$input_data['input_report_title'] = lang('reports_report_options');
		$input_data['input_params'] = $input_params;
		return $input_data;
	}

	public function getOutputData()
	{
		$this->load->model('Category');

		$this->setupDefaultPagination();

		$tabular_data = array();

		$report_data = $this->getData();

		if ($this->settings['display'] == 'tabular') {
			foreach ($report_data as $row) {
				$data_row = array();
				$data_row[] = array('data' => $row['category'], 'align' => 'left');
				//$data_row[] = array('data' => $row['item_id'], 'align' => 'left');
				$data_row[] = array('data' => $row['name'], 'align' => 'left');
				//$data_row[] = array('data' => $row['v_id'], 'align' => 'left');
				$data_row[] = array('data' => $row['item_variation'], 'align' => 'left');
				$data_row[] = array('data' => $row['supplier'], 'align' => 'left');
				$data_row[] = array('data' => to_currency($row['unit_price']), 'align' => 'right');
				$tabular_data[] = $data_row;
			}

			$data = array(
				"view" => 'tabular',
				"title" => lang('reports_cheapest_supplier'),
				"subtitle" => date(get_date_format(), strtotime($this->params['start_date'])) . '-' . date(get_date_format(), strtotime($this->params['end_date'])),
				"headers" => $this->getDataColumns(),
				"data" => $tabular_data,
				"summary_data" => array(),
				"export_excel" => $this->params['export_excel'],
				"pagination" => $this->pagination->create_links()
			);
		} else {
			$data = array();
		}
		return $data;
	}

	private function get_items_with_receivings_query($paginate, $only_item_id = FALSE)
	{
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',', $location_ids);

		if ($this->params['category_id']) {
			if ($this->config->item('include_child_categories_when_searching_or_reporting')) {
				$category_ids = $this->Category->get_category_id_and_children_category_ids_for_category_id($this->params['category_id']);
			} else {
				$category_ids = array($this->params['category_id']);
			}
		}

		$where = $this->receiving_time_where(TRUE);


		if ($this->params['item_id']) {
			$where .= ' AND phppos_receivings_items.item_id in(' . $this->params['item_id'] . ') ';
		}

		if ($this->params['category_id']) {
			$where .= ' AND phppos_items.category_id in(' . implode(',', $category_ids) . ') ';
		}

		$tbl = "
			SELECT phppos_categories.id AS category_id, phppos_categories.name AS category, phppos_items.name, phppos_items.item_id, phppos_item_variations.name AS item_variation, phppos_item_variations.id AS v_id,
			phppos_suppliers.company_name AS supplier, item_unit_price AS unit_price
			FROM phppos_receivings_items
			LEFT JOIN phppos_receivings ON phppos_receivings_items.receiving_id = phppos_receivings.receiving_id
			LEFT JOIN phppos_items ON phppos_receivings_items.item_id = phppos_items.item_id
			LEFT JOIN phppos_categories ON phppos_categories.id = phppos_items.category_id
			LEFT JOIN phppos_suppliers ON phppos_suppliers.person_id = phppos_receivings.supplier_id
			LEFT JOIN phppos_item_variations ON phppos_receivings_items.item_variation_id = phppos_item_variations.id
			WHERE phppos_receivings.deleted = 0 AND quantity_purchased > 0 AND $where
		";

		$tb2 = "
			SELECT phppos_categories.id AS category_id, phppos_categories.name AS category, phppos_items.name, phppos_items.item_id, phppos_item_variations.name AS item_variation, phppos_item_variations.id AS v_id,
			phppos_suppliers.company_name AS supplier, MIN(item_unit_price) AS unit_price
			FROM phppos_receivings_items
			LEFT JOIN phppos_receivings ON phppos_receivings_items.receiving_id = phppos_receivings.receiving_id
			LEFT JOIN phppos_items ON phppos_receivings_items.item_id = phppos_items.item_id
			LEFT JOIN phppos_categories ON phppos_categories.id = phppos_items.category_id
			LEFT JOIN phppos_suppliers ON phppos_suppliers.person_id = phppos_receivings.supplier_id
			LEFT JOIN phppos_item_variations ON phppos_receivings_items.item_variation_id = phppos_item_variations.id
			WHERE phppos_receivings.deleted = 0 AND quantity_purchased > 0 AND $where
			GROUP BY phppos_categories.id, phppos_items.item_id, phppos_item_variations.id
		";

		$limit_offset = "";

		if ($paginate) {
			//If we are exporting NOT exporting to excel make sure to use offset and limit
			if (isset($this->params['export_excel']) && !$this->params['export_excel']) {
				$limit_offset .= " LIMIT " . $this->report_limit;

				if (isset($this->params['offset'])) {
					$limit_offset .= " OFFSET " . $this->params['offset'];
				}
			}
		}

		$query = "
			SELECT tbl.*
			FROM ($tbl) tbl
			INNER JOIN ($tb2) tb2
			ON tb2.item_id = tbl.item_id AND IFNULL(tb2.v_id,0) = IFNULL(tbl.v_id,0) AND tb2.category_id = tbl.category_id
			WHERE tb2.unit_price = tbl.unit_price
			GROUP BY tbl.category_id, tbl.item_id, IFNULL(tbl.v_id,0)
			$limit_offset
		";


		return $this->db->query($query);
	}

	public function getData()
	{

		$items_receivings_data = $this->get_items_with_receivings_query(TRUE)->result_array();

		$return = array();

		$return = $items_receivings_data;

		return $return;
	}

	function getTotalRows()
	{
		return $this->get_items_with_receivings_query(FALSE)->num_rows();
	}

	function getSummaryData()
	{
		return array();
	}
}
