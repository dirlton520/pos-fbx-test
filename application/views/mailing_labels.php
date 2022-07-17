<?php
if ($type == 'pdf')
{
	require_once (APPPATH.'libraries/tfpdf/MY_tfpdf.php');
	$is_mac_safari = $this->agent->is_browser('Safari') && $this->agent->platform() == 'Mac OS X';
	$is_windows_ie = strpos($this->agent->platform(),'Windows') !== FALSE && $this->agent->is_browser('Internet Explorer');
	$is_windows_edge = strpos($this->agent->platform(),'Windows') !== FALSE && $this->agent->is_browser('Edge');

	$force_download_pdf = $is_mac_safari || $is_windows_ie || $is_windows_edge;

	$pdf = new MY_tfpdf();
	$pdf->AddPage('P','Letter');
	$pdf->AddFont('DejaVu','','DejaVuSansCondensed.ttf',true);
	$pdf->SetFont('DejaVu','',10);
	$pdf->SetMargins(0, 0);
	$pdf->SetAutoPageBreak(false);
	$x = $y = $counter = 0;

	foreach($mailing_labels as $label)
	{
		$text = $label['name']."\n";
		$text .= $label['address_1']."\n".($label['address_2'] ? $label['address_2']."\n" : '');
		$text .= $label['city'].' '.$label['state'].' '.$label['zip']."\n";
		$text .= $label['country'];
		
	    $pdf->AveryAddressCell($x,$y,$text);
		 $counter++;
	    $y++; // next row
	    if($y == 10) // end of page wrap to next column
		 { 
	        $x++;
	        $y = 0;
	        if($x == 3 && $counter!=count($mailing_labels)) // end of page
			  {
	            $x = 0;
	            $y = 0;
	            $pdf->AddPage('P','Letter');
	        }
	    }
	}

	$pdf->output('Mailing Labels.pdf',$force_download_pdf ? 'D': 'I');
}
else
{
	$this->load->helper('report');
	$rows = array();

	$header_row = array(lang('common_name'),lang('common_address_1'),lang('common_address_2'),lang('common_city'),	lang('common_state'),lang('common_zip'),lang('common_country'));
	$rows[] = $header_row;

	foreach ($mailing_labels as $r) {
		$row = array(
			$r['name'],
			$r['address_1'],
			$r['address_2'],
			$r['city'],
			$r['state'],
			$r['zip'],
			$r['country'],
		);
	
		$rows[] = $row;
	}

	$this->load->helper('spreadsheet');
	$content = array_to_spreadsheet($rows,'Mailing Labels.'.($this->config->item('spreadsheet_format') == 'XLSX' ? 'xlsx' : 'csv'));
	exit;
}
?>