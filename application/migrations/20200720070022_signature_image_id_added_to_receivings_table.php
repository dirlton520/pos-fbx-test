<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_signature_image_id_added_to_receivings_table extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20200720070022_signature_image_id_added_to_receivings_table.sql'));
	    }

	    public function down() 
			{
	    }

	}