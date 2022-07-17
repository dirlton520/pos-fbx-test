<?php

class Bitrixrequest extends MY_Controller 
{

	function __construct()
	{
		parent::__construct();
		$this->lang->load('bitrix');
		$this->lang->load('login');
		$this->load->helper('cloud');
		$this->load->helper('bitrix_helper');
		$this->load->helper('bitrix_import_property_helper');
		$this->bitrixConfig = getBitrixConfig();
	}

	function lambdafunction() {
		clearCache();
		// ini_set('display_errors', 1);
		// ini_set('display_startup_errors', 1);
		// error_reporting(E_ALL);
		$result = ['statusCode'=>304, 'message' => 'Forbidden'];
		file_put_contents(getcwd() . '/logs/'.'bitrix_lambda-response'.'.log', "=======Start Time: ".time()."=======\n", FILE_APPEND);
		try {
			/** Get JSON POST Data from Lambda function */
			$data = json_decode(file_get_contents('php://input'), true);

			/** Convert SQS Message to JSON */
			$dataJson = base64_decode($data['data']);
			file_put_contents(getcwd() . '/logs/'.'bitrix_lambda-response'.'.log', $dataJson, FILE_APPEND);	
			
			/** Convert JSON to Array */
			$bitrixResponse = json_decode($dataJson, true);

			if (isset($bitrixResponse['event']) && isset($bitrixResponse['response']['result'])) {
				
				/** Add New Section/ Product in all_bitrix_items table. */
				lambdaAddAllItems($bitrixResponse['event'],$bitrixResponse['response']['result']);

				/* Call common helper functions to process SQS data. */
				if (addItemsToPOS($bitrixResponse['event'], $bitrixResponse['response']['result'])) {
					$result = ['statusCode'=>200, 'message' => 'Success'];
				}
				else {
					$result = ['statusCode'=>304, 'message' => 'Forbidden! Something went wrong.'];
				}
			}
			file_put_contents(getcwd() . '/logs/'.'bitrix_lambda-response'.'.log', print_r($bitrixResponse, true), FILE_APPEND);
		}
		catch (\Exception $e) {
			file_put_contents(getcwd() . '/logs/'.'bitrix_lambda-response'.'.log', "Error Message:-".$e->getMessage(), FILE_APPEND);
			file_put_contents(getcwd() . '/logs/'.'bitrix_lambda-response'.'.log', "Error Trace:-".$e->getTraceAsString(), FILE_APPEND);
		}				
		file_put_contents(getcwd() . '/logs/'.'bitrix_lambda-response'.'.log', "=======End Time: ".time()."=======\n", FILE_APPEND);
		echo json_encode($result); die;		
	}
}
?>	