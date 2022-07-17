<?php
if (isset($sale_id)) //End of sale
{ ?>


<div class="row manage-table receipt_<?php echo $this->config->item('receipt_text_size') ? $this->config->item('receipt_text_size') : 'small';?>" id="receipt_wrapper">
	<div class="col-md-6 col-md-offset-3" id="receipt_wrapper_inner">
		<div class="panel panel-piluku">
			<div class="panel-body panel-pad">
				<h3><?php echo lang('common_sale_id'); ?> : <?php echo $sale_id; ?></h3>
				<?php if (isset($customer))
					{
						echo '<h5>'.lang('common_customer').': '.$customer;
					
					if (!empty($customer_email) && isset($can_email) && $can_email) 
					{
						echo '<span class="pull-right">'.anchor('sales/email_receipt/'.$sale_id, lang('common_email_receipt'), array('id' => 'email_receipt','class' => 'btn btn-primary btn-lg hidden-print')).'</span>';
					}
					echo '</h5>';
				} ?>
				<br>
	        	<ul class="list-group">
	        		<li class="list-group-item"><?php echo lang('common_sub_total'); ?> <span class="pull-right">
								<?php if (isset($exchange_name) && $exchange_name) { 
									echo to_currency_as_exchange_register_cart($subtotal);
								?>
								<?php } else {  ?>
								<?php echo to_currency($subtotal); ?>				
								<?php
								}
								?>
 </span></li>
	        		<li class="list-group-item"><?php echo lang('sales_tax'); ?> <span class="pull-right">
	        			
								<?php if (isset($exchange_name) && $exchange_name) { 
									echo to_currency_as_exchange_register_cart($tax*$exchange_rate); ?>
								<?php } else {  ?>
								<?php echo to_currency($tax); ?>				
								<?php
								}
								?>
								
								
								
	        		</span></li>
	        		<li class="list-group-item"><?php echo lang('common_total'); ?>
						<?php echo form_hidden('sale_total',$total,array('id' => 'sale_total')); ?>
					  <span class="pull-right">
	        			
								<?php if (isset($exchange_name) && $exchange_name) { 
									echo to_currency_as_exchange_register_cart($total); ?>
								<?php } else {  ?>
								<?php echo to_currency($total); ?>				
								<?php
								}
								?>
								
	        		</span></li>
	        	</ul>
			            
<?php
	
}
elseif(isset($cart)) //Make sure we have data in cart
{ ?>
<!-- Sales register Clone -->
<?php
$font_size = $this->agent->is_mobile() && !$this->agent->is_tablet() ? '50%' : '70%';
?>
<div class="row register" style="font-size: <?php echo $font_size; ?>;">
	<div class="col-lg-8 col-md-7 col-sm-7 col-xs-12 no-padding-right no-padding-left">
		<!-- Register Items. @contains : Items table -->
		<div class="register-box register-items paper-cut">
			<div class="register-items-holder">
				<?php if ($mode != 'store_account_payment') { ?>					
					<table id="register" class="table table-hover">

					<thead>
						<tr class="register-items-header">
							<th class="item_name_heading" ><?php echo lang('sales_item_name'); ?></th>
							<th class="sales_price"><?php echo lang('common_price'); ?></th>
							<th class="sales_quantity"><?php echo lang('common_quantity'); ?></th>
							<th class="sales_discount"><?php echo lang('common_discount_percent'); ?></th>
							<th ><?php echo lang('common_total'); ?></th>
						</tr>
					</thead>
				
					<tbody class="register-item-content">
						<?php
						$cart_count = 0;
						if(count($cart)==0)	{ ?>
						<tr class="cart_content_area">
							<td colspan='8'>
								<div class='text-center text-warning' > <h3><?php echo lang('common_no_items_in_cart'); ?></h3></div>
							</td>
						</tr>
						<?php 
						}
						else
						{
							
						 foreach(array_reverse($cart, true) as $line=>$item) { 
						 	$cart_count = $cart_count + $item->quantity;

						 	$cur_item_location_info = isset($item->item_id) ? $this->Item_location->get_info($item->item_id) : $this->Item_kit_location->get_info($item->item_kit_id);
						 	?>
							<tr class="register-item-details">
								<td class="padding-left-20"> 
									<span class="item_name"><?php echo H($item->name); ?><?php echo $item->size ? ' ('.H($item->size).')': ''; ?></span>
								</td>
								<td class="text-center">
									<?php echo to_currency($item->unit_price);  ?>
								</td>
								<td class="text-center">
									<?php echo to_quantity($item->quantity); ?>
								</td>
								<td class="text-center">
									<?php echo to_quantity($item->discount); ?>%
								</td>
								<td class="text-center"><?php echo to_currency($item->unit_price*$item->quantity-$item->unit_price*$item->quantity*$item->discount/100); ?></td>
							</tr>
							<tr class="register-item-bottom">
								<td colspan="5"  class="padding-left-10">
									<dl class="register-item-extra-details dl-horizontal">
										
										<?php if ($item->variation_id) {?>
									  <dt><?php echo lang('common_variation') ?></dt>
									  <dd>
											<?php 
											$this->load->model('Item_variations');
											echo H($this->Item_variations->get_variation_name($item->variation_id));
											?>
											</dd>
										<?php
										} 
										?>
									  <dt><?php echo lang('common_description') ?></dt>
									  <dd>
									  <?php 
												if ($item->description!='')
												{
													echo clean_html($item->description);
												}
												else
												{
													echo lang('common_none');
												}
										?>

										<!-- Serial Number if exists -->
										</dd>
										<?php  if(isset($item->is_serialized) && $item->is_serialized==1  && $item->name!=lang('common_giftcard'))	{ ?>
										<dt  class=""><?php echo lang('common_serial_number'); ?> </dt>
									  <dd  class=""><?php echo character_limiter(H($item->serialnumber), 50); ?></dd>
										<?php } ?>
									</dl>
								</td>
							</tr>
						<?php } }  ?>
						</tbody>
					</table>
					
			</div>

			<!-- End of Sales or Return Mode -->
			<?php } else {  ?>

			<table id="register"  class="table table-hover ">

				<thead>
					<tr class="register-items-header">
						<th ><?php echo lang('sales_item_name'); ?></th>
						<th ><?php echo lang('common_payment_amount'); ?></th>
					</tr>
				</thead>
				<tbody id="cart_contents">
					<?php
					foreach(array_reverse($cart, true) as $line=>$item)	
					{
						$cur_item_location_info = isset($item->item_id) ? $this->Item_location->get_info($item->item_id) : $this->Item_kit_location->get_info($item->item_kit_id);
						?>
						 							
						<tr id="reg_item_top" >
							<td class="text text-success"><a tabindex = "-1" href="<?php echo isset($item->item_id) ? site_url('home/view_item_modal/'.$item->item_id)."?redirect=".$this->uri->uri_string() : site_url('home/view_item_kit_modal/'.$item->item_kit_id)."?redirect=".$this->uri->uri_string(); ?>" data-toggle="modal" data-target="#myModal" ><?php echo H($item->name); ?></a></td>
							<td>
								<?php
								echo form_open("sales/edit_item/$line", array('class' => 'line_item_form', 'autocomplete'=> 'off')); 	

									?>
									<a href="#" id="price_<?php echo $line; ?>" class="xeditable" data-validate-number="true" data-type="text" data-value="<?php echo to_currency_no_money($item->unit_price); ?>" data-pk="1" data-name="price" data-url="<?php echo site_url('sales/edit_item/'.$line); ?>" data-title="<?php echo H(lang('common_price')); ?>"><?php echo to_currency_no_money($item->unit_price); ?></a>
									<?php
									echo form_hidden('quantity',to_quantity($item->quantity));
									echo form_hidden('description','');
									echo form_hidden('serialnumber', '');
								?>
							
								</form>		
							</td>
						</tr>
						
						
				 
				<?php } /*Foreach*/?>
			</tbody>
		</table>

					</div>

			<?php }  ?>
			<!-- End of Store Account Payment Mode -->

		</div>
		<!-- /.Register Items -->
	</div>
	<!-- /.Col-lg-8 @end of left Column -->

	<!-- col-lg-4 @start of right Column -->
	<div class="col-lg-4 col-md-5 col-sm-5 col-xs-12 ">
		<!-- If customer is added to the sale -->
		<?php if(isset($customer)) { ?>	
		
		<div class="register-box register-right">
			<!-- Customer Badge when customer is added -->
			<div class="customer-badge">
				<div class="avatar">
					<img src="<?php echo $avatar; ?>" alt="">	
				</div>
				<div class="details">
						<?php if(isset($customer)) { ?>
							<a href="#" class="name">
						<?php } ?>
						<?php echo character_limiter(H($customer), 30); ?> 
					</a>

					<!-- Customer Email  -->
					<?php if(!empty($customer_email)) { ?>
					<span class="email">
						<?php echo character_limiter(H($customer_email), 25); ?>
					</span>
					<?php } ?>
			
				</div>
			</div><!-- /.customer-badge -->
		</div>
		<?php } ?>
		

	<div class="register-box register-summary paper-cut">

		
		<ul class="list-group">
		
		<li class="sub-total list-group-item">
			<span class="key"><?php echo lang('common_sub_total'); ?>:</span>
			<span class="value">
				
				<?php if (isset($exchange_name) && $exchange_name) { 
					echo to_currency_as_exchange_register_cart($subtotal); ?>
				<?php } else {  ?>
				<?php echo to_currency($subtotal); ?>				
				<?php
				}
				?>
			</span>
		</li>
		<li class="list-group-item">
			<span class="key">
				<?php echo lang('sales_tax'); ?>:</td>
			</span>
			<span class="value pull-right">
								<?php if (isset($exchange_name) && $exchange_name) { 
									echo to_currency_as_exchange_register_cart($tax*$exchange_rate); ?>
								<?php } else {  ?>
								<?php echo to_currency($tax); ?>				
								<?php
								}
								?>
			</span>
		</li>
		
		</ul>

		<div class="amount-block">
			<div class="total amount">
				<div class="side-heading">
					<?php echo lang('common_total'); ?>
				</div>
				<div class="amount total-amount" data-speed="1000" data-currency="<?php echo $this->config->item('currency_symbol'); ?>" data-decimals="<?php echo $this->config->item('number_of_decimals') !== NULL && $this->config->item('number_of_decimals') != '' ? (int)$this->config->item('number_of_decimals') : 2; ?>">
					<?php if (isset($exchange_name) && $exchange_name) { 
						echo to_currency_as_exchange_register_cart($total); ?>
					<?php } else {  ?>
					<?php echo to_currency($total); ?>				
					<?php
					}
					?>
				</div>
			</div>
			<div class="total amount-due">
				<div class="side-heading">
					<?php echo lang('common_amount_due'); ?>
				</div>
				<div class="amount">
					<?php if (isset($exchange_name) && $exchange_name) { 
						echo to_currency_as_exchange_register_cart($amount_due); ?>
					<?php } else {  ?>
					<?php echo to_currency($amount_due); ?>				
					<?php
					}
					?>
				</div>
			</div>
		</div>
		<!-- ./amount block -->

<?php if(count($cart) > 0){ ?> 
		<!-- Payment Applied -->
		<?php if(count($payments) > 0) { ?>
			<ul class="list-group payments">
				<?php foreach($payments as $payment_id=>$payment) { ?>
					<li class="list-group-item">
						<span class="key">
							<?php echo $payment->payment_type; ?> 
						</span>
						<span class="value">
							
							<?php if (isset($exchange_name) && $exchange_name) { 
								echo to_currency_as_exchange_register_cart($payment->payment_amount); ?>
							<?php } else {  ?>
							<?php echo to_currency($payment->payment_amount); ?>				
							<?php
							}
							?>
						</span>
					</li>
				<?php } ?>
			</ul>
		<?php } ?>
			
		<?php
	} 
	?>
				
	</div>
</div>
</div>
<!-- /.Sales register clone -->
<?php
}
else
{  ?>
	<br /><br />
	<div class="text-center">
		<?php echo '<h1>'.lang('sales_thank_you_for_shopping_at'). ' '.$this->config->item('company').'!</h1>'; ?>	
	</div>
	<?php

	
}
?>
</div>