<?php
function get_css_files()
{
	$return = array();
	
	$css_files = array(
		array('path' =>'assets/css/bootstrap-3.min.css'),
		array('path' =>'assets/css/jquery-ui-1.10.4.custom.min.css'),
		array('path' =>'assets/css/themify-icons.css'),
		array('path' =>'assets/css/animate.css'),
		array('path' =>'assets/css/ionicons.min.css'),
		array('path' =>'assets/css/forms.css'),
		array('path' =>'assets/css/mail.css'),
		array('path' =>'assets/css/bootstrap-datepicker3.css'),
		array('path' =>'assets/css/bootstrap-datetimepicker.min.css'),
		array('path' =>'assets/css/buttons.css'),
		array('path' =>'assets/css/tabs-accordions.css'),
		array('path' =>'assets/css/popovers-tooltips.css'),
		array('path' =>'assets/css/modals.css'),
		array('path' =>'assets/css/infoboxes.css'),
		array('path' =>'assets/css/basic-tables.css'),
		array('path' =>'assets/css/selectize.css'),
		array('path' =>'assets/css/selectize.bootstrap3.css'),
		array('path' =>'assets/css/bootstrap-editable.css'),
		array('path' =>'assets/css/invoice.css'),
		array('path' =>'assets/css/toastr.css'),
		array('path' =>'assets/css/style.css'),
		array('path' =>'assets/css/custom.css'),
		array('path' =>'assets/css/bootstrap-select.css'),
		array('path' =>'assets/css/select2.css'),
		array('path' =>'assets/css/token-input-facebook.css'),
		array('path' =>'assets/css/bootstrap-colorpicker.min.css'),
		array('path' =>'assets/css/signin2.css'),
		array('path' =>'assets/css/stacktable.css'),
		array('path' =>'assets/css/dark.css'),
		array('path' =>'assets/css/jqbtk.css'),
		array('path' =>'assets/css/jsgrid.css'),
		array('path' =>'assets/css/jsgrid-theme.css'),
		array('path' =>'assets/css/bootstrap-tokenfield.css'),
		array('path' =>'assets/css/pingrid.css'),
		array('path' =>'assets/css/dropzone.min.css'),
		array('path' =>'assets/css/owl.carousel.min.css'),
		array('path' =>'assets/css/owl.theme.default.min.css'),
		array('path' =>'assets/css/fullcalendar.min.css'),

	);
	
	if(!defined("ASSET_MODE") or ASSET_MODE == 'development')
	{
		$return = $css_files;
	}
	else
	{
		$return[] = array('path' =>"assets/css/all.css");
	}
	
	if (function_exists('get_instance'))
	{
	   $CI =& get_instance();
		
		 if ($CI->config->item('dark_mode'))
		 {
			$return[] = array('path' =>'assets/css/theme-black.css');		 	
		 }
		
		if (function_exists('is_rtl_lang'))
		{
			if(is_rtl_lang())
			{
				$return[] = array('path' =>'assets/css/rtl.css');
				$return[] = array('path' =>'assets/css/register-rtl.css');
			}
		}
	}
	
	return $return;
}

function get_js_files()
{
	if(!defined("ASSET_MODE") or ASSET_MODE == 'development')
	{
		return array(
			array('path' =>'assets/js/jquery.js'),
			array('path' =>'assets/js/jquery.clicktoggle.js'),
			array('path' =>'assets/js/jquery-ui.custom.min.js'),
			array('path' =>'assets/js/jquery.ui.touch-punch.min.js'),
			array('path' =>'assets/js/bootstrap-3.min.js'),
			array('path' =>'assets/js/bootbox.min.js'),
			array('path' =>'assets/js/jquery.dataTables.min.js'),
			array('path' =>'assets/js/bootstrap-datatables.js'),
			array('path' =>'assets/js/moment-with-locales.js'),
			array('path' =>'assets/js/bootstrap-datetimepicker.min.js'),
			array('path' =>'assets/js/daterangepicker.js'),
			array('path' =>'assets/js/select2.min.js'),
			array('path' =>'assets/js/imagePreview.js'),
			array('path' =>'assets/js/jquery.tablesorter.min.js'),
			array('path' =>'assets/js/jquery.validate.js'),
			array('path' =>'assets/js/common.js'),
			array('path' =>'assets/js/jquery.form.js'),
			array('path' =>'assets/js/manage_tables.js'),
			array('path' =>'assets/js/jquery.tokeninput.js'),
			array('path' =>'assets/js/jquery.imagerollover.js'),
			array('path' => 'assets/js/bootstrap-colorpicker.min.js'),
			array('path' => 'assets/js/chart.js'),
			array('path' => 'assets/js/SigWebTablet.js'),
			array('path' => 'assets/js/signature_pad.min.js'),
			array('path' => 'assets/js/jquery.playSound.js'),
			array('path' => 'assets/js/toastr.js'),
			array('path' => 'assets/js/selectize.js'),
			array('path' => 'assets/js/jquery.sieve.js'),
			array('path' => 'assets/js/jquery.nicescroll.min.js'),
			array('path' => 'assets/js/wow.min.js'),
			array('path' => 'assets/js/jquery.accordion.js'),
			array('path' => 'assets/js/form-validation/bootstrap-filestyle.js'),
			array('path' => 'assets/js/bootstrap-editable.min.js'),
			array('path' => 'assets/js/core.js'),
			array('path' => 'assets/js/stacktable.js'),
			array('path' => 'assets/js/jqbtk.js'),
			array('path' => 'assets/js/jsgrid.js'),
			array('path' => 'assets/js/bootstrap-tokenfield.js'),
			array('path' => 'assets/js/pingrid.js'),
			array('path' => 'assets/js/html2canvas.min.js'),
			array('path' => 'upup.min.js'),
			array('path' => 'upup.sw.min.js'),
			array('path' => 'assets/js/handlebars.js'),
			array('path' => 'assets/js/pouchdb.min.js'),
			array('path' => 'assets/js/pouchdb.find.js'),
			array('path' => 'assets/js/dropzone.min.js'),
			array('path' => 'assets/js/owl.carousel.min.js'),
			array('path' => 'assets/js/fullcalendar.min.js'),
			array('path' => 'assets/js/locales-all.min.js'),
		);
	}

	return array(
		array('path' =>'assets/js/all.js'),
	);

}

function get_bitrix_css_files() {
	$return[] = array('path' =>'assets/css/bitrixUI.css');
	$return[] = array('path' =>'assets/css/sweetalert.min.css');
	return $return;
}
function get_bitrix_js_files() {
	$return[] = array('path' =>'assets/js/sweetalert.min.js');
	return $return;
}
?>