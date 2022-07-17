<?php
require_once ("interfaces/Iperson_controller.php");
require_once ("Secure_area.php");

abstract class Person_controller extends Secure_area implements iPerson_controller
{
	function __construct($module_id=null)
	{
		parent::__construct($module_id);		
	}
	
	/*
	This returns a mailto link for persons with a certain id. This is called with AJAX.
	*/
	function mailto()
	{
		$people_to_email=$this->input->post('ids');
		
		if($people_to_email!=false)
		{
			$mailto_url='mailto:';
			foreach($this->Person->get_multiple_info($people_to_email)->result() as $person)
			{
				if ($person->email)
				{
					$mailto_url.=$person->email.',';	
				}
			}
			//remove last comma
			$mailto_url=substr($mailto_url,0,strlen($mailto_url)-1);
			
			echo $mailto_url;
			exit;
		}
		echo '#';
	}		
	
	function download($file_id)
	{
		//Don't allow images to cause hangups with session
		session_write_close();
		$this->load->model('Appfile');
		$file = $this->Appfile->get($file_id);
		$this->load->helper('file');
		$this->load->helper('download');
		force_download($file->file_name,$file->file_data);
	}
	
	function delete_file($file_id)
	{
		$this->check_action_permission('add_update');
		$this->Person->delete_file($file_id);
	}
}
?>