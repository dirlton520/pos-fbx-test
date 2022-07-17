<?php

use Automattic\WooCommerce\Client;

require_once ("interfaces/Ecom.php");
require_once APPPATH.'models/Woo_attributes.php';
require_once APPPATH.'models/Woo_attribute_terms.php';
require_once APPPATH.'models/Woo_tags.php';
require_once APPPATH.'models/Woo_tax_classes.php';
require_once APPPATH.'models/Woo_tax_rates.php';
require_once APPPATH.'models/Woo_categories.php';
require_once APPPATH.'models/Woo_products.php';
require_once APPPATH.'models/Woo_product_variations.php';
require_once APPPATH.'models/Woo_orders.php';
require_once APPPATH.'models/Woo_shipping_classes.php';

class Woo extends Ecom
{
	public $categories_result;
	public $tags_result;
	public $attributes_result;
	public $attribute_terms_result;

	public $tax_classes_result;
	public $tax_classes_taxes_result;

	public $phppos_currency_exchange_rates;

	function __construct()
	{
		ini_set('memory_limit','1024M');
		parent::__construct();
		$this->load->helper('text');
		$this->phppos_currency_exchange_rates = $this->Appconfig->get_exchange_rates()->result_array();
	}

	public function save_item_variations($item_id)
	{
		try
		{
			$this->log(lang("save_item_variations_to_woocommerce").': '.$item_id);

			$woo_product_id = $this->get_ecommerce_product_id_for_item_id($item_id);

			$Woo_product_variations	=	new Woo_product_variations($this);
			return $Woo_product_variations->batch_product_variations($item_id, $woo_product_id);
		}
		catch (Exception $e)
		{
			$this->log($e->getMessage());
		}
	}

	public function update_item_from_phppos_to_ecommerce($item_id, $data = array())
	{
		try
		{
			$this->log(lang("save_item_from_phppos_to_ecommerce").' '. $item_id);

			$Woo_products	=	new Woo_products($this);
			return $Woo_products->update_product($item_id, $data);
		}
		catch (Exception $e)
		{
			$this->log($e->getMessage());
		}
	}

	public function save_item_from_phppos_to_ecommerce($item_id)
	{
		try
		{
			$this->log(lang("save_item_from_phppos_to_ecommerce").' '. $item_id);

			$Woo_products	=	new Woo_products($this);
			if($this->get_ecommerce_product_id_for_item_id($item_id))
			{
				return $Woo_products->update_product($item_id);
			}
			else
			{
				return $Woo_products->save_product($item_id);
			}

		}
		catch (Exception $e)
		{
			$this->log($e->getMessage());
		}
	}

	function get_tags($use_cache = TRUE)
	{

		if($use_cache)
		{
			if(empty($this->tags_result))
			{
				$this->tags_result = array_map("intval", $this->get_tags_from_db());
			}

			return $this->tags_result;
		}

		$woo_tags	=	new Woo_tags($this);

		$tags = $woo_tags->get_tags();

 		$return_woo_tags = array();

 		$this->load->model('Tag');

 		$phppos_tags = array();

 		foreach($this->Tag->get_all_for_ecommerce() as $phppos_tag_id => $phppos_tag)
 		{
 			$phppos_tags[mystrtoupper($phppos_tag['name'])] = $phppos_tag_id;

 			if($phppos_tag['ecommerce_tag_id'])
 			{
 				if(is_array($tags) && !in_array($phppos_tag['ecommerce_tag_id'], array_column($tags, 'id')))
 				{
 					$this->Tag->delete($phppos_tag_id);
 				}
 			}
 		}

 		foreach($tags as $index => $tag)
 		{
 			if (!isset($phppos_tags[mystrtoupper($tag['name'])]))
 			{
 				$phppos_tag_id = $this->Tag->save(ucwords(mystrtolower($tag['name'])));
 			} else {
 				$phppos_tag_id = $phppos_tags[mystrtoupper($tag['name'])];
 			}

 			$this->link_tag($phppos_tag_id, $tag['id']);

 			$return_woo_tags[mystrtoupper(html_entity_decode($tag['name']))] =  $tag['id'];
 		}

 		return $return_woo_tags;
	}

	function get_categories($use_cache = TRUE)
	{
		if($use_cache)
		{
			if(empty($this->categories_result))
			{
				$this->categories_result = array_map("intval", $this->get_categories_from_db());
			}

			return $this->categories_result;
		}

		$categories = array();

		$woo_categories	=	new Woo_categories($this);

		foreach($woo_categories->get_categories() as $index => $category)
		{
			$categories[] = array('name' => html_entity_decode($category['name']), 'id' => $category['id'], 'parent' => $category['parent'], 'image' => isset($category['image']['src']) ? $category['image']['src'] : FALSE);
		}

		$tree = array();
		foreach ($categories as $cat)
		{
		    if (!isset($tree[$cat['id']]))
				{
					$tree[$cat['id']] = array();
				}

		    $tree[$cat['id']]['name'] = $cat['name'];

		    if (!isset($tree[$cat['parent']]))
				{
					$tree[$cat['parent']] = array();
				}

		    $tree[$cat['parent']]['children'][$cat['id']] =& $tree[$cat['id']];
		}

		if (!empty($tree))
		{
			$this->categories_result = array_flip($this->build_category_paths($tree[0]['children']));
		}
		else
		{
			$this->categories_result = array();
		}

		$this->load->model('Category');
		$categories_indexed_by_name = $this->Category->get_all_categories_and_sub_categories_as_indexed_by_name_key(false);

		$woo_category_ids = array_column($categories, 'id');

		foreach($this->categories_result as $cat_path => $woo_cat_id)
		{
			$index = array_search($woo_cat_id, $woo_category_ids);
			$image_url = $categories[$index]['image'];

			$image_file_id = false;

			if($image_url && (!isset($categories_indexed_by_name[mystrtoupper($cat_path)]) && (!$categories_indexed_by_name[mystrtoupper($cat_path)]['image_id'])))
			{
		    $allowed_extensions = array('png', 'jpg', 'jpeg', 'gif');
				$extension = mystrtolower(pathinfo(basename($image_url), PATHINFO_EXTENSION));
		    if (in_array($extension, $allowed_extensions))
		    {
		   	 	$this->load->model('Appfile');
					@$image_contents = file_get_contents($image_url);
					if ($image_contents)
					{
			    	$image_file_id = $this->Appfile->save(basename($image_url), $image_contents);
					}
				}
			}

			$this->Category->create_categories_as_needed($cat_path,$categories_indexed_by_name);

			if($image_file_id)
			{
				$this->Category->link_image($categories_indexed_by_name[mystrtoupper($cat_path)], $image_file_id);
			}

			if(isset($categories_indexed_by_name[mystrtoupper($cat_path)]))
			{
				$phppos_category_id = $categories_indexed_by_name[mystrtoupper($cat_path)];
				$this->link_category($phppos_category_id, $woo_cat_id);
			}
		}

		return $this->categories_result;
	}

	function sync_inventory_changes()
	{
		$this->log(lang("sync_inventory_changes"));
		set_time_limit(0);
		$this->sync_inventory_changes_items();
		$this->sync_inventory_changes_variations();
		return TRUE;
	}

	function sync_inventory_changes_items()
	{
		require_once APPPATH.'models/MY_Woo.php';


		$woocommerce	=	new MY_Woo($this);

		$send_call = true;
		$per_page = $woocommerce->woo_read_chunk_size;
		$products = array();
		$page = 1;


		while($send_call == true)
		{

			try
			{
				$result_products = $woocommerce->get('products', array('type' => 'simple','per_page'=>$per_page, 'page'=>$page));
				$this->log("get : products");

				sleep($woocommerce->woo_read_sleep);
			}
			catch(Exception $e)
			{
				$this->log("*******".lang('common_EXCEPTION').": ".var_export($e->getMessage(),TRUE));
			}
			$page++;

			$woo_product_ids = array(-1);
			foreach($result_products as $woo_product)
			{
				$woo_product_ids[] = $woo_product['id'];
			}

			$this->db->select('items.*,SUM(phppos_location_items.quantity) as quantity', FALSE);
			$this->db->from('items');
			$this->db->join('location_items','items.item_id = location_items.item_id','left');
			$this->db->where_in('location_id',$this->ecommerce_store_locations);
			$this->db->where_in('ecommerce_product_id', $woo_product_ids);
			$this->db->group_by('items.item_id');
			$items_result = $this->db->get();

			$items_info = array();
			foreach($items_result->result_array() as $item_result)
			{
				$items_info[$item_result['ecommerce_product_id']] = $item_result;
			}

			foreach($result_products as $woo_product)
			{
				if (isset($items_info[$woo_product['id']]))
				{
					$item_quantity=$woo_quantity="";
					$woo_quantity=$woo_product['stock_quantity'];
					$item_id=$items_info[$woo_product['id']]['item_id'];
					if($item_id!=NULL)
					{
						$item_quantity=$items_info[$woo_product['id']]['quantity'];
					}
					if($item_quantity==="" && $woo_quantity==="")
					{
						//quantity field not available in woocommerce and phppos
						$actual_quantity=0;
					}
					else if($item_quantity==="")
					{
						//quantity field not available in phppos but available in woocommerce
						$actual_quantity=$woo_quantity;
					}
					else if($woo_quantity==="")
					{
						//quantity field not available in woocommerce but available in phppos
						$actual_quantity=$item_quantity;
					}
					else
					{
						//quantity field present both on woocommerce and phppos
						$prev_quantity=   $items_info[$woo_product['id']]['ecommerce_product_quantity'];
						$pos_difference = $prev_quantity - $item_quantity;
						$woo_difference = $prev_quantity - $woo_quantity;
						$difference_sum	= $pos_difference + $woo_difference;
						$actual_quantity = $prev_quantity - $difference_sum;
					}


					if ($actual_quantity != $items_info[$woo_product['id']]['ecommerce_product_quantity'])
					{
						$this->db->where('ecommerce_product_id', $woo_product['id']);
						$this->db->update('items',array('ecommerce_product_quantity' => (int)$actual_quantity));
					}

					//update quantity to woocommerce
					if( $actual_quantity != $woo_quantity )
					{
						$data = array(
							'stock_quantity' => (int)$actual_quantity
						);
						try
						{
							$woocommerce->put('products/'.$woo_product['id'], $data);
							$this->log("put : products/".$woo_product['id']);

							$this->log(lang('item inventory changed in woo')." ".$woo_product['id'] .' ('.to_quantity($actual_quantity).')');

							sleep($woocommerce->woo_write_sleep);
						}
						catch(Exception $e)
						{
							$this->log("*******".lang('common_EXCEPTION').": ".var_export($e->getMessage(),TRUE));
							continue;

						}
					}
					//update quantity to phppos
					if( $actual_quantity != $item_quantity)
					{
						$difference = (int)$actual_quantity - (int)$item_quantity;
						$current_location_quantity= $this->Item_location->get_location_quantity($item_id,$this->ecommerce_store_location);
						$updated_quantity = $current_location_quantity + $difference;;

						if($item_id!=NULL && $difference!=0){
						$cron_job_entry=lang('woo_cron_job_entry');
						$this->db->insert('inventory',array('trans_date'=>date('Y-m-d H:i:s'),'trans_current_quantity' => $updated_quantity,'trans_items' => $item_id,'trans_user'=>1,'trans_comment'=>$cron_job_entry,'trans_inventory'=> $difference,'location_id'=>$this->ecommerce_store_location));

						$this->db->where(array('item_id' => $item_id,'location_id'=>$this->ecommerce_store_location));
						$this->log(lang("item inventory changed in php pos").' '.$item_id .' ('.$updated_quantity.')');
						$this->db->update('location_items',array('quantity'=>$updated_quantity));

						}
					}
				}
			}


			if( count($result_products) < $per_page ){
				$send_call=false;
			}

		}
	}

	function sync_inventory_changes_variations()
	{
		require_once APPPATH.'models/MY_Woo.php';


		$woocommerce	=	new MY_Woo($this);
		$woo_product_variations	=	new Woo_product_variations($this);

		$send_call = true;
		$per_page = $woocommerce->woo_read_chunk_size;
		$products = array();
		$page = 1;


		while($send_call == true)
		{

			try
			{
				$result_products = $woocommerce->get('products', array('type' => 'variable','per_page'=>$per_page, 'page'=>$page));
				$this->log("get : products");

				sleep($woocommerce->woo_read_sleep);
			}
			catch(Exception $e)
			{
				$this->log("*******".lang('common_EXCEPTION').": ".var_export($e->getMessage(),TRUE));
			}
			$page++;

			foreach($result_products as $woo_product)
			{
				$variations = $woo_product_variations->get_product_variations($woo_product['id']);

				$woo_variation_ids = array(-1);
				foreach($variations as $woo_variation)
				{
					$woo_variation_ids[] = $woo_variation['id'];
				}

				$this->db->select('item_variations.*,SUM(phppos_location_item_variations.quantity) as quantity', FALSE);
				$this->db->from('item_variations');
				$this->db->join('location_item_variations','item_variations.id = location_item_variations.item_variation_id','left');
				$this->db->where_in('location_id',$this->ecommerce_store_locations);
				$this->db->where_in('ecommerce_variation_id', $woo_variation_ids);
				$this->db->group_by('item_variations.id');
				$items_variation_result = $this->db->get();

				$item_varations_info = array();
				foreach($items_variation_result->result_array() as $item_variation_result)
				{
					$item_varations_info[$item_variation_result['ecommerce_variation_id']] = $item_variation_result;
				}

				foreach($variations as $woo_variation)
				{
					if (isset($item_varations_info[$woo_variation['id']]))
					{
						$item_quantity=$woo_quantity="";
						$woo_quantity=$woo_variation['stock_quantity'];
						$item_variation_id=$item_varations_info[$woo_variation['id']]['id'];
						if($item_variation_id!=NULL)
						{
							$item_quantity=$item_varations_info[$woo_variation['id']]['quantity'];
						}
						if($item_quantity==="" && $woo_quantity==="")
						{
							//quantity field not available in woocommerce and phppos
							$actual_quantity=0;
						}
						else if($item_quantity==="")
						{
							//quantity field not available in phppos but available in woocommerce
							$actual_quantity=$woo_quantity;
						}
						else if($woo_quantity==="")
						{
							//quantity field not available in woocommerce but available in phppos
							$actual_quantity=$item_quantity;
						}
						else
						{
							//quantity field present both on woocommerce and phppos
							$prev_quantity=   $item_varations_info[$woo_variation['id']]['ecommerce_variation_quantity'];
							$pos_difference = $prev_quantity - $item_quantity;
							$woo_difference = $prev_quantity - $woo_quantity;
							$difference_sum	= $pos_difference + $woo_difference;
							$actual_quantity = $prev_quantity - $difference_sum;
						}


						if ($actual_quantity != $item_varations_info[$woo_variation['id']]['ecommerce_variation_quantity'])
						{
							$this->db->where('ecommerce_variation_id', $woo_variation['id']);
							$this->db->update('item_variations',array('ecommerce_variation_quantity' => (int)$actual_quantity));
						}

						//update quantity to woocommerce
						if( $actual_quantity != $woo_quantity )
						{
							$data = array(
								'stock_quantity' => (int)$actual_quantity
							);
							try
							{
								$woocommerce->put('products/'.$woo_product['id'].'/variations/'.$woo_variation['id'], $data);
								$this->log("put : products/".$woo_product['id'].'/variations/'.$woo_variation['id']);

								$this->log(lang('item inventory changed in woo')." ".$woo_product['id'].'-'.$woo_variation['id'] .' ('.to_quantity($actual_quantity).')');

								sleep($woocommerce->woo_write_sleep);
							}
							catch(Exception $e)
							{
								$this->log("*******".lang('common_EXCEPTION').": ".var_export($e->getMessage(),TRUE));
								continue;

							}
						}
						//update quantity to phppos
						if( $actual_quantity != $item_quantity)
						{
							$difference = (int)$actual_quantity - (int)$item_quantity;
							$current_location_quantity= $this->Item_variation_location->get_location_quantity($item_variation_id,$this->ecommerce_store_location);
							$updated_quantity = $current_location_quantity + $difference;

							if($item_variation_id!=NULL && $difference!=0){
							$cron_job_entry=lang('woo_cron_job_entry');
							$this->db->insert('inventory',array('trans_date'=>date('Y-m-d H:i:s'),'trans_current_quantity' => $updated_quantity,'trans_items' => $item_varations_info[$woo_variation['id']]['item_id'],'item_variation_id' => $item_varations_info[$woo_variation['id']]['id'],'trans_user'=>1,'trans_comment'=>$cron_job_entry,'trans_inventory'=> $difference,'location_id'=>$this->ecommerce_store_location));

							$this->db->where(array('item_variation_id' => $item_variation_id,'location_id'=>$this->ecommerce_store_location));
							$this->log(lang("item inventory changed in php pos").' '.$item_variation_id .' ('.$updated_quantity.')');
							$this->db->update('location_item_variations',array('quantity'=>$updated_quantity));

							}
						}
					}
				}
			}


			if( count($result_products) < $per_page ){
				$send_call=false;
			}

		}
	}

	public function save_category($category_id)
	{
		try
		{
			$this->log(lang("save_category_to_woocommerce").': '.$category_id);

			$Woo_categories	=	new Woo_categories($this);
			return $Woo_categories->save_category($category_id);
		}
		catch (Exception $e)
		{
			$this->log($e->getMessage());
		}
	}

	public function update_category($category_id)
	{
		try
		{
			$this->log(lang("update_category_to_woocommerce").': '.$category_id);

			$Woo_categories	=	new Woo_categories($this);
			return $Woo_categories->update_category($category_id);
		}
		catch (Exception $e)
		{
			$this->log($e->getMessage());
		}
	}

	public function delete_category($category_id)
	{
		try
		{
			$this->log(lang("delete_category_from_woocommerce").': '.$category_id);

			$Woo_categories	=	new Woo_categories($this);
			return $Woo_categories->delete_category($category_id);
		}
		catch (Exception $e)
		{
			$this->log($e->getMessage());
		}
	}

	public function save_tag($tag_name)
	{
		$this->log(lang("save_tag_to_woocommerce").': '.$tag_name);

		$woo_tags	=	new Woo_tags($this);
		return $woo_tags->save_tag($tag_name);
	}

	public function delete_tag($tag_id)
	{

		$this->log(lang("delete_tag_from_woocommerce").': '.$tag_id);

		$woo_tags	=	new Woo_tags($this);
		return $woo_tags->delete_tag($tag_id);
	}

	public function export_phppos_categories_to_ecommerce($root_category_id = null)
	{
		try
		{
			$this->log(lang("export_phppos_categories_to_ecommerce"));

			require_once APPPATH.'models/MY_Woo.php';
			$Woo_categories	=	new Woo_categories($this);

			$Woo_categories->batch_categories($root_category_id);
		}
		catch (Exception $e)
		{
			$this->log($e->getMessage());
			return false;
		}

		return true;
	}

	public function get_woo_category_id($category_path, $woo_cats = NULL)
	{
		if ($woo_cats == NULL)
		{
			$woo_cats = $this->get_categories();
		}

		if (isset($woo_cats[$category_path]))
		{
			return $woo_cats[$category_path];
		}

		return NULL;
	}

	public function get_woo_tag_id($tag_name,$woo_tags = NULL)
	{
		if ($woo_tags == NULL)
		{
			$woo_tags = $this->get_tags();
		}

		if (isset($woo_tags[mystrtoupper($tag_name)]))
		{
			return $woo_tags[mystrtoupper($tag_name)];
		}

		return NULL;
	}

	public function export_phppos_tags_to_ecommerce()
	{
		$this->log(lang("export_phppos_tags_to_ecommerce"));

		$woo_tags	=	new Woo_tags($this);
		return $woo_tags->batch_tags();
	}

	function export_phppos_items_to_ecommerce()
	{
		$this->log(lang("export_phppos_items_to_ecommerce"));

		$Woo_products = new Woo_products($this);
		return $Woo_products->batch_products();
	}

	function import_ecommerce_items_into_phppos()
	{
		$this->log(lang("import_ecommerce_items_into_phppos"));

		$woo_products	=	new Woo_products($this);
		$woo_product_variations	=	new Woo_product_variations($this);

		$products = $woo_products->get_products();

		$ecom_ids = array_column($products,'id');

		if (!empty($ecom_ids))
		{
			if(is_array($ecom_ids))
			{
				$this->db->from('items');

				$this->db->group_start();
				$ecom_ids_chunk = array_chunk($ecom_ids,25);
				foreach($ecom_ids_chunk as $ecom_ids)
				{
					$this->db->or_where_in('ecommerce_product_id',$ecom_ids);
				}
				$this->db->group_end();
				$result = $this->db->get();

				$phppos_items = array();
				while($row = $result->unbuffered_row('array'))
				{
					$phppos_items[$row['ecommerce_product_id']] = $row;
				}
			}
		}

		foreach($products as $product)
		{
			//Skip hidden products
			if ($product['catalog_visibility'] == 'hidden')
			{
				continue;
			}

			//skip other products that aren't simple or variable
			if (!($product['type'] == 'simple' || $product['type'] == 'variable'))
			{
				continue;
			}

			$item_row = isset($phppos_items[$product['id']]) ? $phppos_items[$product['id']] : FALSE;

			$item_id = isset($phppos_items[$product['id']]['item_id']) ? $phppos_items[$product['id']]['item_id'] : FALSE;


			$item_last_modified = isset($item_row['last_modified']) ? strtotime($item_row['last_modified']) : 0;
			$ecommerce_last_modified = strtotime(getDateFromGMT($product['date_modified_gmt']));

			if($ecommerce_last_modified > $item_last_modified)
			{
					$item_id = $this->add_update_item_from_ecommerce_to_phppos($product, $item_row);
					$item_row = (array)$this->Item->get_info($item_id);
			}

			//get variations
			if($product['type'] == 'variable')
			{
				$variations = $woo_product_variations->get_product_variations($product['id']);

				$ecom_ids = array_column($variations,'id');

				if (!empty($ecom_ids))
				{
					if(is_array($ecom_ids))
					{
						$this->db->from('item_variations');

						$this->db->group_start();
						$ecom_ids_chunk = array_chunk($ecom_ids,25);
						foreach($ecom_ids_chunk as $ecom_ids)
						{
							$this->db->or_where_in('ecommerce_variation_id',$ecom_ids);
						}
						$this->db->group_end();
						$result = $this->db->get();

						$phppos_variations = array();
						while($row = $result->unbuffered_row('array'))
						{
							$phppos_variations[$row['ecommerce_variation_id']] = $row;
						}
					}
				}

				$this->load->model('Item_variations');

				foreach($variations as $variation)
				{
					$variation_row = isset($phppos_variations[$variation['id']]) ? $phppos_variations[$variation['id']] : FALSE;
					//check last modified
					$pos_last_modified = strtotime($variation_row['last_modified']);
					$woo_last_modified = strtotime($variation['date_modified_gmt'].'+00:00');

						if($woo_last_modified > $pos_last_modified)
						{
							$variation_id = $this->add_update_item_variation_from_ecommerce_to_phppos($item_row, $variation, $variation_row);
							$variation_row = (array)$this->Item_variations->get_info($variation_id);
						}
					}

				}
		}

		return true;
	}

	private function add_update_item_variation_from_ecommerce_to_phppos($item_row, $woo_variation, $variation_row = false)
	{

		$this->log(lang("add_update_item_variation_from_ecommerce_to_phppos").": ".$woo_variation['id']);

		$variation_id = isset($variation_row['id']) ? $variation_row['id'] : false;

		$ecommerce_last_modified = getDateFromGMT($woo_variation['date_modified_gmt']);

		$this->load->model('Item_variations');

		$attribute_value_ids = array();

		foreach($woo_variation['attributes'] as $attribute)
		{
			$attribute_id = $this->get_attribute_id_from_ecommerce_attribute_id($attribute['id'], $item_row['item_id'],  $attribute['name']);
			$attribute_value_ids[] = $this->lookup_attribute_value_id_from_attribute_id_and_option($attribute_id, $attribute['option']);
		}

		if(!$variation_id)
		{
			//attempt to match with existing variations
			$variation_id = $this->Item_variations->lookup($item_row['item_id'], $attribute_value_ids);
		}

		$sku_sync_field = $this->config->item('sku_sync_field') ? $this->config->item('sku_sync_field') : 'item_number';

		$item_variation = array(
			'item_id' => $item_row['item_id'],
			'ecommerce_variation_id' => $woo_variation['id'],
			'ecommerce_last_modified' => $ecommerce_last_modified,
			'last_modified' => $ecommerce_last_modified,
			'item_number' => $woo_variation['sku'] && ($item_row[$sku_sync_field] != $woo_variation['sku']) ? $woo_variation['sku'] : null,
			'deleted' => 0,
		);

		if ($woo_variation['regular_price'] !== '' && !$this->config->item('online_price_tier'))
		{
			$item_variation['unit_price'] = $woo_variation['regular_price'];
		}
		elseif($woo_variation['regular_price'] !== '' && !$variation_id)//New variation
		{
			$item_variation['unit_price'] = $woo_variation['regular_price'];
		}


		$item_variation['promo_price'] = $woo_variation['sale_price'] ? $woo_variation['sale_price'] : null;
		$item_variation['start_date'] = $woo_variation['date_on_sale_from'] ? $woo_variation['date_on_sale_from'] : null;
		$item_variation['end_date'] = $woo_variation['date_on_sale_to'] ? $woo_variation['date_on_sale_to'] : null;

		$new_variation = !$variation_id;
		$variation_id = $this->Item_variations->save($item_variation, $variation_id, $attribute_value_ids);

		//This is a brand new variation we want to make sure we setup stock correctly
		if ($variation_id && $new_variation && $woo_variation['stock_quantity'] !== NULL)
		{
			$ecommerce_product_quantity_data = array('ecommerce_variation_quantity' => $woo_variation['stock_quantity']);
			$this->Item_variations->save($ecommerce_product_quantity_data,$variation_id);

		  $item_variation_location_data = array(
            'item_variation_id'=>$variation_id,
            'location_id'=>$this->ecommerce_store_location,
            'quantity'=>$woo_variation['stock_quantity']
      );
			$item_variation_location_data = array('item_variation_id'=>$variation_id,'location_id'=>$this->ecommerce_store_location,'quantity'=>$woo_variation['stock_quantity']);
			$this->load->model('Item_variation_location');
			$this->Item_variation_location->save($item_variation_location_data, $variation_id, $this->ecommerce_store_location);
		}

		$this->load->library('image_lib');
		if ($variation_id && isset($woo_variation['image']['id']) && $woo_variation['image']['id'])
		{
			$this->load->model('Item');

			$image_file_id = $this->get_image_file_id_for_ecommerce_image($woo_variation['image']['id']);

			if(!$image_file_id)
			{
		    $allowed_extensions = array('png', 'jpg', 'jpeg', 'gif');
				$extension = mystrtolower(pathinfo(basename($woo_variation['image']['src']), PATHINFO_EXTENSION));

		    if (in_array($extension, $allowed_extensions))
		    {
			@$image_contents = file_get_contents_curl($woo_variation['image']['src']);
			$tmpFilename = tempnam(ini_get('upload_tmp_dir'), 'woo');
			file_put_contents($tmpFilename,$image_contents);


			    $config['image_library'] = 'gd2';
			    $config['source_image']	= $tmpFilename;
			    $config['create_thumb'] = FALSE;
			    $config['maintain_ratio'] = TRUE;
			    $config['width']	 = 1200;
			    $config['height']	= 900;
					$this->image_lib->initialize($config);
			    $this->image_lib->resize();
		   	    $this->load->model('Appfile');
			    $image_contents = file_get_contents($tmpFilename);
					if ($image_contents)
					{
			    	$image_file_id = $this->Appfile->save(basename($woo_variation['image']['src']), $image_contents);
					}
				}

				if ($image_file_id)
				{
					$this->Item->add_image($item_row['item_id'], $image_file_id);
					$this->Item->link_image_to_ecommerce($image_file_id, $woo_variation['image']['id']);
				}
			}

			$this->Item->save_image_metadata($image_file_id, $woo_variation['image']['name'],$woo_variation['image']['alt'], $variation_id);
	 }

	 return $variation_id;
	}

	private function add_update_item_from_ecommerce_to_phppos($woo_product, $item_row = array())
	{
		$this->log(lang("add_update_item_from_ecommerce_to_phppos").": ".$woo_product['name']);

		static $phppos_cats;
		static $ecom_cats;

		static $phppos_attributes;

		if (!$phppos_cats)
		{
			$this->load->model('Category');
			$phppos_cats = array_flip($this->Category->get_all_categories_and_sub_categories_as_indexed_by_category_id(FALSE));
		}

		if (!$ecom_cats)
		{
			$ecom_cats = array_flip($this->get_categories());
		}

		if(!$phppos_attributes)
		{
			$this->load->model('Item_attribute');
			$phppos_attributes = $this->Item_attribute->get_all_indexed_by_name_and_item_id();
		}

		$item_id = isset($item_row['item_id']) ? $item_row['item_id'] : false;

		$product_name = $woo_product['name'];
		$product_id = $woo_product['id'];

		$weight = $woo_product['weight'];
		$dimensions = $woo_product['dimensions'];

		$shipping_class = $woo_product['shipping_class'];

		$quantity = $woo_product['stock_quantity'];
		$item_number = $woo_product['sku'];
		$product_description = $woo_product['description'];
		$product_short_description = $woo_product['short_description'];

		$ecommerce_last_modified = getDateFromGMT($woo_product['date_modified_gmt']);

		$product_category=NULL;

		$product_quantity=0;
		$product_categories=$woo_product['categories'];

		if(count($product_categories)>0)
		{
			$product_selected_category = $product_categories[0]['id'];

			if (isset($phppos_cats[$ecom_cats[$product_selected_category]]))
			{
				$product_category = $phppos_cats[$ecom_cats[$product_selected_category]];
			}
			
			$secondary_categories = array();
			
			for($k=1;$k<count($product_categories);$k++)
			{
				$product_selected_category = $product_categories[$k]['id'];
				if (isset($phppos_cats[$ecom_cats[$product_selected_category]]))
				{
					$secondary_categories[] = $phppos_cats[$ecom_cats[$product_selected_category]];
				}
			}
		}

		$product_tags = $woo_product['tags'];
		$pos_tags = '';

		if(count($product_tags)>0)
		{
			foreach($product_tags as $pro_tag)
			{
				$this->db->from('tags');
				$this->db->where('name',$pro_tag['name']);
				$result = $this->db->get();
				if ($result->num_rows() > 0)
				{
					$tag_from_phppos=$result->row_array();
					$pos_tags.=",".$tag_from_phppos['id'];
				}
			}
		}


		$item_array = array(
			'name'=>$product_name,
			'description' => $this->config->item('woo_enable_html_desc') ?  $product_short_description : strip_tags($product_short_description),
			'long_description' => $this->config->item('woo_enable_html_desc') ? $product_description : strip_tags($product_description),
			'category_id'=>$product_category,
			'ecommerce_product_id'=>$product_id,
			'ecommerce_last_modified' => $ecommerce_last_modified,
			'last_modified' => $ecommerce_last_modified,
			'tax_included' => $this->config->item('prices_include_tax') ? 1 : 0,
		);

		//New item
		if (!$item_id)
		{
			$item_array['commission_percent'] = NULL;
			$item_array['commission_fixed'] = NULL;
			$item_array['commission_percent_type'] = '';
		}

		if ($weight)
		{
			$item_array['weight'] = $weight;
		}

		if(isset($dimensions['length']) && $dimensions['length'] && isset($dimensions['width']) && $dimensions['width'] && isset($dimensions['height']) && $dimensions['height'])
		{
			$item_array['length'] = $dimensions['length'];
			$item_array['width'] = $dimensions['width'];
			$item_array['height'] = $dimensions['height'];
		}

		if ($shipping_class)
		{
			$item_array['ecommerce_shipping_class_id'] = $shipping_class;
		}

		if ($woo_product['tax_class'])
		{
			$this->load->model('Tax_class');
			if ($tax_class_id = $this->Tax_class->get_tax_class_id_from_ecommerce_tax_id($woo_product['tax_class']))
			{
				$item_array['tax_class_id'] = $tax_class_id;
				$item_array['override_default_tax'] = 1;
			}
		}
		else
		{
			$item_array['override_default_tax'] = 0;
		}

		if ($woo_product['regular_price'] !== '' && !$this->config->item('online_price_tier'))
		{
			$item_array['unit_price'] = $woo_product['regular_price'];
		}
		elseif($woo_product['regular_price'] !== '' && !$item_id)//New item
		{
			$item_array['unit_price'] = $woo_product['regular_price'];
		}

		if ($item_number)
		{
			//make sure to save back to the right number field
			$sync_field = $this->config->item('sku_sync_field') ? $this->config->item('sku_sync_field') : 'item_number';

			if ($sync_field != 'item_id')
			{
				$item_array[$sync_field] = $item_number;
			}

			if(!$item_id)
			{
				$this->load->model('Item');
				$item_id = $this->Item->get_item_id($item_number);
			}
		}


		$this->load->model('Item_location');
		$item_location_info = $this->Item_location->get_info($item_id,$this->config->item('ecom_store_location') ? $this->config->item('ecom_store_location') : 1);

		if (!$item_location_info->promo_price && $woo_product['sale_price'])
		{
			$item_array['promo_price'] = $woo_product['sale_price'] ? $woo_product['sale_price'] : null;
		}
		elseif($woo_product['sale_price'])
		{
			$item_location_data['promo_price'] = $woo_product['sale_price'] ? $woo_product['sale_price'] : null;
			$this->Item_location->save($item_location_data,$item_id,$this->config->item('ecom_store_location') ? $this->config->item('ecom_store_location') : 1);
		}

		$item_array['start_date'] = $woo_product['date_on_sale_from'] ? $woo_product['date_on_sale_from'] : null;
		$item_array['end_date'] = $woo_product['date_on_sale_to'] ? $woo_product['date_on_sale_to'] : null;

		$this->load->model('Item');

		//If we cannot save item then stop trying anything else
		if (!$this->Item->save($item_array,$item_id))
		{
			return;
		}
		
		$new_item = !$item_id;

		$item_id = isset($item_array['item_id']) ? $item_array['item_id'] : $item_id;

		foreach($secondary_categories as $category_id)
		{
			$sec_category_id = NULL;
			$this->Item->save_secondory_category($item_id,$category_id,$sec_category_id);
		}


		//This is a brand new item we want to make sure we setup stock correctly
		if ($new_item && $woo_product['stock_quantity'] !== NULL)
		{
			$ecommerce_product_quantity_data = array('ecommerce_product_quantity' => $woo_product['stock_quantity']);
			$this->Item->save($ecommerce_product_quantity_data,$item_id);

			$location_item_array = array('item_id'=>$item_id,'location_id'=>$this->ecommerce_store_location,'quantity'=>$woo_product['stock_quantity']);
			$this->load->model('Item_location');
			$this->Item_location->save($location_item_array, $item_id, $this->ecommerce_store_location);
		}
		if(count($product_tags)>0)
		{
			$this->load->model('Tag');
			$this->Tag->save_tags_for_item($item_id, $pos_tags);
		}


		$product_attributes = $woo_product['attributes'];


		$this->load->model('Item_attribute');
		$this->load->model('Item_attribute_value');

		$all_custom_attrs_for_item = array();

		foreach($phppos_attributes as $name => $attribute)
		{
			if(isset($attribute[$item_id]))
			{
				$all_custom_attrs_for_item[] = $attribute[$item_id];
			}
		}

		foreach($all_custom_attrs_for_item as $key => $attr)
		{
			$found = false;

			foreach($product_attributes as $woo_attr)
			{
				if (mystrtoupper($attr['name']) == mystrtoupper($woo_attr['name']))
				{
					$found = TRUE;
					continue;
				}
			}

			if (!$found)
			{
				$this->Item_attribute->delete($attr['id']);
			}
		}

		if(count($product_attributes) > 0)
		{
			$attribute_ids_to_save = array();
			$attribute_value_ids_to_save = array();

			foreach($product_attributes as $product_attribute)
			{
				if($product_attribute['id'] && isset($phppos_attributes[mystrtoupper($product_attribute['name'])][0]))
				{
					//global
					$pos_attribute = $phppos_attributes[mystrtoupper($product_attribute['name'])][0];
					$attribute_ids_to_save[] = $pos_attribute['id'];
				}
				else
				{
					if(isset($phppos_attributes[mystrtoupper($product_attribute['name'])][$item_id]))
					{
						//existing custom
						$pos_attribute = $phppos_attributes[mystrtoupper($product_attribute['name'])][$item_id];
						$attribute_ids_to_save[] = $pos_attribute['id'];
					}
					else
					{
						//create new custom
						$pos_attribute = array('name' => $product_attribute['name'], 'item_id' => $item_id);
						$attribute_ids_to_save[] = $this->Item_attribute->save($pos_attribute);
					}

					//make sure we have all the terms we need
					foreach($product_attribute['options'] as $option)
					{
						if(!isset($pos_attribute['terms'][mystrtoupper($option)]))
						{
							$id = $this->Item_attribute_value->save($option,$pos_attribute['id']);
							$pos_attribute['terms'][mystrtoupper($option)] = array('id' => $id);
						}
					}
				}

				foreach($product_attribute['options'] as $option)
				{
					$attribute_value_ids_to_save[] = $pos_attribute['terms'][mystrtoupper($option)]['id'];
				}

			}

			$this->Item_attribute->save_item_attributes($attribute_ids_to_save, $item_id);
			$this->Item_attribute_value->save_item_attribute_values($item_id, $attribute_value_ids_to_save);

		}
			$this->load->library('image_lib');
		if (isset($woo_product['images'][0]) && $woo_product['images'][0]['id'])
		{
			foreach($woo_product['images'] as $woo_image)
			{
				$image_file_id = $this->get_image_file_id_for_ecommerce_image($woo_image['id']);

				if(!$image_file_id)
				{
			    $allowed_extensions = array('png', 'jpg', 'jpeg', 'gif');
					$extension = mystrtolower(pathinfo(basename($woo_image['src']), PATHINFO_EXTENSION));

			    if (in_array($extension, $allowed_extensions))
			    {
				@$image_contents = file_get_contents_curl($woo_image['src']);
				$tmpFilename = tempnam(ini_get('upload_tmp_dir'), 'woo');
				file_put_contents($tmpFilename,$image_contents);

				    $config['image_library'] = 'gd2';
				    $config['source_image']	= $tmpFilename;
				    $config['create_thumb'] = FALSE;
				    $config['maintain_ratio'] = TRUE;
				    $config['width']	 = 1200;
				    $config['height']	= 900;
				    $this->image_lib->initialize($config);
				    $this->image_lib->resize();
			   	 	$this->load->model('Appfile');
				   $image_contents = file_get_contents($tmpFilename);


						if ($image_contents)
						{
				    	$image_file_id = $this->Appfile->save(basename($woo_image['src']), $image_contents);
						}
					}

					if (isset($image_file_id))
					{
						$this->Item->add_image($item_id, $image_file_id);
						$this->Item->link_image_to_ecommerce($image_file_id, $woo_image['id']);

						//Features image
						if ($woo_product['images'][0]['id'] == $woo_image['id'])
						{
							$this->Item->set_main_image($item_id, $image_file_id);
						}
					}
				}

  			$this->Item->save_image_metadata($image_file_id, $woo_image['name'],$woo_image['alt']);
			}
		}

		$cron_job_entry=lang('woo_cron_job_entry');

		return $item_id;
	}

	public function delete_item($item_id)
	{
		$this->log(lang("ecom_delete_item").": ".$item_id);

		$woo_products	=	new Woo_products($this);
		$woo_products->delete_product($item_id);
	}

	public function delete_items($item_ids)
	{
		$this->log(lang("ecom_delete_item").": ".var_export($item_ids, TRUE));

		$woo_products	=	new Woo_products($this);
		$woo_products->batch_products($item_ids, array('delete'));
	}

	public function delete_all()
	{
		$woo_products	=	new Woo_products($this);
		$woo_products->batch_products(array(), array('delete'));
	}

	public function undelete_item($item_id)
	{
		$this->log(lang("ecom_undelete_item").": ".$item_id);

		$woo_products	=	new Woo_products($this);
		$woo_products->delete_product($item_id, array('create'));

		parent::unlink_item($item_id);
	}

	public function undelete_items($item_ids)
	{
		$this->log(lang("ecom_undelete_item").": ".var_export($item_ids, TRUE));

		$woo_products	=	new Woo_products($this);
		$woo_products->batch_products($item_ids, array('create'));
	}

	public function undelete_all()
	{
		$this->log(lang("ecom_undelete_item").": ".var_export($item_ids, TRUE));

		$woo_products	=	new Woo_products($this);
		$woo_products->batch_products(array(), array('create'));
	}

	function import_ecommerce_tags_into_phppos()
	{
		//no cache
		$woo_tags = $this->get_tags(FALSE);
	}

	function import_ecommerce_categories_into_phppos()
	{
		//Get categories with NO cache to force a full fetch
		return $this->get_categories(FALSE);
	}

	function import_ecommerce_attributes_into_phppos()
	{
		$this->log(lang("import_ecommerce_attributes_into_phppos"));

		try
		{
			$attributes = $this->get_attributes(false);
			foreach($attributes as $attribute)
			{
				$this->get_attribute_values($attribute['ecommerce_attribute_id'], false);
			}

			return true;
		}
		catch(Exception $e)
		{
			$this->log("*******".lang('common_EXCEPTION').": ".var_export($e->getMessage(),TRUE));
		}
	}

	function export_phppos_attributes_to_ecommerce()
	{
		$this->log(lang("export_phppos_attributes_to_ecommerce"));

		try
		{
			$woo_attributes	=	new Woo_attributes($this);
			$woo_attributes->batch_attributes();
		}
		catch(Exception $e)
		{
			$this->log("*******".lang('common_EXCEPTION').": ".var_export($e->getMessage(),TRUE));
		}
	}

	function get_attributes($use_cache = TRUE)
	{
		$this->log(lang("get_attributes"));

		if($use_cache)
		{
			if(empty($this->attributes_result))
			{
				$this->attributes_result = $this->get_attributes_from_db();
			}

			if(!empty($this->attributes_result))
			{
				return $this->attributes_result;
			}
		}

		$woo_attributes	=	new Woo_attributes($this);
		$attributes = $woo_attributes->get_attributes();

		$this->load->model('Item_attribute');

		$phppos_attributes = array();

		foreach($this->Item_attribute->get_all()->result_array() as $phppos_attribute)
		{
			$phppos_attributes[mystrtoupper($phppos_attribute['name'])] = $phppos_attribute['id'];
		}

		$return_attributes = array();

		foreach($attributes as $woo_attr)
		{
			$return_attributes[] = array(
			'name' => $woo_attr['name'],
			'ecommerce_attribute_id' => $woo_attr['id'],
			);

			if (!isset($phppos_attributes[mystrtoupper($woo_attr['name'])]))
			{
				$item_attr_data = array('name' => ucwords(mystrtolower($woo_attr['name'])));
				$this->Item_attribute->save($item_attr_data);
				$phppos_attribute_id = $item_attr_data['id'];
			}
			else
			{
				$phppos_attr_id = $phppos_attributes[mystrtoupper($woo_attr['name'])];
				$this->link_attribute($phppos_attr_id, $woo_attr['id']);
			}

		}

		return $return_attributes;
	}

	function get_attribute_values($woo_attribute_id, $use_cache = TRUE)
	{
		if($use_cache)
		{
			if(empty($this->attribute_terms_result))
			{
				$this->attribute_terms_result = $this->get_attribute_values_from_db($woo_attribute_id);
			}

			if(!empty($this->attribute_terms_result))
			{
				return $this->attribute_terms_result;
			}
		}

		$this->log(lang("get_attribute_values"));

		$woo_attribute_terms	=	new Woo_attribute_terms($this);
		$attribute_terms = $woo_attribute_terms->get_attribute_terms($woo_attribute_id);

		$attribute_id = $this->get_attribute_id_from_ecommerce_attribute_id($woo_attribute_id);

		if(!$attribute_id)
		{
			$this->get_attributes(false);
			$attribute_id = $this->get_attribute_id_from_ecommerce_attribute_id($woo_attribute_id);
		}

		if (!$attribute_id)
		{
			return array();
		}

		$phppos_attribute_values = array();

		$this->load->model('Item_attribute_value');

		foreach($this->Item_attribute_value->get_values_for_attribute($attribute_id)->result_array() as $phppos_attribute_value)
		{
			$phppos_attribute_values[$woo_attribute_id.'|'.mystrtoupper($phppos_attribute_value['name'])] = $phppos_attribute_value['id'];
		}

		$return_attribute_values = array();

		foreach($attribute_terms as $attribute_term)
		{
		 	$return_attribute_values[] = array(
				'name' => $attribute_term['name'],
		 		'ecommerce_attribute_term_id' => $attribute_term['id'],
		 	);

			if (isset($phppos_attribute_values[$woo_attribute_id.'|'.mystrtoupper($attribute_term['name'])]))
			{
				$phppos_attribute_value_id = $phppos_attribute_values[$woo_attribute_id.'|'.mystrtoupper($attribute_term['name'])];
				$this->link_attribute_value($phppos_attribute_value_id, $attribute_term['id']);
			}
			else
			{
				$phppos_attribute_value_id = $this->Item_attribute_value->save($attribute_term['name'],$attribute_id);
				$this->link_attribute_value($phppos_attribute_value_id, $attribute_term['id']);
			}
		}

		return $return_attribute_values;
	}

	function import_ecommerce_orders_into_phppos()
	{
		$this->log(lang("import_ecommerce_orders_into_phppos"));

		$this->load->model('Sale');
		$this->load->model('Customer');
		$this->load->model('Delivery');
		$this->load->model('Item');
		$this->load->model('Item_variations');
		$this->load->model('Item_location');

		$Woo_orders	=	new Woo_orders($this);

		if ($this->config->item('import_all_past_orders_for_woo_commerce'))
		{
			$params = array();
			//Reset it
			$this->Appconfig->save('import_all_past_orders_for_woo_commerce',0);
		}
		else
		{
			//Get orders 192 hours (8 days).It won't matter if we get the same order twice but 			we don't want to miss any orders. This can happen if orders take awhile to become completed
			$after_date = $this->config->item('last_ecommerce_sync_date') ? gmdate('c', strtotime($this->config->item('last_ecommerce_sync_date')) - (((24*8)*60) * 60)) : gmdate('c', strtotime('1970'));
			$params = array('after' => $after_date);
		}
		$orders_since_last_sync = $Woo_orders->get_orders($params);

		foreach($orders_since_last_sync as $order)
		{
			if ($this->config->item('ecommerce_only_sync_completed_orders'))
			{
				if ($order['status'] == 'completed')
				{
					$this->save_order($order);
				}
			}
			else
			{
				$this->save_order($order);
			}
		}

		$open_orders = $this->get_ecommerce_order_ids_not_completed();
		if (!empty($open_orders))
		{
			$open_orders_chunk = array_chunk($open_orders,5);
			foreach($open_orders_chunk as $open_orders)
			{
				$params = array('include' => $open_orders);
				$open_orders = $Woo_orders->get_orders($params);
				foreach($open_orders as $order)
				{
					if ($this->config->item('ecommerce_only_sync_completed_orders'))
					{
						if ($order['status'] == 'completed')
						{
							$this->save_order($order);
						}
					}
					else
					{
						$this->save_order($order);
					}
				}
			}
		}

		$params = array('status' => 'cancelled','after' => gmdate('c', strtotime('-1 month')));
		$cancelled_orders = $Woo_orders->get_orders($params);
		foreach($cancelled_orders as $order)
		{
			$sale_id = $this->get_sale_id_for_ecommerce_order_id($order['id']);
			$this->db->where('sale_id', $sale_id);
			$this->db->update('sales',array('deleted' => 1));
		}

		return TRUE;
	}

	private function save_order($order)
	{
		$sales_data = array();

		$woo_id = $order['id'];
		$exchange_rate = $this->get_order_currency_exchange_rate($order);
		$customer_id = $this->save_woo_customer_from_order($order);
		$sales_totals = $this->get_sale_totals($order);
		$sale_id = $this->get_sale_id_for_ecommerce_order_id($woo_id);
		
		//If we are importing orders suspended we don't want to overwrite this after 1st import so we don't break edits
		if ($sale_id && $this->config->item('import_ecommerce_orders_suspended'))
		{
			return;
		}
		
		if (!$sale_id && $this->config->item('import_ecommerce_orders_suspended'))
		{	
			$sales_data['suspended'] = $this->config->item('ecommerce_suspended_sale_type_id');
		}
		
		$sales_data['employee_id'] = 1;
		
		if ($this->config->item('ecommerce_only_sync_completed_orders'))
		{
			$sales_data['sale_time'] = date('Y-m-d H:i:s',strtotime($order['date_completed_gmt'].'+00:00'));			
		}
		else
		{
			$sales_data['sale_time'] = date('Y-m-d H:i:s',strtotime($order['date_created_gmt'].'+00:00'));
		}
		
		$sales_data['location_id'] = $this->ecommerce_store_location;
		$sales_data['customer_id'] = $customer_id;
		$sales_data['is_ecommerce'] = 1;
		$sales_data['subtotal'] = $this->convert_currency_value($sales_totals['subtotal'], $exchange_rate);
		$sales_data['total'] = $this->convert_currency_value($sales_totals['total'], $exchange_rate);
		$sales_data['tax'] = $this->convert_currency_value($sales_totals['tax'], $exchange_rate);
		$sales_data['profit'] = $this->convert_currency_value($sales_totals['profit'], $exchange_rate);
		$sales_data['total_quantity_purchased'] = $sales_totals['total_quantity_purchased'];
		$sales_data['comment'] = 'WooCommerce #'.$woo_id;
      if ($order['currency'] !== $this->config->item('currency_code') && $exchange_rate != 1) {
		   $sales_data['comment'] .= ' (converted from ' . $order['currency'] . ' at exchange rate ' . $exchange_rate . ')';
      }
		$sales_data['ecommerce_order_id'] = $woo_id;
		$sales_data['ecommerce_status'] = $order['status'];
		$sales_data['payment_type'] = lang('common_online');

		if ($sale_id)
		{
			$this->db->where('sale_id', $sale_id);
			$this->db->update('sales',$sales_data);

			//Delete sale data
			$this->db->delete('sales_payments', array('sale_id' => $sale_id));
			$this->db->delete('sales_items_taxes', array('sale_id' => $sale_id));
			$this->db->delete('sales_items', array('sale_id' => $sale_id));
			$this->db->delete('sales_item_kits_taxes', array('sale_id' => $sale_id));
			$this->db->delete('sales_item_kits', array('sale_id' => $sale_id));
			$this->db->delete('sales_coupons', array('sale_id' => $sale_id));
			$this->db->delete('sales_deliveries', array('sale_id' => $sale_id));
		}
		else
		{
			$this->db->insert('sales',$sales_data);
			$sale_id = $this->db->insert_id();
		}

		$this->db->insert('sales_payments',
         array(
            'sale_id'=> $sale_id, 'payment_date' => $sales_data['sale_time'] ,'payment_type' =>lang('common_online'),
            'payment_amount' => $this->convert_currency_value($sales_totals['total'], $exchange_rate)
         )
      );

		if ($customer_id)
		{
				$this->save_delivery($order,$sale_id,$customer_id);
		}

		$line_items = $order['line_items'];

		$counter = 0;
		foreach($line_items as $line_item)
		{
			$this->save_line_item($line_item,$sale_id,$counter, $exchange_rate);
			$counter++;
		}

		if ((float)$order['shipping_total'])
		{
			$this->save_custom_line_item($order['shipping_total'],$order['shipping_total'],$order['shipping_tax'],$this->Item->create_or_update_delivery_item(FALSE),$sale_id,$counter, 1, $exchange_rate);
			$counter++;
		}

		foreach($order['refunds'] as $refund_line)
		{
			$total = $refund_line['total'];

			$this->save_custom_line_item($total,0,0,$this->Item->create_or_update_refund_item(FALSE),$sale_id,$counter, 1, $exchange_rate);
			$counter++;
		}

		foreach($order['fee_lines'] as $fee_line)
		{
			$total_tax = 0;
			foreach($fee_line['taxes'] as $taxes)
			{
				$total_tax+=$taxes['total'];
			}

			$total = $fee_line['total'];

			$this->save_custom_line_item($total,0,$total_tax,$this->Item->create_or_update_fee_item(FALSE),$sale_id,$counter, 1, $exchange_rate);
			$counter++;
		}
	}

   /**
    * Get the exchange rate to use when importing this order from WooCommerce.
    *
    * Based on the "currency" defined in the order in WooCommerce, we try to find an exchange rate defined in PHPPOS.
    * If one is available, we return that exchange rate - else we return 1.00 (which will result in NO currency
    * conversion).
    *
    * @param array $order
    * @return float
    */
	function get_order_currency_exchange_rate(array $order) {
      if ($order['currency'] !== $this->config->item('currency_code')) {
         // CONVERT CURRENCY BASED ON RATE IN PHP-POS
         $this->log(sprintf(lang("import_ecommerce_order_currency_conversion_required"), $order['id']));

         $source_currency_rate = null;
         foreach($this->phppos_currency_exchange_rates as $currency_exchange_rate) {
            if ($currency_exchange_rate['currency_code_to'] == $order['currency']) {
               // Currency found - return it's exchange rate
               return $currency_exchange_rate['exchange_rate'];
            }
         }

         // Get this far - we have no exchange rate defined
         $this->log(sprintf(lang('import_ecommerce_order_source_currency_unknown'), $order['id']));
         // Fallback to previous PHPPOS functionality - don't convert currency
         return 1.00;
      } else {
         // Order is in the same currency as PHPPOS - so conversion rate is 1.00
         return 1.00;
      }
   }

   /**
    * Maths to convert a currency value based on an exchange rate.
    *
    * Input 5.00 and 1.20 will result in (5.00*1.20) == 6.00.
    * Input 5.00 and 1.00 will result in (5.00*1.00) == 5.00.
    *
    * @param float $currency_value
    * @param float $exchange_rate
    * @return float
    */
   function convert_currency_value($currency_value, $exchange_rate) {
      return round(
         $currency_value / $exchange_rate,
         $this->config->item('number_of_decimals') !== NULL && $this->config->item('number_of_decimals') != '' ? (int)$this->config->item('number_of_decimals') : 2
      );
   }

	function save_custom_line_item($line_unit_price,$line_cost_price,$total_tax,$item_id,$sale_id,$line_index,$quantity=1, $exchange_rate = 1.00)
	{
		$line_unit_price = $this->convert_currency_value((float)$line_unit_price, $exchange_rate);
		$line_cost_price = $this->convert_currency_value((float)$line_cost_price, $exchange_rate);
		$total_tax = $this->convert_currency_value((float)$total_tax, $exchange_rate);

		if ($line_unit_price)
		{
			if ($line_unit_price)
			{
				$tax_percent = (float)($total_tax/$line_unit_price)*100;
			}
			else
			{
				$tax_percent = 0;
			}

			$sales_items = array();

			$sales_items['sale_id'] = $sale_id;
			$sales_items['item_id'] = $item_id;
			$line_unit_price = $line_unit_price;

			$sales_items['quantity_purchased'] = $quantity;
			$sales_items['line'] = $line_index;
			$sales_items['item_unit_price'] = $line_unit_price;
			$sales_items['item_cost_price'] = $line_cost_price;

			$sales_items['subtotal']=$line_unit_price;
			$sales_items['total']=$line_unit_price+$total_tax;
			$sales_items['tax']=$total_tax;
			$sales_items['profit']=0;

			$this->db->insert('sales_items',$sales_items);

			if ($tax_percent)
			{
				$sales_items_taxes = array(
					'name' => lang('common_sales_tax_1'),
					'sale_id' => $sale_id,
					'item_id' => $item_id,
					'line' => $line_index,
					'percent' => round($tax_percent,2),
				);

				$this->db->insert('sales_items_taxes',$sales_items_taxes);
			}
		}

	}

   /**
    * @param $line_item
    * @param $sale_id
    * @param $line_index
    * @param float $exchange_rate
    */
	private function save_line_item($line_item,$sale_id,$line_index, $exchange_rate = 1.00)
	{
		$sales_items = array();

		$woo_product_id = $line_item['product_id'];
		$woo_variation_id = $line_item['variation_id'];

		$phppos_item_id = $this->get_item_id_for_ecommerce_product($woo_product_id);
		$phppos_variation_id = $this->get_variation_id_for_ecommerce_product_variation($woo_variation_id);

		$sales_items['sale_id'] = $sale_id;
		$sales_items['item_id'] = $phppos_item_id;
		$sales_items['item_variation_id'] = $phppos_variation_id;
		$quantity = $line_item['quantity'];
		$subtotal = $this->convert_currency_value($line_item['total'], $exchange_rate);//Price before tax
		$total_tax = 0;
		foreach($line_item['taxes'] as $taxes)
		{
			$total_tax+=$this->convert_currency_value($taxes['total'], $exchange_rate);
		}

		$total = $subtotal+$total_tax;

		$tax_percent = (float)$subtotal ? ($total_tax/$subtotal)*100 : 0;
		$unit_subtotal = (float)$quantity ? $subtotal/$quantity : $quantity;


		$sales_items['quantity_purchased'] = $quantity;
		$sales_items['line'] = $line_index;
		$sales_items['item_unit_price'] = $subtotal/$quantity;
		$item_info = $this->Item->get_info($phppos_item_id);
		$item_location_info = $this->Item_location->get_info($phppos_item_id);
		$variation_info = $this->Item_variations->get_info($phppos_variation_id);

		if ($variation_info && $variation_info->unit_price)
		{
			$sales_items['regular_item_unit_price_at_time_of_sale'] = $variation_info->unit_price;
		}
		else
		{
			$sales_items['regular_item_unit_price_at_time_of_sale'] = ($item_location_info && $item_location_info->unit_price) ? $item_location_info->unit_price : $item_info->unit_price;
		}


		if ($variation_info && $variation_info->cost_price)
		{
			$sales_items['item_cost_price'] = $variation_info->cost_price;
		}
		else
		{
			$sales_items['item_cost_price'] = $item_location_info->cost_price ? $item_location_info->cost_price : $item_info->cost_price;
		}

		$profit = ($sales_items['item_unit_price']* $quantity) - ($sales_items['item_cost_price'] * $quantity);

		$sales_items['subtotal']=$this->convert_currency_value($subtotal, $exchange_rate);
		$sales_items['total']=$this->convert_currency_value($subtotal+$total_tax, $exchange_rate);
		$sales_items['tax']=$this->convert_currency_value($total_tax, $exchange_rate);
		$sales_items['profit']=$this->convert_currency_value($profit, $exchange_rate);

		$this->db->insert('sales_items',$sales_items);

		if ($tax_percent)
		{
			$sales_items_taxes = array(
				'name' => lang('common_sales_tax_1'),
				'sale_id' => $sale_id,
				'item_id' => $phppos_item_id,
				'line' => $line_index,
				'percent' => round($tax_percent,2),
			);

			$this->db->insert('sales_items_taxes',$sales_items_taxes);
		}
	}

	private function get_sale_totals($order)
	{
		$refund_total = 0;
		foreach($order['refunds'] as $refund_line)
		{
			$refund_total += $refund_line['total'];
		}
		//Postive amount so we can subtract
		$refund_total = abs($refund_total);

		$return = array('subtotal' => $order['total'] - $order['total_tax'] - $refund_total,'total' => $order['total'] - $refund_total,'tax' => $order['total_tax'],'profit' => 0,'total_quantity_purchased' => 0);

		$line_items = $order['line_items'];

		foreach($line_items as $line_item)
		{
			$woo_product_id = $line_item['product_id'];
			$woo_variation_id = $line_item['variation_id'];

			$phppos_item_id = $this->get_item_id_for_ecommerce_product($woo_product_id);
			$phppos_variation_id = $this->get_variation_id_for_ecommerce_product_variation($woo_variation_id);

			$quantity = $line_item['quantity'];
			$subtotal = $line_item['subtotal'];
			$total_tax = $line_item['total_tax'];
			$total = $subtotal+$total_tax;

			$tax_percent = (float)$subtotal ? ($total_tax/$subtotal)*100 : 0;
			$unit_subtotal = (float)$quantity ? $subtotal/$quantity : 0;

			$item_info = $this->Item->get_info($phppos_item_id);
			$item_location_info = $this->Item_location->get_info($phppos_item_id);
			$variation_info = $this->Item_variations->get_info($phppos_variation_id);


			if ($variation_info && $variation_info->cost_price)
			{
				$item_cost_price = $variation_info->cost_price;
			}
			else
			{
				$item_cost_price = $item_location_info->cost_price ? $item_location_info->cost_price : $item_info->cost_price;
			}
			$return['profit'] += ($unit_subtotal * $quantity) - ($item_cost_price * $quantity);
			$return['total_quantity_purchased']+=$quantity;

		}
		$return['profit'] -= $refund_total;
		return $return;
	}

	private function save_woo_customer_from_order($order)
	{
		$customer_shipping = $order['shipping'];
		$customer_billing = $order['billing'];
		$customer = array_merge($customer_billing,$customer_shipping);


		//If this info is empty for shipping then get from billing
		$empty_shipping_key_checks = array('first_name','last_name','email','phone','address_1','address_2','city','state','postcode','country','company');
		foreach($empty_shipping_key_checks as $key_check)
		{
			if(!$customer[$key_check])
			{
				$customer[$key_check] = $customer_billing[$key_check];
			}
		}

		if ($customer['email'])
		{
			//Existing customer lookup by email
			if ($customer['email'] && ($phppos_customer_info = $this->Customer->get_info_by_email($customer['email'])))
			{
				$sale_customer_id = $phppos_customer_info->person_id;
			}
			else
			{
				$person_data = array(
				'first_name'=>$customer['first_name'],
				'last_name'=>$customer['last_name'],
				'email'=>$customer['email'],
				'phone_number'=>$customer['phone'],
				'address_1'=>$customer['address_1'],
				'address_2'=>$customer['address_2'],
				'city'=>$customer['city'],
				'state'=>$customer['state'],
				'zip'=>$customer['postcode'],
				'country'=>$customer['country'],
				);


				$customer_data=array(
					'company_name' => $customer['company'],
				);

				$this->Customer->save_customer($person_data, $customer_data);

				$sale_customer_id = $person_data['person_id'];
			}

			return $sale_customer_id;
		}

		return NULL;
	}

	function save_delivery($order,$sale_id,$customer_id)
	{
		$actual_shipping_date = $order['date_completed_gmt'] ? date('Y-m-d H:i:s',strtotime($order['date_completed_gmt'].'+00:00')) : NULL;
		$estimated_shipping_date = $order['date_paid_gmt'] ? date('Y-m-d H:i:s',strtotime($order['date_paid_gmt'].'+00:00')) : NULL;

		$data = array(
			'sale_id' => $sale_id,
			'shipping_address_person_id' => $customer_id,
			'status' => NULL,
			'actual_shipping_date' =>$actual_shipping_date,
			'estimated_shipping_date' =>$estimated_shipping_date,
		);

		$this->Delivery->save($data);
	}

	function get_tax_class_rates($phppos_tax_class_id,$use_cache = TRUE)
	{
		if($use_cache)
		{
			if(empty($this->tax_classes_taxes_result))
			{
				$this->tax_classes_taxes_result = $this->get_tax_classes_taxes_from_db($phppos_tax_class_id);
			}

			return $this->tax_classes_taxes_result;
		}

		$woo_tax_rates	=	new Woo_tax_rates($this);
		$tax_rates = $woo_tax_rates->get_tax_rates($phppos_tax_class_id);
 		$return_woo_tax_rates = array();

 		$this->load->model('Tax_class');

 		$phppos_woo_to_phppos = array();

 		foreach($this->Tax_class->get_taxes($phppos_tax_class_id, false) as $phppos_tax_rate)
 		{
			if ($phppos_tax_rate['ecommerce_tax_class_tax_rate_id'])
			{
				$phppos_woo_to_phppos[$phppos_tax_rate['ecommerce_tax_class_tax_rate_id']] = $phppos_tax_rate;
			}
 		}

 		foreach($tax_rates as $woo_tax_rate)
 		{
			$order = $woo_tax_rate['id'];
 			if (!isset($phppos_woo_to_phppos[$woo_tax_rate['id']]))
 			{
				$tax_rate = array('order' => $order, 'tax_class_id' => $phppos_tax_class_id, 'name' => $woo_tax_rate['name'],'percent' => $woo_tax_rate['rate'], 'tax_class_id' => $phppos_tax_class_id,'ecommerce_tax_class_tax_rate_id' => $woo_tax_rate['id']);
				$this->Tax_class->save_tax($tax_rate);
 			}
			else
			{
				$tax_rate = array('order' => $order, 'tax_class_id' => $phppos_tax_class_id, 'name' => $woo_tax_rate['name'],'percent' => $woo_tax_rate['rate'], 'tax_class_id' => $phppos_tax_class_id,'ecommerce_tax_class_tax_rate_id' => $woo_tax_rate['id']);
				$this->Tax_class->save_tax($tax_rate,$phppos_woo_to_phppos[$woo_tax_rate['id']]['id']);
			}

 		}

		$this->tax_classes_taxes_result = $this->get_tax_classes_taxes_from_db($phppos_tax_class_id);
		return $this->tax_classes_taxes_result;

	}

	function get_tax_classes($use_cache = TRUE)
	{
		if($use_cache)
		{
			if(empty($this->tax_classes_result))
			{
				$this->tax_classes_result = $this->get_tax_classes_from_db();
			}

			return $this->tax_classes_result;
		}

		$woo_tax_classes	=	new Woo_tax_classes($this);

		$tax_classes = $woo_tax_classes->get_tax_classes();

 		$return_woo_tax_classes = array();

 		$this->load->model('Tax_class');

 		$phppos_tax_classes = array();

 		foreach($this->Tax_class->get_all_for_ecommerce() as $phppos_tax_class_id => $phppos_tax_class)
 		{
 			$phppos_tax_classes[mystrtoupper($phppos_tax_class['name'])] = $phppos_tax_class_id;
 		}

 		foreach($tax_classes as $index => $tax_class)
 		{
 			if (!isset($phppos_tax_classes[mystrtoupper($tax_class['name'])]))
 			{
				$tax_class_data = array('name' => $tax_class['name']);
				$this->Tax_class->save($tax_class_data);
 				$phppos_tax_class_id = $tax_class_data['id'];
 			} else {
 				$phppos_tax_class_id = $phppos_tax_classes[mystrtoupper($tax_class['name'])];
 			}

 			$this->link_tax_class($phppos_tax_class_id, $tax_class['slug']);

 			$return_woo_tax_classes[mystrtoupper(html_entity_decode($tax_class['name']))] =  $tax_class['slug'];
 		}

 		return $return_woo_tax_classes;

	}

	function import_tax_classes_into_phppos()
	{
		$this->log(lang("import_tax_classes_into_phppos"));
		//Get categories with NO cache to force a full fetch
		$return = $this->get_tax_classes(FALSE);
 		$this->load->model('Tax_class');

 		foreach(array_keys($this->Tax_class->get_all_for_ecommerce()) as $phppos_tax_class_id)
		{
			$return = $this->get_tax_class_rates($phppos_tax_class_id,FALSE);
		}
		return $return;
	}

	function export_tax_classes_into_phppos()
	{
		$this->log(lang("export_tax_classes_into_phppos"));
		$woo_tax_classes	=	new Woo_tax_classes($this);

		return $woo_tax_classes->batch_tax_classes();
	}

	public function save_tax_class($tax_class_id)
	{
		$this->log(lang("common_save_tax_class"));
		$woo_tax_class	=	new Woo_tax_classes($this);
		return $woo_tax_class->save_tax_class($tax_class_id);
	}

	function import_shipping_classes_into_phppos()
	{
		$this->log(lang("import_shipping_classes_into_phppos"));
		$woo_shipping_classes	=	new Woo_shipping_classes($this);
		$this->Appconfig->save('woo_shipping_classes',serialize($woo_shipping_classes->get_shipping_classes()));
	}
}
?>