<?php $this->load->view("partial/offline_header"); ?>

<div id="print_receipt_holder" style="display: none;">
	
</div>

<div id="sales_page_holder">
	<div class="alert alert-danger  hidden-print"><?php echo lang('common_offline');?></div>

<div class="row register  hidden-print">	
	
	<div class="col-lg-8 col-md-7 col-sm-12 col-xs-12 no-padding-right no-padding-left">
				<div class="register-box register-items-form">
		<a tabindex="-1" href="#" class="dismissfullscreen hidden"><i class="ion-close-circled"></i></a>
			<div id="itemForm" class="item-form">
				<!-- Item adding form -->
				
				<form action="#" id="add_item_form" class="form-inline" autocomplete="off" method="post" accept-charset="utf-8">
					
					
					<div class="input-group contacts register-input-group">
						
						<input type="text" id="item" name="item"  class="add-item-input pull-left keyboardTop" placeholder=<?php echo json_encode(lang('common_start_typing_item_name')); ?> data-title=<?php echo json_encode(lang('common_item_name')); ?>>
		
					</div>
					
				</form>
			</div>
		</div>
				<!-- Register Items. @contains : Items table -->
		<div class="register-box register-items paper-cut">
			<div class="register-items-holder">
									
					<table id="register" class="table table-hover">

					<thead>
						<tr class="register-items-header">
							<th></th>
							<th class="item_name_heading"><?php echo lang('common_item_name');?></th>
							<th class="sales_price"><?php echo lang('common_unit_price');?></th>
							<th class="sales_quantity"><?php echo lang('common_quantity');?></th>
							<th class="sales_discount"><?php echo lang('common_discount');?></th>
							<th><?php echo lang('common_total');?></th>
						</tr>
					</thead>
						<tbody class="register-item-content" id="cart-content">
								
						</tbody>					
					
					</table>
					
			</div>


		</div>
		<!-- /.Register Items -->
			</div>
	<!-- /.Col-lg-8 @end of left Column -->

	<!-- col-lg-4 @start of right Column -->
	<div class="col-lg-4 col-md-5 col-sm-12 col-xs-12 no-padding-right">
		<div class="register-box register-right">
			<div class="sale-buttons" id="edit-sale-buttons" style="display: none;">
				<a href="#" class="btn btn-cancel" id="cancel_sale_button">
					<i class="ion-close-circled"></i>
					<?php echo lang('common_cancel_edit');?>
				</a>
			</div>

		<!-- If customer is added to the sale -->
		
		<div class="customer-form" id="customer-form">
    
		<div id="selected_customer_form" class="hidden">
			<h3 id="customer_name"></h3>
			<a href="javascript:void(0)" id="remove_customer" class="btn btn-primary"><?php echo H(lang('common_detach')); ?></a>
		</div>
		
		<form action="#" id="select_customer_form" autocomplete="off" class="form-inline" method="post" accept-charset="utf-8">
			<div class="input-group contacts">
				<input type="text" id="customer" name="customer" class="add-customer-input keyboardLeft" data-title=<?php echo json_encode(lang('common_customer_name')); ?> placeholder=<?php echo json_encode(lang('sales_start_typing_customer_name')); ?>>
			</div>
		</form>


		</div> 
			</div>

	<div class="register-box register-summary paper-cut">
		
		<ul class="list-group">

		<li class="sub-total list-group-item">
			<span class="key"><?php echo lang('common_sub_total');?>:</span>
			<span class="value">
						
				<span id="sub_total"></span>
			
			</span>
		</li>
		
		<li class="taxes list-group-item">
			<span class="key"><?php echo lang('common_tax');?>:</span>
			<span class="value">
						
				<span id="taxes"></span>
			
			</span>
		</li>
		
		
				</ul>
				
		<div class="amount-block">
			<div class="total amount">
				<div class="side-heading">
					<?php echo lang('common_total');?>				
				</div>
				<div class="amount total-amount" data-speed="1000" data-currency="$" data-decimals="2">
					<span id="total"></span>
										
					</div>
			</div>
			<div class="total amount-due">
				<div class="side-heading">
					<?php echo lang('common_amount_due');?>				
			</div>
				<div class="amount">
					<span id="amount_due"></span>
					</div>
			</div>
		</div>
		<!-- ./amount block -->

 
		<!-- Payment Applied -->
					<ul class="list-group payments" id="payments">
						
					</ul>
				
						<!-- Add Payment -->
							<div class="add-payment">
								<div class="side-heading"><?php echo lang('common_add_payment')?></div>
									<?php foreach ($payment_options as $key => $value) {
										
										$active_payment =  ($default_payment_type == $value) ? "active" : "";
									?>
										<a tabindex="-1" href="#" class="btn btn-pay select-payment <?php echo $active_payment; ?>" data-payment="<?php echo H($value); ?>">
										<?php echo H($value); ?>
									</a>
				<?php } ?>
				
			
			<form action="#" id="add_payment_form" class="form-inline" autocomplete="off" method="post" accept-charset="utf-8">
				
				<div class="input-group add-payment-form">
					<?php echo form_dropdown('payment_type',$payment_options,$default_payment_type, 'id="payment_types" class="hidden"');?>
					<?php echo form_input(array('name'=>'amount_tendered','id'=>'amount_tendered','value'=>'','class'=>'add-input numKeyboard form-control', 'data-title' => lang('common_payment_amount')));	?>
					<span class="input-group-addon">
						<a href="#" class="" id="add_payment_button"><?php echo lang('common_add_payment'); ?></a>
					</span>
						
				</div>
			</form>
			
			</div>
			
			
			<div id="finish_sale" class="finish-sale" style="display: none;">
				<?php
					echo "<input type='button' class='btn btn-success btn-large btn-block' id='finish_sale_button' value='" . lang('sales_complete_sale') . "' />";
					?>
			</div>		
			
	</div>
	
	
	<!-- Saved Sales -->
	<div id="saved_sales">
		<ul class="list-group saved_sales" id="saved_sales_list" style="list-style: none;">
		
		</ul>
	</div>
	
	
	
</div>
</div>

<script id="sale-receipt-template" type="text/x-handlebars-template">
	<div class="row manage-table receipt_small" id="receipt_wrapper">
		<div class="col-md-12 text-center hidden-print">
			<div class="row">
				<button class="btn btn-primary btn-lg" id="print_button" onclick="window.print()" > <?php echo lang('common_print','',array(),TRUE); ?> </button>		
			</div>
				<br />
				<br />
			<div class="row">
				<button class="btn btn-primary btn-lg" id="print_button" onclick="display_sale_register()" > <?php echo lang('sales_new_sale','',array(),TRUE); ?> </button>		
			</div>
				<br />
		</div>
		<div class="col-md-12" id="receipt_wrapper_inner">
			<div class="panel panel-piluku">
				<div class="panel-body panel-pad">
				    <div class="row">
				        <!-- from address-->
				        <div class="col-md-4 col-sm-4 col-xs-12">
				            <ul class="list-unstyled invoice-address" style="margin-bottom:2px;">
	            				<li class="company-title"><?php echo H($this->config->item('company')); ?></li>
				                <li class="nl2br"><?php echo H($this->Location->get_info_for_key('address')); ?></li>
				                <li><?php echo H($this->Location->get_info_for_key('phone')); ?></li>
								{{#if customer.customer_name}}
									<li><?php echo lang('common_customer_name')?>: {{ customer.customer_name}}</li>
								{{/if}}
				            </ul>
				        </div>
					</div>
				    <!-- invoice heading-->
			
		    <div class="invoice-table">
		        <div class="row">
		            <div class="col-md-12 col-sm-12 col-xs-12">
		                <div class="invoice-head item-name"><?php echo lang('common_item_name','',array(),TRUE); ?></div>
		            </div>
		            <div class="col-md-3 col-sm-3 col-xs-3 gift_receipt_element">
		                <div class="invoice-head text-right item-price"><?php echo lang('common_price','',array(),TRUE);?></div>
		            </div>
		            <div class="col-md-3 col-sm-3 col-xs-3">
		                <div class="invoice-head text-right item-qty"><?php echo lang('common_quantity','',array(),TRUE); ?></div>
		            </div>

		            <div class="col-md-3 col-sm-3 col-xs-3 gift_receipt_element">
		                <div class="invoice-head text-right item-discount"><?php echo lang('common_discount_percent','',array(),TRUE); ?></div>
		            </div>
           
		            <div class="col-md-3 col-sm-3 col-xs-3">
		                <div class="invoice-head pull-right item-total gift_receipt_element"><?php echo lang('common_total','',array(),TRUE).($this->config->item('show_tax_per_item_on_receipt') ? '/'.lang('common_tax','',array(),TRUE) : ''); ?></div>
		            </div>
		
		        </div>
				    </div>
			
				{{#each items}}
		
		   	 		<div class="invoice-table-content">
				        <div class="row receipt-row-item-holder">
				            
							
							<div class="col-md-12 col-sm-12 col-xs-12">
				                <div class="invoice-content invoice-con">
				                    <div class="invoice-content-heading">
										{{this.name}}
				                    </div>
										
									    {{#each this.modifiers}}
										<div class="invoice-desc">
											   {{this.modifier_name}} > {{this.modifier_item_name}} {{ to_currency_no_money this.unit_price}}</li>
										   </div>
									     {{/each}}
										
									
									
			                    	<div class="invoice-desc">
										{{this.selected_variation_name}}
									</div>
			                 		
									<div class="invoice-desc">
	 									{{this.description}}
					                 </div>
								</div>
								
				            </div>
							
				            <div class="col-md-3 col-sm-3 col-xs-3 gift_receipt_element">
				                <div class="invoice-content item-price text-right">							
									{{ this.price }}
								</div>
				            </div>
				            <div class="col-md-3 col-sm-3 col-xs-3 ">
				                <div class="invoice-content item-qty text-right">
									{{ this.quantity }}
								</div>
				            </div>
				      		<div class="col-md-3 col-sm-3 col-xs-3 gift_receipt_element">
				              <div class="invoice-content item-discount text-right">
				              	
								{{ this.discount_percent }}
				              </div>
							  
				            </div>
							
							<div class="col-md-3 col-sm-3 col-xs-3 gift_receipt_element">      
						         <div class="invoice-content item-total pull-right">
									{{ this.line_total }}
								</div>
							 </div>
				    	 </div>					
				    </div>
					{{/each}}
		
				    <div class="invoice-footer gift_receipt_element">
					
				
				        <div class="row">
				            <div class="col-md-offset-4 col-sm-offset-4 col-md-6 col-sm-6 col-xs-8">
				                <div class="invoice-footer-heading"><?php echo lang('common_sub_total','',array(),TRUE); ?></div>
				            </div>
						
				            <div class="col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-value">
					
									{{subtotal}}		
									
								</div>
				            </div>
		       
				        </div>
					
				
							<div class="row">
					            <div class="col-md-offset-4 col-sm-offset-4 col-md-6 col-sm-6 col-xs-8">
					                <div class="invoice-footer-heading"><?php echo lang('common_tax','',array(),TRUE); ?></div>
					            </div>
					            <div class="col-md-2 col-sm-2 col-xs-4">
					                <div class="invoice-footer-value">
						
										{{total_tax}}		
										
									</div>
					            </div>
					        </div>
				
				
			
				        <div class="row">
				            <div class="col-md-offset-4 col-sm-offset-4 col-md-6 col-sm-6 col-xs-8">
				                <div class="invoice-footer-heading"><?php echo lang('common_total','',array(),TRUE); ?></div>
				            </div>
				            <div class="col-md-2 col-sm-2 col-xs-4">
				                <div class="invoice-footer-value invoice-total"  style="font-size: 150%;font-weight: bold;;">
									{{ total}}
								</div>
				            </div>
				        </div> 
			
				        <div class="row">
					            <div class="col-md-offset-4 col-sm-offset-4 col-md-6 col-sm-6 col-xs-8">
					                <div class="invoice-footer-heading"><?php echo lang('common_items_sold','',array(),TRUE); ?></div>
					            </div>
					            <div class="col-md-2 col-sm-2 col-xs-4">
					                <div class="invoice-footer-value invoice-total">{{ total_items_sold }}</div>
					            </div>
				
					
				
				        </div> 
			
						{{#each payments}}
			
							<div class="row">
					            <div class="col-md-offset-4 col-sm-offset-4 col-xs-offset-4 col-md-4 col-sm-4 col-xs-4">
					                <div class="invoice-footer-heading"><?php echo lang('common_payment','',array(),TRUE); ?></div>
					            </div>
					            <div class="col-md-2 col-sm-2 col-xs-4">
										<div class="invoice-footer-value">{{ this.type }}</div>																				
					            </div>
					            <div class="col-md-2 col-sm-2 col-xs-4">
										<div class="invoice-footer-value">{{ to_currency_no_money this.amount }}</div>																				
					            </div>
					
							</div>
							
							{{/each}}
							
					
				    <!-- invoice footer-->						 
				    <div class="row">
				        <div class="col-md-12 col-sm-12 col-xs-12">
				            <div class="invoice-policy" id="invoice-policy-return">
				                <?php echo nl2br(H($this->config->item('return_policy'))); ?>
				            </div>
			
				    </div>
				</div>
				<!--container-->
			</div>		
		</div>
	</div>
	</script>
	
<script id="saved-sale-template" type="text/x-handlebars-template">
	<li>
		<?php echo lang('common_sale'); ?> <strong>{{index}}</strong> <a href="#" data-index={{index}} class='view_saved_sale'><?php echo lang('common_recp');?> </a> | <a href="#" data-index={{index}} class='edit_saved_sale'><?php echo lang('common_edit');?> </a> | <a href="#" data-index={{index}} class='delete_saved_sale'><?php echo lang('common_delete');?> </a><?php echo lang('common_total'); ?>: {{total}}, {{customer}}, <?php echo lang('common_items_sold');?>: {{items_sold}}
		<br /><br />
	</li>
</script>
<script id="cart-payment-template" type="text/x-handlebars-template">
		<li class="list-group-item">
			<span class="key">
				<a href="#" class="delete-payment remove" id="delete_payment_{{index}}" data-payment-index="{{index}}"><i class="icon ion-android-cancel"></i></a>
				{{type}}
			</span>
			<span class="value">{{amount}}</span>
		</li>
</script>

<script id="cart-item-template" type="text/x-handlebars-template">
	
		<tr class="register-item-details">
				<td class="text-center"> 
					<a href="#" class="delete-item" tabindex="-1" data-cart-index="{{index}}"><i class="icon ion-android-cancel"></i></a>
				</td>
			
			<td> 
				{{name}}
			</td>
			
		<td class="text-center">
			<a href="#" id="price_0" class="xeditable xeditable-price" data-validate-number="true" data-type="text" data-pk="1" data-name="price" data-index="{{index}}" data-title="Price">{{to_currency_no_money price}}</a>									 
		</td>
		<td class="text-center">
			<a href="#" id="quantity_0" class="xeditable" data-type="text"  data-validate-number="true"  data-pk="1" data-name="quantity" data-index="{{index}}" data-title="Qty.">{{to_quantity quantity}}</a>
		</td>
		
		<td class="text-center">
			<a href="#" id="discount_0" class="xeditable" data-type="text" data-validate-number="true"  data-pk="1" data-name="discount_percent" data-index="{{index}}" data-title="Disc %">{{discount_percent}}</a>									
		</td>
		<td class="text-center">
				{{to_currency_no_money line_total}}									 
		 </td>
	</tr>
	<tr class="register-item-bottom">
		<td>&nbsp;</td>
		<td colspan="5">
			<dl class="register-item-extra-details dl-horizontal">
				<dt><?php echo lang('common_description'); ?></dt>
				<dd>{{description}}</dd>	
			</dl>
			
			<dl class="register-item-extra-details dl-horizontal">
				<dt><?php echo lang('common_variation'); ?></dt>
				<dd>
					<select data-index="{{index}}" data-orig-price="{{orig_price}}" class="variation">
					 {{#select selected_variation}}
						 <option value=""><?php echo lang('common_none'); ?></option>
				     	
						{{#each variations}}
				     	  <option value="{{this.variation_id}}">{{this.name}}</option>
				     	{{/each}}
						
					 {{/select}}
					</select>
					
				</dd>	
			</dl>
			
				&nbsp;<strong><?php echo lang('common_modifiers'); ?></strong>
					
				<ul style="list-style: none;">				
			    {{#each modifiers}}
			       <li>
					   <input {{checked (lookup ../selected_item_modifiers this.modifier_item_id) }} data-index="{{../index}}" type="checkbox" id="modifier_{{../index}}_{{this.modifier_item_id}}" class="modifier" value="{{this.modifier_item_id}}" /> 
	   				<label for="modifier_{{../index}}_{{this.modifier_item_id}}"><span></span></label>
					   {{this.modifier_name}} > {{this.modifier_item_name}} {{ to_currency_no_money this.unit_price}}</li>
			     {{/each}}
				</ul>
			
		</td>
	</tr>
	
</script>

<script>	
	function getPromoPrice(promo_price, start_date, end_date)
	{
		if (parseFloat(promo_price) && start_date == null && end_date == null)
		{
			return parseFloat(promo_price);
		}
		else if(parseFloat(promo_price) && start_date != null && end_date != null)
		{
			var today = moment(new Date().toYMD());
			if (today.isBetween(start_date, end_date) || today.isSame(start_date) || today.isSame(end_date))
			{
				return parseFloat(promo_price);
			}
		}			
					
		return null;
	}
	(function() {
	    Date.prototype.toYMD = Date_toYMD;
	    function Date_toYMD() {
	        var year, month, day;
	        year = String(this.getFullYear());
	        month = String(this.getMonth() + 1);
	        if (month.length == 1) {
	            month = "0" + month;
	        }
	        day = String(this.getDate());
	        if (day.length == 1) {
	            day = "0" + day;
	        }
	        return year + "-" + month + "-" + day;
	    }
	})();
	
	Handlebars.registerHelper("to_currency_no_money", function(val) {
	  return to_currency_no_money(val);
	});
	
	Handlebars.registerHelper("to_quantity", function(val) {
	  return to_quantity(val);
	});
	
    Handlebars.registerHelper('select', function( value, options ){
           var $el = $('<select />').html( options.fn(this) );
           $el.find('[value="' + value + '"]').attr({'selected':'selected'});
           return $el.html();
       });
	   
   Handlebars.registerHelper("checked", function (condition) {
       return (condition) ? "checked" : "";
   });
	
	var cart_item_template = Handlebars.compile(document.getElementById("cart-item-template").innerHTML);
	var cart_payment_template = Handlebars.compile(document.getElementById("cart-payment-template").innerHTML);
	var saved_sale_template = Handlebars.compile(document.getElementById("saved-sale-template").innerHTML);
	var sale_receipt_template = Handlebars.compile(document.getElementById("sale-receipt-template").innerHTML);
	
	//data structures for cart
	
	var current_edit_index = null;
	var cart = JSON.parse(localStorage.getItem('cart')) || {};
	
	if(typeof cart.items == 'undefined')
	{
		cart['items'] = [];
	}
	if(typeof cart.payments == 'undefined')
	{
		cart['payments'] = [];
	}
	
	if(typeof cart.customer == 'undefined')
	{
		cart['customer'] = {};
	}
	
	
	try
	{
		var db_customers = new PouchDB('phppos_customers',{revs_limit: 1});
		var db_items = new PouchDB('phppos_items',{revs_limit: 1});
	}
	catch(exception_var)
	{
		
	}
	$(document).on('click','.delete_saved_sale', function(event)
	{
		event.preventDefault();
		
		var delete_index = $(this).data('index');
		bootbox.confirm(<?php echo json_encode(lang('sales_confirm_finish_sale')); ?>, function(result) {
			if (result) 
			{
	  		  var allSales = JSON.parse(localStorage.getItem("sales")) || [];
			  allSales.splice(delete_index, 1);
			  localStorage.setItem("sales", JSON.stringify(allSales));
	  		  renderUi();		
			  
			}
		});
	});
	
	
	
	$(document).on('click', '.view_saved_sale', function(event)
	{
		event.preventDefault();
		
		var allSales = JSON.parse(localStorage.getItem("sales")) || [];
		
		displayReceipt(allSales[$(this).data('index')]);
	});
	
	$(document).on('click', '.edit_saved_sale', function(event)
	{
		event.preventDefault();
		  var allSales = JSON.parse(localStorage.getItem("sales")) || [];
		  cart = allSales[$(this).data('index')];
		  current_edit_index = $(this).data('index');
		  renderUi();
	});
	
	  $(document).on("click", '#cancel_sale_button', function(event) 
	  {
		  event.preventDefault();
  		  cart = {};
    		  cart['items'] = [];
    		  cart['payments'] = [];
    		  cart['customer'] = {};
	  	  current_edit_index = null;
		
			  renderUi();
	  });
	  
	  $(document).on("click", '#finish_sale_button', function(event) 
	  {
		  
		bootbox.confirm(<?php echo json_encode(lang('sales_confirm_finish_sale')); ?>, function(result) 
		 {
			if (result) 
			{
	  		  //Reset cart
	  		  cart = {};
	    		  cart['items'] = [];
	    		  cart['payments'] = [];
	    		  cart['customer'] = {};
		  		
	  		  var sale = localStorage.getItem('cart');
			  displayReceipt(JSON.parse(sale));
	  		  //Save sales
	  		  var allSales = JSON.parse(localStorage.getItem("sales")) || [];
			  
			  if (current_edit_index !== null)
			  {
				  allSales[current_edit_index] = JSON.parse(sale);
			  }
			  else
			  {
			  	allSales.push(JSON.parse(sale)); 
			  }
			  localStorage.setItem("sales", JSON.stringify(allSales));
		  
			  current_edit_index = null;
	  		  renderUi();
			}
		});
		
	  });
	  
	  $(document).on("click", '.modifier', function(event) 
  	  {
    	var index = $(this).data('index');
		
		if (typeof cart['items'][index]['selected_item_modifiers'] == 'undefined')
		{
			cart['items'][index]['selected_item_modifiers'] = {};
		}
		cart['items'][index]['selected_item_modifiers'][$(this).val()] = $(this).prop('checked');
		
		renderUi();
	  });
	  
	  $(document).on("change", '.variation', function(event) 
	  {
		
		var price = false;
		var variation_name = '';
  		var index = $(this).data('index');
  		if (typeof index !=='undefined')
  		{
			for(var k=0;k<cart['items'][index]['variations'].length;k++)
			{
				if (cart['items'][index]['variations'][k]['variation_id'] == $(this).val())
				{			
					if (cart['items'][index]['variations'][k]['unit_price'])
					{
						price = cart['items'][index]['variations'][k]['unit_price'];

						var promo_price = cart['items'][index]['variations'][k]['promo_price'];
						var start_date = cart['items'][index]['variations'][k]['start_date']
						var end_date = cart['items'][index]['variations'][k]['end_date']
							
						var computed_promo_price = getPromoPrice(promo_price, start_date,end_date)
						
						if (computed_promo_price)
						{
							price = computed_promo_price;
						}
					}
					
					variation_name = cart['items'][index]['variations'][k]['name'];
					
					break;
				}
			}
			
			if (price)
			{
  				cart['items'][index]['price'] = price;
			}
			else
			{
  				cart['items'][index]['price'] = $(this).data('orig-price');				
			}
			
			cart['items'][index]['selected_variation'] = $(this).val();
			cart['items'][index]['selected_variation_name'] = variation_name;
			renderUi();
			
  		}
		
	  });
	
	$("#select_customer_form").submit(function(e)
  	{
		e.preventDefault();
  	
  	});
	$("#add_item_form").submit(function(e)
	{
		e.preventDefault();
		
		var search = $("#item").val().toLocaleLowerCase();
		db_items.find({
		  selector: {
				"$or":[
					{item_id: search},
					{product_id: search},
					{item_number: search}
				]
			},
		  fields: ['_id', 'name','description','unit_price','promo_price','start_date','end_date','category','quantity','item_id','variations','modifiers','taxes','tax_included']
		}, function (err, result) 
		{
		  if (err) { return console.log(err); }
			
			var results = result.docs;
			if (results.length)
			{
				var item = results[0];
				
				var item_id = item.item_id;
				var item_name = item.name;
				var item_description = item.description;
				var quantity = 1;
				var unit_price = to_currency_no_money(item.unit_price);
				var promo_price = to_currency_no_money(item.promo_price);
				var start_date = item.start_date;
				var end_date = item.end_date;
				
				var selling_price = parseFloat(unit_price);
			
			
				var computed_promo_price = getPromoPrice(promo_price, start_date,end_date)
				
				if (computed_promo_price)
				{
					selling_price = computed_promo_price;
				}
					
				selling_price = to_currency_no_money(selling_price);
				
				var variations = item.variations;
				var modifiers = item.modifiers;
				var taxes = item.taxes;
				var tax_included = item.tax_included;
				addItem({
					name: item_name,
					description: item_description,
					item_id: item_id,
					quantity: 1,
					price: selling_price,
					orig_price: selling_price,
					discount_percent:0,
					variations: variations,
					modifiers: modifiers,
					taxes: taxes,
					tax_included: tax_included
				});
				
				$("#item").val("");
				renderUi();
			}
		});		
	});
	
	//Refactor for performance based on https://stackoverflow.com/questions/58999498/pouch-db-fast-search
	
	$( "#customer").autocomplete({
 		source:async function(request,response)
		{
			var default_image = '<?php echo base_url(); ?>'+'assets/img/user.png';
				
			var search = escapeRegExp($("#customer").val() ? $("#customer").val() : ' ').toLocaleLowerCase();
			
			var descending = false;
			
		    const search_results = await db_customers.query('search', {
		      include_docs: true,
			  limit: 20,
		      reduce: false,
		      descending: descending,
		      startkey: descending ? search + '\uFFF0' : search,
		      endkey: descending ? search : search + '\uFFF0'
		    });
			
		  	var results = search_results.rows;
			var db_response = [];
			for(var k=0;k<results.length;k++)
			{
				var row = results[k].doc;
				var customer = {image: default_image,label: row.first_name+' '+row.last_name,value: row.person_id};
				db_response.push(customer);
			}
			response(db_response);
		},
		delay: 500,
 		autoFocus: false,
 		minLength: 0,
 		select: function( event, ui ) 
 		{
			var person_id = ui.item.value;
			var customer_name = ui.item.label;
			
			cart['customer']['person_id'] = person_id;
			cart['customer']['customer_name'] = customer_name;
			
			renderUi();
			$(this).val(''); return false;
			
 		},

 		}).data("ui-autocomplete")._renderItem = function (ul, item) {
		         return $("<li class='customer-badge suggestions'></li>")
		             .data("item.autocomplete", item)
			           .append('<a class="suggest-item"><div class="avatar">' +
									'<img src="' + item.image + '" alt="">' +
								'</div>' +
								'<div class="details">' +
									'<div class="name">' + 
										item.label +
									'</div>' + 
									'<span class="email">'+'</span>' +
								'</div></a>')
		             .appendTo(ul);
     };
	
	
	
 	//Refactor for performance based on https://stackoverflow.com/questions/58999498/pouch-db-fast-search
	
			
	$( "#item" ).autocomplete({
 		source: async function(request,response)
		{
			var default_image = '<?php echo base_url(); ?>'+'assets/img/item.png';
				
			var search = escapeRegExp($("#item").val() ? $("#item").val() : ' ').toLocaleLowerCase();
			
			var descending = false;
			
		    const search_results = await db_items.query('search', {
		      include_docs: true,
			  limit: 20,
		      reduce: false,
		      descending: descending,
		      startkey: descending ? search + '\uFFF0' : search,
		      endkey: descending ? search : search + '\uFFF0'
		    });
			
		  	var results = search_results.rows;
			var db_response = [];
			for(var k=0;k<results.length;k++)
			{
				var row = results[k].doc;
				var item = {tax_included: row.tax_included,taxes: row.taxes,variations: row.variations, modifiers: row.modifiers,description: row.description,unit_price: to_currency_no_money(row.unit_price), promo_price: row.promo_price, start_date: row.start_date, end_date: row.end_date, image: default_image,label: row.name+' - '+to_currency_no_money(row.unit_price),category:row.category,quantity: to_quantity(row.quantity),value: row.item_id};
				db_response.push(item);
			}
			response(db_response);
			
	
		},
		delay: 500,
 		autoFocus: false,
 		minLength: 0,
 		select: function( event, ui ) 
 		{
			var item_id = ui.item.value;
			var item_name = ui.item.label;
			var item_description = ui.item.description;
			var quantity = 1;
			var variations = ui.item.variations;
			var modifiers = ui.item.modifiers;
			var taxes = ui.item.taxes;
			var tax_included = ui.item.tax_included;
			var unit_price = ui.item.unit_price;
			var promo_price = ui.item.promo_price;
			var start_date = ui.item.start_date;
			var end_date = ui.item.end_date;
			
			var selling_price = parseFloat(unit_price);
			
			var computed_promo_price = getPromoPrice(promo_price, start_date,end_date)
			
			if (computed_promo_price)
			{
				selling_price = computed_promo_price;
			}
		
			selling_price = to_currency_no_money(selling_price);
			
			addItem({
				name: item_name,
				description: item_description,
				item_id: item_id,
				quantity: 1,
				price: selling_price,
				orig_price: selling_price,
				discount_percent:0,
				variations: variations,
				modifiers: modifiers,
				taxes: taxes,
				tax_included: tax_included
			});			
			renderUi();
			$(this).val(''); return false;
 		},
	}).data("ui-autocomplete")._renderItem = function (ul, item) {
     return $("<li class='item-suggestions'></li>")
         .data("item.autocomplete", item)
         .append('<a class="suggest-item"><div class="item-image">' +
					'<img src="' + item.image + '" alt="">' +
				'</div>' +
				'<div class="details">' +
					'<div class="name">' + 
						item.label +
					'</div>' +
					'<span class="attributes">' + '<?php echo lang("common_category"); ?>' + ' : <span class="value">' + (item.category ? item.category : <?php echo json_encode(lang('common_none')); ?>) + '</span></span>' +
					(typeof item.quantity !== 'undefined' && item.quantity!==null ? '<span class="attributes">' + '<?php echo lang("common_quantity"); ?>' + ' <span class="value">'+item.quantity + '</span></span>' : '' )+
					(item.attributes ? '<span class="attributes">' + '<?php echo lang("common_attributes"); ?>' + ' : <span class="value">' +  item.attributes + '</span></span>' : '' ) +
				
				'</div>')
         .appendTo(ul);
     };
	
 		function selectPayment(e)
 		{
 			e.preventDefault();
 			$('#payment_types').val($(this).data('payment'));
 			$('.select-payment').removeClass('active');
 			$(this).addClass('active');
 			$("#amount_tendered").focus();
 			$("#amount_tendered").attr('placeholder','');			
 		}
	
	 	function renderUi()
	 	{
			
			$("#saved_sales_list").empty();
			
			
			var saved_sales = JSON.parse(localStorage.getItem('sales')) || {};
			
			for(var k=saved_sales.length-1;k>=0;k--)
			{
				var saved_sale = saved_sales[k];
				var total = get_total(saved_sale);				
				var items_sold = get_total_items_sold(saved_sale);
				
				
				var customer = <?php echo json_encode(lang('common_none')) ?>;
				
				if (saved_sale['customer'] && saved_sale['customer']['person_id'])
				{
					customer = saved_sale['customer']['customer_name'];
				}
				
				var sale = {index: k,total: total, customer: customer,items_sold: items_sold};
				$("#saved_sales_list").append(saved_sale_template(sale));
			}
			
			
			localStorage.setItem("cart", JSON.stringify(cart));
			$("#cart-content").empty();
			
			for (var k=0;k<cart['items'].length;k++)
			{
				var cart_item = cart['items'][k];
				cart['items'][k]['line_total'] = cart_item['price']*cart_item['quantity']-cart_item['price']*cart_item['quantity']*cart_item['discount_percent']/100;
				cart['items'][k]['index'] = k;
				$("#cart-content").prepend(cart_item_template(cart['items'][k]));
			}
			
			if (cart['items'].length || cart['payments'].length || (cart['customer'] && cart['customer']['person_id']))
			{
				$("#edit-sale-buttons").show();
			}
			else
			{
				$("#edit-sale-buttons").hide();
			}
			
	    $('.xeditable').editable({
	    	success: function(response, newValue) 
				{
					//persist data
					var field = $(this).data('name');
					var index = $(this).data('index');
					if (typeof index !=='undefined')
					{
						cart['items'][index][field] = newValue;
					}
					renderUi();
				}
	    });
			
	    $('.xeditable').on('shown', function(e, editable) {

	    	editable.input.postrender = function() {
					//Set timeout needed when calling price_to_change.editable('show') (Not sure why)
					setTimeout(function() {
		         editable.input.$input.select();
				}, 200);
		    };
		})
		
			$("#payments").empty();
			
			for (var k=0;k<cart['payments'].length;k++)
			{
				var payment = cart['payments'][k];
				cart['payments'][k]['index'] = k;
				$("#payments").append(cart_payment_template(cart['payments'][k]));
			}
			
			if (cart.payments.length)
			{
				$("#finish_sale").show();
			}
			else
			{
				$("#finish_sale").hide();				
			}
			
			var subtotal = get_subtotal(cart);
			var taxes = get_taxes(cart);
			
			var total = get_total(cart);
			var amount_due = get_amount_due(cart);
			$("#sub_total").html(subtotal);
			$("#taxes").html(taxes);
			$("#total").html(total);
			$("#amount_due").html(amount_due);
			$("#amount_tendered").val(amount_due);
			if (cart['customer'] && cart['customer']['person_id'])
			{
				$("#customer_name").html(cart['customer']['customer_name']);
				$("#selected_customer_form").removeClass('hidden');
				$("#select_customer_form").addClass('hidden');	
			}
			else
			{
				$("#customer").val('');
				$("#selected_customer_form").addClass('hidden');
				$("#select_customer_form").removeClass('hidden');
			}
			
	 	}
		
		function addPayment(e)
		{
			e.preventDefault();
			var amount = $("#amount_tendered").val();
			var type = $("#payment_types").val();
			
			cart['payments'].push({amount:amount,type:type});
			renderUi();
		}
		
		$('.select-payment').on('click mousedown',selectPayment);
		
		$("#add_payment_form").submit(addPayment);
		$("#add_payment_button").click(addPayment);
		
	  $(document).on("click", 'a.delete-item', function(event) 
		{
			event.preventDefault();
	    cart.items.remove($(this).data('cart-index'));
			renderUi();
	  });
		
	  $(document).on("click", 'a.delete-payment', function(event) 
		{
			event.preventDefault();
	    cart.payments.remove($(this).data('payment-index'));
			renderUi();
	  });
		
	  $(document).on("click", '#remove_customer',function(event) 
		{
			cart.customer = {};
			renderUi();
		});
		
			
		renderUi();
	
	function get_price_without_tax_for_tax_incuded_item(cart_item)
	{
		
		var tax_info = cart_item.taxes;
		var item_price_including_tax = cart_item.price;
		
		if (tax_info.length == 2 && tax_info[1]['cumulative'] == '1')
		{
			var to_return = item_price_including_tax/(1+(tax_info[0]['percent'] /100) + (tax_info[1]['percent'] /100) + ((tax_info[0]['percent'] /100) * ((tax_info[1]['percent'] /100))));
		}
		else //0 or more taxes NOT cumulative
		{
			var total_tax_percent = 0;
	
			for(var k=0;k<tax_info.length;k++)
			{
				var tax = tax_info[k]
				total_tax_percent+=tax['percent'];
			}
	
			var to_return = item_price_including_tax/(1+(total_tax_percent /100));
		}
		
		return to_return;
		
	}
	
	function get_price_without_tax_for_tax_incuded_modifier_item(cart_item,modifier_item)
	{
		
		var tax_info = cart_item.taxes;
		var item_price_including_tax = modifier_item.unit_price;
		
		if (tax_info.length == 2 && tax_info[1]['cumulative'] == '1')
		{
			var to_return = item_price_including_tax/(1+(tax_info[0]['percent'] /100) + (tax_info[1]['percent'] /100) + ((tax_info[0]['percent'] /100) * ((tax_info[1]['percent'] /100))));
		}
		else //0 or more taxes NOT cumulative
		{
			var total_tax_percent = 0;
	
			for(var k=0;k<tax_info.length;k++)
			{
				var tax = tax_info[k]
				total_tax_percent+=tax['percent'];
			}
	
			var to_return = item_price_including_tax/(1+(total_tax_percent /100));
		}
		
		return to_return;
		
	}
	
		
	function get_subtotal(cart)
	{
		if(typeof cart.items != 'undefined')
		{
			var subtotal = 0;
			
			for(var k=0;k<cart.items.length;k++)
			{
				var cart_item = cart.items[k];
				
				if (cart_item.tax_included == '1')
				{
					price = get_price_without_tax_for_tax_incuded_item(cart_item);
				}
				else
				{
					price = cart_item['price'];
				}
				
				for (const modifier_id in cart_item.selected_item_modifiers) 
				{
					if (cart_item.selected_item_modifiers[modifier_id])
					{
						for(var j=0;j<cart_item.modifiers.length;j++)
						{
							if (cart_item.modifiers[j]['modifier_item_id'] == modifier_id)
							{
								if (cart_item.tax_included == '1')
								{
									var modifier_price = get_price_without_tax_for_tax_incuded_modifier_item(cart_item,cart_item.modifiers[j])
									
								}
								else
								{
									var modifier_price = parseFloat(to_currency_no_money(cart_item.modifiers[j]['unit_price']));									
								}
								
								price = parseFloat(price) +  modifier_price;
								break;
							}
						}
					}
				 
				}
				subtotal+=price*cart_item['quantity']-cart_item['price']*cart_item['quantity']*cart_item['discount_percent']/100;
			}
			
			return to_currency_no_money(subtotal);
		}
		return 0;
	}
	
	function get_taxes(cart)
	{
		
		if(typeof cart.items != 'undefined')
		{		
			var total_tax = 0;
				
			for(var k=0;k<cart.items.length;k++)
			{
				var cart_item = cart.items[k];
				
				if (cart_item.tax_included == '1')
				{
					price = get_price_without_tax_for_tax_incuded_item(cart_item);
				}
				else
				{
					price = cart_item['price'];
				}
				
				for (const modifier_id in cart_item.selected_item_modifiers) 
				{
					if (cart_item.selected_item_modifiers[modifier_id])
					{
						for(var j=0;j<cart_item.modifiers.length;j++)
						{
							if (cart_item.modifiers[j]['modifier_item_id'] == modifier_id)
							{
								if (cart_item.tax_included == '1')
								{
									var modifier_price = get_price_without_tax_for_tax_incuded_modifier_item(cart_item,cart_item.modifiers[j])
									
								}
								else
								{
									var modifier_price = parseFloat(to_currency_no_money(cart_item.modifiers[j]['unit_price']));									
								}
								price = parseFloat(price) +  modifier_price;
								break;
							}
						}
					}
				 
				}
				
				for(var j=0;j<cart_item.taxes.length;j++)
				{
					var tax = cart_item.taxes[j]
					var quantity = cart_item.quantity;
					var discount = cart_item.discount_percent;
										
					if (tax['cumulative'] != '0')
					{
						var prev_tax = ((price*quantity-price*quantity*discount/100))*((cart_item.taxes[j-1]['percent'])/100);
						var tax_amount=(( (price*quantity-price*quantity*discount/100)) + prev_tax)*((tax['percent'])/100);					
					}
					else
					{
						var tax_amount=((price*quantity-price*quantity*discount/100))*((tax['percent'])/100);
					}
					
					total_tax+=tax_amount;
					
				}
			}
			
			return to_currency_no_money(total_tax);
		}
		else
		{
			return 0;
		}
	}
	
	function get_total(cart)
	{
		return to_currency_no_money(parseFloat(get_subtotal(cart)) + parseFloat(get_taxes(cart)));
	}
	
	function get_payments_total(cart)
	{
		var total = 0;
		for(var k=0;k<cart['payments'].length;k++)
		{
			total+=parseFloat(cart['payments'][k]['amount']);
		}
		
		return to_currency_no_money(total);
	}
	
	function get_amount_due(cart)
	{
		return to_currency_no_money(parseFloat(get_total(cart)) - parseFloat(get_payments_total(cart)));
	}
	
	function get_total_items_sold(cart)
	{
		var total = 0;
		if(typeof cart.items != 'undefined')
		{
			var subtotal = 0;
			
			for(var k=0;k<cart.items.length;k++)
			{
				total+=parseFloat(cart.items[k]['quantity']);
			}
		}
				
		return to_currency_no_money(total)
	}

	function display_sale_register()
	{
		$("#print_receipt_holder").hide();
		$("#sales_page_holder").show();
		
	}
	
	function get_modifier_unit_total(cart_item)
	{
		var unit_total = 0;
		
		for(var k=0;k<cart_item.modifiers.length;k++)
		{
			var mod_item = cart_item.modifiers[k];
			unit_total+=parseFloat(mod_item['unit_price']);
		}
		
		return unit_total;
		
	}
	
	function get_modifiers_subtotal(cart_item)
	{
		var sub_total = 0;
		
		for(var k=0;k<cart_item.modifiers.length;k++)
		{
			var mod_item = cart_item.modifiers[k];
			sub_total+=parseFloat(mod_item['unit_price']) * cart_item['quantity'];
		}
		
		return sub_total;
	}
	
	function displayReceipt(sale)
	{
		$("#print_receipt_holder").empty();
		
		sale.total_items_sold = get_total_items_sold(sale);
		sale.subtotal = get_subtotal(sale);
		sale.total_tax = get_taxes(sale);
		sale.total = get_total(sale);
		
		for(var k=0;k<sale.items.length;k++)
		{
			sale.items[k].price = parseFloat(sale.items[k].price) + get_modifier_unit_total(sale.items[k]); 
			sale.items[k].line_total = parseFloat(sale.items[k].line_total) + get_modifiers_subtotal(sale.items[k]);
		}
		
		$("#print_receipt_holder").append(sale_receipt_template(sale));
		$("#print_receipt_holder").show();
		$("#sales_page_holder").hide();
		
	}  
	$("#item").focus();
	
	//Select all text in the input when input is clicked
	$("input:text, textarea").not(".description,#comment,#internal_notes").click(function() {
		$(this).select();
	});
	
	function addItem(item)
	{
		cart['items'].push(item);
	}
</script>

	
<?php $this->load->view("partial/offline_footer"); ?>
