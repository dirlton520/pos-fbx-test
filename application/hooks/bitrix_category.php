<?php
	
    function category()
    {
        $CI = &get_instance();
        $controller = $CI->router->fetch_class(); 
        $method     = $CI->router->fetch_method();
        if(  $method == 'delete' && $controller == 'items'  ){ 
            $itemIds = $_POST['ids']; 
            $CIT = get_instance();
            $CIT->load->model('Item');
            foreach($itemIds as $item){
               $productIds = $CIT->db->query("SELECT `product_id` FROM `phppos_items` WHERE `item_id` = '".$item."'")->result_array();
               foreach($productIds as $ids){
                    $CS = get_instance();
                    $CS->load->model('BitrixSubscription');
                    $data = ['type' => 2, 'value' => $ids['product_id']];
                    $CS->BitrixSubscription->deleteByValue($data);
                }
            }
        }

        else if( $method == 'delete_category' && $controller == 'items' ){ 
            $sectionId = $_POST['category_id'];
            $CC = get_instance();
			$CC->load->model('Category');
            $CIT = get_instance();
            $CIT->load->model('Item');
            $CS = get_instance();
			$CS->load->model('BitrixSubscription');
            $parentCategoryItem = $CIT->db->query("SELECT `product_id` FROM `phppos_items` WHERE `category_id` = '".$sectionId."'")->result_array();
            if($parentCategoryItem){
                foreach($parentCategoryItem as $item){
                    
                    $data = ['type' => 2, 'value' => $item['product_id']];
                    $CS->BitrixSubscription->deleteByValue($data);
                }
            }

            $childCategoryParentId = $CC->db->query("SELECT `bitrix_section_id` FROM `phppos_categories` WHERE `id` = '".$sectionId."'")->row();            
            $childCategoryId = $CC->db->query("SELECT `id`, `bitrix_section_id` FROM `phppos_categories` WHERE `bitrix_section_parent_id` = '".$childCategoryParentId->bitrix_section_id."'")->result_array();
            if($childCategoryId){
                foreach($childCategoryId as $item){
                    
                    $childCategoryItem = $CIT->db->query("SELECT `product_id` FROM `phppos_items` WHERE `category_id` = '".$item['id']."'")->result_array();
                    foreach($childCategoryItem as  $childItem){
                        $data = ['type' => 2, 'value' => $childItem['product_id']];
                        $CS->BitrixSubscription->deleteByValue($data);

                    }
                    $data = ['type' => 1, 'value' => $item['bitrix_section_id']];
                    $CS->BitrixSubscription->deleteByValue($data);

                
                }
            }

            $data = ['type' => 1, 'value' => $childCategoryParentId->bitrix_section_id];
            $CS->BitrixSubscription->deleteByValue($data);  
        }
    }
    ?>