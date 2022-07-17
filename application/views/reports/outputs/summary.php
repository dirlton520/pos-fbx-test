<h3><?php echo $title.' - '.$subtitle;?></h3>
<div class="row">
	<?php foreach($summary_data as $name=>$value) { ?>
	    <div class="col-md-3 col-xs-12 col-sm-6 summary-data">
	        <div class="info-seven primarybg-info">
	            <div class="logo-seven hidden-print"><i class="ti-widget dark-info-primary"></i></div>
					
					<?php
					if(!is_numeric($value))
					{
		            echo $value;
		            echo '<p>'.lang('reports_'.$name).'</p>';						
					}
					elseif($name == 'total_number_of_items_sold' || $name == 'damaged_qty' || $name == 'average_quantity' || $name == 'total_items_in_inventory' || $name == 'number_items_counted' || $name == 'hours' || $name == 'times_rules_applied' || $name == 'sales_per_time_period')
					{
		            echo str_replace(' ','&nbsp;', to_quantity($value));
		            echo '<p>'.lang('reports_'.$name).'</p>';
					}
					else
					{
		            echo to_currency($value);
		            echo '<p>'.lang('reports_'.$name).'</p>';
					}
					?>
	        </div>
	    </div>
	<?php }?>
</div>