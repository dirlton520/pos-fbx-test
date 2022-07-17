<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI = get_instance();

/* Provide Bitrix Production URL */
$hostUrl = "https://bitrixtophppos.sparkfn.io/";
$hostUrl = $hostUrl."sqscalls/bitrix-config-request.php?time=".time();
$response = $CI->curl->simple_get($hostUrl);
$config = json_decode(base64_decode($response), true);