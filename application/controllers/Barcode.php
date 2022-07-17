<?php
require_once (APPPATH."libraries/php-barcode-generator/src/BarcodeGenerator.php");
require_once (APPPATH."libraries/php-barcode-generator/src/BarcodeGeneratorSVG.php");
require_once (APPPATH."libraries/php-barcode-generator/src/BarcodeGeneratorPNG.php");

class Barcode extends MY_Controller 
{
	function __construct()
	{
		parent::__construct();	
	}
	
	function index($type='png')
	{
		$text = rawurldecode($this->input->get('text'));
		$barcode = rawurldecode($this->input->get('barcode'));
		$scale = $this->input->get('scale') ? $this->input->get('scale') : 1;
		$thickness = $this->input->get('thickness') ? $this->input->get('thickness') : 30;
		if ($type == 'png')
		{
			$font_size = $this->input->get('font_size') ? $this->input->get('font_size') : 9;
			$generator = new Picqer\Barcode\BarcodeGeneratorPNG();
			header("Cache-Control: max-age=2592000");
			header('Expires: '.gmdate('D, d M Y H:i:s', strtotime('+1 month')).' GMT');
			header('Pragma: cache');
			header('Content-Type: image/png');
			
			if (function_exists('header_remove'))
			{
			  foreach(headers_list() as $header)
				{
					if (strpos($header, 'Set-Cookie') === 0) 
					{
			         header_remove('Set-Cookie');
					}
				}
			}
			
			echo $generator->getBarcode($barcode, $text,$generator::TYPE_CODE_128,$scale,$thickness,$font_size);
		}
		elseif($type=='svg')
		{
			$font_size = $this->input->get('font_size') ? $this->input->get('font_size') : 13;
			$generator = new Picqer\Barcode\BarcodeGeneratorSVG();
			header("Cache-Control: max-age=2592000");
			header('Expires: '.gmdate('D, d M Y H:i:s', strtotime('+1 month')).' GMT');
			header('Pragma: cache');
			header('Content-type: image/svg+xml');
			
			if (function_exists('header_remove'))
			{
			  foreach(headers_list() as $header)
				{
					if (strpos($header, 'Set-Cookie') === 0) 
					{
			         header_remove('Set-Cookie');
					}
				}
			}
			
			echo $generator->getBarcode($barcode, $text,$generator::TYPE_CODE_128,$scale,$thickness,$font_size);
			
		}
	}

}
?>