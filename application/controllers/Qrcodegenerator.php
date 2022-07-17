<?php
require_once (APPPATH."libraries/php-qrcode/qrcode.php");


class Qrcodegenerator extends MY_Controller 
{
	function __construct()
	{
		parent::__construct();	
	}

	function index()
	{
		$qrcode = rawurldecode($this->input->get('qrcode'));
		$scale = rawurldecode($this->input->get('scale'));

		if(!$scale) {
			$scale = 2;
		}

		$options = array(
			's'=>'qr',
			'sf'=> $scale, 
		);
		$generator = new QRCode($qrcode, $options);

		/* Output directly to standard output. */
		$generator->output_image();
	}
}
