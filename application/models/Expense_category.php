<?php
class Expense_category extends MY_Model
{
	/*
	Determines if a given category id exists
	*/
	function exists($category_id)
	{
		$this->db->from('expenses_categories');
		$this->db->where('id', $category_id);
		$query = $this->db->get();

		return ($query->num_rows() == 1);
	}

	function get_all_categories_and_sub_categories_as_tree()
	{
		$categories = $this->get_all_categories_and_sub_categories();

		$objects = array();
		// turn to array of objects to make sure our elements are passed by reference
		foreach ($categories as $k => $v) {
			$node = new StdClass();
			$node->id = $k;
			$node->parent_id = $v['parent_id'];
			$node->name = $v['name'];
			$node->depth = $v['depth'];
			$node->children = array();
			$objects[$k] = $node;
		}

		// list dependencies parent -> children
		foreach ($objects as $node) {
			$parent_id = $node->parent_id;
			if ($parent_id !== null) {
				$objects[$parent_id]->children[] = $node;
			}
		}

		return array_filter($objects, array('Expense_category', '_filter_to_root'));
	}

	function get_all_categories_and_sub_categories_as_indexed_by_name_key($can_cache = TRUE, $format_function = 'strtoupper')
	{
		$categories = $this->sort_categories_and_sub_categories($this->get_all_categories_and_sub_categories(NULL, 0, $can_cache));

		foreach ($categories as $index => $cat) {
			if (!isset($categories[$index]['key'])) {
				$categories[$index]['key'] = $cat['name'] . '|';
			} else {
				$categories[$index]['key'] .= $cat['name'] . '|' . $categories[$index]['key'];
			}

			if ($cat['parent_id']) {
				$this->key_categories($categories, $index, $cat['parent_id']);
			}
		}

		$indexed_categories = array();

		foreach ($categories as $category_id => $category) {
			$indexed_categories[$format_function(rtrim($category['key'], '|'))] = $category_id;
		}

		return $indexed_categories;
	}

	function get_all_categories_and_sub_categories_as_indexed_by_category_id($can_use_cache = TRUE, $parent = NULL)
	{
		if ($parent === NULL) {
			$categories = $this->sort_categories_and_sub_categories($this->get_all_categories_and_sub_categories($parent, 0, $can_use_cache));
		} else {
			//This is a hack for Woo commerce to make sure we only get a specific category tree so when we save an item we just create categories needed
			$categories = array();

			$parent_cat_info = $this->Category->get_info($parent);
			$parent_cat_info->depth = 0;
			$categories[$parent] = (array) $parent_cat_info;

			$categories_children = $this->get_all_categories_and_sub_categories($parent, 0, $can_use_cache);

			foreach ($categories_children as $key => $child_category) {
				$categories_children[$key]['depth']++;
			}

			$categories = $categories + $categories_children;
			$categories  =  $this->sort_categories_and_sub_categories($categories);
		}
		foreach ($categories as $index => $cat) {
			if (!isset($categories[$index]['key'])) {
				$categories[$index]['key'] = $cat['name'] . '|';
			} else {
				$categories[$index]['key'] .= $cat['name'] . '|' . $categories[$index]['key'];
			}

			if ($cat['parent_id']) {
				$this->key_categories($categories, $index, $cat['parent_id']);
			}
		}

		$indexed_categories = array();

		foreach ($categories as $category_id => $category) {
			$indexed_categories[$category_id] = rtrim($category['key'], '|');
		}

		return $indexed_categories;
	}

	function key_categories(&$categories, $cur_cat_index, $parent_id)
	{
		$parent_category = $categories[$parent_id];

		$categories[$cur_cat_index]['key'] = $parent_category['name'] . '|' . $categories[$cur_cat_index]['key'];

		if ($parent_category['parent_id']) {
			$this->key_categories($categories, $cur_cat_index, $parent_category['parent_id']);
		}
	}

	function get_all_categories_and_sub_categories($parent_id = NULL, $depth = 0, $can_use_cache = TRUE)
	{
		$categories = $this->get_all($parent_id, TRUE, 10000, 0, 'name', 'asc', $can_use_cache);
		if (!empty($categories)) {
			foreach ($categories as $id => $value) {
				$categories[$id]['depth'] = $depth;
			}

			foreach (array_keys($categories) as $id) {
				$subcategories = $this->get_all_categories_and_sub_categories($id, $depth + 1);

				if (!empty($subcategories)) {
					$this->load->helper('array');
					$categories = array_replace($categories, $subcategories);
				}
			}

			return $categories;
		} else {
			return $categories;
		}
	}

	function sort_categories_and_sub_categories($categories)
	{
		$objects = array();
		// turn to array of objects to make sure our elements are passed by reference
		foreach ($categories as $k => $v) {
			$node = new StdClass();
			$node->id = $k;
			$node->parent_id = $v['parent_id'];
			$node->name = $v['name'];
			$node->depth = $v['depth'];
			$node->children = array();
			$objects[$k] = $node;
		}


		// list dependencies parent -> children
		foreach ($objects as $node) {
			$parent_id = $node->parent_id;
			if ($parent_id !== null) {
				$objects[$parent_id]->children[] = $node;
			}
		}

		// clean the object list to make kind of a tree (we keep only root elements)
		$sorted = array_filter($objects, array('Expense_category', '_filter_to_root'));

		// flatten recursively
		$categories = self::_flatten($sorted);

		$return = array();

		foreach ($categories as $category) {
			$return[$category->id] = array('depth' => $category->depth, 'name' => $category->name, 'parent_id' => $category->parent_id);
		}

		return $return;
	}

	static function _filter_to_root($node)
	{
		return $node->depth === 0;
	}

	static function _flatten($elements)
	{
		$result = array();

		foreach ($elements as $element) {
			if (property_exists($element, 'children')) {
				$children = $element->children;
				unset($element->children);
			} else {
				$children = null;
			}

			$result[] = $element;

			if (isset($children)) {
				$flatened = self::_flatten($children);

				if (!empty($flatened)) {
					$result = array_merge($result, $flatened);
				}
			}
		}
		return $result;
	}

	function get_all_for_ecommerce($root_category_id = NULL)
	{
		$phppos_cats = $this->get_all_categories_and_sub_categories_as_indexed_by_category_id(FALSE, $root_category_id);

		$return = array();
		foreach (array_keys($phppos_cats) as $phppos_category_id) {
			$info = (array)$this->get_info($phppos_category_id);

			if (!$info['system_category'] && !$info['exclude_from_e_commerce']) {
				$return[$phppos_category_id] = $info;
			}
		}

		return $return;
	}

	function get_all($parent_id = NULL, $show_hidden = FALSE, $limit = 10000, $offset = 0, $col = 'name', $order = 'asc', $can_use_cache = TRUE)
	{
		static $cache = array();

		if (!$can_use_cache || !$cache) {
			$this->db->from('expenses_categories');
			$this->db->where('deleted', 0);

			$this->db->order_by($col, $order);

			foreach ($this->db->get()->result_array() as $result) {
				$cache[$result['parent_id'] ? $result['parent_id'] : 0][] = array('name' => $result['name'],  'parent_id' => $result['parent_id'], 'id' => $result['id']);
			}
		}

		$return = array();

		$key = $parent_id == NULL ? 0 : $parent_id;
		if (isset($cache[$key])) {
			foreach ($cache[$key] as $row) {
				$return[$row['id']] = array('name' => $row['name'], 'parent_id' => $row['parent_id'], 'depth' => NULL);
			}
		}

		return array_slice($return, $offset, $limit, TRUE);
	}

	function get_all_categories_including_children($show_hidden = FALSE, $limit = 10000, $offset = 0, $col = 'name', $order = 'asc', $can_use_cache = TRUE)
	{
		$return = array();

		$this->db->from('expenses_categories');
		$this->db->where('deleted', 0);

		$this->db->order_by($col, $order);

		foreach ($this->db->get()->result_array() as $result) {
			$return[] = array('name' => $result['name'], 'parent_id' => $result['parent_id'], 'id' => $result['id']);
		}

		return array_slice($return, $offset, $limit, TRUE);
	}



	function get_search_suggestions($search)
	{
		if (!trim($search)) {
			return array();
		}

		$suggestions = array();
		$this->db->select('name, id');
		$this->db->from('expenses_categories');
		$this->db->where('deleted', 0);
		$this->db->like('name', $search, 'both');

		$this->db->limit(25);
		$by_category = $this->db->get();
		foreach ($by_category->result() as $row) {
			$suggestions[] = array('id' => $row->id, 'label' => $row->name);
		}

		return $suggestions;
	}

	function get_ecommerce_category_id($category_id)
	{
		$this->db->from('expenses_categories');
		$this->db->where('id', $category_id);
		$query = $this->db->get();

		if ($query->num_rows() == 1) {
			$row = $query->row();
			return $row->ecommerce_category_id;
		} else {
			return NULL;
		}
	}

	/*
	Gets information about a particular category
	*/
	function get_info($category_id, $can_use_cache = FALSE)
	{
		static $cache;

		if ($can_use_cache && isset($cache[$category_id])) {
			return $cache[$category_id];
		}

		$this->db->from('expenses_categories');
		$this->db->where('id', $category_id);

		$query = $this->db->get();

		if ($query->num_rows() == 1) {
			$row = $query->row();
			$cache[$category_id] = $row;
			return $row;
		} else {
			//Get empty base parent object, as $item_id is NOT an item
			$item_obj = new stdClass();

			//Get all the fields from items table
			$fields = array('id', 'last_modified', 'deleted', 'parent_id', 'name');

			foreach ($fields as $field) {
				$item_obj->$field = '';
			}
			$cache[$category_id] = $item_obj;

			return $item_obj;
		}
	}

	/*
	Gets information about multiple categories
	*/
	function get_multiple_info($category_ids)
	{
		$this->db->from('expenses_categories');
		$this->db->where_in('id', $category_ids);
		$this->db->order_by("name", "asc");
		return $this->db->get();
	}

	function count_all($parent_id = NULL, $show_hidden = FALSE)
	{
		$this->db->from('expenses_categories');
		$this->db->where('deleted', 0);


		if ($parent_id === NULL) {
			$this->db->where('parent_id IS NULL', null, false);
		} else if ($parent_id) {
			$this->db->where('parent_id', $parent_id);
		}
		return $this->db->count_all_results();
	}

	function get_category_id($name)
	{
		$categories = $this->get_all_categories_and_sub_categories_as_indexed_by_name_key();
		$name = strtoupper($name);
		return isset($categories[$name]) ? $categories[$name] : NULL;
	}

	function create_categories_as_needed($category_name, &$categories_indexed_by_name)
	{
		$category_list = explode('|', $category_name);

		for ($k = 0; $k < count($category_list); $k++) {
			$category = $category_list[$k];
			$category_string = implode('|', array_slice($category_list, 0, $k + 1));

			if (!isset($categories_indexed_by_name[strtoupper($category_string)])) {
				$parent_category_search = substr($category_string, 0, strrpos($category_string, '|') === FALSE ? NULL : strrpos($category_string, '|'));
				$parent_id = isset($categories_indexed_by_name[strtoupper($parent_category_search)]) ? $categories_indexed_by_name[strtoupper($parent_category_search)] : NULL;
				$categories_indexed_by_name[strtoupper($category_string)] = $this->save($category, NULL, $parent_id);
			}
		}
	}

	function save($category_name = "", $parent_id = NULL, $category_id = FALSE)
	{
		if ($category_id == FALSE) {
			if ($category_name) {
				if ($this->db->insert('expenses_categories', array('name' => $category_name, 'parent_id' => $parent_id, 'last_modified' => date('Y-m-d H:i:s')))) {
					return $this->db->insert_id();
				}
			}

			return FALSE;
		} else {
			$this->db->where('id', $category_id);

			$update_data = array();

			$update_data['last_modified'] = date('Y-m-d H:i:s');

			if ($category_name) {
				$update_data['name'] = $category_name;
			}

			if ($category_name) {
				$update_data['parent_id'] = $parent_id;
			}

			if ($this->db->update('expenses_categories', $update_data)) {
				return $category_id;
			}
		}
		return FALSE;
	}

	/*
	Deletes one category
	*/
	function delete($category_id)
	{
		$this->db->where('id', $category_id);
		return $this->db->update('expenses_categories', array('deleted' => 1, 'last_modified' => date('Y-m-d H:i:s')));
	}

	function get_category_id_and_children_category_ids_for_category_id($category_id)
	{
		$return = array();

		$categories = $this->get_all_categories_and_sub_categories_as_indexed_by_category_id_valued_by_category_id();


		foreach ($categories as $category_id_loop => $category_listing) {
			$categories_in_category = explode('|', $category_listing);
			if (in_array($category_id, $categories_in_category)) {
				$return[] = $category_id_loop;
			}
		}

		return $return;
	}


	function get_all_categories_and_sub_categories_as_indexed_by_category_id_valued_by_category_id()
	{
		$categories = $this->sort_categories_and_sub_categories($this->get_all_categories_and_sub_categories());

		//Add id to value as attribute (needed)
		foreach (array_keys($categories) as $index) {
			$categories[$index]['id'] = $index;
		}

		foreach ($categories as $index => $cat) {
			if (!isset($categories[$index]['key'])) {
				$categories[$index]['key'] = $index . '|';
			} else {
				$categories[$index]['key'] .= $index . '|' . $categories[$index]['key'];
			}

			if ($cat['parent_id']) {
				$this->key_categories_valued_by_category_id($categories, $index, $cat['parent_id']);
			}
		}

		$indexed_categories = array();

		foreach ($categories as $category_id => $category) {
			$indexed_categories[$category_id] = rtrim($category['key'], '|');
		}

		return $indexed_categories;
	}

	function key_categories_valued_by_category_id(&$categories, $cur_cat_index, $parent_id)
	{
		$parent_category = $categories[$parent_id];

		$categories[$cur_cat_index]['key'] = $parent_category['id'] . '|' . $categories[$cur_cat_index]['key'];

		if ($parent_category['parent_id']) {
			$this->key_categories_valued_by_category_id($categories, $cur_cat_index, $parent_category['parent_id']);
		}
	}

	function get_full_path($category_id, $delimiter = ' > ')
	{
		static $categories;

		if (!$categories) {
			$categories = $this->get_all_categories_and_sub_categories_as_indexed_by_category_id();
		}

		if (isset($categories[$category_id])) {
			return str_replace('|', $delimiter, $categories[$category_id]);
		}

		return lang('common_none');
	}

	function get_root_parent_category_id($category_id)
	{
		static $categories;

		if (!$categories) {
			$categories = $this->get_all_categories_and_sub_categories_as_indexed_by_category_id_valued_by_category_id();
		}

		if (isset($categories[$category_id])) {
			$path = $categories[$category_id];
			$parts = explode('|', $path);

			return $parts[0];
		}

		return NULL;
	}

	function search_count_all($search, $deleted = 0, $limit = 10000)
	{
		if (!$deleted) {
			$deleted = 0;
		}

		$this->db->from('expenses_categories');

		if ($search) {
			$this->db->where("expenses_categories.name LIKE '" . $this->db->escape_like_str($search) . "%' and deleted=$deleted");
		} else {
			$this->db->where('expenses_categories.deleted', $deleted);
		}

		$this->db->limit($limit);
		$result = $this->db->get();
		return $result->num_rows();
	}

	/*
    Preform a search on categories
   */

	function search($search, $deleted = 0, $limit = 20, $offset = 0, $column = 'id', $orderby = 'asc')
	{

		if (!$deleted) {
			$deleted = 0;
		}


		$this->db->from('expenses_categories');
		if ($search) {
			$this->db->where("expenses_categories.name LIKE '" . $this->db->escape_like_str($search) . "%' and deleted=$deleted");
		} else {
			$this->db->where('expenses_categories.deleted', $deleted);
		}

		$this->db->order_by($column, $orderby);

		$this->db->limit($limit);
		$this->db->offset($offset);
		$return = array();

		foreach ($this->db->get()->result_array() as $result) {
			$return[$result['id']] = array('name' => $result['name'], 'parent_id' => $result['parent_id'], 'id' => $result['id']);
		}

		return $return;
	}

}
