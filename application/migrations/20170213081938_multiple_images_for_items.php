<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_multiple_images_for_items extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170213081938_multiple_images_for_items.sql'));
	    }

	    public function down() 
			{
	    }

	}