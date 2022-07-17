<?php $this->load->view("partial/header"); ?>

<div class="manage_buttons hidden-print">
	<div class="row">
		<div class="col-md-6">	
			<div class="buttons-list">
				<div class="pull-right-btn">
					<ul class="list-inline">
						<li>
							<button class="btn btn-primary btn-lg hidden-print" id="new_sale_button_1" onclick="window.location='<?php echo site_url('sales'); ?>'" > <?php echo '&laquo; '.lang('common_try_again'); ?> </button>	
						</li>
						
						<li>
							<button class="btn btn-primary btn-lg hidden-print" id="print_button" onclick="window.print();" > <?php echo lang('common_print'); ?> </button>		
						</li>
					</ul>
				</div>
			</div>				
		</div>
	</div>
</div>
<div class="row manage-table receipt_<?php echo $this->config->item('receipt_text_size') ? $this->config->item('receipt_text_size') : 'small';?>" id="receipt_wrapper">
	<div class="col-md-12" id="receipt_wrapper_inner">
		<div class="panel panel-piluku">
			<div class="panel-body panel-pad">
			    <div class="row">
			        <!-- from address-->
			        <div class="col-md-4 col-sm-4 col-xs-12">
			            <ul class="list-unstyled invoice-address">
			                <?php if($this->config->item('company_logo')) {?>
			                	<li class="invoice-logo">
									<?php echo img(array('src' => $this->Appconfig->get_logo_image())); ?>
			                	</li>
			                <?php } ?>
			                <li><?php echo $this->config->item('company'); ?></li>
			                <li><?php echo H($this->Location->get_info_for_key('address', isset($override_location_id) ? $override_location_id : FALSE)); ?></li>
			                <li><?php echo H($this->Location->get_info_for_key('phone', isset($override_location_id) ? $override_location_id : FALSE)); ?></li>
			                <?php if($this->config->item('website')) { ?>
								<li><?php echo H($this->config->item('website')); ?></li>
								<?php } ?>
							<li class="title">
								<span class="pull-left"> <?php echo H($receipt_title); ?></span>
								<span class="pull-right"><?php echo H($transaction_time) ?></span>
							</li>
			            </ul>
			        </div>
			        <!--  sales-->
			        <div class="col-md-4 col-sm-4 col-xs-12">
			            <ul class="list-unstyled invoice-detail">
							<li>
								 <?php echo H($receipt_title); ?>
								 <br>
								 <strong><?php echo H($transaction_time) ?></strong>
							</li>
			            	<li><span class="text-danger" style="color: #df6c6e;"><strong><?php echo lang('sales_declined'); ?></strong> <?php echo H($text_response); ?></span></li>
								
							<?php
							echo '<li id="total"><span>'.lang('common_total').'</span>: '.to_currency($total).'</li>';
							
							if ($register_name)
							{
							?>
								<li><span><?php echo lang('common_register_name').':'; ?></span><?php echo H($register_name); ?></li>		
							<?php
							}
							?>				
							
							<li><span><?php echo lang('common_employee').":"; ?></span><?php echo H($employee); ?></li>
							<?php 
							if($this->Location->get_info_for_key('enable_credit_card_processing',isset($override_location_id) ? $override_location_id : FALSE))
							{
								echo '<li id="merchant_id"><span>'.lang('common_merchant_id').'</span>: '.H($this->Location->get_merchant_id(isset($override_location_id) ? $override_location_id : FALSE)).'</li>';
							}
							
							?>
							
							<?php if ($masked_account) { ?>
							<li><?php echo H($masked_account); ?></li>
							<?php } ?>
							
							<?php if ($ref_no) { ?>
							<li><?php echo lang('sales_ref_no'). ': '.H($ref_no); ?></li>
							<?php } ?>
							
							<?php if ($auth_code) { ?>
							<li><?php echo lang('sales_auth_code'). ': '.H($auth_code); ?></li>
							<?php } ?>							

							<?php if ($tran_type) { ?>
							<li><?php echo lang('sales_transaction_type'). ': '.H($tran_type); ?></li>
							<?php } ?>
							
							<?php if ($application_label) { ?>
							<li><?php echo lang('sales_application_label').': '.H($application_label); ?></li>
							<?php } ?>
							
							<?php if ($aid) { ?>
							<li><?php echo 'AID: '.H($aid); ?></li>
							<?php } ?>
							
							<?php if ($tvr) { ?>
							<li><?php echo 'TVR: '.H($tvr); ?></li>
							<?php } ?>
							
							
							<?php if ($tsi) { ?>
							<li><?php echo 'TSI: '.H($tsi); ?></li>
							<?php } ?>
							
							
							<?php if ($arc) { ?>
							<li><?php echo 'ARC: '.H($arc); ?></li>
							<?php } ?>

							<?php if ($cvm) { ?>
							<li><?php echo 'CVM: '.H($cvm); ?></li>
							<?php } ?>
							
						</ul>
			        </div>
			        <!-- to address-->
			        <div class="col-md-4 col-sm-4 col-xs-12">
			            <?php if(isset($customer)) { ?>
				            <ul class="list-unstyled invoice-address invoiceto">
									<li class="invoice-to"><?php echo lang('sales_invoice_to');?>:</li>
									<li><?php echo lang('common_customer').": ".H($customer); ?></li>
									
									<?php if (!$this->config->item('remove_customer_contact_info_from_receipt')) { ?>
										<?php if(!empty($customer_address_1)){ ?><li><?php echo lang('common_address'); ?> : <?php echo H($customer_address_1. ' '.$customer_address_2); ?></li><?php } ?>
										<?php if (!empty($customer_city)) { echo '<li>'.H($customer_city.' '.$customer_state.', '.$customer_zip).'</li>';} ?>
										<?php if (!empty($customer_country)) { echo '<li>'.H($customer_country).'</li>';} ?>			
										<?php if(!empty($customer_phone)){ ?><li><?php echo lang('common_phone_number'); ?> : <?php echo H($customer_phone); ?></li><?php } ?>
										<?php if(!empty($customer_email)){ ?><li><?php echo lang('common_email'); ?> : <?php echo H($customer_email); ?></li><?php } ?>
									<?php } ?>
				            </ul>
						<?php } ?>
			        </div>
			    </div>
			        <?php
						foreach($payments as $payment_id=>$payment)
						{ 
					?>
						<div class="row">
				            <div class="col-md-offset-6 col-sm-offset-6 col-md-2 col-sm-2 col-xs-5">
				                <div class="invoice-footer-heading"><?php echo (isset($show_payment_times) && $show_payment_times) ?  date(get_date_format().' '.get_time_format(), strtotime($payment->payment_date)) : lang('common_payment'); ?></div>
				            </div>
				            <div class="col-md-2 col-sm-2 col-xs-4">
									<div class="invoice-footer-value"><?php $splitpayment=explode(':',$payment->payment_type); echo H($splitpayment[0]); ?></div>											
				            </div>
				            <div class="col-md-2 col-sm-2 col-xs-3">
								<div class="invoice-footer-value invoice-payment"><?php echo to_currency($payment->payment_amount); ?></div>
				            </div>
				        </div>
					<?php
						}
					?>
			</div>
			<!--container-->
		</div>		
	</div>
</div>
<?php $this->load->view("partial/footer"); ?>