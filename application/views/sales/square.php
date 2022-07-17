<script type="text/data" id="amount"><?php echo $amount;?></script>
<script type="text/data" id="currency"><?php echo H($currency);?></script>
<script type="text/data" id="notes"><?php echo H($notes);?></script>
<script type="text/data" id="location_id"><?php echo H($square_location_id);?></script>

<?php $this->load->view("partial/header"); ?>
<div id="status"><?php echo lang('common_wait');?> <?php echo img(array('src' => base_url().'assets/img/ajax-loader.gif')); ?></div>

<div class="panel panel-piluku">
	<div class="panel-body">
	   <h4 id="title"><?php echo lang('sales_square_app_app_message');?></h4> 
		 <a href="https://itunes.apple.com/us/app/square-point-of-sale-pos/id335393788?mt=8"><?php echo lang('sales_square_ios')?></a><br />
	   <a href="https://play.google.com/store/apps/details?id=com.squareup"><?php echo lang('sales_square_android');?></a><br /><br />
		<a class="btn btn-block btn-primary" href="<?php echo site_url('sales'); ?>"><?php echo lang('common_back_to_sales');?></a>
		 
	</div>
</div>
<?php $this->load->view("partial/footer"); ?>