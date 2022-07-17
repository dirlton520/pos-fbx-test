<?php
class App_files extends MY_Controller 
{
	function __construct()
	{
		parent::__construct();	
	}
	
	function view_signed_url($file_id,$extra_file_name = FALSE)
	{
		$this->load->model('Appfile');
		
		$signature = $this->input->get('signature');
		
		if ($signature == $this->Appfile->get_signature($file_id))
		{
			$this->_output_file($file_id);
		}
		else
		{
		    header("HTTP/1.1 401 Unauthorized");
		    exit;
		}
	}
	
	//We have a seperate url for this so we can cache with cloudflare, it is the same as view
	function view_cacheable($file_id,$extra_file_name = FALSE)
	{
		$this->view($file_id,$extra_file_name);
	}
	
	//$extra_file_name can be used for SEO purposes but is not acutally used in function
	function view($file_id,$extra_file_name = FALSE)
	{ 
		$this->load->model('Appfile');
		
		//cast to index in case we have extension
		$file_id = (int)$file_id;
		
		if (!$this->Employee->is_logged_in())
		{
		    header("HTTP/1.1 401 Unauthorized");
		    exit;
		}
		
		$this->_output_file($file_id);
	}
	
	private function _output_file($file_id)
	{
		//Don't allow images to cause hangups with session
		session_write_close();
		$this->load->model('Appfile');
		$file = $this->Appfile->get($file_id);
		$file_name = $file->file_name;
		$this->load->helper('file');
		header("Cache-Control: max-age=2592000");
		header('Expires: '.gmdate('D, d M Y H:i:s', strtotime('+1 month')).' GMT');
		header('Pragma: cache');
		header('Content-Disposition: inline; filename="'.$file_name.'"');
		header("Content-type: ".get_mime_by_extension($file->file_name));
		
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
		echo $file->file_data;
	}
}
?>