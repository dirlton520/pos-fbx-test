<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_tag_id_tags extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20220404103239_tag_id_tags.sql'));
	    }

	    public function down() 
			{
	    }

	} 
