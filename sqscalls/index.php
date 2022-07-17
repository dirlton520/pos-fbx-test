<?php

include_once './vendor/autoload.php';
use Aws\Sqs\SqsClient; 
use Aws\Exception\AwsException;

class SqsRequestBitrixPlugin 
{

	function sendMessageToSqsQueue($data)
	{
		$outboundAuthCode = 'j0iyp3frlqbll8b7ibohko3363nucd99';

		$actionType = [
			'ONCRMPRODUCTADD' => 'ADD',
			'ONCRMPRODUCTUPDATE' => 'UPDATE',
			'ONCRMPRODUCTDELETE' => "DELETE",
			'ONCRMPRODUCTSECTIONADD' => 'ADD',
			'ONCRMPRODUCTSECTIONUPDATE' => 'UPDATE',
			'ONCRMPRODUCTSECTIONDELETE' => "DELETE"			
		];
		
		$bitrixUser = "56130"; 
		$inboundAccessToken = 'hx1bm2tfhko32bva';		
		$inboundUrls = [
			'ONCRMPRODUCTADD' => $bitrixUser."/".$inboundAccessToken."/crm.product.get.json?id=",
			'ONCRMPRODUCTUPDATE' => $bitrixUser."/".$inboundAccessToken."/crm.product.get.json?id=",
			'ONCRMPRODUCTDELETE' => "NOREQUEST",
			'ONCRMPRODUCTSECTIONADD' => $bitrixUser."/".$inboundAccessToken."/crm.productsection.get.json?id=",
			'ONCRMPRODUCTSECTIONUPDATE' => $bitrixUser."/".$inboundAccessToken."/crm.productsection.get.json?id=",
			'ONCRMPRODUCTSECTIONDELETE' => "NOREQUEST"
		];

		$sqsCredentials = array(
			'region' => 'ap-south-1',
			'version' => 'latest',
			'credentials' => array(
				'key'    => 'AKIAYFUQQDUMJA5VIU67',
				'secret' => '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',
			)
		);	
		
		$queueName = 'BITRIXTOPHPPOSDEV';
		
		if (isset($inboundUrls[$data['event']]) && (isset($data['auth']['client_endpoint'])) && (isset($data['auth']['application_token']) && $data['auth']['application_token'] == $outboundAuthCode)) {
			$inboundUrlWithKey = $inboundUrls[$data['event']];

			$messageData = [];
			$result = [];
			if ($inboundUrlWithKey == 'NOREQUEST') {
				$messageData['action'] = $actionType[$data['event']];
				$messageData['event'] = $data['event'];
				$jsonResult = '{"result":{"ID":"'.$data['data']['FIELDS']['ID'].'"}}';
				$messageData['response'] = json_decode($jsonResult);
			}
			else {
				$messageData['action'] = $actionType[$data['event']];
				$messageData['event'] = $data['event'];
				$queryUrl = $data['auth']['client_endpoint'].$inboundUrlWithKey.$data['data']['FIELDS']['ID'];
				$jsonResult = file_get_contents($queryUrl);
				$messageData['response'] = json_decode($jsonResult);
			}

			if (!empty($messageData)) {
				$messageData['event'] = $data['event'];
				$messageData['field_id'] = $data['data']['FIELDS']['ID'];
				try {
					$sqsClient = new SqsClient($sqsCredentials);
                    $resultSqs = $sqsClient->getQueueUrl(array('QueueName' => $queueName));
					$queueUrl = $resultSqs->get('QueueUrl');
					$sqsClient->sendMessage([
						'QueueUrl' => $queueUrl,
						'MessageBody' => base64_encode(json_encode($messageData))					 	
					]);
					file_put_contents(getcwd() . '/logs/'.'sqs_success_log.log', "New message added to SQS Queue Successfully (Event - ".$data['event']." Field ID - ".$data['data']['FIELDS']['ID'].")"."\n", FILE_APPEND);
				} catch (\Exception $e) {
					file_put_contents(getcwd() . '/logs/'.'bitrix_request_log.log', $e->getMessage()."\n", FILE_APPEND);
				}
			}
			else {
				file_put_contents(getcwd() . '/logs/'.'bitrix_request_log.log', "messageDate is empty. \n", FILE_APPEND);				
			}
		}
		return true;
	}
}

$data = $_REQUEST;
try{

	file_put_contents(getcwd() . '/logs/'.'logresponse.log', json_encode($data), FILE_APPEND);
	$class = new SqsRequestBitrixPlugin();
	$class->sendMessageToSqsQueue($data);
	die("Success");	
}
catch (\Exception $e){
	file_put_contents(getcwd() . '/logs/'.'error.log', $e->getMessage(), FILE_APPEND);
	echo $e->getMessage(); die("===");
}
?>