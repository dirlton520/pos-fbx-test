<?php
class Ecommerce extends MY_Controller 
{	
		function __construct()
		{
			ini_set('memory_limit','1024M');
			parent::__construct();
			if (!is_cli())//Running from web should have store config permissions
			{	
				$this->load->model('Employee');
				$this->load->model('Location');
				if(!$this->Employee->is_logged_in())
				{
					redirect('login?continue='.rawurlencode(uri_string().'?'.$_SERVER['QUERY_STRING']));
				}
		
				if(!$this->Employee->has_module_permission('config',$this->Employee->get_logged_in_employee_info()->person_id))
				{
					redirect('no_access/config');
				}
			}			
		}
		
		public function shopify_return_url_subscription()
		{
			$charge_id = $this->input->get('charge_id');
			$this->Appconfig->save('shopify_charge_id',$charge_id);
			redirect('config/shopify?step=3');
		}
		
		
		public function oauth_shopify()
		{
			$shop = $this->config->item('shopify_shop');
			$api_key = SHOPIFY_API_KEY;
			$redirect_url =  (!defined("ENVIRONMENT") or ENVIRONMENT == 'development') ? 'https://phppointofsalestaging.com/shopify_redirect.php' : 'https://phppointofsale.com/shopify_redirect.php';
			
			$url = site_url('ecommerce/oauth_shopify_finish');
			$scopes = 'read_products,write_products,read_customers,write_customers,read_orders,write_orders,read_inventory,write_inventory,read_locations';	
			redirect("https://$shop.myshopify.com/admin/oauth/authorize?client_id=$api_key&scope=$scopes&redirect_uri=$redirect_url&state=$url");
		}
		
		
		public function oauth_shopify_finish()
		{
			$was_installed_before = $this->config->item('shopify_code');
			$shopify_code = $this->input->request('code');
			$this->Appconfig->save('shopify_code',$shopify_code);
			
			if (isset($_GET['shop']))
			{
				list($parsed_shop) = explode('.', $_GET['shop']);
				$this->config->set_item('shopify_shop',$parsed_shop);
				$this->Appconfig->save('shopify_shop',$parsed_shop);
			}
			$this->Appconfig->save('ecommerce_platform','shopify');
			
			$shop = $this->config->item('shopify_shop').'.myshopify.com';
			
			$query = array(
			  "client_id" => SHOPIFY_API_KEY, // Your API key
			  "client_secret" => SHOPIFY_API_SECRET_KEY, // Your app credentials (secret key)
			  "code" => $shopify_code // Grab the access key from the URL
			);

			// Generate access token URL
			$access_token_url = "https://" . $shop . "/admin/oauth/access_token";

			// Configure curl client and execute request
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, $access_token_url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_POST, count($query));
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query));
			$result = curl_exec($ch);
			curl_close($ch);
			// Store the access token
			$result = json_decode($result, true);
			$access_token = $result['access_token'];
			
			if (!$access_token)
			{
				redirect('config/shopify?step=1&error=access_token');
			}
			else
			{
				$this->Appconfig->save('shopify_oauth_token',$access_token);

				$ch = curl_init("https://" . $shop.'/admin/api/2021-04/locations.json');  
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
				//Don't verify ssl...just in case a server doesn't have the ability to verify
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				$headers = array(  
			    	'X-Shopify-Access-Token: '.$access_token,  
					'Content-Type: application/json',                                                                              
				);
				
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		 	   $return_locations = json_decode(curl_exec($ch), TRUE); 
			   $location_id = $return_locations['locations'][0]['id'];
			   $this->Appconfig->save('shopify_location_id',$location_id);
			
			   //If we were cancelled before redirect right to activating billing
			   if ($this->config->item('shopify_was_cancelled') || $was_installed_before)
			   {
				   redirect('ecommerce/activate_shopify_billing');				   
			   	
			   }
			   else
			   {
					redirect('config/shopify?step=2');
			   }
			}
		}
				
		public function cancel_shopify_billing()
		{
			$this->load->model('Shopify');
			if (!$this->Shopify->cancel_subscription())
			{
				die(lang('config_shopfiy_billing_failed'));
			}
			else
			{
				$this->Appconfig->delete('shopify_charge_id');
				$this->Appconfig->save('shopify_was_cancelled',1);
				redirect('config?search=shopify');
			}
		}
		
		
		function oauth_shopify_disconnect($remove_store = 1)
		{
			if ($remove_store)
			{
	        	$this->Appconfig->delete('shopify_shop');
		        $this->Appconfig->delete('shopify_location_id');
	        }
			
			$this->Appconfig->delete('shopify_code');
	        $this->Appconfig->delete('shopify_oauth_token');
	        redirect('config?search=shopify');
		}
		
		public function activate_shopify_billing()
		{
			$this->load->model('Shopify');
			
			//Cancel out old plan
			if ($this->config->item('shopify_charge_id'))
			{
				if (!$this->Shopify->cancel_subscription())
				{
					die(lang('config_shopfiy_billing_failed'));
				}
			}
			if (!$this->Shopify->create_subscription())
			{
				die(lang('config_shopfiy_billing_failed'));
			}
		}
						
		public function cancel()
		{
			$this->load->model('Appconfig');
			$this->Appconfig->save('kill_ecommerce_cron',1);
			$this->Appconfig->save('ecommerce_cron_running',0);
			$this->Appconfig->save('ecommerce_sync_percent_complete',100);
			$platform=$this->Appconfig->get("ecommerce_platform");
			
			if($platform=="woocommerce")
			{
				$platform_model="woo";
				$this->load->model($platform_model);
			}
		}
			
		function manual_sync()
		{
			$this->cron();
		}
		
		/*
		This function is used to sync the PHPPOS items with online ecommerce store.
		*/
		// $base_url is used NOT used in this function but in application/config/config.php
		//$db_override is NOT used at all; but in database.php to select database based on CLI args for cron in cloud
      public function cron($base_url='', $db_override = '')
      {
				
				ignore_user_abort(TRUE);
				set_time_limit(0);
				ini_set('max_input_time','-1');
				session_write_close();
				
				//Cron's always run on current server path; but if we are between migrations we should run the cron on the previous folder passing along any arguements
				if (defined('SHOULD_BE_ON_OLD') && SHOULD_BE_ON_OLD)
				{
					global $argc, $argv;
					$prev_folder = isset($_SERVER['CI_PREV_FOLDER']) ?  $_SERVER['CI_PREV_FOLDER'] : 'PHP-Point-Of-Sale-Prev';
					system('php '.FCPATH."$prev_folder/index.php ecommerce cron ".$argv[3].$prev_folder.'/ '.$argv[4]);
					exit();
				}
				
				$this->load->helper('demo');
				if (is_on_demo_host())
				{
					echo json_encode(array('success' => FALSE, 'message' => lang('common_disabled_on_demo')));
					die();
				}
				try
				{	
					$this->Appconfig->save('kill_ecommerce_cron',0);
					
					$platform_model="";
					$this->load->model("Appconfig");
					if ($this->Appconfig->get_raw_ecommerce_cron_running())
					{
						echo json_encode(array('success' => FALSE, 'message' => lang('common_ecommerce_running')));
						die();
					}
				
					$this->load->model('Location');
					if ($timezone = ($this->Location->get_info_for_key('timezone',$this->config->item('ecom_store_location') ? $this->config->item('ecom_store_location') : 1)))
					{
						date_default_timezone_set($timezone);
					}

					$this->Appconfig->save('ecommerce_cron_running',1);
					$this->Appconfig->save('ecommerce_sync_percent_complete',0);
					$platform=$this->Appconfig->get("ecommerce_platform");
					
					
					if($platform=="woocommerce")
					{
						$platform_model="woo";
					}
					elseif($platform == 'shopify')
					{
						$platform_model="shopify";
					}
					
					if( $platform_model != "" )
					{
						$ecommerce_cron_sync_operations_settings = unserialize($this->config->item('ecommerce_cron_sync_operations'));
						$this->load->model($platform_model);
						$this->lang->load('config');
						
						$valid_operations_in_order = array(
							"sync_inventory_changes",
							"import_ecommerce_tags_into_phppos",
							"import_ecommerce_categories_into_phppos",
							"import_ecommerce_attributes_into_phppos",
							"import_tax_classes_into_phppos",
							"import_shipping_classes_into_phppos",
							"import_ecommerce_items_into_phppos",
							"import_ecommerce_orders_into_phppos",
							"export_phppos_tags_to_ecommerce",
							"export_phppos_categories_to_ecommerce",
							"export_phppos_attributes_to_ecommerce",
							"export_tax_classes_into_phppos",
							"export_phppos_items_to_ecommerce"
						);
						
						$ecommerce_cron_sync_operations = array();
						
						foreach($valid_operations_in_order as $valid_operation)
						{
							if(in_array($valid_operation, $ecommerce_cron_sync_operations_settings))
							{
								$ecommerce_cron_sync_operations[] = $valid_operation;
							}
						}
						
						$numsteps = count($ecommerce_cron_sync_operations);
						$stepsCompleted = 0;
						
						foreach($ecommerce_cron_sync_operations as $operation)
						{
							if (is_cli())
							{
								echo "START $operation\n";
							}
							
							$percent = floor(($stepsCompleted/$numsteps)*100);
							$message = lang("config_".$operation);
							$this->$platform_model->update_sync_progress($percent, $message);
							
							$this->$platform_model->$operation();
							$stepsCompleted ++;
							
							if (is_cli())
							{
								echo "DONE $operation\n";
							}
						}
						
						$percent = floor(($stepsCompleted/$numsteps)*100);
						$message = lang("config_".$operation);
						$this->$platform_model->update_sync_progress($percent, $message);
						
						$this->load->model('Appconfig');
						
						$sync_date = date('Y-m-d H:i:s');
						$this->Appconfig->save('last_ecommerce_sync_date', $sync_date);
						if (is_cli())
						{
							echo "\n\n***************************DONE***********************\n";
						}
						
						$this->$platform_model->save_log();
						echo json_encode(array('success' => TRUE, 'date' =>$sync_date));
					}
		
					$this->Appconfig->save('ecommerce_sync_percent_complete',100);
					$this->Appconfig->save('ecommerce_cron_running',0);
	      }
				catch(Exception $e)
				{
					if (is_cli())
					{
						echo "*******EXCEPTION: ".var_export($e->getMessage(),TRUE);
					}
					$this->Appconfig->save('ecommerce_cron_running',0);				
				}
			}
		}
?>