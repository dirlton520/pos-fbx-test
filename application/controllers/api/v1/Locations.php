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
class Locations extends REST_Controller {
	
		protected $methods = [
        'index_get' => ['level' => 1, 'limit' => 20],

      ];

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
    }
			
		private function _location_result_to_array($location)
		{
				$location_return = array(
					'location_id' => (int)$location->location_id,
					'name' => $location->name,
					'address' => $location->address,
					'color' => $location->color,
					'company' => $location->company,
					'website' => $location->website,
					'phone' => $location->phone,
					'fax' => $location->fax,
					'email' => $location->email,
					'cc_email' => $location->cc_email,
					'bcc_email' => $location->bcc_email,
					'return_policy' => $location->return_policy,
					'receive_stock_alert' =>(boolean) $location->receive_stock_alert,
					'stock_alert_email' => $location->stock_alert_email,
					'timezone' => $location->timezone,
					'mailchimp_api_key' => $location->mailchimp_api_key,
					'platformly_api_key' => $location->platformly_api_key,
					'platformly_project_id' => (int)$location->platformly_project_id,
					'enable_credit_card_processing' => (boolean)$location->enable_credit_card_processing,
					'credit_card_processor' => $location->credit_card_processor,
					'stripe_public' => $location->stripe_public,
					'stripe_private' => $location->stripe_private,
					'braintree_merchant_id' => $location->braintree_merchant_id,
					'braintree_public_key' => $location->braintree_public_key,
					'braintree_private_key' => $location->braintree_private_key,
					'stripe_currency_code' => $location->stripe_currency_code,
					'hosted_checkout_merchant_id' => $location->hosted_checkout_merchant_id,
					'hosted_checkout_merchant_password' => $location->hosted_checkout_merchant_password,
					'emv_merchant_id' => $location->emv_merchant_id,
					'net_e_pay_server' => $location->net_e_pay_server,
					'com_port' => $location->com_port,
					'secure_device_override_emv' => $location->secure_device_override_emv,
					'secure_device_override_non_emv' => $location->secure_device_override_non_emv,
					'ebt_integrated' => $location->ebt_integrated,
					'integrated_gift_cards' => $location->integrated_gift_cards,
					'tax_class_id' => $location->tax_class_id,
					'default_tax_1_rate' => $location->default_tax_1_rate,
					'default_tax_1_name' => $location->default_tax_1_name,
					'default_tax_2_rate' => $location->default_tax_2_rate,
					'default_tax_2_name' => $location->default_tax_2_name,
					'default_tax_2_cumulative' => $location->default_tax_2_cumulative,
					'default_tax_3_rate' => $location->default_tax_3_rate,
					'default_tax_3_name' => $location->default_tax_3_name,
					'default_tax_4_rate' => $location->default_tax_4_rate,
					'default_tax_4_name' => $location->default_tax_4_name,
					'default_tax_5_rate' => $location->default_tax_5_rate,
					'default_tax_5_name' => $location->default_tax_5_name,
					'company_logo' => $location->company_logo ? secure_app_file_url($location->company_logo) : '',
					'tax_id' => $location->tax_id,
				);
				
				foreach($this->Register->get_all($location->location_id)->result_array() as $register)
				{
					$location_return['registers'][] = (array)$register;
				}
				return $location_return;
		}

				
    public function index_get($location_id = NULL)
    {
			$this->load->model('Location');
			$this->load->helper('url');
			$this->load->helper('date');
			
			if ($location_id === NULL)
      {
      	$search = $this->input->get('search');
      	$search_field = $this->input->get('search_field');
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
					
					$locations = $this->Location->search($search, 0, $limit!==NULL ? $limit : 20, $offset!==NULL ? $offset : 0,$sort_col,$sort_dir,$search_field)->result();
					$total_records = $this->Location->search_count_all($search, 0,10000,$search_field);
				}
				else
				{
					$sort_col = $this->input->get('sort_col') ? $this->input->get('sort_col') : 'name';
					$sort_dir = $this->input->get('sort_dir') ? $this->input->get('sort_dir') : 'asc';
					
					$locations = $this->Location->get_all(0,$limit!==NULL ? $limit : 20, $offset!==NULL ? $offset : 0,$sort_col,$sort_dir)->result();
					$total_records = $this->Location->count_all(0);
				}
				
				$locations_return = array();
				foreach($locations as $location)
				{
						$locations_return[] = $this->_location_result_to_array($location);
				}
				
				header("x-total-records: $total_records");
				
				$this->response($locations_return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
      }
      else
      {
      			if (!is_numeric($location_id))
      			{
							$this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
      			}
      			
        		$location = $this->Location->get_info($location_id);
        		
        		if ($location->location_id)
        		{
        			$location_return = $this->_location_result_to_array($location);
							$this->response($location_return, REST_Controller::HTTP_OK);
					}
					else
					{
							$this->response(NULL, REST_Controller::HTTP_NOT_FOUND);
					}			
      }
    }
}
