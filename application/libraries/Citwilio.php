<?php defined('BASEPATH') OR exit('No direct script access allowed');
// Require the bundled autoload file - the path may need to change
// based on where you downloaded and unzipped the SDK
require __DIR__ . '/twilio/src/Twilio/autoload.php';

// Use the REST API Client to make requests to the Twilio REST API

class Citwilio
{
    /** @var Twilio\Rest\Client */
    protected $client;
    public $account_sid;
    public $auth_token;

    public function __construct($auth)
    {
        $this->account_sid = $auth['account_sid'];
        $this->auth_token  = $auth['auth_token'];

        try {
			
		    $this->client = new \Twilio\Rest\Client($this->account_sid, $this->auth_token);
		    $curlOptions = [ CURLOPT_SSL_VERIFYHOST => false, CURLOPT_SSL_VERIFYPEER => false];
		    $this->client->setHttpClient(new \Twilio\Http\CurlClient($curlOptions));
			
        } catch (Exception $e) {
            echo 'Twilio API Error: ',  $e->getMessage(), "\n";
        }
    }

    public function send_sms(string $from_number, string $to_number, string $message)
    {
		$CI =& get_instance();
		
        if($CI->config->item('send_sms_via_whatsapp') == 1){
            $to_number = 'whatsapp:'.$to_number;
            $from_number = 'whatsapp:'.$from_number;
        }
        
        try {
            return $this->client->messages->create($to_number, [
                'from' => $from_number,
                'body' => $message,
            ]);
        } catch (Exception $e) {
            echo 'Twilio API Error: ',  $e->getMessage(), "\n";
        }
    }
}