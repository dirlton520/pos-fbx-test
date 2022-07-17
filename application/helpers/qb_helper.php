<?php
require_once APPPATH . 'libraries/qb/src/config.php';
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper;
use QuickBooksOnline\API\DataService\DataService;

/*
* $flagReturnNullIfNoTokenFound => This flag is used to return null, if we dont have access tokens. In config page we are getting chart of accounts from qb on page load. If we dont have access tokens, this flag make sure that we don't make the api call.
*/
function _get_data_service($authed = true, $flagReturnNullIfNoTokenFound = false)
{
	$CI =& get_instance();
    // $igc_payment_amount = $cart->get_payment_amount(lang('common_integrated_gift_card'));
    $params = array(
        'auth_mode' => 'oauth2',
        'ClientID' => QUICKBOOKS_CLIENT_ID,
        'ClientSecret' => QUICKBOOKS_CLIENT_SECRET,
        'RedirectURI' => ENVIRONMENT == 'development' ? DATA_SERVICE_REDIRECT_URI_1 : DATA_SERVICE_REDIRECT_URI_2,
        'scope' => "com.intuit.quickbooks.accounting",
        'baseUrl' => ucfirst(ENVIRONMENT),
        'state' => site_url('quickbooks/initial_auth'),
    );

    //If we have authed we can include this data so we can make API calls
    if ($authed && $CI->config->item('quickbooks_access_token') && $CI->config->item('quickbooks_refresh_token') && $CI->config->item('quickbooks_realm_id')) {
        $params['accessTokenKey'] = $CI->config->item('quickbooks_access_token');
        $params['refreshTokenKey'] = $CI->config->item('quickbooks_refresh_token');
        $params['QBORealmID'] = $CI->config->item('quickbooks_realm_id');
        $flagReturnNullIfNoTokenFound = false;
    }
    if ($flagReturnNullIfNoTokenFound) {
        return null;
    } else {
        return DataService::Configure($params);
    }
}


function refresh_tokens($redirect_to_store_config = 0)
{
    $CI =& get_instance();

    // Get the access token expire time from app config
    $access_token_expire_at = $CI->config->item("access_token_expire_at");
    // Flag added to for refresh token code.
    $flagRefreshToken = false;
    if((!empty($access_token_expire_at)) && ($redirect_to_store_config == 0))
    {
        // get current timestamp
        $current_time_stamp = time();
        // Get the Difference between access token time stamp and current time stamp in seconds
        $timeDifference = $access_token_expire_at - $current_time_stamp;
        // Access token is valid till 1 hour, so we will refresh it 30 minutes before it expires
        if ($timeDifference < 1800) {
            $flagRefreshToken = true;
        }
    } else {
        $flagRefreshToken = true;
    }

    // if flagRefreshToken is true then it will execute the refresh code
    if ($flagRefreshToken) {
        $oauth2LoginHelper = new OAuth2LoginHelper(QUICKBOOKS_CLIENT_ID,QUICKBOOKS_CLIENT_SECRET);
        $accessTokenObj = $oauth2LoginHelper->refreshAccessTokenWithRefreshToken($CI->config->item('quickbooks_refresh_token'));
        $accessTokenValue = $accessTokenObj->getAccessToken();
        $refreshTokenValue = $accessTokenObj->getRefreshToken();
        // Get the access token expired timestamp
        $accessTokenExpiresAt = strtotime($accessTokenObj->getAccessTokenExpiresAt());
        
        // it just update the tokens in database, but doesnt update the tokens in current config
        $CI->Appconfig->save('quickbooks_access_token',$accessTokenValue);
        $CI->Appconfig->save('quickbooks_refresh_token',$refreshTokenValue);

        // Save the access token expired timestamp in the database
        $CI->Appconfig->save('access_token_expire_at',$accessTokenExpiresAt);

        // refresh the tokens in current config, without page reload
        $CI->config->set_item('quickbooks_access_token', $accessTokenValue); 
        $CI->config->set_item('quickbooks_refresh_token', $refreshTokenValue);

        // set the the access token expired timestamp in the set_item in config, so that we can refresh the token based on this timestamp
        $CI->config->set_item('access_token_expire_at', $accessTokenExpiresAt);


        if ($redirect_to_store_config)
        {
            $search = rawurlencode(lang('common_quickbooks'));				
            redirect("config?search=$search");
        }
        return true;
    }
}

function _get_all_taxes() {
    $CI =& get_instance();
    $dataService = _get_data_service();
    $tax_offset = 0;
    $tax_per_page = 100;
    while (1) {
        // Refresh Tokens if it is near to expire (i.e before 5 minutes)
        refresh_tokens(0);
        $allTaxcode = $dataService->FindAll('TaxCode', $tax_offset, $tax_per_page);
        $error = $dataService->getLastError();
        if (!$error) {
            $tax_offset += $tax_per_page;
            if (!empty($allTaxcode) and count($allTaxcode) > 0) {
                foreach ($allTaxcode as $taxes) {
                    if ($taxes->SalesTaxRateList){
                        $salesTaxList = $taxes->SalesTaxRateList;
                        $salesTaxRateDetail = $salesTaxList->TaxRateDetail;
                        if (is_array($salesTaxRateDetail)){
                            foreach ($salesTaxRateDetail as $salesTaxRate){
                                $taxRateId = $salesTaxRate->TaxRateRef;
                                $allTaxRates = $dataService->FindById('TaxRate',$taxRateId);
                                $error = $dataService->getLastError();
                                if (!$error) {
                                    if (!empty($allTaxRates)) {
                                        $taxArray[$taxes->Id] = $allTaxRates->RateValue;
                                    }
                                }
                            }
                        }else{
                            $taxRateId = $salesTaxRateDetail->TaxRateRef;
                            $allTaxRates = $dataService->FindById('TaxRate',$taxRateId);
                            $error = $dataService->getLastError();
                            if (!$error) {
                                if (!empty($allTaxRates)) {
                                    $taxArray[$taxes->Id] = $allTaxRates->RateValue;
                                }
                            }
                        }
                        
                        
                    }
                }
                return $taxArray;
            } else {
                break;
            }
        } else {
            $last_error = $dataService->getLastError();
            $xml = simplexml_load_string($last_error->getResponseBody());
            $error_message = (string)$xml->Fault->Error->Detail;
            return $error_message;
        }
    }
}

?>
