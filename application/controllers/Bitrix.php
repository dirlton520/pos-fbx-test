<?php

require_once ("Secure_area.php");
require_once ("interfaces/Idata_controller.php");
class Bitrix extends Secure_area implements Idata_controller
{
	private $ecom_model;
	private $awsApiUrl;
	private $bitrixConfig;

	function __construct()
	{
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
		parent::__construct('bitrix');
		$this->lang->load('bitrix');
		$this->lang->load('module');
		$this->load->model('Appconfig');
		$this->load->model('BitrixSubscription');
		$this->load->model('AllBitrixItem');
		$this->load->model('BitrixFieldMapping');
		$this->load->model('Item');
		$this->load->model('Category');
		$this->load->helper('bitrix_helper');
		$this->load->helper('bitrix_import_property_helper');
		$this->bitrixConfig = getBitrixConfig();
		$this->awsApiUrl = $this->bitrixConfig['aws']['api_url'];
		
		if ($this->Appconfig->get_key_directly_from_database("ecommerce_platform"))
		{
			require_once (APPPATH."models/interfaces/Ecom.php");
			$this->ecom_model = Ecom::get_ecom_model();
		}
	}	

	function check_unsubscribe_permission($action_id)
	{
		if ($this->Employee->has_module_action_permission($this->module_id, $action_id, $this->Employee->get_logged_in_employee_info()->person_id))
		{
			return '1';
		}
		else{
			return '0';
		}
	}

	function index($offset=0)
	{	
		/* Subscribe current host to Lambda functions */
		$this->subscribeCurrentPosToDynamoDB();
		
		$this->check_action_permission('add');
		/* Enable cache for current controller */
		if ($this->bitrixConfig['bitrix']['enable_web_page_cache'] == 1) {
			$this->output->cache($this->bitrixConfig['bitrix']['web_page_cache_valid_time']);
		}
		else {
			clearCache();
			setCache('check-cache-status', $this->bitrixConfig['bitrix']['enable_web_page_cache']);
		}

		$this->importAllFromBitrix();

		$alreadySubscribed = $this->BitrixSubscription->get_all()->result_array();

		$itemSynced = $this->BitrixSubscription->count_all();		
		$result = $this->AllBitrixItem->getAllSectionsAsBitrixResponse();

		$finalResultArray = [];
		if (isset($result['result']) && !empty($result['result'])) {
			$finalResultArray = $this->buildSectionTree($result['result']);
		}
		$unsubscribe = $this->check_unsubscribe_permission('delete');
		$html = '<ul class="treeview panel-piluku">
		<div class="panel-heading list-group-item">
		<label for="tall" style="cursor: pointer;" id="rootCategoryLabel" class="dark-heading reload-grid" data-sectionid="">Categories</label></div>';	

		$html .= $this->buildSectionTreeHtml($finalResultArray, 'tall', $alreadySubscribed, $offset, '');
		$html .= '</ul">';
		
		$this->load->view('bitrix/subscribe', ['itemssynced' => $itemSynced,'sectiontree' => $html, 'already_subscribed' => $alreadySubscribed, 'parentSectionId' => $offset, 'unsubscribe' => $unsubscribe]); 
	}
	
	function checkCacheStatus() {
		$response['reload_page'] = 0;
		$checkCacheStatus = getCache('check-cache-status');		
		
		if ($checkCacheStatus == false || $checkCacheStatus != $this->bitrixConfig['bitrix']['enable_web_page_cache']) 
		{
			if ($this->bitrixConfig['bitrix']['enable_web_page_cache'] == 0) {
				$this->output->delete_cache('/bitrix/index');
				$this->output->delete_cache('/bitrix');
			}

			if ($checkCacheStatus == 0) {
				$response['reload_page'] = 0;
			}			
			else {
				$response['reload_page'] = 1;
			}
			$checkCacheStatus = $this->bitrixConfig['bitrix']['enable_web_page_cache'];
			setCache('check-cache-status', $checkCacheStatus);
			
		}
		echo json_encode($response); die;
	}

	function forceClearCache($return = true) {
		clearCache();
		$this->output->delete_cache('/bitrix/index');
		$this->output->delete_cache('/bitrix');		
		$response['reload_page'] = 1;
		if ($return) {
			echo json_encode($response); die;
		}		
	}

	private function importAllFromBitrix() {
		
		$refreshProductImportGridLambdaOnly = $this->bitrixConfig['bitrix']['refresh_product_import_grid_lambda_only'];
		if ($refreshProductImportGridLambdaOnly == 1) {
			if ($this->AllBitrixItem->count_all() == 0) {
				$allSectionsData = $this->importAllSections();
				$allProductsData = $this->importAllProducts();
			}		
		}
		else {
			$allSectionsData = getCache('import-all-sections-from-bitrix');		
		
			if ($allSectionsData == false || empty($allSectionsData))
			{
				$allSectionsData = $this->importAllSections();
				setCache('import-all-sections-from-bitrix', $allSectionsData);
			}
	
			$allProductsData = getCache('import-all-products-from-bitrix');
			
			if ($allProductsData == false || empty($allProductsData))
			{			
				$allProductsData = $this->importAllProducts();
				setCache('import-all-products-from-bitrix', $allProductsData);	 
			}
		}
	}
	
	private function importAllSections($next = 0, $item = []) {
		$inboundUrl = $this->bitrixConfig['bitrix']['inbound_url']."crm.productsection.list.json";
		if ($next) {
			$inboundUrl .= '?start='.$next;
		}
        $jsonResult = callInboundAPI($inboundUrl);
		$result = json_decode($jsonResult, true);
		if (isset($result['result']) && !empty($result['result'])) {
			foreach($result['result'] as $section) {
				$item[$section['ID']]['item_id'] = $section['ID'];
				$item[$section['ID']]['parent_id'] = (!empty($section['SECTION_ID']))?$section['SECTION_ID']:0;
				$item[$section['ID']]['title'] = $section['NAME'];
				$item[$section['ID']]['type'] = 1;
				$item[$section['ID']]['price'] = 0;
				$item[$section['ID']]['currency_code'] = '';
				$item[$section['ID']]['preview_image'] = '';
				$id = $this->AllBitrixItem->getByItemIdAndType($section['ID'], 1);
				if ($id > 0) {
					$item[$section['ID']]['id'] = $id;
				}
				$this->AllBitrixItem->save($item[$section['ID']]);
			}
			if (isset($result['next']) && $result['next'] > 0) {
				return $this->importAllSections($result['next'], $item);
			}
			else {
				return $item;
			}
		}		
	}

	private function importAllProducts($next = 0, $item = []) {
		$inboundUrl = $this->bitrixConfig['bitrix']['inbound_url']."crm.product.list.json";
		if ($next) {
			$inboundUrl .= '?start='.$next;
		}
        $jsonResult = callInboundAPI($inboundUrl);
		$result = json_decode($jsonResult, true);
		if (isset($result['result']) && !empty($result['result'])) {
			foreach($result['result'] as $product) {
				$fieldId = (isset($product['PREVIEW_PICTURE']['id']) && !empty($product['PREVIEW_PICTURE']['id']))? $product['PREVIEW_PICTURE']['id']: 0;
				$item[$product['ID']]['item_id'] = $product['ID'];
				$item[$product['ID']]['parent_id'] = (!empty($product['SECTION_ID']))?$product['SECTION_ID']:0;
				$item[$product['ID']]['title'] = $product['NAME'];
				$item[$product['ID']]['type'] = 2;
				$item[$product['ID']]['price'] = $product['PRICE'];
				$item[$product['ID']]['currency_code'] = $product['CURRENCY_ID'];
				$item[$product['ID']]['preview_image'] = (!empty($fieldId) && $fieldId > 0)? $this->bitrixConfig['bitrix']['inbound_url']."catalog.product.download?fields[fieldName]=previewPicture&fields[fileId]=".$fieldId."&fields[productId]=".$product['ID']: base_url('assets/assets/images/default.png');
				$id = $this->AllBitrixItem->getByItemIdAndType($product['ID'], 2);
				if ($id > 0) {
					$item[$product['ID']]['id'] = $id;
				}
				$this->AllBitrixItem->save($item[$product['ID']]);				
			}
			if (isset($result['next']) && $result['next'] > 0) {
				return $this->importAllProducts($result['next'], $item);
			}
			else {
				return $item;
			}
		}		
	}	

	private function subscribeCurrentPosToDynamoDB() {
		$url = $this->awsApiUrl;
		$curl = curl_init($url);
		$data = [
			'TableName' => $this->bitrixConfig['aws']['dynamodb'], 
			'Item' => [
				"host_url" => base_url(),
				"is_active" => 1
			], 
			"authtoken" => md5(base_url())
		];
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($curl);
		curl_close($curl);
		if ($result == 'Success') {
			return true;
		}
		else {
			return false;
		}		
	}

	private function buildSectionTree($items) {
		$childs = [];
		foreach($items as &$item){
			$childs[$item['SECTION_ID']][] = &$item;
			unset($item);
		}
		foreach($items as &$item){
			if (isset($childs[$item['ID']])){
				$item['childs'] = $childs[$item['ID']];
			}
		}
		return $childs[''];
	}

	public function bitrixgridjson() {				
		$this->check_action_permission('add');
		$postData = json_decode(file_get_contents('php://input'), true);
		$searchProductId = '';		
		$collectSections = (isset($postData['section_child']))? false: true;
		$cpage = (isset($postData['cpage']))?$postData['cpage']:0;
		$limit = (isset($postData['mlimit']))?$postData['mlimit']:50;
		$levelcount = (isset($postData['levelcount']))?$postData['levelcount']+1:1;
		$sectionId = (isset($postData['filters']['SECTION_ID']) && $postData['filters']['SECTION_ID'] > 0)?$postData['filters']['SECTION_ID']:'0';
		$productId = (isset($postData['filters']['PRODUCT_ID']) && !empty($postData['filters']['PRODUCT_ID']))?$postData['filters']['PRODUCT_ID']:'';
		
		$allSyncedItems = (isset($postData['filters']['all_synced_items']) && $postData['filters']['all_synced_items'] == 'all_synced_items')? true: false;
		
		$onlySearchByProduct = false;
		if (!empty($productId)) {
			$onlySearchByProduct = true;
			$collectSections = false;
		}
		
		$dataSubscribedSections = [];
		$dataSubscribedProducts = [];
		$alreadySubscribed = $this->BitrixSubscription->get_all()->result_array();

		if (!empty($alreadySubscribed)) {
			foreach($alreadySubscribed as $aSub) {
				if ($aSub['type'] == 1) {
					$dataSubscribedSections[] = $aSub['value'];
				}
				else {
					$dataSubscribedProducts[] = $aSub['value'];
				}
			}
		}

		$finalData = [];
		$filters = ['total' => 0, 'totalProducts' => 0, 'totalSections' => 0, 'cpage' => $cpage, 'mlimit' => $limit];
		$allRecords = [];
		if ($allSyncedItems) {
			$allRecords = $this->AllBitrixItem->getAllSyncedItems($limit, $cpage);
			$filters['total'] = $this->AllBitrixItem->getAllSyncedItemsCount();
		}
		else if ($onlySearchByProduct) {
			// die($productId);
			$allRecords = $this->AllBitrixItem->getItemOnProductSearch($productId, $limit, $cpage);
			$filters['total'] = $this->AllBitrixItem->getItemOnProductSearchCount($productId);
		}
		else {
			$allRecords = $this->AllBitrixItem->getItemsByParentId($sectionId, $limit, $cpage, $collectSections);
			$filters['total'] = $this->AllBitrixItem->getItemsCountByParentId($sectionId, $collectSections);
		}
		
		$totalProducts = 0;
		$totalSections = 0;

		if (!empty($allRecords)) {
			foreach ($allRecords as $item) {
				if (($item['type'] == 1)) {
					$totalSections += 1;
				}
				else {
					$totalProducts += 1;
				}
				$isChecked = ($item['type'] == 1)? (in_array($item['item_id'], $dataSubscribedSections))?1:0 : (in_array($item['item_id'], $dataSubscribedProducts))?1:0;
				$finalData[] = ['parent_id' => $item['parent_id'],'product_image' => $item['preview_image'], 'id' => $item['item_id'], 'name'=> str_repeat("-",$levelcount)." ".$item['title'], 'price'=> $item['price'].' '.$item['currency_code'], 'is_section'=>$item['type'], 'is_checked' => $isChecked];
			}
		}

		$filters['totalProducts'] =  $totalProducts;
		$filters['totalSections'] =  $totalSections;
		
		$response = ['data' => $finalData, 'filters' => $filters];

		header('Content-Type: application/json');
    	echo json_encode($response);
	}

	private function buildSectionTreeHtml($items, $level = 'tall', $subscribedItems = [], $offset, $currentSectionId = '', $levelCount = 1) {
		$subscibedItemsArray = (isset($subscribedItems[0]['value']) && !empty($subscribedItems[0]['value']))?explode(',', $subscribedItems[0]['value']):[];
		$curActiveSectionClass = (($offset > 0) && ($offset == $currentSectionId))?'active-section' :'';
		$ulClass = ($level == 'tall')?'class="list-group '.$curActiveSectionClass.'"':'class="hidden '.$curActiveSectionClass.'"';
		$liClass = ($level == 'tall')?'class="list-group-item"':'';
		$childs = '<ul '.$ulClass.'>';
		
		foreach ($items as $key => $item) {
				$keyInc = $key+1;
				$levelId = $level."-".$keyInc;
				$hasChild = isset($item['childs']);
				$checked = (in_array($items[$key]['ID'], $subscibedItemsArray))?'checked="checked"': '';
				$checkedClass = (in_array($items[$key]['ID'], $subscibedItemsArray))?'custom-checked': 'custom-checked';
				
				$treeChildIcon = ($hasChild)? '<i class="icon ti-angle-up"></i>':'';

				$childs .= '<li '.$liClass.'>
				<input '.$checked.' class="checkbox_'.$items[$key]['ID'].'" type="checkbox" value="'.$items[$key]['ID'].'" name="subscription[]" id="'.$levelId.'">
				<label for="'.$levelId.'" class="'.$checkedClass.' level-'.$levelCount.' toggle-child-sections"><a class="reload-grid" href="javascript:void(0)" data-levelcount="'.$levelCount.'" data-scetionname="'.$item['NAME'].'" data-sectionid="'.$items[$key]['ID'].'" >'.str_repeat("-",$levelCount).' '.$item['NAME'].'</a>'.$treeChildIcon.'</label>';
				if ($hasChild) {
					$childs .= $this->buildSectionTreeHtml($item['childs'], $levelId, $subscribedItems, $offset, $items[$key]['ID'], $levelCount+1);
				}
				$childs .= '</li>';
				
		}
		$childs .= '</ul>';
		return $childs;
	}	
	
		
	function search()
	{}
	
	function suggest()
	{}

	
	function view($item_id=-1, $sale_or_receiving = 'sale')
	{}

	function save($id=-1)
	{
		clearCache();
		if ($this->subscribeCurrentPosToDynamoDB()) {
			$this->check_action_permission('add');	
			$sections = $this->input->post('section');
			$products = $this->input->post('product');
			
			if (!empty($sections)) {
				foreach ($sections as $section) {
					$sectionAlreadySubscribed = $this->db->query("SELECT `id` FROM `phppos_bitrix_subscription` WHERE `type` = '1' AND `value` = '".$section."'")->result_array();
					if (count($sectionAlreadySubscribed) == 0) {
						$data = ['type' => 1, 'value' => $section];
						if ($this->BitrixSubscription->save($data))	{
							$this->importSection($section, $this->bitrixConfig['bitrix']['subscribe_child_sections']);
						}
					}
				}
			}
	
			if (!empty($products)) {
				foreach ($products as $productData) {
					$productData = explode("_", $productData);
					$product = $productData[1];
					$sectionAlreadySubscribed = $this->db->query("SELECT `id` FROM `phppos_bitrix_subscription` WHERE `type` = '2' AND `value` = '".$product."'")->result_array();
					if (count($sectionAlreadySubscribed) == 0) {
						$data = ['type' => 2, 'value' => $product];
						if ($this->BitrixSubscription->save($data))	{
							$this->importProduct($product);
						}
					}
				}
			}
		}
		$this->forceClearCache(false);
		redirect('/bitrix/index');
	}

	private function importProduct($product) {
		$jsonResult = callInboundAPI($this->bitrixConfig['bitrix']['inbound_url']."catalog.product.list.json?select[0]=id&select[1]=iblockId&select[2]=*&filter[iblockId]=".$this->bitrixConfig['bitrix']['parent_catalog_iblockId']."&filter[id]=".$product);
		$result = json_decode($jsonResult, true);
		if(isset($result['result']['products']) && !empty($result['result']['products'])) {
			addProductsToBitrix($result['result']['products']);
		}
	}

	private function importSection($section, $subscribeChildSections = false) {		
		if (!empty($section) && $section > 0) {
			$jsonResult = callInboundAPI($this->bitrixConfig['bitrix']['inbound_url']."crm.productsection.get.json?ID=".$section);
			$result = json_decode($jsonResult, true);
			if(isset($result['result']) && !empty($result['result'])) {
				$item = $result['result'];
				if (addSectionsToBitrix($item, $addChildProducts = true)) {
					if ($subscribeChildSections) {
						$childSections['result'] = $this->getAllChildSections($section);
						if (isset($childSections['result']) && count($childSections['result']) > 0) {
							foreach ($childSections['result'] as $childSection) {
								$sectionAlreadySubscribed = $this->db->query("SELECT `id` FROM `phppos_bitrix_subscription` WHERE `type` = '1' AND `value` = '".$childSection['ID']."'")->result_array();
								if (count($sectionAlreadySubscribed) == 0) {
									$data = ['type' => 1, 'value' => $childSection['ID']];
									if ($this->BitrixSubscription->save($data))	{
										if (!empty($childSection['ID']) && $childSection['ID'] > 0) {
											$this->importSection($childSection['ID'], $subscribeChildSections);
										}										
									}
								}
							}						
						}
					}
				}
			}
		}
		return true;
	}

	private function getAllChildSections($section, $next = 0, $item = []) {
		if (!empty($section) && $section > 0) {
			$inboundUrl = $this->bitrixConfig['bitrix']['inbound_url']."crm.productsection.list.json?FILTER[SECTION_ID]=".$section;
			if ($next) {
				$inboundUrl .= '?start='.$next;
			}
			$jsonResult = callInboundAPI($inboundUrl);
			$result = json_decode($jsonResult, true);
			if (isset($result['result']) && !empty($result['result'])) {
				foreach($result['result'] as $section) {
					$item[] = $section;
				}
				if (isset($result['next']) && $result['next'] > 0) {
					return $this->importAllSections($section, $result['next'], $item);
				}
				else {
					return $item;
				}
			}
		}		
	}

	function delete()
	{
			$this->forceClearCache(false);
			$response = ['status' => 1 , 'message' => 'Something Went Wrong Please Check The Details'];
			$this->check_action_permission('delete');
			$productId = json_decode(file_get_contents('php://input'), true);
			$response = productDelete($productId);
			header('Content-Type: application/json');
			echo json_encode($response); die;
	}
	
	function categoryDelete()
	{
		$this->forceClearCache(false);
		$response = ['status' => 0 , 'message' => 'Something went wrong, Please try again.' ];
		$sectionId = json_decode(file_get_contents('php://input'), true);

		$response = categorysDelete($sectionId);
		header('Content-Type: application/json');
		echo json_encode($response);		

	}
		
	
	

}




?>