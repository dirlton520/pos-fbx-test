<?php
$this->load->view("partial/header"); 
$this->load->helper('demo');
$this->load->helper('update');

?>
<style>
.multisteps-form__progress {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(0, 1fr));
}

.multisteps-form__progress-btn {
  transition-property: all;
  transition-duration: 0.15s;
  transition-timing-function: linear;
  transition-delay: 0s;
  position: relative;
  padding-bottom: 60px;
  color: rgba(108, 117, 125, 0.7);
  text-indent: -9999px;
  border: none;
  background-color: transparent;
  outline: none !important;
  cursor: pointer;
}
@media (min-width: 500px) {
  .multisteps-form__progress-btn {
    text-indent: 0;
  }
}
.multisteps-form__progress-btn::before {
    position: absolute;
    bottom: 26px;
    left: 50%;
    display: block;
    width: 20px;
    height: 20px;
    content: '';
    -webkit-transform: translateX(-50%);
    transform: translateX(-50%);
    transition: all 0.15s linear 0s, -webkit-transform 0.15s cubic-bezier(0.05, 1.09, 0.16, 1.4) 0s;
    transition: all 0.15s linear 0s, transform 0.15s cubic-bezier(0.05, 1.09, 0.16, 1.4) 0s;
    transition: all 0.15s linear 0s, transform 0.15s cubic-bezier(0.05, 1.09, 0.16, 1.4) 0s, -webkit-transform 0.15s cubic-bezier(0.05, 1.09, 0.16, 1.4) 0s;
    border: 5px solid currentColor;
    border-radius: 50%;
    background-color: #fff;
    box-sizing: border-box;
    z-index: 3;
}
.multisteps-form__progress-btn::after {
    position: absolute;
    bottom: 33px;
    left: calc(-50% - 13px / 2);
    transition-property: all;
    transition-duration: 0.15s;
    transition-timing-function: linear;
    transition-delay: 0s;
    display: block;
    width: 100%;
    height: 5px;
    content: '';
    background-color: currentColor;
    z-index: 1;
}
.multisteps-form__progress-btn:first-child:after {
  display: none;
}
.multisteps-form__progress-btn.js-active {
  color: #5cb85c;
}
/*.multisteps-form__progress-btn.js-active:before {*/
/*  -webkit-transform: translateX(-50%) scale(1.2);*/
/*          transform: translateX(-50%) scale(1.2);*/
/*  background-color: currentColor;*/
/*}*/

.multisteps-form__form {
  position: relative;
}

.multisteps-form__panel {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 0;
  opacity: 0;
  visibility: hidden;
}
.multisteps-form__panel.js-active {
  height: auto;
  opacity: 1;
  visibility: visible;
}
.multisteps-form__panel[data-animation="scaleOut"] {
  -webkit-transform: scale(1.1);
          transform: scale(1.1);
}
.multisteps-form__panel[data-animation="scaleOut"].js-active {
  transition-property: all;
  transition-duration: 0.2s;
  transition-timing-function: linear;
  transition-delay: 0s;
  -webkit-transform: scale(1);
          transform: scale(1);
}
.multisteps-form__panel[data-animation="slideHorz"] {
  left: 50px;
}
.multisteps-form__panel[data-animation="slideHorz"].js-active {
  transition-property: all;
  transition-duration: 0.25s;
  transition-timing-function: cubic-bezier(0.2, 1.13, 0.38, 1.43);
  transition-delay: 0s;
  left: 0;
}
.multisteps-form__panel[data-animation="slideVert"] {
  top: 30px;
}
.multisteps-form__panel[data-animation="slideVert"].js-active {
  transition-property: all;
  transition-duration: 0.2s;
  transition-timing-function: linear;
  transition-delay: 0s;
  top: 0;
}
.multisteps-form__panel[data-animation="fadeIn"].js-active {
  transition-property: all;
  transition-duration: 0.3s;
  transition-timing-function: linear;
  transition-delay: 0s;
}
.multisteps-form__panel[data-animation="scaleIn"] {
  -webkit-transform: scale(0.9);
          transform: scale(0.9);
}
.multisteps-form__panel[data-animation="scaleIn"].js-active {
  transition-property: all;
  transition-duration: 0.2s;
  transition-timing-function: linear;
  transition-delay: 0s;
  -webkit-transform: scale(1);
          transform: scale(1);
}

/**/
.panels-wrap-box,
.multisteps-form-topbar {
	background: #fff;
	border-radius: 3px;
	padding: 20px;
	margin-bottom: 10px;
}
.multisteps-form__step-icon {
    background-color: #979ea3;
    width: 60px;
    height: 60px;
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0 auto 10px;
    border-radius: 50%;
}
.multisteps-form__progress-btn.js-active .multisteps-form__step-icon {
    background-color: currentColor;
}
.multisteps-form__step-counts {
	position: absolute;
	bottom: 0;
	left: 0;
	right: 0;
}
.multisteps-form__content ul {
	padding-left: 11px;
}
.w-100 {
    width: 100%;
}
.button-row {
    display: flex;
    border-top: 2px solid #e9ebee;
    padding-top: 20px;
    margin-top: 10px;
}
.ml-auto {
    margin-left: auto;
}
.mr-auto {
    margin-right: auto;
}
.mx-auto {
    margin-left: auto;
    margin-right: auto;
}
.form_button-row {
    display: flex;
}
.multisteps-form__content .btn,
.form_button-row .btn,
.top-row .btn {
    border-radius: 3px;
}
.steps_label_text {
	color: #888888;
}
.multisteps-form__title {
	margin-top: 0;
	margin-bottom: 10px;
}
.multisteps-form__content {
	margin-top: 20px;
}
.multisteps-form__step-icon img {
    width: 30px;
    height: 30px;
}
.multisteps-container {
    max-width: 1170px;
    margin: 0 auto;
}
.multisteps-form__progress-btn.js-done {
	text-decoration: line-through;
}
.multisteps-form__step-label,
.multisteps-form__label {
    color: #979ea3;
}
@media only screen and (min-width:768px) {
    .multisteps-form__step-icon img {
        width: 40px;
        height: 40px;
    }
    .multisteps-form__step-icon {
        width: 100px;
        height: 100px;
    }
    .panels-wrap-box, .multisteps-form-topbar {
    	padding: 40px;
    }
}
@media only screen and (max-width:767px) {
    .multisteps-form__step-label {
    	display: none;
    }
    .multisteps-form__step-icon {
    	margin: 0 auto 0px;
    }
}
@media only screen and (max-width:499px) {
    .multisteps-form__progress-btn {
    	text-indent: 0px;
    	font-size: 10px;
    }
    .multisteps-form__step-icon {
        display: none;
    }
    .multisteps-form__progress-btn {
    	padding-bottom: 50px;
    }
}
/**/
</style>

<?php
if ($this->input->get('error') == 'access_token')
{
?>
	<div class="alert alert-danger">
		<strong><?php echo lang('common_shopify_access_token_error'); ?></strong>
	</div>
<?php	
}
?>
<div class="overflow-hidden" style="margin-top:10px;">
      <!--multisteps-form-->
      <div class="multisteps-form">
        <!--progress bar-->
        <div class="multisteps-form-topbar">
        <div class="multisteps-container">
            <div class="multisteps-form__progress">
              <button class="multisteps-form__progress-btn js-active" data-panel="1" type="button" title="Install Shopify app">
                 <span class="multisteps-form__step-icon"><img class="icon_click_evnt" src="https://developer.phppointofsalehosting.com/shopify/assets/img/computer.svg" alt=""></span><span class="multisteps-form__step-label"><?php echo lang('config_install_shopify_app');?></span>
                 <span class="multisteps-form__step-counts"><?php echo lang('config_step_1');?></span>
                </button>
              <button class="multisteps-form__progress-btn" type="button" data-panel="2" title="Connect Billing">
                  <span class="multisteps-form__step-icon"><img class="icon_click_evnt" src="https://developer.phppointofsalehosting.com/shopify/assets/img/online-payment.svg" alt=""></span><span class="multisteps-form__step-label"><?php echo lang('config_connect_billing');?></span>
                  <span class="multisteps-form__step-counts"><?php echo lang('config_step_2');?></span>
              </button>
              <button class="multisteps-form__progress-btn" type="button" data-panel="3" title="Choose Sync Options">
                  <span class="multisteps-form__step-icon"><img class="icon_click_evnt" src="https://developer.phppointofsalehosting.com/shopify/assets/img/businesswoman.svg" alt=""></span><span class="multisteps-form__step-label"><?php echo lang('config_choose_sync_options')?></span>
                  <span class="multisteps-form__step-counts"><?php echo lang('config_step_3');?></span>
              </button>
              <button class="multisteps-form__progress-btn" type="button" data-panel="4" title="Sync">
                  <span class="multisteps-form__step-icon"><img class="icon_click_evnt" src="https://developer.phppointofsalehosting.com/shopify/assets/img/sync.svg" alt=""></span><span class="multisteps-form__step-label"><?php echo lang('config_sync')?></span>
                  <span class="multisteps-form__step-counts"><?php echo lang('config_step_4');?></span>
              </button>
            </div>
        </div>
        </div>
        <!--form panels-->
        <div class="panels-wrap-box-wrap">
        <form action="<?php echo site_url('config/save_shopify_config');?>" id="shopify_config_form" method="POST">
             <div class="panels-wrap-box">
            <div class="multisteps-form__form">
              <!--single form panel-->
              <div class="multisteps-form__panel shadow p-4 rounded bg-white js-active" data-panell="1" data-animation="scaleIn">
                <p class="steps_label_text"><?php echo lang('config_install_shopify_app');?></p>
								
					<?php
					echo form_hidden('shopify_shop',$this->config->item('shopify_shop'));
					
					if ($this->config->item('shopify_shop'))
					{
					?>
					<div class='text-center'>
						<br />
						<br />
						<p><?php echo lang('config_connected_to_shopify')?> [<strong><?php echo $this->config->item('shopify_shop').'.myshopify.com' ?></strong>]</p>
						
						<br />
						<br />						
						
						<a href="<?php echo site_url('ecommerce/oauth_shopify_disconnect');?>" class="btn btn-danger" id="shopify_oauth_disconnect"><?php echo lang('config_disconnect_to_shopify'); ?></a>
						<br /><br />
						
					</div>
					
					<?php	
					}
					else
					{
					?>
					<div class='text-center'>
						<p><a href="https://apps.shopify.com/php-point-of-sale" target="_blank"><?php echo lang('config_connect_shopify_in_app_store')?></a></p>
						<br />
						<br />
					</div>
					
					<?php
					}
					?>
				
				
                <div class="button-row">
                  <button class="btn btn-primary btn-lg ml-auto js-btn-next" type="button" title=<?php echo json_encode(lang('common_next'));?>><?php echo lang('common_next');?></button>
                </div>
				
				
			  </div>
			
              <!--single form panel-->
              <div class="multisteps-form__panel shadow p-4 rounded bg-white" data-panell="2" data-animation="scaleIn">
                <p class="steps_label_text"><?php echo lang('config_connect_billing')?></p>
				
				<div class='text-center'>
					<?php
					if ($this->config->item('shopify_charge_id'))
					{
					?>
                    	<a href="<?php echo site_url('ecommerce/cancel_shopify_billing');?>" class="btn btn-danger" id="shopify_cancel_billing"><?php echo lang('config_cancel_shopify'); ?></a>
						<script>
						$("#shopify_cancel_billing").click(function(e)
						{
							e.preventDefault();
							
							bootbox.confirm(<?php echo json_encode(lang('config_confirm_cancel_shopify')); ?>, function(response)
							{
								if (response)
								{
									window.location = $("#shopify_cancel_billing").attr('href');
								}
							});
							
						})
						</script>
					<?php	
					}
					else
					{
					?>
                	<a href="<?php echo site_url('ecommerce/activate_shopify_billing');?>" class="btn btn-success" id="shopify_activate_billing"><?php echo str_replace('{SHOPIFY_PRICE}',SHOPIFY_PRICE,lang('config_shopify_billing_terms')); ?></a>
					<?php
					}
					?>
				
				
                <div class="button-row">
                  <button class="btn btn-primary btn-lg js-btn-prev" type="button" title=<?php echo json_encode(lang('common_previous'));?>><?php echo lang('common_previous');?></button>
                  <button class="btn btn-primary btn-lg ml-auto js-btn-next" type="button" title=<?php echo json_encode(lang('common_next'));?>><?php echo lang('common_next');?></button>
                </div>
				
              </div>
		  </div>
              <!--single form panel-->
              <div class="multisteps-form__panel shadow p-4 rounded bg-white" data-panell="3" data-animation="scaleIn">
                 <p class="steps_label_text"><?php echo lang('config_choose_sync_options')?></p>
              
			  	
				
                <div class="button-row" style="padding: 30px">
                  <button class="btn btn-primary btn-lg js-btn-prev" type="button" title=<?php echo json_encode(lang('common_previous'));?>><?php echo lang('common_previous');?></button>
                  <button class="btn btn-primary btn-lg ml-auto js-btn-next" type="button" title=<?php echo json_encode(lang('common_next'));?>><?php echo lang('common_next');?></button>
                </div>
				
             <div class="form-group" data-keyword="<?php echo H(lang('config_keyword_ecommerce')) ?>">	
 							<?php echo form_label(lang('config_ecommerce_cron_sync_operations').':', 'ecommerce_cron_sync_operations',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
 								<div class="col-sm-9 col-md-9 col-lg-10 ecommerce_cron_sync_operations">
 								  	<ul id="check-list-box" data-name="ecommerce_cron_sync_operations[]" class="list-group checked-list-box">
 											 <li class="list-group-item" data-value="sync_inventory_changes" data-color="success"><?php echo lang('config_sync_inventory_changes'); ?></li>
 											 <li class="list-group-item" data-value="import_ecommerce_items_into_phppos" data-color="success"><?php echo lang('config_import_ecommerce_items_into_phppos'); ?></li>
 											 <li class="list-group-item" data-value="import_ecommerce_orders_into_phppos" data-color="success"><?php echo lang('config_import_ecommerce_orders_into_phppos'); ?></li>
				  		                   	 <li class="list-group-item" data-value="export_phppos_categories_to_ecommerce" data-color="success"><?php echo lang('config_export_phppos_categories_to_ecommerce'); ?></li>
 											 <li class="list-group-item" data-value="export_phppos_items_to_ecommerce" data-color="success"><?php echo lang('config_export_phppos_items_to_ecommerce'); ?></li>
 								    </ul>
									
 								</div>
								
 				</div>
				
				<input id="save_sync_options" type="button" class="btn btn-primary pull-right" value="<?php echo lang('common_save'); ?>"/>
				
				
				
				
   				 <script>
					 
			     $("#save_sync_options").click(function()
				 {
					 $("#shopify_config_form").ajaxSubmit();
					show_feedback('success',<?php echo json_encode(lang('common_saved_successfully')); ?>,<?php echo json_encode(lang('common_success')); ?>);
				 });
   				 var checklist_ecom = <?php echo json_encode(unserialize($this->config->item('ecommerce_cron_sync_operations'))); ?>;

   				 $(function () {
   				 		$group = $('.ecommerce_cron_sync_operations .list-group.checked-list-box');
   				     $group.find('.list-group-item').each(function () {
   				         // Settings
   				         var $widget = $(this),
   				             $checkbox = $('<input type="checkbox" class="hidden" />'),
   				 						value = ($widget.data('value') ? $widget.data('value') : '1'),
   				             color = ($widget.data('color') ? $widget.data('color') : "primary"),
   				             style = ($widget.data('style') == "button" ? "btn-" : "list-group-item-"),
   				             settings = {
   				                 on: {
   				                     icon: 'glyphicon glyphicon-check'
   				                 },
   				                 off: {
   				                     icon: 'glyphicon glyphicon-unchecked'
   				                 }
   				             };
           					 
   				 				$widget.css('cursor', 'pointer');
   				 				$checkbox.val(value).attr('name', $group.data('name'));
   				         $widget.append($checkbox);

   				         // Event Handlers
   				         $widget.on('click', function () {
   				             $checkbox.prop('checked', !$checkbox.is(':checked'));
   				             $checkbox.triggerHandler('change');
   				         });

   				         $checkbox.on('change', function () {
   				             updateDisplay();
   				         });

   				         // Actions
   				         function updateDisplay() {
   				             var isChecked = $checkbox.is(':checked');
   				             // Set the button's state
   				             $widget.data('state', (isChecked) ? "on" : "off");
   				             // Set the button's icon
   				             $widget.find('.state-icon')
   				                 .removeClass()
   				                 .addClass('state-icon ' + settings[$widget.data('state')].icon);

   				             // Update the button's color
   				             if (isChecked) {
   				                 $widget.addClass(style + color);
   				             } else {
   				                 $widget.removeClass(style + color);
   				 			}

   				 			if (isChecked) {	
   				 				if(typeof $widget.data('requires') == 'object')
   				 				{
   				 					$.each($widget.data('requires'), function(key, value)
   				 					{
   				 						$(":checkbox[value="+value+"]").prop("checked",true).trigger('change');
   				 					});
   				 				}
   				 			} else {
   				 				$group.find('.list-group-item').each(function(){
   				 					if(typeof $(this).data('requires') == 'object')
   				 					{
   				 						var that = this;
   				 						$.each($(this).data('requires'), function(key, value) {
							
   				 							if(value == $widget.data('value'))
   				 							{
   				 								$(that).find(":checkbox").prop("checked",false).trigger('change');
   				 							}
   				 						});
   				 					}
   				 				});
   				 			}
   				         }

   				         // Initialization
   				         function init() {
   				 			if($.inArray($widget.data('value'), checklist_ecom) !== -1)
   				 			{
   				 				$widget.data('checked', true);
   				 			}
					
   				           	if ($widget.data('checked') == true) {
   				               	$checkbox.prop('checked', !$checkbox.is(':checked'));
   				           	}
   				           	updateDisplay();

   				           	// Inject the icon if applicable
   				           	if ($widget.find('.state-icon').length == 0) {
   				               	$widget.prepend('<span class="state-icon ' + settings[$widget.data('state')].icon + '"></span>');
   				           	}
   				         }
   				         init();
   				     });
   				 });
   			 </script>
			
			  </div>
                    
              <!--single form panel-->
              <div class="multisteps-form__panel shadow p-4 rounded bg-white" data-panell="4" data-animation="scaleIn">
                 <p class="steps_label_text"><?php echo lang('config_sync');?></p>
				 
				 <div class="text-center">
             		 <a href="<?php echo site_url('ecommerce/manual_sync');?>" class="btn btn-success" id="shopify_sync"><?php echo lang('config_sync'); ?></a>
				 </div>
				 
				 <script>
				 $("#shopify_sync").click(function(e)
				 {
					e.preventDefault();
					
					bootbox.confirm(<?php echo json_encode(lang('confirmation_woocommerce_cron')); ?>, function(response)
					{
						if (response)
						{
							$.get($("#shopify_sync").attr('href'));
							alert(<?php echo json_encode(lang('config_ecommerce_sync_running')); ?>);
						}
					});
				 	
				 });
				 </script>
                 <div class="button-row" style="display: block;">
                   <button class="btn btn-primary btn-lg ml-auto js-btn-prev" type="button" title=<?php echo json_encode(lang('common_previous'));?>><?php echo lang('common_previous');?></button>
                 </div>
				 
			</div>
			
			</div>
            </form>
        </div>
      </div>
    </div>
    </div>


<script>
//DOM elements
const DOMstrings = {
  stepsBtnClass: 'multisteps-form__progress-btn',
  stepsBtns: document.querySelectorAll('.multisteps-form__progress-btn'),
  stepsBar: document.querySelector('.multisteps-form__progress'),
  stepsForm: document.querySelector('.multisteps-form__form'),
  stepsFormTextareas: document.querySelectorAll('.multisteps-form__textarea'),
  stepFormPanelClass: 'multisteps-form__panel',
  stepFormPanels: document.querySelectorAll('.multisteps-form__panel'),
  stepPrevBtnClass: 'js-btn-prev',
  stepNextBtnClass: 'js-btn-next' };


//remove class from a set of items
const removeClasses = (elemSet, className) => {

  elemSet.forEach(elem => {

    elem.classList.remove(className);

  });

};

//return exect parent node of the element
const findParent = (elem, parentClass) => {

  let currentNode = elem;

  while (!currentNode.classList.contains(parentClass)) {
    currentNode = currentNode.parentNode;
  }

  return currentNode;

};

//get active button step number
const getActiveStep = elem => {
    
    if(elem.classList.contains('multisteps-form__progress-btn')) {
        // this means button is clicked
        
        return Array.from(DOMstrings.stepsBtns).indexOf(elem);
    } else {
        return Array.from(DOMstrings.stepsBtns).indexOf(elem.parentElement);
    }
  
};

//set all steps before clicked (and clicked too) to active
const setActiveStep = activeStepNum => {


  //remove active state from all the state
  removeClasses(DOMstrings.stepsBtns, 'js-active');

  //set picked items to active
  DOMstrings.stepsBtns.forEach((elem, index) => {

    if (index <= activeStepNum) {
      elem.classList.add('js-active');
    }

  });
};

//get active panel
const getActivePanel = () => {

  let activePanel;

  DOMstrings.stepFormPanels.forEach(elem => {

    if (elem.classList.contains('js-active')) {

      activePanel = elem;

    }

  });

  return activePanel;

};

//open active panel (and close unactive panels)
const setActivePanel = activePanelNum => {

  //remove active class from all the panels
  removeClasses(DOMstrings.stepFormPanels, 'js-active');

  //show active panel
  DOMstrings.stepFormPanels.forEach((elem, index) => {
    if (index === activePanelNum) {

      elem.classList.add('js-active');

      setFormHeight(elem);

    }
  });

};

//set form height equal to current panel height
const formHeight = activePanel => {

  const activePanelHeight = activePanel.offsetHeight;

  DOMstrings.stepsForm.style.height = `${activePanelHeight+120}px`;

};

const setFormHeight = () => {
  const activePanel = getActivePanel();

  formHeight(activePanel);
};

//STEPS BAR CLICK FUNCTION
DOMstrings.stepsBar.addEventListener('click', e => {
  
  //check if click target is a step button
  const eventTarget = e.target;
  
  if(eventTarget.classList.contains('multisteps-form__step-counts') || eventTarget.classList.contains('multisteps-form__progress-btn') ||  eventTarget.classList.contains('multisteps-form__step-label') || eventTarget.classList.contains('icon_click_evnt') || eventTarget.classList.contains('multisteps-form__step-icon')) {
     // do nothing just continue 
 
  } else {
      return;
  }
  

//   if (!eventTarget.classList.contains(`${DOMstrings.stepsBtnClass}`)) {
//     return;
//   }

  //get active button step number
  const activeStep = getActiveStep(eventTarget);
  

  //set all steps before clicked (and clicked too) to active
  setActiveStep(activeStep);

  //open active panel
  //setActivePanel(activeStep);
});

//PREV/NEXT BTNS CLICK
DOMstrings.stepsForm.addEventListener('click', e => {

  const eventTarget = e.target;

  //check if we clicked on `PREV` or NEXT` buttons
  if (!(eventTarget.classList.contains(`${DOMstrings.stepPrevBtnClass}`) || eventTarget.classList.contains(`${DOMstrings.stepNextBtnClass}`)))
  {
    return;
  }

  //find active panel
  const activePanel = findParent(eventTarget, `${DOMstrings.stepFormPanelClass}`);

  let activePanelNum = Array.from(DOMstrings.stepFormPanels).indexOf(activePanel);

  //set active step and active panel onclick
  if (eventTarget.classList.contains(`${DOMstrings.stepPrevBtnClass}`)) {
    activePanelNum--;

  } else {

    activePanelNum++;

  }

  setActiveStep(activePanelNum);
  setActivePanel(activePanelNum);

});

//SETTING PROPER FORM HEIGHT ONLOAD
window.addEventListener('load', setFormHeight, false);

//SETTING PROPER FORM HEIGHT ONRESIZE
window.addEventListener('resize', setFormHeight, false);

//changing animation via animation select !!!YOU DON'T NEED THIS CODE (if you want to change animation type, just change form panels data-attr)

const setAnimationType = newType => {
  DOMstrings.stepFormPanels.forEach(elem => {
    elem.dataset.animation = newType;
  });
};

//selector onchange - changing animation
const animationSelect = document.querySelector('.pick-animation__select');

// animationSelect.addEventListener('change', () => {
//   const newAnimationType = animationSelect.value;

//   setAnimationType(newAnimationType);
// });   
</script>
<script type="text/javascript">
$(document).ready(function(){
    $(".multisteps-form__progress button").on('click', function(){
        var panel = parseInt($(this).data('panel'));
        $('.panels-wrap-box').find('.multisteps-form__panel').each(function(){
            $(this).removeClass('js-active');
            
            $('.panels-wrap-box').find("[data-panell="+panel+"]").addClass('js-active');
        
        })
        
    })
    
})

<?php
if ($this->input->get('step'))
{
?>
	setActiveStep(<?php echo json_encode($this->input->get('step') - 1 ) ?>);
	setActivePanel(<?php echo json_encode($this->input->get('step') - 1 ) ?>);
<?php
}
?>

</script>
<?php $this->load->view("partial/footer"); ?>.
