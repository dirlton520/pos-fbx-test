<?php $this->load->view("partial/header");
$barcode_width = $this->input->get('barcode_width') ? $this->input->get('barcode_width') : ($this->config->item('barcode_width') ? $this->config->item('barcode_width') : 1.9);
$barcode_height = $this->input->get('barcode_height') ? $this->input->get('barcode_height') : ($this->config->item('barcode_height') ? $this->config->item('barcode_height') : .79);
$scale = $this->input->get('scale') ? $this->input->get('scale') : ($this->config->item('scale') ? $this->config->item('scale') : 1);
$thickness = $this->input->get('thickness') ? $this->input->get('thickness') : ($this->config->item('thickness') ? $this->config->item('thickness') : 30);
$font_size = $this->input->get('font_size') ? $this->input->get('font_size') : ($this->config->item('font_size') ? $this->config->item('font_size') : 13);
$overall_font_size = $this->input->get('overall_font_size') ? $this->input->get('overall_font_size') : ($this->config->item('overall_font_size') ? $this->config->item('overall_font_size') : 10);
?>
<style>
	
@page {
	margin: 0 !important;
	padding: 0 !important;
}
	
@media print
{
	.wrapper {
  	 overflow: visible;
	 font-family: serif !important;
	}
}

.barcode-label
{
	-webkit-box-sizing: content-box;
	-moz-box-sizing: content-box;
	box-sizing: content-box;
	width: <?php echo $barcode_width; ?>in;
	height:<?php echo $barcode_height; ?>in;
	letter-spacing: normal;
	word-wrap: break-word;
	overflow: hidden;
	margin:0 auto;
	text-align:center;
	padding: 10px;
	font-size: <?php echo $overall_font_size;?>pt;
	line-height: .9em;
	 font-family: serif !important;
}

.item-price-barcode
{
	font-size: 115%;
}

</style>
<div class="hidden-print" style="text-align: center;margin-top: 20px;">
	<?php
	$labels_saved = $this->Appconfig->get_barcoded_labels()->result_array();
	?>
	<select id="saved_barcoded_labels">
		<option value="">--<?php echo H(lang('common_load_saved_value'));?>--</option>
		<?php
		foreach($labels_saved as $label_saved)
		{
			$label_settings = unserialize($label_saved['value']);
			echo "<option value='".H(json_encode($label_settings))."'>".$label_settings['saved_name']."</option>";
		}
		?>
	</select>
	<form method="get" action="<?php echo site_url('home/save_barcode_settings'); ?>" id="barcode_form">
		<div class="row">
			<div class="col-md-12">
		<div class="panel-body">
		
		<div class="form-group">
			<?php echo form_label(lang('items_overall_barcode_width').':', 'barcode_width',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
			<div class="col-sm-9 col-md-9 col-lg-10">
				<?php echo form_input(array(
					'name'=>'barcode_width',
					'id'=>'barcode_width',
					'class'=>'form-control form-inps',
					'value'=>$barcode_width)
				);?>
			</div>
		</div>
		
		<div class="form-group">
			<?php echo form_label(lang('items_overall_barcode_height').':', 'barcode_height',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
			<div class="col-sm-9 col-md-9 col-lg-10">
				<?php echo form_input(array(
					'name'=>'barcode_height',
					'id'=>'barcode_height',
					'class'=>'form-control form-inps',
					'value'=>$barcode_height)
				);?>
			</div>
		</div>

		<div class="form-group">
			<?php echo form_label(lang('items_overall_font_size').':', 'overall_font_size',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
			<div class="col-sm-9 col-md-9 col-lg-10">
				<?php echo form_input(array(
					'name'=>'overall_font_size',
					'id'=>'overall_font_size',
					'class'=>'form-control form-inps',
					'value'=>$overall_font_size)
				);?>
			</div>
		</div>

		<div class="form-group">
			<?php echo form_label(lang('items_barcode_image_width').':', 'scale',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
			<div class="col-sm-9 col-md-9 col-lg-10">
				<?php echo form_input(array(
					'name'=>'scale',
					'id'=>'scale',
					'class'=>'form-control form-inps',
					'value'=>$scale)
				);?>
			</div>
		</div>
		
		
		<div class="form-group">
			<?php echo form_label(lang('items_barcode_image_height').':', 'thickness',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
			<div class="col-sm-9 col-md-9 col-lg-10">
				<?php echo form_input(array(
					'name'=>'thickness',
					'id'=>'thickness',
					'class'=>'form-control form-inps',
					'value'=>$thickness)
				);?>
			</div>
		</div>
		
		<div class="form-group">
			<?php echo form_label(lang('items_barcode_image_font_size').':', 'font_size',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
			<div class="col-sm-9 col-md-9 col-lg-10">
				<?php echo form_input(array(
					'name'=>'font_size',
					'id'=>'font_size',
					'class'=>'form-control form-inps',
					'value'=>$font_size)
				);?>
			</div>
		</div>
		
		<div class="form-group">
			<?php echo form_label(lang('items_zerofill_barcode').':', 'zerofill_barcode',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
			<div class="col-sm-9 col-md-9 col-lg-10">
				<?php echo form_input(array(
					'name'=>'zerofill_barcode',
					'id'=>'zerofill_barcode',
					'class'=>'form-control form-inps',
					'value'=>$this->config->item('zerofill_barcode') ? $this->config->item('zerofill_barcode') : 10)
				);?>
			</div>
		</div>
		<div class="form-group">
			<?php echo form_label(lang('common_save_above_values_name').':', 'saved_name',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
			<div class="col-sm-9 col-md-9 col-lg-10">
				<?php echo form_input(array(
					'name'=>'saved_name',
					'id'=>'saved_name',
					'class'=>'form-control form-inps',
					'value'=>'')
				);?>
			</div>
		</div>
		
		
	</div>
		<input type="submit" class="btn btn-lg btn-primary">
	</div>
</div>
	</form>
	
	<?php
	if(isset($excel_url) && $excel_url)
	{
	?>
	<form action="<?php echo $excel_url; ?>" method="POST">
	<br />	
	<?php
	
	
		
	if ($this->input->post('item_id'))
	{
		echo form_hidden('item_id',$this->input->post('item_id'));
	}

	if ($this->input->post('items_number_of_barcodes'))
	{
		echo form_hidden('items_number_of_barcodes',$this->input->post('items_number_of_barcodes'));	
	}
	
	if ($this->input->post('item_variations_number_of_barcodes'))
	{
		foreach($this->input->post('item_variations_number_of_barcodes') as $var_id => $qty)
		{
			echo form_hidden('item_variations_number_of_barcodes['.$var_id.']',$qty);				
		}
	}
	?>
	<input type="submit" class="btn btn-success btn-lg" value="<?php echo lang('common_excel_export'); ?>">
	<?php	
	}
	?>
</form>
	
	<br /><br />
	<a class="btn btn-danger text-white hidden-print" id="reset_labels" href="<?php echo site_url('home/reset_barcode_labels');?>"><?php echo lang('items_reset_labels');?></a><br /><br /><br />

	<button class="btn btn-primary text-white hidden-print" id="print_button" onclick="window.print();"><?php echo lang('common_print'); ?></button>	
</div>	
<?php 
$company = ($company = $this->Location->get_info_for_key('company')) ? $company : $this->config->item('company');

for($k=0;$k<count($items);$k++)
{
	$customer_name = "";
	if( isset($customers) && array_key_exists($k, $customers)){
		$customer_name = $customers[$k]['customer_name'];
	}

	$item = $items[$k];
	$expire_key = (isset($from_recv) ? $from_recv : 0).'|'.ltrim($item['id'],0);
	$barcode = $item['id'];
	$text = $item['name'];
	
	if(!$this->config->item('hide_expire_date_on_barcodes') && isset($items_expire[$expire_key]) && $items_expire[$expire_key] && !$this->config->item('hide_name_on_barcodes'))
	{
		$text.= " (".lang('common_expire_date').' '.$items_expire[$expire_key].')';		
	}
	elseif (isset($from_recv) && !$this->config->item('hide_name_on_barcodes'))
	{
		if (!$this->config->item('disable_recv_number_on_barcode'))
		{
			$text.= " (RECV $from_recv)";
		}
	}
	
	if($customer_name != ""){
		echo '<p style="font-size: 10pt;
		line-height: .9em;
		font-family: Arial, Helvetica, sans-serif !important;
		color: #000000 !important;
		width: 100%; 
		text-align: center;
		margin: 20px 0 0 0; ">'.$customer_name.'</p>';
	}

	$page_break_after = ($k == count($items) -1) ? 'auto' : 'always';
	echo "<div class='barcode-label' style='page-break-after: $page_break_after'>".($this->config->item('show_barcode_company_name') ? $company."<br />" : '').(!$this->config->item('hide_barcode_on_barcode_labels') ? "<img style='vertical-align:baseline;'src='".site_url('barcode/index/svg').'?barcode='.rawurlencode($barcode).'&text='.rawurlencode($barcode)."&scale=$scale&thickness=$thickness&font_size=$font_size' /><br />" : $barcode.'<br />').$text."</div>";
}
?>
<script>
	<?php if (isset($_POST) && count($_POST)) { ?>
		var post_data = <?php echo json_encode($_POST); ?>;
		var post_data_clean = [];
		
		for (var name in post_data) 
		{
			var value = post_data[name];
			post_data_clean.push({name: name,value: value});
		}
	<?php } ?>
	
	if (typeof post_data !== 'undefined') 
	{
		$("#barcode_form").submit(function(e){
			e.preventDefault();
			$(this).ajaxSubmit(function()
			{
				post_submit(<?php echo json_encode(current_url()); ?>,"POST",post_data_clean);
			});
		});
		
		$("#excel_form").submit(function(e){
			e.preventDefault();
			$(this).ajaxSubmit(function()
			{
				post_submit(<?php echo json_encode(current_url()); ?>,"POST",post_data_clean);
			});
		});
		
		
		$("#reset_labels").click(function(e)
		{
			e.preventDefault();
			$.get($(this).attr('href'), function()
			{
				post_submit(<?php echo json_encode(current_url()); ?>,"POST",post_data_clean);
			});
		});
	}
	else
	{
		$("#barcode_form").submit(function(e){
			e.preventDefault();
			$(this).ajaxSubmit(function()
			{
				window.location.reload();
			});
		});
	}
	
	$("#saved_barcoded_labels").change(function()
	{
		if ($(this).val())
		{
			var settings = JSON.parse($(this).val());
		
			for(var key in settings)
			{
				$("#"+key).val(settings[key]);
			}
		}
	});
	</script>
<?php $this->load->view("partial/footer"); ?>