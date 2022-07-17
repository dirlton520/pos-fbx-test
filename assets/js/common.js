function addURLParam(sourceURL, key, value)
{
	sourceURL += (sourceURL.indexOf("?") == -1 ? "?" : "&");
	sourceURL += encodeURIComponent(key) + "=" + encodeURIComponent(value);
	return sourceURL;
}

function removeURLParam(key, sourceURL) {
    var rtn = sourceURL.split("?")[0],
        param,
        params_arr = [],
        queryString = (sourceURL.indexOf("?") !== -1) ? sourceURL.split("?")[1] : "";
    if (queryString !== "") {
        params_arr = queryString.split("&");
        for (var i = params_arr.length - 1; i >= 0; i -= 1) {
            param = params_arr[i].split("=")[0];
            if (param === key) {
                params_arr.splice(i, 1);
            }
        }
        rtn = rtn + "?" + params_arr.join("&");
    }
    return rtn;
}

function to_currency_no_money(number, decimals)
{
	var decimals = parseInt(decimals) || 2;
	
	var decimals_system_decide = true;
	
	if(decimals <= 5 && MONEY_NUM_DECIMALS !== null && MONEY_NUM_DECIMALS !== '')
	{
		decimals = parseInt(MONEY_NUM_DECIMALS);
		decimals_system_decide = false;
	}
	
	return ret = parseFloat(number).toFixed(decimals);
}

function to_quantity(val)
{
	if (typeof val == 'undefined' || val==null)
	{
		return '';
	}
	
	if (typeof val == 'string')
	{
		//https://stackoverflow.com/questions/3612744/remove-insignificant-trailing-zeros-from-a-number
		return parseFloat(val);
	}
	
	return val;
}

function get_dimensions() 
{
	var dims = {width:0,height:0};
	
  if( typeof( window.innerWidth ) == 'number' ) {
    //Non-IE
    dims.width = window.innerWidth;
    dims.height = window.innerHeight;
  } else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
    //IE 6+ in 'standards compliant mode'
    dims.width = document.documentElement.clientWidth;
    dims.height = document.documentElement.clientHeight;
  } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
    //IE 4 compatible
    dims.width = document.body.clientWidth;
    dims.height = document.body.clientHeight;
  }
  
  return dims;
}

function show_feedback(type,message,title,optionsOverride)
{
	optionsOverride = optionsOverride || {};
	
	optionsOverride['toastClass'] = 'toast hidden-print'; 
	toastr[type](message,title,optionsOverride);
		
	if (ENABLE_SOUNDS)
	{
		if (type == 'success')
		{
			$.playSound(BASE_URL + 'assets/sounds/success');
		}
		else if (type == 'warning')
		{
			$.playSound(BASE_URL + 'assets/sounds/warning');
		}
		else if (type == 'error')
		{
			$.playSound(BASE_URL + 'assets/sounds/error');
		}
	}
}

function giftcard_swipe_field($field)
{
	$field.keyup(function()
	{
		var cur_val = $(this).val();
		
		//Remove starting % or ; (Track 1 and Track 2)
		if (cur_val.substring(0,1) == '%' || cur_val.substring(0,1) == ';')
		{
			cur_val = cur_val.substring(1);
		}
		
		//remove ending ?
		if(cur_val.substring(cur_val.length - 1) == '?')
		{
			cur_val = cur_val.substring(0,cur_val.length - 1);
		}
		
		$(this).val(cur_val);
	});
	
}

$(document).keydown(function(event) 
{
	//F1 or f3
	if (event.keyCode == 112 || event.keyCode == 114)
	{
		window.location = SITE_URL + "/sales";
	}
});

$(document).ready(function()
{
	$(document).on('click', 'a[data-target="#myModal"]', function(event)
	{
		//Needed so when we have many modal dialogs we always reload (it doesn't reload by default). Only reload urls that do NOT start with #
		if ($(this).attr('href').lastIndexOf('#', 0) !== 0)
		{
			$('#myModal').html('');
			$('#myModal').load($(this).attr('href'));
		}
	});
	
	$(document).on('click', 'a[data-target="#myModalDisableClose"]', function(event)
	{
		//Needed so when we have many modal dialogs we always reload (it doesn't reload by default). Only reload urls that do NOT start with #
		if ($(this).attr('href').lastIndexOf('#', 0) !== 0)
		{
			$('#myModalDisableClose').html('');
			$('#myModalDisableClose').load($(this).attr('href'));
		}
	});
	
	
	$('.show_more_taxes').click(function()
	{
		//disable cumulative
		$(this).parent().prev().find('.cumulative_checkbox').prop('disabled', true);
		$(this).parent().prev().find('.cumulative_checkbox').prop('checked', false);
		$(this).parent().next().show();
		$(this).remove();
	});
	
	//Prevent cumulative on load of taxes
	$(".more_taxes_container:visible").each(function(index,el)
	{
		$(this).prev().prev().find('.cumulative_checkbox').prop('disabled', true);
		$(this).prev().prev().find('.cumulative_checkbox').prop('checked', false);
		
	});
});

//Autocomplete on ipad/phone	
$(document).on('touchstart', "ul.ui-autocomplete.ui-menu li a", function(e)
{
    $(this).addClass('autocomplete-touch-start');
    $(this).removeClass('autocomplete-touch-end');			
});	

$(document).on('touchend', "ul.ui-autocomplete.ui-menu li a", function(e)
{
    $(this).addClass('autocomplete-touch-end');
    $(this).removeClass('autocomplete-touch-start');
});	

function salesRecvFullScreen() 
{
	$(".top-bar").hide();
	$(".breadcrumb").hide();
	$(".left-bar").hide();
	$("#footers").hide();
	$("#sales_page_holder").addClass('fullscreen-enabled');
	$(".content").css('margin', 0).css('padding', 0);
	$(".main-content").css('margin', 0).css('padding', 0);
	$('.dismissfullscreen').removeClass('hidden');
}

function salesRecvDismissFullscren() 
{
	$(".top-bar").show();
	$(".breadcrumb").show();
	$(".left-bar").show();
	$("#footers").show();
	$("#sales_page_holder").removeClass('fullscreen-enabled');
	$(".content").css('margin', '').css('padding', '');
	$(".main-content").css('margin', '').css('padding', '');
	$('.dismissfullscreen').addClass('hidden');
}

function date_time_picker_inline_linked($start_container, $end_container, format, onChanageCallback)
{
		var $start_hidden_field = $("<input type='hidden'>").attr('name', $start_container.attr('id')).attr('id',$start_container.attr('id') +'_field' ).attr('class', 'filter_value');
		
		if($start_container.data('date'))
		{
			$start_hidden_field.attr("disabled",false);
			$start_hidden_field.val($start_container.data('date'));
		} else {
			$start_hidden_field.attr("disabled",true);
		}
		
 	 	$start_container.datetimepicker({
			 defaultDate: $start_container.data('date') ? $start_container.data('date') : false,
			 inline: true,
			 useCurrent: false
     }).append($start_hidden_field);
		 
		var $end_hidden_field = $("<input type='hidden'>").attr('name', $end_container.attr('id')).attr('id',$end_container.attr('id') +'_field' ).attr('class', 'filter_value');
		
		if($end_container.data('date'))
		{
			$end_hidden_field.attr("disabled",false);
			$end_hidden_field.val($start_container.data('date'));
		} else {
			$end_hidden_field.attr("disabled",true);
		}
		
    $end_container.datetimepicker({
			 	defaultDate: $end_container.data('date') ? $end_container.data('date') : false,
			 	inline: true,
        useCurrent: false
     }).append($end_hidden_field);
		 
     $start_container.on('dp.change', function (e) {
			 
		 		if(e.date)
		 		{
	        $end_container.data('DateTimePicker').minDate(e.date);
					
		 			var formated_date = e.date.locale('en').format(format);
		 			$start_hidden_field.val(formated_date);
					$start_hidden_field.attr("disabled",false);
					$start_hidden_field.trigger('change');
					
		 		} else {
	        $end_container.data('DateTimePicker').minDate(false);
		 			$start_hidden_field.val('');
					$start_hidden_field.attr("disabled",true);
					$start_hidden_field.trigger('change');
		 		}
 			 onChanageCallback();
     });
		 
     $end_container.on('dp.change', function (e) {
		 		if(e.date)
		 		{
          $start_container.data('DateTimePicker').maxDate(e.date);
					
		 			var formated_date = e.date.locale('en').format(format);
		 			$end_hidden_field.val(formated_date).trigger('change');
		 		} else {
          $start_container.data('DateTimePicker').maxDate(false);
		 			$end_hidden_field.val('').trigger('change');
		 		}
 			 onChanageCallback();
     });
}

function date_time_picker_field($field, format)
{
	
  $field.on("dp.change", function(e) 
	{
		if(e.date)
		{
			var formated_date = e.date.locale('en').format(format);
			$(this).val(formated_date);
		} else {
			$(this).val('');
		}
		
   });
	
	if (IS_MOBILE)
	{
		$field.attr('readonly','readonly');
	}
	
  $field.datetimepicker({stepping: 5,format: format, locale: LOCALE, ignoreReadonly: IS_MOBILE ? true : false});	
}

function mercury_emv_pad_reset(post_host, listener_port, reset_data,callback,error_callback)
{
    callback = typeof callback !== 'undefined' ? callback : false;
    error_callback = typeof error_callback !== 'undefined' ? error_callback : false;
	delete $.ajaxSettings.headers["cache-control"]
 	$.ajax('http://'+post_host+':'+listener_port+'/method4',
 	{
		data: reset_data,
		dataType: 'text',
 		method: 'POST',
		cache: true,
		headers: { 'Invoke-Control': 'EMVX'},
 		success:function(listener_response) 
 		{
   			var data = listener_response.split("&");
   			var processed_data = [];

   			for(var i = 0; i < data.length; i++)
   			{
   			    var m = data[i].split("=");
   			    processed_data[m[0]] = m[1];
   			}

				$.post(SITE_URL+"/sales/set_sequence_no_emv", {sequence_no:processed_data.SequenceNo}, function()
				{
					if (callback)
					{
						callback();
					}
				});
								
 		},
   	error: function() 
		{
			if (error_callback)
			{
				error_callback();
			}
   	}
 	});
	
}

function emv_param_download(post_host, listener_port, init_data, success_message, error_message,callback)
{ 
   callback = typeof callback !== 'undefined' ? callback : false;
	delete $.ajaxSettings.headers["cache-control"]
 	$.ajax('http://'+post_host+':'+listener_port+'/method4',
 	{
		data: init_data,
		dataType: 'text',
 		method: 'POST',
		cache: true,
		headers: { 'Invoke-Control': 'EMVX'},
 		success:function(listener_response) 
 		{
   			var data = listener_response.split("&");
   			var processed_data = [];

   			for(var i = 0; i < data.length; i++)
   			{
   			    var m = data[i].split("=");
   			    processed_data[m[0]] = m[1];
   			}		

   			if (processed_data.CmdStatus != 'Success')
   			{
					var additional_message = decodeURIComponent(processed_data.TextResponse.replace(/\+/g, '%20'));
					show_feedback('error',error_message+': '+additional_message,COMMON_ERROR);
   			}
   			else
   			{
					show_feedback('success',success_message,COMMON_SUCCESS);
   			}
				
				$.post(SITE_URL+"/sales/set_sequence_no_emv", {sequence_no:processed_data.SequenceNo}, function()
				{
					if (callback)
					{
						callback();
					}
				});
 		},
   	error: function() 
		{
			show_feedback('error',error_message,COMMON_ERROR);			
			if (callback)
			{
				callback();
			}
   	}
 	});
}

function date_time_picker_field_report($field, format)
{
	var id_of_field = $field.attr('id');
	var name_of_field = $field.attr('name');
	
	var id_copy_of_field = id_of_field+'_formatted';
	var name_copy_of_field = name_of_field+'_formatted';
	
	//Set $field to be a copy that is used for actual display of date info
	$field.attr('id', id_copy_of_field);
	$field.attr('name', name_copy_of_field);
	
	if (IS_MOBILE)
	{
		$field.attr('readonly','readonly');
	}
	
	//create hidden input to track field behind the scenes (better value)
	$('<input>').attr({
	    type: 'hidden',
	    id: id_of_field,
	    name: name_of_field
	}).insertAfter($field);
	
		
   $field.on("dp.change", function(e) 
	{	
		//If we have a space seperator it has a time; otherwise just a date (time has 2 spaces; between date + time and AM/PM (12 hour))
		var does_date_have_time = format.indexOf(' ') != -1;
		var date = e.date;

		var formated_date = null;

		if (does_date_have_time)
		{
			formated_date = date.locale('en').format("YYYY-MM-DD HH:mm");
		}
		else
		{
			formated_date = date.locale('en').format("YYYY-MM-DD");
		}

		$('#'+id_of_field).val(formated_date);
		
		
		if (id_of_field == 'start_date' || id_of_field == 'end_date')
		{
			//This is for reports to make sure complex radio is checked when switching a date field; firefix doesn't recognize .click()
			if ($("#complex_radio").length)
			{
				$("#complex_radio").prop('checked', true);
			}
		}
		
		if (id_of_field == 'start_date_compare' || id_of_field == 'end_date_compare')
		{
			//This is for reports to make sure complex radio is checked when switching a date field; firefix doesn't recognize .click()
			if ($("#complex_radio").length)
			{
				$("#complex_radio_compare").prop('checked', true);
			}
		}
		
   });
	
	var defaultDate = null;
	
	if (id_of_field == 'start_date' || id_of_field == 'start_date_compare')
	{
		defaultDate = moment();
		defaultDate.set('hour', 0);
		defaultDate.set('minute', 0);
	}
	else if(id_of_field == 'end_date' || id_of_field == 'end_date_compare')
	{
		defaultDate = moment();
		defaultDate.set('hour', 23);
		defaultDate.set('minute', 59);
	}
	
   $field.datetimepicker({format: format, locale: LOCALE, defaultDate: defaultDate, ignoreReadonly: IS_MOBILE ? true : false});		
}

function is_int(n) 
{
   return n % 1 === 0;
}

function do_prompt_count(message,item_id,variation_id,ele)
{
	bootbox.prompt({
	  title: message,
	  value: '',
	  callback: function(quantity) 
		{
	  	if (quantity)
	  	{
				$.post($(ele).attr('href'),{item_id: item_id,quantity: quantity, variation_id:variation_id}, function(response)
				{
					show_feedback('success', response.message,COMMON_SUCCESS);
					$(ele).parent().parent().fadeOut();
				},'json');
	  	}
	  }
	
	});
	
	return false;
}

function do_link_confirm(message, ele)
{
	var url = $(ele).attr('href');
	bootbox.confirm(message, function(result)
	{
		if (result)
		{
			window.location = url;
		}
	});
	return false;
}

$(document).ready(function()
{	
	$(".delete_saved_report").click(function(e)
	{
		e.preventDefault();
		var $that = $(this);
		$.get($(this).attr('href'), function(response)
		{
			show_feedback('success', response.message,COMMON_SUCCESS);
			
			window.location.href = removeURLParam('key', window.location.href);
		}, 'json');
	});
	
	$(".save_report_button").click(function()
	{
		bootbox.prompt({
		  title: $(this).data('message'),
		  value: '',
		  callback: function(name) {
		  
		  	if (name)
		  	{
				$.post(SITE_URL+"/reports/add_saved_report",{name: name, url: CURRENT_URL_RELATIVE}, function(response)
				{
					show_feedback('success', response.message,COMMON_SUCCESS);
					window.location.href = addURLParam(window.location.href, 'key', response.key);
				},'json');
		  	}
		  }
		
		});
	});
});

function decodeHtml(html) {
    var txt = document.createElement("textarea");
    txt.innerHTML = html;
    return txt.value;
}

(function () {
  $(document).on('shown.bs.dropdown','.table-responsive', function (e) {
    var $table = $('.table-responsive').eq(0),
        $menu = $(e.target).find('.dropdown-menu'),
        tableOffsetHeight_a = $table.offset().top + $table.height(),
        menuOffsetHeight_a = $menu.offset().top + $menu.outerHeight(true);
			
    if (menuOffsetHeight_a > tableOffsetHeight_a)
		{
			$menu.closest('.upordown').removeClass('dropdown').addClass('dropup');
		
	    tableOffsetHeight_b = $table.offset().top,
	    menuOffsetHeight_b = $menu.offset().top;
			
			if (menuOffsetHeight_b - tableOffsetHeight_b < 100)
				$menu.addClass('inline-flex');
		}
			
  });
})();

function post_submit(action, method, values) 
{
    var form = $('<form/>', {
        action: action,
        method: method
    });
    $.each(values, function() {
        form.append($('<input/>', {
            type: 'hidden',
            name: this.name,
            value: this.value
        }));    
    });
    form.appendTo('body').submit();
}

function issue_integrated_giftcard(giftcard_value,manually_enter_card,processor_params,success_callback,error_callback)
{
	delete $.ajaxSettings.headers["cache-control"];
	
	if (typeof processor_params.iptran_device_id !== "undefined")
	{
		var data = {
			'value': giftcard_value,
			'manually_enter_card': manually_enter_card ? 1 : 0
		};
			
		$.ajax({
		url: SITE_URL+'/giftcards/issue_integrated_giftcard', 
		data: data,
		success: success_callback,
		error: error_callback,
		cache: true,
		contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
		type: 'POST'
		});
	}
	else
	{
			var invoice_no = INVOICE_NO;
			var data = {
				'HostOrIP': processor_params.net_e_pay_server,
				'IpPort': 9100,
				'TStream':'Transaction',
				'MerchantID': processor_params.merchant_id,
				'TranType': 'PrePaid',
				'IpAddress': processor_params.net_e_pay_server,
				'TranCode': 'Issue',
				'Memo': 'PHP POS EMV '+APPLICATION_VERSION,
				'InvoiceNo': invoice_no,
				'RefNo': invoice_no,
				'SecureDevice': processor_params.secure_device_non_emv,
				'ComPort': processor_params.com_port,
				'Purchase': giftcard_value,
				'AcctNo': manually_enter_card ? 'Prompt' : 'SecureDevice',
				'OperatorID': EMPLOYEE_PERSON_ID
			};
				
			$.ajax({
			url: 'http://127.0.0.1:'+processor_params.listener_port+'/method4', 
			data: data,
			success: success_callback,
			error: error_callback,
			cache: true,
			contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
			type: 'POST',
			headers: { 'Invoke-Control': 'PDCX'}
		});
	}
}

function void_issue_integrated_giftcard(giftcard_value,auth_code,manually_enter_card,processor_params,success_callback,error_callback)
{
	delete $.ajaxSettings.headers["cache-control"];
	
	if (typeof processor_params.iptran_device_id !== "undefined")
	{
		var data = {
			'value': giftcard_value,
			'auth_code': auth_code,
			'manually_enter_card': manually_enter_card ? 1 : 0
		};
			
		$.ajax({
		url: SITE_URL+'/giftcards/void_issue_integrated_giftcard', 
		data: data,
		success: success_callback,
		error: error_callback,
		cache: true,
		contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
		type: 'POST'
		});
	}
	else
	{
		var invoice_no = INVOICE_NO;
			var data = {
				'HostOrIP': processor_params.net_e_pay_server,
				'IpPort': 9100,
				'TStream':'Transaction',
				'MerchantID': processor_params.merchant_id,
				'TranType': 'PrePaid',
				'IpAddress': processor_params.net_e_pay_server,
				'TranCode': 'VoidIssue',
				'Memo': 'PHP POS EMV '+APPLICATION_VERSION,
				'AuthCode' : auth_code,
				'InvoiceNo': invoice_no,
				'RefNo': invoice_no,
				'SecureDevice': processor_params.secure_device_non_emv,
				'ComPort': processor_params.com_port,
				'Purchase': giftcard_value,
				'AcctNo': manually_enter_card ? 'Prompt' : 'SecureDevice',
				'OperatorID': EMPLOYEE_PERSON_ID
			};
				
			$.ajax({
			url: 'http://127.0.0.1:'+processor_params.listener_port+'/method4', 
			data: data,
			success: success_callback,
			error: error_callback,
			cache: true,
			contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
			type: 'POST',
			headers: { 'Invoke-Control': 'PDCX'}
		});
	}
}

function sale_integrated_giftcard(sale_value,manually_enter_card,processor_params,success_callback,error_callback)
{
	delete $.ajaxSettings.headers["cache-control"];
	
	if (typeof processor_params.iptran_device_id !== "undefined")
	{
		var data = {
			'sale_value': sale_value,
			'manually_enter_card': manually_enter_card ? 1 : 0
		};
			
		$.ajax({
		url: SITE_URL+'/giftcards/sale_integrated_giftcard', 
		data: data,
		success: success_callback,
		error: error_callback,
		cache: true,
		contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
		type: 'POST'
		});
	}
	else
	{
		var invoice_no = INVOICE_NO;
		var data = {
			'HostOrIP': processor_params.net_e_pay_server,
			'IpPort': 9100,
			'TStream':'Transaction',
			'MerchantID': processor_params.merchant_id,
			'TranType': 'PrePaid',
			'IpAddress': processor_params.net_e_pay_server,
			'TranCode': 'Sale',
			'Memo': 'PHP POS EMV '+APPLICATION_VERSION,
			'InvoiceNo': invoice_no,
			'RefNo': invoice_no,
			'SecureDevice': processor_params.secure_device_non_emv,
			'ComPort': processor_params.com_port,
			'Purchase': sale_value,
			'AcctNo': manually_enter_card ? 'Prompt' : 'SecureDevice',
			'OperatorID': EMPLOYEE_PERSON_ID
		};
				
		$.ajax({
		url: 'http://127.0.0.1:'+processor_params.listener_port+'/method4', 
		data: data,
		success: success_callback,
		error: error_callback,
		cache: true,
		contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
		type: 'POST',
		headers: { 'Invoke-Control': 'PDCX'}
		});
	}
}

function reload_integrated_giftcard(reload_amount,manually_enter_card,processor_params,success_callback,error_callback)
{
	delete $.ajaxSettings.headers["cache-control"];
	
	if (typeof processor_params.iptran_device_id !== "undefined")
	{
		var data = {
			'reload_amount': reload_amount,
			'manually_enter_card': manually_enter_card ? 1 : 0
		};
			
		$.ajax({
		url: SITE_URL+'/giftcards/reload_integrated_giftcard', 
		data: data,
		success: success_callback,
		error: error_callback,
		cache: true,
		contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
		type: 'POST'
		});
	}
	else
	{
		var invoice_no = INVOICE_NO;
		var data = {
			'HostOrIP': processor_params.net_e_pay_server,
			'IpPort': 9100,
			'TStream':'Transaction',
			'MerchantID': processor_params.merchant_id,
			'TranType': 'PrePaid',
			'IpAddress': processor_params.net_e_pay_server,
			'TranCode': 'Reload',
			'Memo': 'PHP POS EMV '+APPLICATION_VERSION,
			'InvoiceNo': invoice_no,
			'RefNo': invoice_no,
			'SecureDevice': processor_params.secure_device_non_emv,
			'ComPort': processor_params.com_port,
			'Purchase': reload_amount,
			'AcctNo': manually_enter_card ? 'Prompt' : 'SecureDevice',
			'OperatorID': EMPLOYEE_PERSON_ID
		};
				
		$.ajax({
		url: 'http://127.0.0.1:'+processor_params.listener_port+'/method4', 
		data: data,
		success: success_callback,
		error: error_callback,
		cache: true,
		contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
		type: 'POST',
		headers: { 'Invoke-Control': 'PDCX'}
		});
	}
}
$(document).ready(function()
{
	$(".delete-custom-image a").click(function(e)
	{	
		e.preventDefault();
		var $that = $(this);
		bootbox.confirm(CONFIRM_IMAGE_DELETE, function(result)
		{
			if (result)
			{
				$.get($that.attr('href'), function()
				{
					//face out image and link
					$that.parent().fadeOut();
					$that.parent().prev().fadeOut();
				});
			}
		});
	});
});

$(document).ready(function() {	
	$("#config_columns.report-config a").on("click", function(e) {
		e.preventDefault();
		
		if($(this).attr("id") == "reset_to_default")
		{
			var keyname = $('#url_segment').val();
			//Send a get request wihtout columns will clear column prefs
			$.post(SITE_URL+"/reports/save_column_prefs_reports", {keyname:keyname}, function()
			{
				location.reload();
			});
		}
		
		if(!$(e.target).hasClass("handle"))
		{
			var $checkboxs = $(this).find("input[type=checkbox]");
			$checkboxs.prop("checked", !$checkboxs.prop("checked")).trigger("change");
		}
		
		return false;
	});
	$("#config_columns.report-config input[type=checkbox]").change(
		function(e) {
			var columns = $("#config_columns.report-config input:checkbox:checked").map(function(){
		return $(this).val();
		}).get();
			var keyname = $('#url_segment').val();
			//Send a get request for save column prefs
			$.post(SITE_URL+"/reports/save_column_prefs_reports", {columns:columns,keyname:keyname}, function(json)
			{
				$(columns).each(function( index, data ) {
				  /* $('.column'+data).show(); */
					var $th = $("."+data);
					var $td = $th.closest('table').find('td:nth-child('+($th.index()+1)+')');
					$th.show();
					$td.show();
				});
				var columns1 = $("#config_columns.report-config input:checkbox:not(:checked)").map(function(){
				return $(this).val();
				}).get();
				$(columns1).each(function( index, data ) {
				  /* $('.column'+data).hide(); */
					var $th = $("."+data);
					var $td = $th.closest('table').find('td:nth-child('+($th.index()+1)+')');
					$th.hide();
					$td.hide();
				});
				
			});
			
	});
});

// Array Remove - By John Resig (MIT Licensed)
Array.prototype.remove = function(from, to) {
  var rest = this.slice((to || from) + 1 || this.length);
  this.length = from < 0 ? this.length + from : from;
  return this.push.apply(this, rest);
};


function escapeRegExp(string) {
  return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); // $& means the whole matched string
}