<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_categories_images_and_colors extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/20161227200153_categories_images_and_colors.sql'));
	    }

	    public function down() 
			{
	    }

	}