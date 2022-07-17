<!DOCTYPE html>
<head>
	<style>
		*{font-family: Calibri, sans-serif;}
		.invoice-desc{font-size:10px;}
	</style>
</head>
<body>
<?php
if (isset($error_message))
{
    echo '<h1 style="text-align: center;">'.$error_message.'</h1>';
    exit;
}

$company = ($company = $this->Location->get_info_for_key('company', isset($override_location_id) ? $override_location_id : FALSE)) ? $company : $this->config->item('company');
$company_logo = ($company_logo = $this->Location->get_info_for_key('company_logo', isset($override_location_id) ? $override_location_id : FALSE)) ? $company_logo : $this->config->item('company_logo');

$item_custom_fields_to_display = array();
$supplier_custom_fields_to_display = array();
$receiving_custom_fields_to_display = array();

for($k=1; $k <= NUMBER_OF_PEOPLE_CUSTOM_FIELDS; $k++){
    $item_custom_field = $this->Item->get_custom_field($k,'show_on_receipt');
    $supplier_custom_field = $this->Supplier->get_custom_field($k,'show_on_receipt');
    $recv_custom_field = $this->Receiving->get_custom_field($k,'show_on_receipt');

    if($recv_custom_field) {
    	$receiving_custom_fields_to_display[] = $k;
    }
		
    if($item_custom_field){
    	$item_custom_fields_to_display[] = $k;
    }
		
    if($supplier_custom_field){
    	$supplier_custom_fields_to_display[] = $k;
    }
}
?>

<table width="100%" >
    <tr>
        <td colspan="2" valign="top" align="left" style="width:33.33%">


            <?php if($company_logo) {?>
                <p id="company_logo" class="invoice-logo">
                    <?php echo img(array('src' => secure_app_file_url($company_logo))); ?>
                </p>
            <?php } ?>
            <p id="company_name"  class="company-title"><b><?php echo H($company); ?></b></p>
            <p id="company_address" class="nl2br"><?php echo H($this->Location->get_info_for_key('address',isset($override_location_id) ? $override_location_id : FALSE)); ?></p>
            <p id="company_phone"><?php echo H($this->Location->get_info_for_key('phone',isset($override_location_id) ? $override_location_id : FALSE)); ?></p>
            <p id="sale_receipt"><?php echo H($is_po ? lang('receivings_purchase_order','',array(),TRUE) : $receipt_title); ?></p>
            <p id="sale_time"><?php echo H($transaction_time); ?></p>

    		</td>
        <td colspan="2" valign="top" align="center" style="width:33.33%">
    <!--  sales-->


            <?php if (!isset($transfer_to_location)) {?>
                <p id="sale_id"><span><?php echo $is_po ? lang('receivings_purchase_order','',array(),TRUE) : lang('receivings_id','',array(),TRUE).": "; ?></span><?php echo $is_po ? H($receiving_id_raw) : H($receiving_id); ?></p>
            <?php } else { ?>
                <p id="sale_id"><span><?php echo lang('receivings_transfer_id','',array(),TRUE).": "; ?></span><?php echo H($receiving_id_raw); ?></p>
            <?php } ?>
            <p id="employee"><span><?php echo lang('common_employee','',array(),TRUE).": "; ?></span><?php echo H($employee); ?></p>
        </td>

        <td colspan="2" valign="top" align="right" style="width:33.33%">
    <?php if(isset($supplier) || isset($transfer_to_location)) { ?>
                <?php if(isset($supplier)) { ?>
                    <p id="supplier"><?php echo lang('common_supplier','',array(),TRUE).": ".H($supplier); ?></p>
                    <?php if(!empty($supplier_address_1)){ ?><p><?php echo lang('common_address','',array(),TRUE); ?> : <?php echo H($supplier_address_1. ' '.$supplier_address_2); ?></p><?php } ?>
                    <?php if (!empty($supplier_city)) { echo '<p>'.H($supplier_city.' '.$supplier_state.', '.$supplier_zip).'</p>';} ?>
                    <?php if (!empty($supplier_country)) { echo '<p>'.H($supplier_country).'</p>';} ?>
                    <?php if(!empty($supplier_phone)){ ?><p><?php echo lang('common_phone_number','',array(),TRUE); ?> : <?php echo H($supplier_phone); ?></p><?php } ?>
                    <?php if(!empty($supplier_email)){ ?><p><?php echo lang('common_email','',array(),TRUE); ?> : <?php echo H($supplier_email); ?></p><?php } ?>

                    <?php
                    foreach($supplier_custom_fields_to_display as $custom_field_id)
                    {
                        ?>
                        <?php

                        $supplier_info = $this->Supplier->get_info($supplier_id);

                        if ($supplier_info->{"custom_field_${custom_field_id}_value"})
                        {
                            ?>
                            <div class="invoice-desc">
                                <?php

                                if ($this->Supplier->get_custom_field($custom_field_id,'type') == 'checkbox')
                                {
                                    $format_function = 'boolean_as_string';
                                }
                                elseif($this->Supplier->get_custom_field($custom_field_id,'type') == 'date')
                                {
                                    $format_function = 'date_as_display_date';
                                }
                                elseif($this->Supplier->get_custom_field($custom_field_id,'type') == 'email')
                                {
                                    $format_function = 'strsame';
                                }
                                elseif($this->Supplier->get_custom_field($custom_field_id,'type') == 'url')
                                {
                                    $format_function = 'strsame';
                                }
                                elseif($this->Supplier->get_custom_field($custom_field_id,'type') == 'phone')
                                {
                                    $format_function = 'strsame';
                                }
                                elseif($this->Supplier->get_custom_field($custom_field_id,'type') == 'image')
                                {
                                    $this->load->helper('url');
                                    $format_function = 'file_id_to_image_thumb_right';
                                }
                                elseif($this->Supplier->get_custom_field($custom_field_id,'type') == 'file')
                                {
                                    $this->load->helper('url');
                                    $format_function = 'file_id_to_download_link';
                                }
                                else
                                {
                                    $format_function = 'strsame';
                                }

                                echo '<p><span>'.lang('common_supplier','',array(),TRUE).' '.($this->Supplier->get_custom_field($custom_field_id,'hide_field_label') ? '' : $this->Supplier->get_custom_field($custom_field_id,'name').':').'</span> '.$format_function($supplier_info->{"custom_field_${custom_field_id}_value"}).'</p>';
                                ?>
                            </div>
                        <?php
                        }
                    }
                    ?>



                <?php } ?>
                <?php if(isset($transfer_to_location)) { ?>
                    <p id="transfer_from"><span><?php echo lang('receivings_transfer_from','',array(),TRUE).': ' ?></span><?php echo H($transfer_from_location); ?></p>
                    <p id="transfer_to"><span><?php echo lang('receivings_transfer_to','',array(),TRUE).': ' ?></span><?php echo H($transfer_to_location); ?></p>
                <?php } ?>
    <?php } ?>
        </td>
    </tr>
</table>

<table><tr><td>&nbsp;</td></tr></table>

<!-- invoice heading-->
<?php
	$x_col = 6;
	$xs_col = 4;
	if($discount_exists){
	    $x_col = 4;
	    $xs_col = 3;

	    if($this->config->item('wide_printer_receipt_format')){
	        $x_col = 4;
	        $xs_col = 2;
	    }
	}else{
	  if($this->config->item('wide_printer_receipt_format')){
	    $x_col = 6;
	    $xs_col = 2;
	  }
	}
?>

<?php 
	$col_span = 2;
	if (!$this->config->item('hide_all_prices_on_recv')) {
		$col_span += 2;
		
		if($discount_exists) {
			$col_span += 1;
		}
	}
?>

<table width="100%">
	<tr><td colspan="<?php echo $col_span; ?>"><b><?php echo lang('common_item_name','',array(),TRUE); ?></b></td></tr>
	<tr>
		<td>&nbsp;</td>
		<?php if (!$this->config->item('hide_all_prices_on_recv')) { ?>
		<td align="right"><?php echo lang('common_price','',array(),TRUE); ?></td>	
		<?php } ?>
		
		<td align="right"><?php echo lang('common_quantity','',array(),TRUE); ?></td>
		<?php if (!$this->config->item('hide_all_prices_on_recv')) { ?>
			<?php if($discount_exists) { ?>
			<td align="right"><?php echo lang('common_discount_percent','',array(),TRUE); ?></td>
			<?php } ?>
			<td align="right"><?php echo lang('common_total','',array(),TRUE); ?></td>
		<?php } ?>
	</tr>



<?php
$number_of_items_sold = 0;
$number_of_items_returned = 0;

foreach(array_reverse($cart_items, true) as $line=>$item) { ?>

    <?php

    if ($item->quantity > 0 && $item->name != lang('common_store_account_payment','',array(),TRUE) && $item->name != lang('common_discount','',array(),TRUE) && $item->name != lang('common_refund','',array(),TRUE) && $item->name != lang('common_fee','',array(),TRUE)){
        $number_of_items_sold = $number_of_items_sold + $item->quantity;
    }elseif ($item->quantity < 0 && $item->name != lang('common_store_account_payment','',array(),TRUE) && $item->name != lang('common_discount','',array(),TRUE) && $item->name != lang('common_refund','',array(),TRUE) && $item->name != lang('common_fee','',array(),TRUE)){
        $number_of_items_returned = $number_of_items_returned + abs($item->quantity);
    }

    $item_number_for_receipt = false;

    if ($this->config->item('show_item_id_on_recv_receipt')){
        switch($this->config->item('id_to_show_on_sale_interface')){
			case 'number':
			$item_number_for_receipt = property_exists($item,'item_number') ? H($item->item_number) : '';
			break;
		
			case 'product_id':
			$item_number_for_receipt = property_exists($item,'product_id') ? H($item->product_id) : ''; 
			break;
		
			case 'id':
			$item_number_for_receipt = property_exists($item,'item_id') ? H($item->item_id) : ''; 
			break;
		
			default:
			$item_number_for_receipt = property_exists($item,'item_number') ? H($item->item_number) : '';
			break;
        }
    }
    ?>
    <!-- invoice items-->

                    <tr><td colspan="<?php echo $col_span;?>"><?php echo H($item->name); ?><?php if ($item->size){ ?> (<?php echo H($item->size); ?>)<?php } ?></td></tr>

                    <?php if ($item_number_for_receipt){ ?>
                        <tr>
                        	<td colspan="<?php echo $col_span;?>">
                            <small><?php echo $item_number_for_receipt; ?></small>
                        </td>
                      </tr>
                    <?php } ?>

                        <tr>
	                        <td colspan="<?php echo $col_span;?>">
	                         <small><?php echo $item->variation_name ? H($item->variation_name) : ''; ?></small>
	                         
												<?php
												if (property_exists($item,'quantity_unit_quantity') && $item->quantity_unit_quantity !== NULL){?>
		                  	<div class="invoice-desc">
														<?php echo lang('common_quantity_unit_name'). ': '.$item->quantity_units[$item->quantity_unit_id].', '.lang('common_quantity_units').': ' .H(to_quantity($item->quantity_unit_quantity)); ?>
													</div>
												
												<?php } ?>
	                         
	                        </td>
                      </tr>
                    <?php if (!$this->config->item('hide_desc_on_receipt') && !$item->description=="" ) {?>
                        <tr>
                        	<td colspan="<?php echo $col_span;?>">
                        		<small><?php echo clean_html($item->description); ?></small>
                        	</td>
                      </tr>
                    <?php } ?>
                    <?php if (isset($item->serialnumber) && $item->serialnumber !="") { ?>
                        <tr>
                        	<td colspan="<?php echo $col_span;?>">
                        <small><?php echo H($item->serialnumber); ?></small>
                        </td>
                      </tr>
                    <?php } ?>

                    <?php foreach($item_custom_fields_to_display as $custom_field_id){ ?>
                        <?php if(get_class($item) == 'PHPPOSCartItemRecv' && $this->Item->get_custom_field($custom_field_id) !== false) {
                            $item_info = $this->Item->get_info($item->item_id);

                            if ($item_info->{"custom_field_${custom_field_id}_value"}){ ?>
                        <tr>
                        	<td colspan="<?php echo $col_span;?>"><small>
                                    <?php
                                    if ($this->Item->get_custom_field($custom_field_id,'type') == 'checkbox'){
                                        $format_function = 'boolean_as_string';
                                    }elseif($this->Item->get_custom_field($custom_field_id,'type') == 'date'){
                                        $format_function = 'date_as_display_date';
                                    }elseif($this->Item->get_custom_field($custom_field_id,'type') == 'email'){
                                        $format_function = 'strsame';
                                    }elseif($this->Item->get_custom_field($custom_field_id,'type') == 'url'){
                                        $format_function = 'strsame';
                                    }elseif($this->Item->get_custom_field($custom_field_id,'type') == 'phone'){
                                        $format_function = 'strsame';
                                    }elseif($this->Item->get_custom_field($custom_field_id,'type') == 'image'){
                                        $this->load->helper('url');
                                        $format_function = 'file_id_to_image_thumb_right';
                                    }elseif($this->Item->get_custom_field($custom_field_id,'type') == 'file'){
                                        $this->load->helper('url');
                                        $format_function = 'file_id_to_download_link';
                                    } else {
                                        $format_function = 'strsame';
                                    }
                                    echo ($this->Item->get_custom_field($custom_field_id,'hide_field_label') ? '' : $this->Item->get_custom_field($custom_field_id,'name').':').' '.$format_function($item_info->{"custom_field_${custom_field_id}_value"});
                                  ?></small>
                        </td>
                      </tr>
                            <?php
                            }
                        }
                    }?>

<tr>
	<td>&nbsp;</td>
            <?php if (!$this->config->item('hide_all_prices_on_recv')) { ?>
                <td align="right" style="border-collapse: collapse; border-bottom:1px dashed #000000;">
                    <?php echo ($mode == 'transfer' && !$see_cost_price) ? "" : to_currency($item->unit_price,10); ?>
                </td>
            <?php } ?>

                <td align="right"><?php echo to_quantity($item->quantity); ?></td>

            <?php if (!$this->config->item('hide_all_prices_on_recv')) { ?>
                <?php if($discount_exists) { ?>
                    
                        <td align="right"><?php echo to_quantity($item->discount); ?></td>
                    
                <?php } ?>
                
                    <td align="right">
                        <?php if ($this->config->item('indicate_taxable_on_receipt') && $item->taxable && !empty($taxes)) {
                            echo '<small>*'.lang('common_taxable','',array(),TRUE).'</small>';
                        }
                        ?>
                        <?php echo ($mode == 'transfer' && !$see_cost_price) ? "" : to_currency($item->unit_price*$item->quantity-$item->unit_price*$item->quantity*$item->discount/100,10); ?>
                    </td>
            <?php } ?>
          </tr>
          <tr><td colspan="4"><hr style="margin:0;padding:0;"></td></tr>
<?php } ?>



<?php
foreach($receiving_custom_fields_to_display as $custom_field_id)
{
    if($this->Receiving->get_custom_field($custom_field_id) !== false && $this->Receiving->get_custom_field($custom_field_id) !== false)
    {
        if ($cart->{"custom_field_${custom_field_id}_value"})
        {
            if ($this->Receiving->get_custom_field($custom_field_id,'type') == 'checkbox')
            {
                $format_function = 'boolean_as_string';
            }
            elseif($this->Receiving->get_custom_field($custom_field_id,'type') == 'date')
            {
                $format_function = 'date_as_display_date';
            }
            elseif($this->Receiving->get_custom_field($custom_field_id,'type') == 'email')
            {
                $format_function = 'strsame';
            }
            elseif($this->Receiving->get_custom_field($custom_field_id,'type') == 'url')
            {
                $format_function = 'strsame';
            }
            elseif($this->Receiving->get_custom_field($custom_field_id,'type') == 'phone')
            {
                $format_function = 'strsame';
            }
            elseif($this->Receiving->get_custom_field($custom_field_id,'type') == 'image')
            {
                $this->load->helper('url');
                $format_function = 'file_id_to_image_thumb_right';
            }
            elseif($this->Receiving->get_custom_field($custom_field_id,'type') == 'file')
            {
                $this->load->helper('url');
                $format_function = 'file_id_to_download_link';
            }
            else
            {
                $format_function = 'strsame';
            }
            ?>
					<tr>
						<td colspan="<?php echo $col_span; ?>">
						<?php
					    if (!$this->Receiving->get_custom_field($custom_field_id,'hide_field_label'))
					    {
					        echo $this->Receiving->get_custom_field($custom_field_id,'name');
					    }
					    else
					    {
					        echo $format_function($cart->{"custom_field_${custom_field_id}_value"});
					    }
					    if (!$this->Receiving->get_custom_field($custom_field_id,'hide_field_label'))
					    {
					        echo $format_function($cart->{"custom_field_${custom_field_id}_value"});
					    }
					    ?>
						</td>
					</tr>


        <?php
        }
    }
}
?>


<tr>
	<td colspan="<?php echo $col_span; ?>" align="center">
  <small><?php echo H($comment); ?></small>
	</td>
</tr>
<tr>
	<td colspan="<?php echo $col_span; ?>" align="center">
  &nbsp;
	</td>
</tr>

</table>


<table width="100%">
    <?php
    if (!$this->config->item('hide_all_prices_on_recv')) {
        ?>
<!-- tax start -->
        <?php if (!empty($taxes)) {?>
        	<tr>
	          <td align="right" colspan="2">
	              <?php echo lang('common_sub_total','',array(),TRUE); ?>
	          </td>
	          <td align="right">
	              <?php echo ($mode == 'transfer' && !$see_cost_price) ? "" : to_currency($subtotal); ?>
	          </td>
          </tr>
            <?php if ($this->config->item('group_all_taxes_on_receipt')) { ?>
                <?php
                $total_tax = 0;
                foreach($taxes as $name => $value)
                {
                    $total_tax+=$value;
                }
                ?>
		        	<tr>
			          <td align="right" colspan="2">
		             	<?php echo lang('common_tax','',array(),TRUE); ?>
			          </td>
			          <td align="right">
		               <?php echo ($mode == 'transfer' && !$see_cost_price) ? "" : to_currency($total_tax); ?>
			          </td>
		          </tr>
            <?php }else {?>
                <?php foreach($taxes as $name => $value) { ?>
				        	<tr>
					          <td align="right" colspan="2">
		                	<?php echo H($name); ?>
					          </td>
					          <td align="right">
		                	<?php echo ($mode == 'transfer' && !$see_cost_price) ? "" : to_currency($value); ?>
					          </td>
				          </tr>
                <?php } ?>
            <?php } ?>
        <?php } ?>

        <!-- tax end -->
        <!-- total start -->
      	<tr>
          <td align="right" colspan="2">
      			<?php echo lang('common_total','',array(),TRUE); ?>
          </td>
          <td align="right">
      			<?php echo ($mode == 'transfer' && !$see_cost_price) ? "" : to_currency($total); ?>
          </td>
        </tr>
      
    <?php 
    	} //End hide all prices on recv
    ?>

    <?php if ($number_of_items_sold) { ?>
  	<tr>
      <td align="right" colspan="2">
            <?php echo lang('common_items_purchased','',array(),TRUE); ?>
      </td>
      <td align="right">
            <?php echo to_quantity($number_of_items_sold); ?>
      </td>
    </tr>
    <?php } ?>

    <?php if ($number_of_items_returned) { ?>
  	<tr>
      <td align="right" colspan="2">
       <?php echo lang('common_items_returned','',array(),TRUE); ?>
      </td>
      <td align="right">
            <?php echo to_quantity($number_of_items_returned); ?>
      </td>
    </tr>
    <?php } ?>
	

    <?php
    if (!$this->config->item('hide_all_prices_on_recv')) {
        ?>

        <?php
        foreach($payments as $payment_id=>$payment)
        {
            ?>
            <tr>
                <td align="right">
                    <?php echo (isset($show_payment_times) && $show_payment_times) ?  date(get_date_format().' '.get_time_format(), strtotime($payment->payment_date)) : lang('common_payment','',array(),TRUE); ?>
                </td>
                
                <td align="right">
                    <?php $splitpayment=explode(':',$payment->payment_type); echo H($splitpayment[0]); ?>
                </td>

                <td align="right">
                    <?php echo ($mode == 'transfer' && !$see_cost_price) ? "" : to_currency($payment->payment_amount); ?>
                </td>
          </tr>
        <?php
        }
        ?>

        <?php if(isset($amount_change)) { ?>
            <tr>
                <td align="right">
                    <?php echo lang('common_amount_tendered','',array(),TRUE); ?>
                </td>
                <td align="right">
                    <?php echo ($mode == 'transfer' && !$see_cost_price) ? "" : to_currency($amount_tendered); ?>
                </td>
            </tr>
            
            <tr>
                <td align="right">
                    <?php echo lang('common_change_due','',array(),TRUE); ?>
                </td>
                <td align="right">
                    <?php echo H($amount_change); ?>
                </td>
            </tr>
        <?php } ?>
    <?php } //end hide all prices recv
    ?>

    <?php if (isset($supplier_balance_for_sale) && (double)$supplier_balance_for_sale && !$this->config->item('hide_store_account_balance_on_receipt')) {?>

        <tr>
            <td align="right" colspan="2">
                <?php echo lang('receivings_supplier_account_balance','',array(),TRUE); ?>
            </td>
            <td align="right">
                <?php echo ($mode == 'transfer' && !$see_cost_price) ? "" : to_currency($supplier_balance_for_sale); ?>
            </td>
        </tr>
    <?php
    }
    ?>
<!-- invoice footer -->
<tr>
	<td colspan="3" align="center">
  &nbsp;
	</td>
</tr>

	<?php if (!$this->config->item('hide_barcode_on_sales_and_recv_receipt')) {?>
	    <tr>
	      <td  id="barcode" colspan="3" align="center">
	          <?php echo "<img src='".site_url('barcode/index/png')."?barcode=$receiving_id&text=$receiving_id' />"; ?>
	      </td>
	    </tr>
	<?php } ?>

</table>
</body>
</html>