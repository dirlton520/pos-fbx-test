<?php
require_once APPPATH.'libraries/qb/src/config.php';
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper;
use QuickBooksOnline\API\Facades\Account as QBAccount;
use QuickBooksOnline\API\Facades\JournalEntry as QBJournalEntry;
use QuickBooksOnline\API\Facades\QuickBookClass as QBClass;

class Quickbooks extends MY_Controller 
{   
    
    public $log_text = '';
    function __construct()
    {
        ini_set('memory_limit','1024M');
        parent::__construct();
        $this->lang->load('config');
    
        if (!is_cli())//Running from web should have store config permissions
        {   
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
            
    public function cancel()
    {
        $this->load->model('Appconfig');
        $this->Appconfig->save('kill_qb_cron',1);
        $this->Appconfig->save('qb_cron_running',0);
        $this->Appconfig->save('qb_sync_percent_complete',100);
    }
    
        
    function manual_sync()
    {
        $this->cron();
    }
            
            
            
            
    function refresh_tokens($redirect_to_store_config = 0)
    {

        // Moved this code to a common helper qb_helper.php, so that we can use it in different controllers
        $this->load->helper('qb');
        return refresh_tokens($redirect_to_store_config);
    }

    function initial_auth()
    {
        $search = rawurlencode(lang('common_quickbooks'));

        try
        {
            $dataService = $this->_get_data_service(FALSE);
            $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
            $accessTokenObj = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken($this->input->get("code"), $this->input->get("realmId"));
            $accessTokenValue = $accessTokenObj->getAccessToken();
            $refreshTokenValue = $accessTokenObj->getRefreshToken();
            $this->Appconfig->save('quickbooks_access_token',$accessTokenValue);
            $this->Appconfig->save('quickbooks_refresh_token',$refreshTokenValue);
            $this->Appconfig->save('quickbooks_realm_id',$this->input->get('realmId'));
            redirect("config?search=$search");
        }
        catch(Exception $e)
        {
            redirect("config?search=$search");
        }
        
    }
    
    private function _get_data_service($authed =TRUE)
    {
        // Moved this code to a common helper qb_helper.php, so that we can use it in different controllers
        $this->load->helper('qb');
        $dataService = _get_data_service();
        return $dataService;
    }

    function oauth()
    {
        $this->load->helper('qb');
        try {
        $dataService = _get_data_service(FALSE);
        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
        $authorizationCodeUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();
        }
        catch (Exception $e) {
            echo $e->getMessage();
        }
        
        redirect($authorizationCodeUrl);
    }

    //Check If account already exist in qb
    private function _accounts_exist()
    {
        
        try{
            $result_refresh_token = $this->refresh_tokens();
            $dataService = $this->_get_data_service();
            $check_accounts = $dataService->FindAll('Account');
            $accounts = [];
            foreach($check_accounts as $check_account)
            {
                $accounts[strtoupper($check_account->Name)] = $check_account->Id;
            }
            return $accounts;
        }
        catch (Exception $e) {
            $this->_log(lang('common_sync_account_exist_exception').$e->getMessage());
        }
        return false;
    }

    private function _verifyAccountData($dataType, $data, $journalEntryAccounts){
        try{
            global $existing_accounts;

            // Master Account Details
            $accountsMaster = array("total_gross_sales"=>
                array(
                    "Name" => "PHP POS ".strtoupper(lang('common_gross_income')),
                    "AccountType" => "Income",
                    "AccountSubType" => "SalesOfProductIncome",
                    ),
                    "discounts" => 
                array(
                    "Name" => "PHP POS ".strtoupper(lang('common_discounts')),
                    "AccountType" => "Income",
                    "AccountSubType" => "DiscountsRefundsGiven",
                    ),
                    "gift_card_item" => 
                array(
                    "Name" => "PHP POS ".strtoupper(lang('common_gift_card')),
                    "AccountType" => "Other Current Liabilities",
                    "AccountSubType" => "OtherCurrentLiabilities",
                   ),
                    "house_account_item" => 
                array(
                    "Name" => "PHP POS ".strtoupper(lang('common_store_account')),
                    "AccountType" => "Other Current Liabilities",
                    "AccountSubType" => "OtherCurrentLiabilities",
                    ),
                    "amount_over" =>
                array(
                    "Name" => "PHP POS ".strtoupper(lang('common_over_short')),
                    "AccountType" => "Expenses",
                    "AccountSubType" =>  "OtherSellingExpenses",
                    ),
                    "petty_cash" =>
                array(
                    "Name" => "PHP POS ".strtoupper(lang('common_petty_cash')),
                    "AccountType" => "Bank",
                    "AccountSubType" =>  "CashOnHand",
                    ),
                    "refunds" =>
                array(
                        "Name" => "PHP POS ".strtoupper(lang('common_refunds')),
                        "AccountType" => "Income",
                        "AccountSubType" => "SalesOfProductIncome",
                    ),    
                    "cogs" =>
                array(
                    "Name" => "PHP POS ".strtoupper(lang('common_cogs')),
                    "AccountType" => "Cost of Goods Sold",
                    "AccountSubType" => "SuppliesMaterialsCogs",
                    ),
                    "cogs_credit" =>
                array(
                    "Name" => "PHP POS ".strtoupper(lang('common_cogs_inventory')),
                    "AccountType" => "Other Current Assets",
                    "AccountSubType" => "Inventory",
                    ),
                    "amount_short" =>
                array(
                    "Name" => "PHP POS ".strtoupper(lang('common_amount_short')),
                    "AccountType" => "Income",
                    "AccountSubType" => "SalesOfProductIncome",
                    ),


                );
            if ($dataType === "total_gross_sales" || $dataType === "discounts" || $dataType === "refunds" || $dataType === "house_account_item" || $dataType === "gift_card_item"){

                // check if the account already exists
                if (!array_key_exists(strtoupper($accountsMaster[$dataType]["Name"]), $existing_accounts)) {
                    // create a new account
                    $this->_createAccount($accountsMaster[$dataType]);
                }
            }

            if ($dataType === "amount_over" ){

                // check if the account already exists
                if (!array_key_exists(strtoupper($accountsMaster[$dataType]["Name"]), $existing_accounts)) {
                    // create a new account
                    $this->_createAccount($accountsMaster[$dataType]);
                }

                // check if the account already exists
                if (!array_key_exists(strtoupper($accountsMaster["petty_cash"]["Name"]), $existing_accounts)) {
                    // create a new account
                    $this->_createAccount($accountsMaster["petty_cash"]);
                }
            }

            if ($dataType === "amount_short" ){

                // check if the account already exists
                if (!array_key_exists(strtoupper($accountsMaster[$dataType]["Name"]), $existing_accounts)) {
                    // create a new account
                    $this->_createAccount($accountsMaster[$dataType]);
                }

                // check if the account already exists
                if (!array_key_exists(strtoupper($accountsMaster["petty_cash"]["Name"]), $existing_accounts)) {
                    // create a new account
                    $this->_createAccount($accountsMaster["petty_cash"]);
                }
            }

            if ($dataType === "cogs" ){

                // check if the account already exists
                if (!array_key_exists(strtoupper($accountsMaster[$dataType]["Name"]), $existing_accounts)) {
                    // create a new account
                    $this->_createAccount($accountsMaster[$dataType]);
                }

                // check if the account already exists
                if (!array_key_exists(strtoupper($accountsMaster["cogs_credit"]["Name"]), $existing_accounts)) {
                    // create a new account
                    $this->_createAccount($accountsMaster["cogs_credit"]);
                }
            }

            //check if the data type is taxes
            if ($dataType === "taxes"){
                foreach($data as $taxRate => $value)
                {
                    $taxAccount = strtoupper("PHP POS Tax Payable " .$taxRate);
                    if (!array_key_exists($taxAccount, $existing_accounts)) 
                    {
                        $returnAccountTemplate =  array(
                            "Name" => $taxAccount,
                            "AccountType" => "Other Current Liabilities",
                            "AccountSubType" => "SalesTaxPayable",
                        );
                        // create a new account
                        $this->_createAccount($returnAccountTemplate);
                    }
                    $journalEntryAccounts[] = array(
                        "Name" =>  strtoupper($taxAccount),
                        "id" => $existing_accounts[strtoupper($taxAccount)],
                        "postingType" => "Credit",
                        "amount" => $value["tax"]
                    );
                    
                }
                
            }
            //check if the data type is payments
            if ($dataType === "payments"){
                // check if the payments have returns in it and create dynamic accounts if needed.
                if(!empty($data['returns']))
                {
                    foreach($data['returns'] as $paymentMethod => $value)
                    {
                        $paymentMethodAccount = "";
												$paymentMethod = strtoupper($paymentMethod);
                        if ($paymentMethod === "GIFT CARD" || $paymentMethod === "HOUSE ACCOUNT"){
                            
                            switch ($paymentMethod){
                                case 'GIFT CARD':
                                    $paymentMethodAccount = "PHP POS ".strtoupper(lang('common_gift_card'));
                                    break;
                                case 'HOUSE ACCOUNT':
                                    $paymentMethodAccount = "PHP POS ".strtoupper(lang('common_store_account'));
                                    break;
                            }

                            if (!array_key_exists(strtoupper($paymentMethodAccount), $existing_accounts)) 
                            {
                                $returnAccountTemplate =  array(
                                    "Name" => strtoupper($paymentMethodAccount),
                                    "AccountType" => "Other Current Liabilities",
                                    "AccountSubType" => "OtherCurrentLiabilities",
                                    );
                                    // create a new account
                                $this->_createAccount($accountsMaster[$dataType]);
                            }
                        }else{
                            $paymentMethodAccount = strtoupper("PHP POS returns $paymentMethod");
                            if (!array_key_exists($paymentMethodAccount, $existing_accounts)) 
                            {
                                $returnAccountTemplate =  array(
                                    "Name" => $paymentMethodAccount,
                                    "AccountType" => "Bank",
                                    "AccountSubType" => "CashOnHand",
                                    );
                                    // create a new account
                                $this->_createAccount($returnAccountTemplate);
                            }
                        }
                        $journalEntryAccounts[] = array(
                            "Name" =>  strtoupper($paymentMethodAccount),
                            "id" => $existing_accounts[strtoupper($paymentMethodAccount)],
                            "postingType" => "Debit",
                            "amount" => $value
                        );
                    }
                }

                // check if the payments have sales in it and create dynamic accounts if needed. 
                if(!empty($data['sales']))
                {
                    foreach($data['sales'] as $paymentMethod => $value)
                    {
                        $paymentMethodAccount = "";
                        $paymentMethod = strtoupper($paymentMethod);
                        if ($paymentMethod === "GIFT CARD" || $paymentMethod === "STORE ACCOUNT"){
                            switch ($paymentMethod){
                                case 'GIFT CARD':
                                    $paymentMethodAccount = "PHP POS ".strtoupper(lang('common_gift_card'));
                                    break;
                                case 'STORE ACCOUNT':
                                    $paymentMethodAccount = "PHP POS ".strtoupper(lang('common_store_account'));
                                    break;
                            }

                            if (!array_key_exists(strtoupper($paymentMethodAccount), $existing_accounts)) 
                            {
                                $returnAccountTemplate =  array(
                                    "Name" => strtoupper($paymentMethodAccount),
                                    "AccountType" => "Other Current Liabilities",
                                    "AccountSubType" => "OtherCurrentLiabilities",
                                    );
                                    // create a new account
                                $this->_createAccount($accountsMaster[$dataType]);
                            }
                        }else{
                            $paymentMethodAccount = strtoupper("PHP POS SALES $paymentMethod");
                            if (!array_key_exists(strtoupper($paymentMethodAccount), $existing_accounts)) 
                            {
                                $returnAccountTemplate =  array(
                                    "Name" => strtoupper($paymentMethodAccount),
                                    "AccountType" => "Bank",
                                    "AccountSubType" => "CashOnHand",
                                    );
                                // create a new account
                                $this->_createAccount($returnAccountTemplate);
                                
                            }
                        }
                        $journalEntryAccounts[] = array(
                            "Name" =>  strtoupper($paymentMethodAccount),
                            "id" => $existing_accounts[$paymentMethodAccount],
                            "postingType" => "Debit",
                            "amount" => $value
                        );
                    }
                }
            }

            // setup the template for the amount to be posted to qbo.
            switch ($dataType){
                case 'total_gross_sales':
                case 'gift_card_item':
                case 'house_account_item':
                    $journalEntryAccounts[] = array(
                        "Name" => strtoupper($accountsMaster[$dataType]["Name"]),
                        "id" => $existing_accounts[strtoupper($accountsMaster[$dataType]["Name"])],
                        "postingType" => "Credit", 
                        "amount" => $data
                    );
                    break;
                case 'discounts':
                case 'refunds':
                    $journalEntryAccounts[] = array(
                        "Name" => strtoupper($accountsMaster[$dataType]["Name"]),
                        "id" => $existing_accounts[strtoupper($accountsMaster[$dataType]["Name"])],
                        "postingType" => "Credit", 
                        "amount" => $data
                    );
                    break;
                case 'cogs':
                    $journalEntryAccounts[] = array(
                        "Name" =>strtoupper($accountsMaster[$dataType]["Name"]),
                        "id" => $existing_accounts[strtoupper($accountsMaster[$dataType]["Name"])],
                        "postingType" => "Debit",
                        "amount" => $data
                    );
                    $journalEntryAccounts[] = array(
                        "Name" => "PHP POS ".strtoupper(lang('common_cogs_inventory')),
                        "id" => $existing_accounts["PHP POS ".strtoupper(lang('common_cogs_inventory'))],
                        "postingType" => "Credit",
                        "amount" => $data
                    );
                    break;
                case 'amount_over':
                    $journalEntryAccounts[] = array(
                        "Name" => strtoupper($accountsMaster[$dataType]["Name"]),
                        "id" => $existing_accounts[strtoupper($accountsMaster[$dataType]["Name"])],
                        "postingType" => "Debit",
                        "amount" => $data
                    );
                    $journalEntryAccounts[] = array(
                        "Name" => strtoupper("PHP POS Petty Cash"),
                        "id" => $existing_accounts["PHP POS ".strtoupper(lang('common_petty_cash'))],
                        "postingType" => "Credit",
                        "amount" => $data
                    );
                    break;
                case 'amount_short':
                    $journalEntryAccounts[] = array(
                        "Name" => strtoupper($accountsMaster[$dataType]["Name"]),
                        "id" => $existing_accounts[strtoupper($accountsMaster[$dataType]["Name"])],
                        "postingType" => "Credit",
                        "amount" => $data
                    );
                    $journalEntryAccounts[] = array(
                        "Name" => strtoupper("PHP POS Petty Cash"),
                        "id" => $existing_accounts["PHP POS ".strtoupper(lang('common_petty_cash'))],
                        "postingType" => "Debit",
                        "amount" => $data
                    );
                    break;
                
            }
            return $journalEntryAccounts;
        }catch (Exception $e) {
            $this->_log(lang('common_sync_account_exist_exception').$e->getMessage());
        }
    }

    private function _createAccount($accountDetails)
    {
    
    	if ($accountDetails)
    	{
        try {
            $result_refresh_token = $this->refresh_tokens();
            $dataService = $this->_get_data_service();

            $account_create = QBAccount::create($accountDetails);
            $account_response = $dataService->Add($account_create);
            $error = $dataService->getLastError();

            if(!$error)
            {
                global $existing_accounts;
                $existing_accounts[strtoupper($accountDetails["Name"])] = $account_response->Id;
            }
            else
            {
                $xml = simplexml_load_string($error->getResponseBody());
                $error_message = (string)$xml->Fault->Error->Detail;
                return $error_message; 
            }

        }catch (Exception $e) {
            $this->_log(lang('common_sync_account_exist_exception').$e->getMessage());
        }
        
    	}
    }
    

    private function classId($id)
    {
        $jsonClasses = $this->Appconfig->get_qb_classes();
        $classes = [];
        if(!empty($jsonClasses))
        {
            $classes = json_decode($jsonClasses);
        }
        if(!empty($classes))
        {
            foreach($classes as $class)
            {
                if($class->id == $id)
                {
                    if($class->id == $id)
                        return $class->class_id;
                }
            }
        }

        return false;
    }

    // Create Journal Entry
    public function _createJournalEntry()
    {
        try
        {
            $export_start_date = $this->config->item('qb_export_start_date') ? $this->config->item('qb_export_start_date') : date(get_date_format());
            $this->Appconfig->save('qb_export_date',$export_start_date);
            $export_previous_date = date("Y-m-d",strtotime(' -1 day'));
            if (!$export_start_date){
                $export_start_date = date('Y-m-d', strtotime(' -1 day'));
            }
            $fetchRecordDates = []; 

            while (strtotime($export_start_date) <= strtotime($export_previous_date)) {
                $fetchRecordDates[] = array('date'=> $export_start_date);
                $export_start_date = date ("Y-m-d", strtotime("+1 day", strtotime($export_start_date)));
            }
            						
            // Call the function to create classes for all the locations, if needed
            $this->_createClass();


            global $existing_accounts;
            $existing_accounts = $this->_accounts_exist();

            $successHistoricalRecords = $this->Appconfig->get_qb_journal_entry_records() ?$this->Appconfig->get_qb_journal_entry_records():serialize(array());
            $syncJobRecordsStatus = unserialize($successHistoricalRecords);
            
            foreach($fetchRecordDates as $fetchByDate)
            {
                $fetchDate = date("Y-m-d",strtotime($fetchByDate['date']));
                $result_refresh_token = $this->refresh_tokens();
                $dataService = $this->_get_data_service();
                
                
                foreach($this->Location->get_all()->result_array() as $location)
                {   
                    if (!array_key_exists($location['location_id'],$syncJobRecordsStatus)){
                        $syncJobRecordsStatus[$location['location_id']] = array();
                    }
                    if (!in_array($fetchDate,$syncJobRecordsStatus[$location['location_id']])){
		                //Fetching Accounts from db
		                $eof = $this->QuickbooksModel->getEndOfDay($fetchDate);
						
                        $endOfDaySummaryData = $eof[$location['location_id']];
                        $journalEntryAccounts = [];
                        foreach ($endOfDaySummaryData as $categoryType => $data){
                            $journalEntryAccounts = $this->_verifyAccountData($categoryType,$data,$journalEntryAccounts);
                        }
												
                        $journal_entry_create = array();
                        $journal_entry_create['Adjustment'] = false;
                        $journal_entry_create['domain'] = 'QBO';
                        $journal_entry_create['sparse'] = false;
                        $journal_entry_create['SyncToken'] = '0';
                        $journal_entry_create['TxnDate'] = $fetchDate;
                    
                        $journal_entry_lines = [];
                        
                        //Fetching class id of the location
                        $classId = $this->classId($location['location_id']);
                        $netAmount = floatval(0.00);
                        foreach($journalEntryAccounts as $account)
                        {
                            // check if the account is over_short and the rounding off issue is happening , add the amount to the amount to be added.
                            if ($account['Name'] === "PHP POS ".strtoupper(lang('common_amount_short')) && $netAmount !== 0.00){
                                // reverse the impact of the pending amount so that the proper credit/debit can be applied to je
                                $netAmount  = -1 * $netAmount;
                                $account['amount'] = number_format(floatval($account['amount']),2, '.', '') + floatval($netAmount);
                               
                                $account['postingType'] =  ($account['amount'] < 0?"Debit":"Credit");
                                $account['amount'] = abs($account['amount']);
                            }
                            // if the amount it -ve, reverse the posting type to avoid any errors.
                            if ($account['amount'] < 0){
                                $account['postingType'] =  ($account['postingType'] === "Debit"?"Credit":"Debit");
                            }
                            $account['amount'] = number_format(abs($account['amount']),2 ,'.', '');
                            // if thee is any pending amount left over , then apply it using here. 
                            if ($account['Name'] === "PHP POS ".strtoupper(lang('common_petty_cash')) && $account['amount'] === 0.00 && $netAmount !== 0.00){
                                $account['amount'] = floatval($netAmount);
                            }
                            
                            if ($account['postingType'] === "Credit"){
                                $netAmount = number_format(($netAmount === 0.00 ? number_format(floatval($account['amount']),2, '.', '') : floatval($netAmount) + number_format(floatval($account['amount']),2, '.', '')),2, '.', '');
                            }else{
                                $netAmount = number_format(floatval($netAmount) - number_format(floatval($account['amount']),2, '.', ''),2, '.', '');
                            }
                            
                            if(@$account['amount'] != "" && @$account['amount'] != 0)
                            {
                                $journal_entry_line = array(
                                    'Amount' => number_format(abs($account['amount']),2 ,'.', ''),
                                    'DetailType' => 'JournalEntryLineDetail',
                                    'JournalEntryLineDetail' => array(
                                        'PostingType' =>$account['postingType'],
                                        'AccountRef' => array(
                                            'name' => $account['Name'],
                                            'value' => $account['id']
                                            ),
                                        'ClassRef'=> array(
                                            'value' => $classId 
                                        )
                                    )
                                );
                                $journal_entry_lines[] = $journal_entry_line;
                            }
                        }
                        $journal_entry_create['Line'] = $journal_entry_lines;
                        if (count($journal_entry_lines)){
                            $journal_entry_receipt_create = QBJournalEntry::create($journal_entry_create);
                            $journal_entry_receipt_create_result = $dataService->Add($journal_entry_receipt_create);
                            $error = $dataService->getLastError();
                            $error_message = "";
                            $journalEntryId = "";
                            if ($error) {
                                $xml = simplexml_load_string($error->getResponseBody());
                                $error_message = (string)$xml->Fault->Error->Detail;
                                $this->_log("*******" . lang('common_EXCEPTION') . "$fetchDate: ". $error_message);
                                
                                // in case of failure, the sucess date should be repmoved from db.
                                $successHistoricalRecords = $this->Appconfig->get_qb_journal_entry_records() ?$this->Appconfig->get_qb_journal_entry_records():serialize(array());
                                $syncJobRecords = unserialize($successHistoricalRecords);
                                if (array_key_exists($location['location_id'],$syncJobRecords)){
                                    $locationJobRecord = $syncJobRecords[$location['location_id']];
                                    if (in_array($fetchDate,$locationJobRecord)){
                                        array_splice($syncJobRecords[$location['location_id']], array_search($fetchDate,$syncJobRecords[$location['location_id']]),1);
                                        $this->Appconfig->save('qb_journal_entry_records',serialize($syncJobRecords));
                                    }
                                }
                            }   
                            else
                            {
															
								$this->_log($fetchByDate['date']);
															
                                // store the success records data in the db.
                                $successHistoricalRecords = $this->Appconfig->get_qb_journal_entry_records() ?$this->Appconfig->get_qb_journal_entry_records():serialize(array());
                                $syncJobRecords = unserialize($successHistoricalRecords);
                                if (!array_key_exists($location['location_id'],$syncJobRecordsStatus)){
                                    $syncJobRecords[$location['location_id']] = array();
                                }
                                if (!in_array($fetchDate,$syncJobRecordsStatus[$location['location_id']])){
                                    $syncJobRecords[$location['location_id']][] = $fetchDate;
                                }
                                
                                $this->Appconfig->save('qb_journal_entry_records',serialize($syncJobRecords));
                                
                            }
                        }
                        
                    }
                }
				
	            $this->_log(lang('success_journal_entry'));
            }
                
        }
        catch (Exception $e) {
            $this->_log(lang('common_journal_entry').$e->getMessage());
            return false;
        }
        
        
    }

        

    //Create class
    private function _createClass()
    {
        try
        {
            foreach($this->Location->get_all()->result_array() as $location)
            {
                $not_exist = $this->class_exist($location['name']);
                if($not_exist)
                {
                    $result_refresh_token = $this->refresh_tokens();
                    $dataService = $this->_get_data_service();
                    $class_receipt_create = QBClass::create(array('Name'=>$location['name']));
                    $class_receipt_create_result = $dataService->Add($class_receipt_create);
                    $error = $dataService->getLastError();
                    if ($error) {
                        $xml = simplexml_load_string($error->getResponseBody());
                        $error_message = (string)$xml->Fault->Error->Detail;
                        $this->_log("*******" . lang('common_EXCEPTION') . " creating class: ". $error_message);
                    }
                    else
                    {
                        $newclass['Name'] = $location['name'];
                        $newclass['id'] = $location['location_id'];
                        $newclass['class_id'] = $class_receipt_create_result->Id;
                        $newclasses[] = $newclass;
                    }
                }
                
            }
            
            if(!empty($newclasses))
            {
                $classJson = json_encode($newclasses);
                if(!empty($classes))
                {
                    $classJson =  json_encode(array_merge($classes,$newclasses));
                }
                $this->Appconfig->save('qb_classes',$classJson);
                
            }
            
            return true;
        }
        catch (Exception $e) 
        {
            $this->_log(lang('common_add_class').$e->getMessage());
        }
        return false;
        

    }

    public function class_exist($name)
    {
        $jsonClasses = $this->Appconfig->get_qb_classes();
        $classes = [];
        if(!empty($jsonClasses))
        {
            $classes = json_decode($jsonClasses);
        }
        if(!empty($classes))
        {
            foreach($classes as $class)
            {
                if($class->Name == $name)
                {
                    return false;
                }
            }
        }
        return true;
    }
    
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
                system('php '.FCPATH."$prev_folder/index.php quickbooks cron ".$argv[3].$prev_folder.'/ '.$argv[4]);
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
                
                $this->Appconfig->save('kill_qb_cron',0);
                
                if ($this->Appconfig->get_raw_qb_cron_running())
                {
                    echo json_encode(array('success' => FALSE, 'message' => lang('common_qb_running')));
                    die();
                }
            
                $this->load->model('Location');
                if ($timezone = ($this->Location->get_info_for_key('timezone', 1)))
                {
                    date_default_timezone_set($timezone);
                }

                $this->Appconfig->save('qb_cron_running',1);
                $this->Appconfig->save('qb_sync_percent_complete',0);
                $qb_sync_operations = unserialize($this->config->item('qb_sync_operations'));
                $valid = array('export_journalentry_to_quickbooks');
                $numsteps = count($qb_sync_operations);
                $stepsCompleted = 0;
                foreach($qb_sync_operations as $operation)
                {
                    if (is_cli())
                    {
                        echo "START $operation\n";
                    }
                    
                    if(in_array($operation, $valid))
                    {
                        // Refresh Tokens if is near to expire (that is before 30 minutes)
                        $result_refresh_token = $this->refresh_tokens();
                        if ($result_refresh_token) {
                            $dataService = $this->_get_data_service();
                        }
                        $percent = floor(($stepsCompleted/$numsteps)*100);
                        $message = lang("config_".$operation);
                        $this->_update_sync_progress($percent, $message);
                        $operation_method = "_createJournalEntry";
                        
                        $this->$operation_method();
                        $stepsCompleted ++;
                    }
                    if (is_cli())
                    {
                        echo "DONE $operation\n";
                    }
                    $this->_kill_if_needed();
                }
                $percent = floor(($stepsCompleted/$numsteps)*100);
                $message = lang("config_".$operation);
                $this->_update_sync_progress($percent, $message);
                $this->load->model('Appconfig');
                $sync_date = date('Y-m-d H:i:s');
                $this->Appconfig->save('last_qb_sync_date', $sync_date);
                if (is_cli())
                {
                    echo "\n\n***************************DONE***********************\n";
                }
                $this->_save_log();
                $this->Appconfig->save('qb_cron_running',0);                
                $this->Appconfig->save('qb_sync_percent_complete',100);             
                
                echo json_encode(array('success' => TRUE, 'date' =>$sync_date));
            
            
            }
            catch(Exception $e)
            {
                if (is_cli())
                {
                    echo "*******EXCEPTION 1: ".var_export($e->getMessage(),TRUE);
                }
                $this->Appconfig->save('qb_cron_running',0);                
            }
    }
        
        
        
    private function _build_paths($tree, $path = '') 
    {
        $result = array();
        foreach ($tree as $id => $cat) 
            {
            $result[$id] = $path . $cat['name'];
            if (isset($cat['children'])) 
                    {
                $result += $this->_build_paths($cat['children'], $result[$id] . '|');
            }
        }
        return $result;
    }
                        
        
        
    function _log($msg)
    {
        $msg = date(get_date_format().' h:i:s ').': '.$msg."\n"; 

        if (is_cli())
        {
            echo $msg;
        }
        $this->log_text.=$msg;
    }
    
    function _save_log()
    {
    $CI =& get_instance();  
        $CI->load->model("Appfile");
        $this->Appfile->save('quickbooks_log.txt',$this->log_text,'+72 hours');
    }
    
    private function _kill_if_needed()
    {
        if ($this->Appconfig->get_raw_kill_qb_cron())
        {
            if (is_cli())
            {
                echo date(get_date_format().' h:i:s ').': KILLING CRON'."\n";
            }
    
            $this->Appconfig->save('kill_qb_cron',0);
            echo json_encode(array('success' => TRUE, 'cancelled' => TRUE, 'sync_date' => date('Y-m-d H:i:s')));
            $this->_save_log();
            die();
        }
    }
    
    function _update_sync_progress($progress,$message)
    {
        $this->Appconfig->save('qb_sync_percent_complete',$progress);
        $this->Appconfig->save('qb_sync_message', $message ? $message : '');
    }
        
}

        
?>