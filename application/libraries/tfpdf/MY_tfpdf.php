<?php
require_once("tfpdf.php");

class MY_tfpdf extends TFPDF
{
	
  function __construct()
  {
      parent::__construct();
  }
	
	function _putcatalog()
	{
	  parent::_putcatalog();
	  // Disable the page scaling option in the printing dialog
	  // SEE http://stackoverflow.com/a/8444588/627473
	  $this->_out('/ViewerPreferences [/PrintScaling/None]');
	}
	
	public function AveryBarcodeCell($x, $y, $company_name, $barcode_image, $barcode_text,$barcode_item_id_value) 
	{
		$left = 4.7625; // 0.1875" in mm
		$top = 12.7; // 0.5" in mm
		$width = 66.675; // 2.625" in mm
		$height = 25.4; // 1.0" in mm
		$hgap = 3.175; // 0.125" in mm
		$vgap = 0.0;
		
		$barcode_text = character_limiter($barcode_text,100,'...');
		
    $x = $left + (($width + $hgap) * $x);
    $y = $top + (($height + $vgap) * $y);
	 
    $this->SetXY($x, $y+1);
    $this->MultiCell($width, 3, $company_name, 0, 'C');
	 
		if ($barcode_image)
		{
			$this->Image($barcode_image,$x + ($width/2) - 13,$y+4,0,0,'PNG');
		}
		elseif($barcode_item_id_value)
		{
	    $this->SetXY($x, $y+9);
	    $this->MultiCell($width, 3, $barcode_item_id_value, 0, 'C');
		}
    $this->SetXY($x, $y+($height/1.58));
    $this->MultiCell($width, $height/8.0, $barcode_text, 0, 'C');
	}	
	
	function AveryAddressCell($x,$y,$text)
	{
		$left = 4.7625; // 0.1875" in mm
		$top = 12.7; // 0.5" in mm
		$width = 66.675; // 2.625" in mm
		$height = 25.4; // 1.0" in mm
		$hgap = 3.175; // 0.125" in mm
		$vgap = 0.0;
		
    $x = $left + (($width + $hgap) * $x);
    $y = $top + (($height + $vgap) * $y);
	 
    $this->SetXY($x+5, $y+1);
    $this->MultiCell($width, $height/6.0, $text, 0, 'L');
	}
	
}
?>