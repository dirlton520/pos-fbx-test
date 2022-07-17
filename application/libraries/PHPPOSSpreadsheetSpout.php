<?php
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Common\Type;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

class PHPPOSSpreadsheetSpout extends PHPPOSSpreadsheet
{
	private $reader;
	private $data;
	
	function __construct($inputFileName = NULL, $type='xlsx')
	{
		if ($inputFileName)
		{
			$CI =& get_instance();
			
			if (strtolower($type) == 'xlsx')
			{
				$this->reader = ReaderEntityFactory::createXLSXReader();
			}
			else
			{
				$this->reader = ReaderEntityFactory::createCSVReader();
			}
	
			$this->reader->open($inputFileName);
			
			$this->data = array();
			foreach ($this->reader->getSheetIterator() as $sheet) 
			{
				foreach($sheet->getRowIterator() as $row)
				{
					$this->data[] = $row->toArray();
				}
				
				//only read first sheet
				break;
			}
		}
	}
	
	public static function getFirstRow($inputFileName, $type='xlsx')
	{
		$CI =& get_instance();
		if (strtolower($type) == 'xlsx')
		{
			$reader = ReaderEntityFactory::createXLSXReader();
		}
		else
		{
			$reader = ReaderEntityFactory::createCSVReader();
		}

		$reader->open($inputFileName);
		foreach ($reader->getSheetIterator() as $sheet) 
		{
			foreach($sheet->getRowIterator() as $row)
			{
				//only need 1st row
				return $row->toArray();
			}
			
			//Empty spreadsheet
			return array();
		}
	}
	
	//$column starts at 0 and row starts at 1
	public function getCellByColumnAndRow($column, $row)
	{
		if ($this->data)
		{
			if (isset($this->data[$row-1][$column]))
			{
				//Do minus 0 as our rows start at 0
				return $this->data[$row-1][$column];	
			}
		}
		
		return NULL;
		
	}
	public function getNumberOfRows()
	{
		if ($this->data)
		{
			return count($this->data);
		}
		
		return null;
	}
	
	//$data is a matrix to export to excel where each row is gzcompressed to json_encoded
	function arrayToSpreadsheetGzJsonEncoded($arr,$filename, $is_report = false,$email = NULL)
	{
		$CI =& get_instance();

		
		if ($is_report)
		{
			define('SPOUT_EXCEL_WRITER_CELL_FORMAT',0);
		}
		else
		{
			//If we are NOT a report make sure we set text format to 49 (Text format for excel imports)
			define('SPOUT_EXCEL_WRITER_CELL_FORMAT',49);
		}
		if ($CI->config->item('spreadsheet_format') == 'XLSX')
		{
			$writer = WriterEntityFactory::createXLSXWriter(); // for XLSX files				
		}
		else
		{
			$writer = WriterEntityFactory::createCSVWriter(); // for CSV files
		}
		
		if (method_exists($writer,'setTempFolder'))
		{
			$writer->setTempFolder(ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir());
		}
		
		if ($email)
		{
			$tmpFilename = tempnam(ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir(), 'iexcel');
			$writer->openToFile($tmpFilename);
		}
		else
		{
			$writer->openToBrowser($filename); // stream data directly to the browser
		}
		foreach($arr as $row)
		{			
			$row = json_decode(gzdecode($row));
			
			if ($is_report)
			{
				for($k=0;$k<count($row);$k++)
				{
					$row[$k] = $this->stripCurrency($row[$k]);
				
					$hasleading_zero = substr($row[$k],0,1) == '0';

					if (is_numeric($row[$k]) && !$hasleading_zero)
					{
						$row[$k] = (double)$row[$k];
					}
				
				}
			}
			
			$writer->addRow(WriterEntityFactory::createRowFromArray($row));		
		}
		
		
		$writer->close();
		
		if ($email)
		{
			$CI->load->library('email');
			$config['mailtype'] = 'html';				
			$CI->email->initialize($config);
			$CI->email->from($CI->Location->get_info_for_key('email') ? $CI->Location->get_info_for_key('email') : 'no-reply@mg.phppointofsale.com', $CI->config->item('company'));
			$CI->email->to($email); 				
			$CI->email->subject(lang('reports_report'));
			$CI->email->attach($tmpFilename, 'attachment', 'report.'.($CI->config->item('spreadsheet_format') == 'XLSX' ? 'xlsx' : 'csv'));
			$CI->email->message(lang('reports_report'));
			$CI->email->send();
			
			header('Content-Description: File Transfer');
			header('Content-Disposition: attachment; filename="'.'report.'.($CI->config->item('spreadsheet_format') == 'XLSX' ? 'xlsx' : 'csv').'"');
			header('Connection: Keep-Alive');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			readfile($tmpFilename);
		}
	}
	
	//$data is a matrix to export to excel
	public function arrayToSpreadsheet($arr,$filename, $is_report = false)
	{
		$CI =& get_instance();

		
		if ($is_report)
		{
			define('SPOUT_EXCEL_WRITER_CELL_FORMAT',0);
		}
		else
		{
			//If we are NOT a report make sure we set text format to 49 (Text format for excel imports)
			define('SPOUT_EXCEL_WRITER_CELL_FORMAT',49);
		}
		if ($CI->config->item('spreadsheet_format') == 'XLSX')
		{
			$writer = WriterEntityFactory::createXLSXWriter(); // for XLSX files				
		}
		else
		{
			$writer = WriterEntityFactory::createCSVWriter(); // for CSV files
		}
		
		if (method_exists($writer,'setTempFolder'))
		{
			$writer->setTempFolder(ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir());
		}
		
		$writer->openToBrowser($filename); // stream data directly to the browser
		
		if ($is_report)
		{
			
			for($k = 0;$k < count($arr);$k++)
			{
				for($j = 0;$j < count($arr[$k]); $j++)
				{
					$arr[$k][$j] = $this->stripCurrency($arr[$k][$j]);
					
					$hasleading_zero = substr($arr[$k][$j],0,1) == '0';
					
					if (is_numeric($arr[$k][$j]) && !$hasleading_zero)
					{
						$arr[$k][$j] = (double)$arr[$k][$j];
					}
				}
			}
		}
		
		foreach($arr as $row)
		{
			$writer->addRow(WriterEntityFactory::createRowFromArray($row));		
		}		
	
		$writer->close();
	}
}	
?>