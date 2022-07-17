<?php
require_once (APPPATH.'libraries/tcpdf/MY_tcpdf.php');
require_once (APPPATH.'libraries/tfpdf/MY_tfpdf.php');

$is_mac_safari = $this->agent->is_browser('Safari') && $this->agent->platform() == 'Mac OS X';
$is_windows_ie = strpos($this->agent->platform(),'Windows') !== FALSE && $this->agent->is_browser('Internet Explorer');
$is_windows_edge = strpos($this->agent->platform(),'Windows') !== FALSE && $this->agent->is_browser('Edge');

$force_download_pdf = $is_mac_safari || $is_windows_ie || $is_windows_edge;
if (!$this->config->item('use_rtl_barcode_library'))
{	
	$pdf = new MY_tfpdf();
	$pdf->AddPage('P','Letter');
	$pdf->AddFont('DejaVu','','DejaVuSansCondensed.ttf',true);
	$pdf->SetFont('DejaVu','',10);
	$pdf->SetMargins(0, 0);
	$pdf->SetAutoPageBreak(false);
}
else
{	
	$pdf = new MY_tcpdf();
	$pdf->AddPage('P','Letter');
	$pdf->SetFont('DejaVuSansCondensed','',10);
	$pdf->SetMargins(0, 0);
	$pdf->SetAutoPageBreak(false,0);
	$pdf->setImageScale(1.53);
}
$x = $y = $counter = 0;
$company = ($company = $this->Location->get_info_for_key('company')) ? $company : $this->config->item('company');

if ($skip > 0)
{
	$x = floor($skip/10);
	$y = $skip % 10;
}

foreach($items as $item)
{
	$barcode = $item['id'];
	$image = site_url('barcode').'?barcode='.rawurlencode($barcode).'&text='.rawurlencode($barcode)."&scale=$scale";
	$expire_key = (isset($from_recv) ? $from_recv : 0).'|'.ltrim($item['id'],0);
	$text = preg_replace('#<span style="text-decoration: line-through.*?</span>#','',$item['name']);
	$text = strip_tags($text);
	
	if(!$this->config->item('hide_expire_date_on_barcodes') && isset($items_expire[$expire_key]) && $items_expire[$expire_key] && !$this->config->item('hide_name_on_barcodes'))
	{
		$text.= " (".lang('common_expire_date').' '.$items_expire[$expire_key].')';		
	}
	
	if ($this->config->item('hide_barcode_on_barcode_labels'))
	{
		$image = NULL;
	}
	
    $pdf->AveryBarcodeCell($x, $y, $this->config->item('show_barcode_company_name') ? $company : '', $image,$text,$barcode);
	 $counter++;
    $y++; // next row
    if($y == 10) // end of page wrap to next column
	 { 
        $x++;
        $y = 0;
        if($x == 3 && $counter!=count($items)) // end of page
		  {
            $x = 0;
            $y = 0;
            $pdf->AddPage('P','Letter');
        }
    }
}

$pdf->output('Barcode Sheet.pdf',$force_download_pdf ? 'D': 'I');
?>