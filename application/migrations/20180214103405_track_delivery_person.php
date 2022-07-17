<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_track_delivery_person extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20180214103405_track_delivery_person.sql'));
	    }

	    public function down() 
			{
	    }

	}