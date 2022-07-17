<!DOCTYPE html>
<head>
	<style>
		*{font-family: Calibri, sans-serif;}
		.invoice-desc{font-size:10px;}
	</style>
</head>
<body>
<?php
$this->load->helper('sale');
$return_policy = ($loc_return_policy = $this->Location->get_info_for_key('return_policy', isset($override_location_id) ? $override_location_id : FALSE)) ? $loc_return_policy : $this->config->item('return_policy');
$company = ($company = $this->Location->get_info_for_key('company', isset($override_location_id) ? $override_location_id : FALSE)) ? $company : $this->config->item('company');
$website = ($website = $this->Location->get_info_for_key('website', isset($override_location_id) ? $override_location_id : FALSE)) ? $website : $this->config->item('website');
$company_logo = ($company_logo = $this->Location->get_info_for_key('company_logo', isset($override_location_id) ? $override_location_id : FALSE)) ? $company_logo : $this->config->item('company_logo');
$tax_id = ($tax_id = $this->Location->get_info_for_key('tax_id', isset($override_location_id) ? $override_location_id : FALSE)) ? $tax_id : $this->config->item('tax_id');

$is_integrated_credit_sale = is_sale_integrated_cc_processing($cart);
$is_sale_integrated_ebt_sale = is_sale_integrated_ebt_sale($cart);
$is_credit_card_sale = is_credit_card_sale($cart);

$signature_needed = $this->config->item('capture_sig_for_all_payments') || (($is_credit_card_sale && !$is_integrated_credit_sale) || is_store_account_sale($cart));
$item_custom_fields_to_display = array();
$sale_custom_fields_to_display = array();
$item_kit_custom_fields_to_display = array();
$customer_custom_fields_to_display = array();
$employee_custom_fields_to_display = array();
$work_order_custom_fields_to_display  = array();


for ($k = 1; $k <= NUMBER_OF_PEOPLE_CUSTOM_FIELDS; $k++) {
    $item_custom_field = $this->Item->get_custom_field($k, 'show_on_receipt');
    $sale_custom_field = $this->Sale->get_custom_field($k, 'show_on_receipt');
    $item_kit_custom_field = $this->Item_kit->get_custom_field($k, 'show_on_receipt');
    $customer_custom_field = $this->Customer->get_custom_field($k, 'show_on_receipt');
    $employee_custom_field = $this->Employee->get_custom_field($k, 'show_on_receipt');
  	$work_order_custom_field = $this->Work_order->get_custom_field($k,'show_on_receipt');

    if ($item_custom_field) {
        $item_custom_fields_to_display[] = $k;
    }

    if ($sale_custom_field) {
        $sale_custom_fields_to_display[] = $k;
    }

    if ($item_kit_custom_field) {
        $item_kit_custom_fields_to_display[] = $k;
    }

    if ($customer_custom_field) {
        $customer_custom_fields_to_display[] = $k;
    }

    if ($employee_custom_field) {
        $employee_custom_fields_to_display[] = $k;
    }
	
  	 if ($work_order_custom_field)
  	 {
  	 	$work_order_custom_fields_to_display[] = $k;
  	 }
	
}

//Check for EMV signature for non pin verified
if (!$signature_needed && $is_integrated_credit_sale) {
    foreach ($payments as $payment_id => $payment) {
        if ($payment->cvm != 'PIN VERIFIED') {
            $signature_needed = TRUE;
            break;
        }
    }
}

if (isset($error_message)) {
    echo '<h1 style="text-align: center;">' . $error_message . '</h1>';
    exit;
}

?>

<table width="100%">
<tr>
    <td colspan="2" valign="top" align="left" style="width:40%;">
        <!-- from address-->
        <?php if ($company_logo) { ?>
            <p class="invoice-logo">
                <?php echo img(array('src' => secure_app_file_url($company_logo))); ?>
            </p>
        <?php } ?>
        <h4 class="company-title"><?php echo H($company); ?></h4>
				<?php if ($tax_id) {?>
        <h4 class="tax-id-title"><?php echo lang('common_tax_id').': '.H($tax_id); ?></h4>
				<?php } ?>
        <?php if(!$this->config->item('hide_location_name_on_receipt')){ ?>
        <?php if ($this->Location->count_all() > 1) { ?>
            <p><?php echo H($this->Location->get_info_for_key('name', isset($override_location_id) ? $override_location_id : FALSE)); ?></p>
        <?php } ?>
        <?php } ?>

        <p><?php echo nl2br(H($this->Location->get_info_for_key('address', isset($override_location_id) ? $override_location_id : FALSE))); ?></p>

        <p><?php echo H($this->Location->get_info_for_key('phone', isset($override_location_id) ? $override_location_id : FALSE)); ?></p>
        <?php if ($website) { ?>
            <p><?php echo H($website); ?></p>
        <?php } ?>
    </td>

    <td colspan="2" valign="top" align="center" style="width:30%;">
        <!--  sales-->

        <p>
            <?php if ($receipt_title && (!isset($sale_type) || $sale_type != lang('common_estimate'))) {?>
                <?php echo H($receipt_title); ?><?php echo ($total) < 0 ? ' (' . lang('sales_return') . ')' : ''; ?>
                <br>
            <?php } ?>
            <strong><?php echo H($transaction_time) ?></strong>
        </p>

        <p>
            <span><?php echo lang('common_sale_id') . ":"; ?></span>
            <?php echo H($sale_id); ?>
        </p>
        <?php if (isset($deleted) && $deleted) { ?>
            <p><span class="text-danger" style="color: #df6c6e;"><strong><?php echo lang('sales_deleted_voided'); ?></strong></span></p>
        <?php } ?>
        <?php if (isset($sale_type)) { ?>
            <p><?php echo H($sale_type); ?></p>
        <?php } ?>

        <?php if ($is_ecommerce) { ?>
            <p><?php echo lang('common_ecommerce'); ?></p>
        <?php } ?>

        <?php
        if ($this->Register->count_all(isset($override_location_id) ? $override_location_id : FALSE) > 1 && $register_name) {
            ?>
            <p><span><?php echo lang('common_register_name') . ': '; ?></span><?php echo H($register_name); ?></p>
        <?php
        }
        ?>

        <?php
        if ($tier) {
            ?>
            <p>
                <span><?php echo $this->config->item('override_tier_name') ? $this->config->item('override_tier_name') : lang('common_tier_name') . ':'; ?></span><?php echo H($tier); ?>
            </p>
        <?php
        }
        ?>

        <?php if (!$this->config->item('remove_employee_from_receipt')) { ?>
            <p><span><?php echo lang('common_employee') . ":"; ?></span><?php echo H($this->config->item('remove_employee_lastname_from_receipt')?$employee_firstname:$employee); ?></p>
            <?php
            foreach ($employee_custom_fields_to_display as $custom_field_id) {
                ?>
                <?php

                $employee_info = $this->Employee->get_info($sold_by_employee_id);

                if ($employee_info->{"custom_field_${custom_field_id}_value"}) {
                    ?>
                    <div class="invoice-desc">
                        <?php

                        if ($this->Employee->get_custom_field($custom_field_id, 'type') == 'checkbox') {
                            $format_function = 'boolean_as_string';
                        } elseif ($this->Employee->get_custom_field($custom_field_id, 'type') == 'date') {
                            $format_function = 'date_as_display_date';
                        } elseif ($this->Employee->get_custom_field($custom_field_id, 'type') == 'email') {
                            $format_function = 'strsame';
                        } elseif ($this->Employee->get_custom_field($custom_field_id, 'type') == 'url') {
                            $format_function = 'strsame';
                        } elseif ($this->Employee->get_custom_field($custom_field_id, 'type') == 'phone') {
                            $format_function = 'strsame';
                        } 
												elseif($this->Employee->get_custom_field($custom_field_id, 'type') == 'image')
												{
													$format_function = 'file_id_to_image_thumb_right';
												}
												else {
                            $format_function = 'strsame';
                        }

                        echo '<p><span>' . lang('common_employee') . ' ' . ($this->Employee->get_custom_field($custom_field_id, 'hide_field_label') ? '' : $this->Employee->get_custom_field($custom_field_id, 'name') . ':') . '</span> ' . $format_function($employee_info->{"custom_field_${custom_field_id}_value"}) . '</p>';
                        ?>
                    </div>
                <?php
                }
            }
            ?>
        <?php } ?>
        <?php
        if (H($this->Location->get_info_for_key('enable_credit_card_processing', isset($override_location_id) ? $override_location_id : FALSE))) {
					
					if (!$this->config->item('hide_merchant_id_from_receipt'))
					{
            echo '<p id="merchant_id"><span>' . lang('common_merchant_id') . ':</span> ' . H($this->Location->get_merchant_id(isset($override_location_id) ? $override_location_id : FALSE)) . '</p>';
					}
				}
        ?>
    </td>

    <td colspan="2" valign="top" align="right" style="width:30%;">
        <!-- to address-->
        <?php if (isset($customer)) { ?>
            <?php if (!$this->config->item('remove_customer_name_from_receipt')) { ?>
                <p class="invoice-to"><?php echo lang('sales_invoice_to'); ?>:</p>
                <p><?php echo lang('common_customer') . ": " . H($customer); ?></p>

            <?php } ?>

            <?php if (!$this->config->item('remove_customer_company_from_receipt')) { ?>
                <?php if (!empty($customer_company)) { ?>
                    <p><?php echo lang('common_company') . ": " . H($customer_company); ?></p><?php } ?>
            <?php } ?>

            <?php if (!$this->config->item('remove_customer_contact_info_from_receipt')) { ?>
                <?php if (!empty($customer_address_1) || !empty($customer_address_2)) { ?>
                    <p><?php echo lang('common_address'); ?>
                    : <?php echo H($customer_address_1 . ' ' . $customer_address_2); ?></p><?php } ?>
                <?php if (!empty($customer_city)) {
                    echo '<p>' . H($customer_city . ' ' . $customer_state . ', ' . $customer_zip) . '</p>';
                } ?>
                <?php if (!empty($customer_country)) {
                    echo '<p>' . H($customer_country) . '</p>';
                } ?>
                <?php if (!empty($customer_phone)) { ?><p><?php echo lang('common_phone_number'); ?>
                    : <?php echo H($customer_phone); ?></p><?php } ?>
								<?php if (!$this->config->item('hide_email_on_receipts')) { ?>
								
                <?php if (!empty($customer_email)) { ?><p><?php echo lang('common_email'); ?>
                    : <?php echo H($customer_email); ?></p><?php } ?>
           				 <?php } ?>
								<?php } ?>

            <?php
            foreach ($customer_custom_fields_to_display as $custom_field_id) {
                ?>
                <?php
                $customer_info = $this->Customer->get_info($customer_id);

                if ($customer_info->{"custom_field_${custom_field_id}_value"}) {
                    ?>
                    <div class="invoice-desc">
                        <?php

                        if ($this->Customer->get_custom_field($custom_field_id, 'type') == 'checkbox') {
                            $format_function = 'boolean_as_string';
                        } elseif ($this->Customer->get_custom_field($custom_field_id, 'type') == 'date') {
                            $format_function = 'date_as_display_date';
                        } elseif ($this->Customer->get_custom_field($custom_field_id, 'type') == 'email') {
                            $format_function = 'strsame';
                        } elseif ($this->Customer->get_custom_field($custom_field_id, 'type') == 'url') {
                            $format_function = 'strsame';
                        } elseif ($this->Customer->get_custom_field($custom_field_id, 'type') == 'phone') {
                            $format_function = 'strsame';
                        } 
                     	 	elseif ($this->Customer->get_custom_field($custom_field_id, 'type') == 'image')
												{
													$format_function = 'file_id_to_image_thumb_right';
												}
												else {
                            $format_function = 'strsame';
                        }

                        echo '<p>' . ($this->Customer->get_custom_field($custom_field_id, 'hide_field_label') ? '' : $this->Customer->get_custom_field($custom_field_id, 'name') . ':') . ' ' . $format_function($customer_info->{"custom_field_${custom_field_id}_value"}) . '</p>';
                        ?>
                    </div>
                <?php
                }
            }
            ?>
        <?php } ?>

    </td>
</tr>
<tr><td colspan="6"><br></td></tr>
<tr>
    <td colspan="3" valign="top" align="left">
        <!-- delivery address-->
        <?php if (isset($delivery_person_info)) { ?>
            <p class="invoice-to"><?php echo lang('deliveries_shipping_address'); ?>:</p>
            <p><?php echo lang('common_name') . ": " . H($delivery_person_info['first_name'] . ' ' . $delivery_person_info['last_name']); ?></p>

            <?php if (!empty($delivery_person_info['address_1']) || !empty($delivery_person_info['address_2'])) { ?>
                <p><?php echo lang('common_address'); ?>
                : <?php echo H($delivery_person_info['address_1'] . ' ' . $delivery_person_info['address_2']); ?></p><?php } ?>
            <?php if (!empty($delivery_person_info['city'])) {
                echo '<p>' . H($delivery_person_info['city'] . ' ' . $delivery_person_info['state'] . ', ' . $delivery_person_info['zip']) . '</p>';
            } ?>
            <?php if (!empty($delivery_person_info['country'])) {
                echo '<p>' . H($delivery_person_info['country']) . '</p>';
            } ?>
            <?php if (!empty($delivery_person_info['phone'])) { ?><p><?php echo lang('common_phone_number'); ?>
                : <?php echo H($delivery_person_info['phone']); ?></p><?php } ?>
            <?php if (!empty($delivery_person_info['email'])) { ?><p><?php echo lang('common_email'); ?>
                : <?php echo H($delivery_person_info['email']); ?></p><?php } ?>

        <?php } ?>
    </td>
    <td colspan="3" valign="top" align="right">
        <?php if (!empty($delivery_info['estimated_delivery_or_pickup_date']) || !empty($delivery_info['tracking_number']) || !empty($delivery_info['comment'])) { ?>
            <p class="invoice-to"><?php echo lang('deliveries_delivery_information'); ?>:</p>
            <?php if (!empty($delivery_info['estimated_delivery_or_pickup_date'])) { ?>
                <p><?php echo lang('deliveries_estimated_delivery_or_pickup_date'); ?>
                : <?php echo date(get_date_format() . ' ' . get_time_format(), strtotime($delivery_info['estimated_delivery_or_pickup_date'])); ?></p><?php } ?>
            <?php if (!empty($delivery_info['tracking_number'])) { ?>
                <p><?php echo lang('deliveries_tracking_number'); ?>
                : <?php echo H($delivery_info['tracking_number']); ?></p><?php } ?>
            <?php if (!empty($delivery_info['comment'])) { ?><p><?php echo lang('common_comment'); ?>
                : <?php echo H($delivery_info['comment']); ?></p><?php } ?>
        <?php } ?>
    </td>
</tr>
<tr><td colspan="6"><br></td></tr>
</table>

<table style="width:100%">
    <?php
    $x_col = 6;
    $xs_col = 4;
    if ($discount_exists) {
        $x_col = 4;
        $xs_col = 3;

        if ($this->config->item('wide_printer_receipt_format')) {
            $x_col = 4;
            $xs_col = 2;
        }
    } else {
        if ($this->config->item('wide_printer_receipt_format')) {
            $x_col = 6;
            $xs_col = 2;
        }
    }
    ?>

    <!-- invoice heading-->
    <tr>
        <td colspan="4"><p class="invoice-head item-name"><?php echo lang('common_item_name'); ?></p></td>
    </tr>
    <tr>
        <td align="right" style="width:300px;">
            <p class="invoice-head text-right item-price"><?php echo lang('common_price') . ($this->config->item('show_tax_per_item_on_receipt') ? '/' . lang('common_tax') : ''); ?></p>
        </td>
        <td align="right"><p class="invoice-head text-right item-qty"><?php echo lang('common_quantity'); ?></p></td>
        <td align="right">
        <?php if ($discount_exists) { ?>
            <p class="invoice-head text-right item-discount"><?php echo lang('common_discount_percent'); ?></p>
        <?php } ?>
        </td>
        <td align="right">
            <p class="invoice-head pull-right item-total gift_receipt_element"><?php echo lang('common_total') . ($this->config->item('show_tax_per_item_on_receipt') ? '/' . lang('common_tax') : ''); ?></p>
        </td>
    </tr>


<?php
if ($discount_item_line = $cart->get_index_for_flat_discount_item()) {
    $discount_item = $cart->get_item($discount_item_line);
    $cart->delete_item($discount_item_line);
    $cart->add_item($discount_item, false);
    $cart_items = $cart->get_items();
}

$number_of_items_sold = 0;
$number_of_items_returned = 0;

foreach (array_reverse($cart_items, true) as $line => $item) {
    if ($item->tax_included) {
        if (get_class($item) == 'PHPPOSCartItemSale') {
            if ($item->tax_included) {
                $this->load->helper('items');
                $unit_price = to_currency_no_money(get_price_for_item_including_taxes($item->item_id, $item->unit_price));
                $price_including_tax = $unit_price;
                $price_excluding_tax = get_price_for_item_excluding_taxes($item->item_id, $unit_price);
            }
        } else {
            if ($item->tax_included) {
                $this->load->helper('item_kits');
                $unit_price = to_currency_no_money(get_price_for_item_kit_including_taxes($item->item_kit_id, $item->unit_price));
                $price_including_tax = $unit_price;
                $price_excluding_tax = get_price_for_item_kit_excluding_taxes($item->item_kit_id, $unit_price);
            }
        }
    } else {
        $unit_price = $item->unit_price;

        //item
        if (get_class($item) == 'PHPPOSCartItemSale') {
            $this->load->helper('items');
            $price_excluding_tax = $unit_price;
            $price_including_tax = get_price_for_item_including_taxes($item->item_id, $item->unit_price);

        } else //Kit
        {
            $this->load->helper('item_kits');
            $price_excluding_tax = $unit_price;
            $price_including_tax = get_price_for_item_kit_including_taxes($item->item_kit_id, $item->unit_price);
        }
    }
    $price_including_tax = $price_including_tax * (1 - ($item->discount / 100));
    $price_excluding_tax = $price_excluding_tax * (1 - ($item->discount / 100));
    $item_tax_amount = ($price_including_tax - $price_excluding_tax);

    if ($item->quantity > 0 && $item->name != lang('common_store_account_payment') && $item->name != lang('common_discount') && $item->name != lang('common_refund') && $item->name != lang('common_fee')) {
        $number_of_items_sold = $number_of_items_sold + $item->quantity;
    } elseif ($item->quantity < 0 && $item->name != lang('common_store_account_payment') && $item->name != lang('common_discount') && $item->name != lang('common_refund') && $item->name != lang('common_fee')) {
        $number_of_items_returned = $number_of_items_returned + abs($item->quantity);
    }

    $item_number_for_receipt = false;

    if ($this->config->item('show_item_id_on_receipt')) {
        switch ($this->config->item('id_to_show_on_sale_interface')) {
            case 'number':
                $item_number_for_receipt = property_exists($item,'item_number') ? H($item->item_number) : H($item->item_kit_number);
                break;

            case 'product_id':
                $item_number_for_receipt = property_exists($item,'product_id') ? H($item->product_id) : '';
                break;

            case 'id':
                $item_number_for_receipt = property_exists($item,'item_id') ? H($item->item_id) : 'KIT ' . H($item->item_kit_id);
                break;

            default:
                $item_number_for_receipt = property_exists($item,'item_number') ? H($item->item_number) : H($item->item_kit_number);
                break;
        }
    }

    ?>
    <!-- invoice items-->
    <tr>
    	<td colspan="4">
           <?php echo H($item->name); ?>
                        <?php if ($item_number_for_receipt) { ?> - <?php echo $item_number_for_receipt; ?><?php } ?><?php if ($item->size) { ?> (<?php echo H($item->size); ?>)<?php } ?>


                    <div class="invoice-desc">
                        <?php
                        echo isset($item->variation_name) && $item->variation_name ? H($item->variation_name) : '';
                        ?>
                    </div>

										<?php
										if (property_exists($item,'quantity_unit_quantity') && $item->quantity_unit_quantity !== NULL){?>
                  	<div class="invoice-desc">
												<?php echo lang('common_quantity_unit_name'). ': '.$item->quantity_units[$item->quantity_unit_id].', '.lang('common_quantity_units').': ' .H(to_quantity($item->quantity_unit_quantity)); ?>
											</div>
										
										<?php } ?>

                    
                    <?php if (!$this->config->item('hide_desc_on_receipt') && !$item->description == "") { ?>
                      <div class="invoice-desc">
                          <?php  echo clean_html($item->description); ?>
                      </div>
                    <?php } ?>
                   

                    
                    <?php if (isset($item->serialnumber) && $item->serialnumber != "") { ?>
                      <div class="invoice-desc">
                      	<?php echo H($item->serialnumber); ?>
                      </div>
                    <?php } ?>
                    

					<?php
					$can_display_image = $this->config->item('show_images_on_receipt') && $item->main_image_id;
					if ($can_display_image) {
					?>
						<div class="invoice-desc">
							<?php
							echo img(array(
								'width' => ($this->config->item('show_images_on_receipt_width_percent') ? $this->config->item('show_images_on_receipt_width_percent') : '25') . '%',
								'src' => secure_app_file_url($item->main_image_id)
							));
							?>
						</div>
					<?php
					}
					?>

                    <?php
                    foreach ($item_custom_fields_to_display as $custom_field_id) {
                        ?>
                        <?php
                        if (get_class($item) == 'PHPPOSCartItemSale' && $this->Item->get_custom_field($custom_field_id) !== false) {
                            $item_info = $this->Item->get_info($item->item_id);

                            if ($item_info->{"custom_field_${custom_field_id}_value"}) {
                                ?>
                                <div class="invoice-desc">
                                    <?php

                                    if ($this->Item->get_custom_field($custom_field_id, 'type') == 'checkbox') {
                                        $format_function = 'boolean_as_string';
                                    } elseif ($this->Item->get_custom_field($custom_field_id, 'type') == 'date') {
                                        $format_function = 'date_as_display_date';
                                    } elseif ($this->Item->get_custom_field($custom_field_id, 'type') == 'email') {
                                        $format_function = 'strsame';
                                    } elseif ($this->Item->get_custom_field($custom_field_id, 'type') == 'url') {
                                        $format_function = 'strsame';
                                    } elseif ($this->Item->get_custom_field($custom_field_id, 'type') == 'phone') {
                                        $format_function = 'strsame';
																		}
																		elseif ($this->Item->get_custom_field($custom_field_id, 'type') == 'image')
																		{
																			$format_function = 'file_id_to_image_thumb_right';
																		}
																		 else {
                                        $format_function = 'strsame';
                                    }

                                    echo ($this->Item->get_custom_field($custom_field_id, 'hide_field_label') ? '' : $this->Item->get_custom_field($custom_field_id, 'name') . ':') . ' ' . $format_function($item_info->{"custom_field_${custom_field_id}_value"});
                                    ?>
                                </div>
                            <?php
                            }
                        }
                    }

                    foreach ($item_kit_custom_fields_to_display as $custom_field_id) {
                        if (get_class($item) == 'PHPPOSCartItemKitSale' && $this->Item_kit->get_custom_field($custom_field_id) !== false && $this->Item_kit->get_custom_field($custom_field_id) !== false) {
                            $item_info = $this->Item_kit->get_info($item->item_kit_id);

                            if ($item_info->{"custom_field_${custom_field_id}_value"}) {
                                ?>
                                <div class="invoice-desc">
                                    <?php

                                    if ($this->Item_kit->get_custom_field($custom_field_id, 'type') == 'checkbox') {
                                        $format_function = 'boolean_as_string';
                                    } elseif ($this->Item_kit->get_custom_field($custom_field_id, 'type') == 'date') {
                                        $format_function = 'date_as_display_date';
                                    } elseif ($this->Item_kit->get_custom_field($custom_field_id, 'type') == 'email') {
                                        $format_function = 'strsame';
                                    } elseif ($this->Item_kit->get_custom_field($custom_field_id, 'type') == 'url') {
                                        $format_function = 'strsame';
                                    } elseif ($this->Item_kit->get_custom_field($custom_field_id, 'type') == 'phone') {
                                        $format_function = 'strsame';
                                   
																	  } 
																		elseif ($this->Item_kit->get_custom_field($custom_field_id, 'type') == 'phone') {
																			$format_function = 'file_id_to_image_thumb_right';
																	 	}
																		else {
                                        $format_function = 'strsame';
                                    }

                                    echo ($this->Item_kit->get_custom_field($custom_field_id, 'hide_field_label') ? '' : $this->Item_kit->get_custom_field($custom_field_id, 'name') . ':') . ' ' . $format_function($item_info->{"custom_field_${custom_field_id}_value"});
                                    ?>
                                </div>
                            <?php
                            }
                        }
                        ?>
                    <?php
                    }


                    if (isset($item->rule['type'])) {

                        echo '<br class="gift_receipt_element"><i class="gift_receipt_element">' . H($item->rule['name']) . '</i>';
                        if (isset($item->rule['rule_discount'])) {
                            echo '<br class="gift_receipt_element"><i class="gift_receipt_element"><u class="gift_receipt_element">' . lang('common_discount') . '</u>: ' . to_currency($item->rule['rule_discount']) . '</i>';
                        }
                    }

                    ?>
    </td>

    </tr><!-- end item name row -->
    <tr>
    		<td align="right">



                    <?php if ($this->config->item('show_orig_price_if_marked_down_on_receipt') && $item->regular_price > $unit_price) { ?>
                        <span class="strikethrough"><?php echo to_currency($item->regular_price, 10); ?></span>
                    <?php } ?>

                    <?php echo to_currency($unit_price + $item->get_modifier_unit_total(), 10) . ($this->config->item('show_tax_per_item_on_receipt') ? '/' . to_currency($item_tax_amount) : ''); ?>
        </td>
        <td align="right">

                    <?php
                    if ($this->config->item('number_of_decimals_for_quantity_on_receipt') && floor($item->quantity) != $item->quantity) {
                        echo to_currency_no_money($item->quantity, $this->config->item('number_of_decimals_for_quantity_on_receipt'));
                    } else {
                        echo to_quantity($item->quantity);
                    }
                    ?>
            </td>
        <td align="right">
            <?php if ($discount_exists) { ?>
                    <div class="invoice-content item-discount text-right"><?php echo to_quantity($item->discount); ?></div>
            <?php } ?>
        </td>

        <td align="right">
            <?php
                if ($this->config->item('indicate_taxable_on_receipt') && $item->taxable && !empty($taxes)) {
                    echo '<small>*' . lang('common_taxable') . '</small>';
                }
            ?>
            <?php 
                if ($this->config->item('indicate_non_taxable_on_receipt') && !($item->taxable && !empty($taxes))) {
                    $label = lang('common_no_tax');
                    if($this->config->item('override_symbol_non_taxable') != "")
                    $label = $this->config->item('override_symbol_non_taxable');
                    echo '<small>*' .$label. '</small>';
                }
            ?>
            <?php echo to_currency(+$item->get_modifiers_subtotal() + ($unit_price * $item->quantity - $unit_price * $item->quantity * $item->discount / 100), 10) . ($this->config->item('show_tax_per_item_on_receipt') ? '/' . to_currency($item_tax_amount * $item->quantity) : ''); ?>
        </td>

</tr>
<tr><td colspan="4"><hr style="margin:0;padding:0;"></td></tr>
<?php } ?>

<?php
foreach ($sale_custom_fields_to_display as $custom_field_id) {
    if ($this->Sale->get_custom_field($custom_field_id) !== false && $this->Sale->get_custom_field($custom_field_id) !== false) {
        if ($cart->{"custom_field_${custom_field_id}_value"}) {
            ?>
            <?php

            if ($this->Sale->get_custom_field($custom_field_id, 'type') == 'checkbox') {
                $format_function = 'boolean_as_string';
            } elseif ($this->Sale->get_custom_field($custom_field_id, 'type') == 'date') {
                $format_function = 'date_as_display_date';
            } elseif ($this->Sale->get_custom_field($custom_field_id, 'type') == 'email') {
                $format_function = 'strsame';
            } elseif ($this->Sale->get_custom_field($custom_field_id, 'type') == 'url') {
                $format_function = 'strsame';
            } 
						elseif ($this->Sale->get_custom_field($custom_field_id, 'type') == 'phone') 
						{
                $format_function = 'strsame';
            } 
						elseif($this->Sale->get_custom_field($custom_field_id, 'type') == 'image')
						{
							$format_function = 'file_id_to_image_thumb_right';
						}
						else 
						{
                $format_function = 'strsame';
            }
            ?>
            <tr>
                <td colspan="2">
                <div class="invoice-content-heading"><?php
                    if (!$this->Sale->get_custom_field($custom_field_id, 'hide_field_label')) {
                        echo $this->Sale->get_custom_field($custom_field_id, 'name');
                    } else {
                        echo $format_function($cart->{"custom_field_${custom_field_id}_value"});
                    }

                    ?>
                </div>
                <div class="invoice-desc"><?php
                    if (!$this->Sale->get_custom_field($custom_field_id, 'hide_field_label')) {
                        echo $format_function($cart->{"custom_field_${custom_field_id}_value"});
                    }
                    ?>
                </div>
                </td>
                <td colspan="2"></td>
            </tr>
        <?php
        }
    }
    ?>
<?php
}
?>

<?php
foreach($work_order_custom_fields_to_display as $custom_field_id)
{
	if($this->Work_order->get_custom_field($custom_field_id) !== false && $this->Work_order->get_custom_field($custom_field_id) !== false)
	{
		if ($cart->{"work_order_custom_field_${custom_field_id}_value"})
		{
			if ($this->Work_order->get_custom_field($custom_field_id,'type') == 'checkbox')
			{
				$format_function = 'boolean_as_string';
			}
			elseif($this->Work_order->get_custom_field($custom_field_id,'type') == 'date')
			{
				$format_function = 'date_as_display_date';				
			}
			elseif($this->Work_order->get_custom_field($custom_field_id,'type') == 'email')
			{
				$format_function = 'strsame';					
			}
			elseif($this->Work_order->get_custom_field($custom_field_id,'type') == 'url')
			{
				$format_function = 'strsame';					
			}
			elseif($this->Work_order->get_custom_field($custom_field_id,'type') == 'phone')
			{
				$format_function = 'strsame';					
			}
			elseif($this->Work_order->get_custom_field($custom_field_id,'type') == 'image')
			{
				$this->load->helper('url');
				$format_function = 'file_id_to_image_thumb_right';					
			}
			elseif($this->Work_order->get_custom_field($custom_field_id,'type') == 'file')
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
                <td colspan="2">
                <div class="invoice-content-heading"><?php
                    if (!$this->Work_order->get_custom_field($custom_field_id, 'hide_field_label')) {
                        echo $this->Work_order->get_custom_field($custom_field_id, 'name');
                    } else {
                        echo $format_function($cart->{"work_order_custom_field_${custom_field_id}_value"});
                    }

                    ?>
                </div>
                <div class="invoice-desc"><?php
                    if (!$this->Work_order->get_custom_field($custom_field_id, 'hide_field_label')) {
                        echo $format_function($cart->{"work_order_custom_field_${custom_field_id}_value"});
                    }
                    ?>
                </div>
                </td>
                <td colspan="2"></td>
            </tr>
        <?php
    	}
	}
	?>
<?php
}
?>





















<!-- end item panel -->

<!-- subtotal -->
<?php if ($exchange_name) { ?>

    <tr>
        <td colspan="3" align="right">
            <div class="invoice-footer-heading"><?php echo lang('common_exchange_to') . ' ' . H($exchange_name); ?></div>
        </td>
        <td align="right">
            <div class="invoice-footer-value">x <?php echo to_currency_no_money($exchange_rate); ?></div>
        </td>
    </tr>

<?php } ?>

<tr>
    <td colspan="3" align="right">
        <div class="invoice-footer-heading"><?php echo lang('common_sub_total'); ?></div>
    </td>

    <td align="right">
        <div class="invoice-footer-value">
            <?php if (isset($exchange_name) && $exchange_name) {
                echo to_currency_as_exchange($cart, $subtotal);
                ?>
            <?php } else { ?>
                <?php echo to_currency($subtotal); ?>
            <?php
            }
            ?>
        </div>
    </td>
</tr>

<?php $total_tax_amount = 0; if ($this->config->item('group_all_taxes_on_receipt')) { ?>
    <?php
    $total_tax = 0;
    foreach ($taxes as $name => $value) {
        $total_tax += $value;
    }
    ?>
    <tr>
        <td colspan="3" align="right">
            <div class="invoice-footer-heading"><?php echo lang('common_tax'); ?></div>
        </td>

        <td align="right">
            <div class="invoice-footer-value">
                <?php if (isset($exchange_name) && $exchange_name) {
                    echo $total_tax_amount = to_currency_as_exchange($cart, $total_tax * $exchange_rate);
                    ?>
                <?php } else { ?>
                    <?php echo $total_tax_amount = to_currency($total_tax * $exchange_rate); ?>
                <?php
                }
                ?>

            </div>
        </td>
    </tr>

<?php } else { ?>
    <?php $total_tax = 0; foreach ($taxes as $name => $value) { $total_tax += $value; ?>
        <tr >
            <td colspan="3" align="right">
                <div class="invoice-footer-heading"><?php echo H($name); ?></div>
            </td>
            <td align="right">
                <div class="invoice-footer-value">


                    <?php if (isset($exchange_name) && $exchange_name) {
                        echo to_currency_as_exchange($cart, $value * $exchange_rate);
                        ?>
                    <?php } else { ?>
                        <?php echo to_currency($value); ?>
                    <?php
                    }
                    ?>


                </div>
            </td>
        </tr>
    <?php }
    $total_tax_amount = to_currency($total_tax);
 } ?>
<tr>
    <td colspan="3" align="right">
        <div class="invoice-footer-heading"><?php echo lang('common_total'); ?></div>
    </td>

    <td align="right">
        <div class="invoice-footer-value invoice-total">
            <?php if (isset($exchange_name) && $exchange_name) {
                ?>
                <?php echo $total_invoice_amount = $this->config->item('round_cash_on_sales') && $is_sale_cash_payment ? to_currency_as_exchange($cart, round_to_nearest_05($total)) : to_currency_as_exchange($cart, $total); ?>
            <?php } else { ?>
                <?php echo $total_invoice_amount = $this->config->item('round_cash_on_sales') && $is_sale_cash_payment ? to_currency(round_to_nearest_05($total)) : to_currency($total); ?>
            <?php
            }
            ?>

        </div>
    </td>
</tr>

<tr>
    <?php if ($number_of_items_sold) { ?>
        <td colspan="3" align="right">
            <div class="invoice-footer-heading"><?php echo lang('common_items_sold'); ?></div>
        </td>
        <td align="right">
            <div class="invoice-footer-value invoice-total"><?php echo to_quantity($number_of_items_sold); ?></div>
        </td>
    <?php } ?>

    <?php if ($number_of_items_returned) { ?>

        <td colspan="3" align="right">
            <div class="invoice-footer-heading"><?php echo lang('common_items_returned'); ?></div>
        </td>
        <td align="right">
            <div class="invoice-footer-value invoice-total"><?php echo to_quantity($number_of_items_returned); ?></div>
        </td>
    <?php } ?>
</tr>

<?php
foreach ($payments as $payment_id => $payment) {
    ?>
    <tr>
        <td colspan="3" align="right">
            <div class="invoice-footer-heading"><?php echo (isset($show_payment_times) && $show_payment_times) ? date(get_date_format() . ' ' . get_time_format(), strtotime($payment->payment_date)) : lang('common_payment'); ?></div>

            <?php if (($is_integrated_credit_sale || sale_has_partial_credit_card_payment($cart) || $is_sale_integrated_ebt_sale || sale_has_partial_ebt_payment($cart)) && ($payment->payment_type == lang('common_credit') || $payment->payment_type == lang('sales_partial_credit') || $payment->payment_type == lang('common_ebt') || $payment->payment_type == lang('common_partial_ebt') || $payment->payment_type == lang('common_ebt_cash') || $payment->payment_type == lang('common_partial_ebt_cash'))) { ?>
                <div class="invoice-footer-value"><?php echo $is_sale_integrated_ebt_sale ? 'EBT ' : ''; ?><?php echo H($payment->card_issuer . ': ' . $payment->truncated_card); ?></div>
            <?php } else { ?>
                <div class="invoice-footer-value">&nbsp;<?php $splitpayment = explode(':', $payment->payment_type); echo H($splitpayment[0]); ?></div>
            <?php } ?>
        </td>

        <td align="right">
            <div class="invoice-footer-value invoice-payment">
                <?php
                if (isset($exchange_name) && $exchange_name) {
                    ?>
                    <?php echo $this->config->item('round_cash_on_sales') && $payment->payment_type == lang('common_cash') ? to_currency_as_exchange($cart, round_to_nearest_05($payment->payment_amount)) : to_currency_as_exchange($cart, $payment->payment_amount); ?>
                <?php } else { ?>
                    <?php echo $this->config->item('round_cash_on_sales') && $payment->payment_type == lang('common_cash') ? to_currency(round_to_nearest_05($payment->payment_amount)) : to_currency($payment->payment_amount); ?>
                <?php
                }

                ?>
            </div>
        </td>
</tr>
    <tr>
        <?php if (($is_integrated_credit_sale || sale_has_partial_credit_card_payment($cart) || $is_sale_integrated_ebt_sale || sale_has_partial_ebt_payment($cart)) && ($payment->payment_type == lang('common_credit') || $payment->payment_type == lang('sales_partial_credit') || $payment->payment_type == lang('common_ebt') || $payment->payment_type == lang('common_partial_ebt') || $payment->payment_type == lang('common_ebt_cash') || $payment->payment_type == lang('common_partial_ebt_cash'))) { ?>

            <td colspan="4">
                <?php if ($payment->entry_method) { ?>
                    <div class="invoice-footer-value invoice-footer-value-cc"><?php echo lang('sales_entry_method') . ': ' . H($payment->entry_method); ?></div>
                <?php } ?>

                <?php if ($payment->tran_type) { ?>
                    <div class="invoice-footer-value invoice-footer-value-cc"><?php echo lang('sales_transaction_type') . ': ' . ($is_sale_integrated_ebt_sale ? 'EBT ' : '') . H($payment->tran_type); ?></div>
                <?php } ?>

                <?php if ($payment->application_label) { ?>
                    <div class="invoice-footer-value invoice-footer-value-cc"><?php echo lang('sales_application_label') . ': ' . H($payment->application_label); ?></div>
                <?php } ?>

                <?php if ($payment->ref_no) { ?>
                    <div class="invoice-footer-value invoice-footer-value-cc"><?php echo lang('sales_ref_no') . ': ' . H($payment->ref_no); ?></div>
                <?php } ?>
                <?php if ($payment->auth_code) { ?>
                    <div class="invoice-footer-value invoice-footer-value-cc"><?php echo lang('sales_auth_code') . ': ' . H($payment->auth_code); ?></div>
                <?php } ?>

                <?php if ($payment->aid) { ?>
                    <div class="invoice-footer-value invoice-footer-value-cc"><?php echo 'AID: ' . H($payment->aid); ?></div>
                <?php } ?>

                <?php if ($payment->tvr) { ?>
                    <div class="invoice-footer-value invoice-footer-value-cc"><?php echo 'TVR: ' . H($payment->tvr); ?></div>
                <?php } ?>


                <?php if ($payment->tsi) { ?>
                    <div class="invoice-footer-value invoice-footer-value-cc"><?php echo 'TSI: ' . H($payment->tsi); ?></div>
                <?php } ?>


                <?php if ($payment->arc) { ?>
                    <div class="invoice-footer-value invoice-footer-value-cc"><?php echo 'ARC: ' . H($payment->arc); ?></div>
                <?php } ?>

                <?php if ($payment->cvm) { ?>
                    <div class="invoice-footer-value invoice-footer-value-cc"><?php echo 'CVM: ' . H($payment->cvm); ?></div>
                <?php } ?>
            </td>
        <?php } ?>
    </tr>
<?php
}
?>

<?php foreach ($payments as $payment) { ?>
    <?php if (strpos($payment->payment_type, lang('common_giftcard')) === 0) { ?>
        <?php $giftcard_payment_row = explode(':', $payment->payment_type); ?>

        <tr>
            <td colspan="2" align="right">
                <div class="invoice-footer-heading"><?php echo lang('sales_giftcard_balance'); ?></div>
            </td>
            <td align="right">
                <div class="invoice-footer-value"><?php echo H($payment->payment_type); ?></div>
            </td>
            <td align="right">
                <div class="invoice-footer-value invoice-payment"><?php echo to_currency($this->Giftcard->get_giftcard_value(end($giftcard_payment_row))); ?></div>
            </td>
        </tr>
    <?php } ?>
<?php } ?>

<?php
foreach ($integrated_gift_card_balances as $integrated_giftcard_number => $balance) {
    ?>
    <tr>
        <td colspan="2" align="right">
            <div class="invoice-footer-heading"><?php echo lang('sales_giftcard_balance'); ?></div>
        </td>
        <td align="right">
            <div class="invoice-footer-value"><?php echo H($integrated_giftcard_number); ?></div>
        </td>
        <td align="right">
            <div class="invoice-footer-value invoice-payment"><?php echo to_currency($balance); ?></div>
        </td>
    </tr>
<?php } ?>

<?php if ($amount_change >= 0) { ?>
    <tr>
        <td colspan="3" align="right">
            <div class="invoice-footer-heading"><?php echo lang('common_change_due'); ?></div>
        </td>

        <td align="right">
            <div class="invoice-footer-value invoice-total">
                <?php if (isset($exchange_name) && $exchange_name) {
                    $amount_change_default_currency = $amount_change * pow($exchange_rate, -1);

                    ?>

                    <?php
                    if ($amount_change_default_currency != $amount_change) {
                        ?>
                        <?php echo $this->config->item('round_cash_on_sales') && $is_sale_cash_payment ? to_currency_as_exchange($cart, round_to_nearest_05($amount_change)) : to_currency_as_exchange($cart, $amount_change); ?>
                        <br/><?php echo lang('common_or'); ?><br/>
                    <?php
                    }
                    ?>
                    <?php echo $this->config->item('round_cash_on_sales') && $is_sale_cash_payment ? to_currency(round_to_nearest_05($amount_change_default_currency)) : to_currency($amount_change_default_currency); ?>

                <?php } else { ?>
                    <?php echo $this->config->item('round_cash_on_sales') && $is_sale_cash_payment ? to_currency(round_to_nearest_05($amount_change)) : to_currency($amount_change); ?>
                <?php
                }
                ?>
            </div>
        </td>
    </tr>
<?php
} else {
    ?>
    <?php if (!$is_ecommerce) { ?>
        <tr>
            <td colspan="3" align="right">
                <div class="invoice-footer-heading"><?php echo lang('common_amount_due'); ?></div>
            </td>

            <td align="right">
                <div class="invoice-footer-value invoice-total">
                    <?php if (isset($exchange_name) && $exchange_name) {
                        ?>
                        <?php echo $this->config->item('round_cash_on_sales') && $is_sale_cash_payment ? to_currency_as_exchange($cart, round_to_nearest_05($amount_change * -1)) : to_currency_as_exchange($cart, $amount_change * -1); ?>
                    <?php } else { ?>
                        <?php echo $this->config->item('round_cash_on_sales') && $is_sale_cash_payment ? to_currency(round_to_nearest_05($amount_change * -1)) : to_currency($amount_change * -1); ?>
                    <?php
                    }
                    ?>
                </div>
            </td>
        </tr>
    <?php
    }
}
?>

<?php if (isset($ebt_balance) && ($ebt_balance) !== FALSE) { ?>
    <tr>
        <td colspan="3" align="right">
            <div class="invoice-footer-heading"><?php echo lang('sales_ebt_balance_amount'); ?></div>
        </td>
        <td align="right">
            <div class="invoice-footer-value invoice-total"><?php echo to_currency($ebt_balance); ?></div>
        </td>
    </tr>
<?php
}
?>

<?php if (isset($customer_balance_for_sale) && (float)$customer_balance_for_sale && !$this->config->item('hide_store_account_balance_on_receipt')) { ?>
    <tr>
        <td colspan="3" align="right">
            <div class="invoice-footer-heading"><?php echo lang('sales_customer_account_balance'); ?></div>
        </td>
        <td align="right">
            <div class="invoice-footer-value invoice-total"><?php echo to_currency($customer_balance_for_sale); ?></div>
        </td>
    </tr>
<?php
}
?>

<?php if (!$disable_loyalty && $this->config->item('enable_customer_loyalty_system') && isset($sales_until_discount) && !$this->config->item('hide_sales_to_discount_on_receipt') && $this->config->item('loyalty_option') == 'simple') { ?>
    <tr>
        <td colspan="3" align="right">
            <div class="invoice-footer-heading"><?php echo lang('common_sales_until_discount'); ?></div>
        </td>
        <td align="right">
            <div class="invoice-footer-value invoice-total"><?php echo $sales_until_discount <= 0 ? lang('sales_redeem_discount_for_next_sale') : to_quantity($sales_until_discount); ?></div>
        </td>
    </tr>
<?php
}
?>


<?php if (!$disable_loyalty && $this->config->item('enable_customer_loyalty_system') && isset($customer_points) && !$this->config->item('hide_points_on_receipt') && $this->config->item('loyalty_option') == 'advanced') { ?>
    <tr>
        <td colspan="3" align="right">
            <div class="invoice-footer-heading"><?php echo lang('common_points'); ?></div>
        </td>
        <td align="right">
            <div class="invoice-footer-value invoice-total"><?php echo to_quantity($customer_points); ?></div>
        </td>
    </tr>
<?php
}
?>


<?php
if ($ref_no) { ?>
    <tr>
        <td colspan="3" align="right">
            <div class="invoice-footer-heading"><?php echo lang('sales_ref_no'); ?></div>
        </td>
        <td align="right">
            <div class="invoice-footer-value invoice-total"><?php echo H($ref_no); ?></div>
        </td>
    </tr>
<?php
}
if (isset($auth_code) && $auth_code) { ?>
    <tr>
        <td colspan="3" align="right">
            <div class="invoice-footer-heading"><?php echo lang('sales_auth_code'); ?></div>
        </td>
        <td align="right">
            <div class="invoice-footer-value invoice-total"><?php echo H($auth_code); ?></div>
        </td>
    </tr>
<?php
}
?>

<?php
if ($this->config->item('show_total_discount_on_receipt')) { ?>
    <tr>
        <td colspan="3" align="right">
            <div class="invoice-footer-heading"><?php echo lang('sales_total_discount'); ?></div>
        </td>
        <td align="right">
            <div class="invoice-footer-value invoice-total"><?php echo to_currency($cart->get_total_discount()); ?></div>
        </td>
    </tr>
<?php
}
?>
<tr><td colspan="4"><br></td></tr>
<tr>
    <td colspan="4" align="center">
        <div class="text-center">
            <?php if ($show_comment_on_receipt == 1) {
                echo H($comment);
            }
            ?>
        </div>
    </td>
</tr>

<!-- invoice footer-->

<tr>
    <td colspan="4" align="center">
        <div class="invoice-policy">
            <?php echo nl2br(H($return_policy)); ?>
        </div>
    </td>
</tr>

<tr>
    <td colspan="4" align="center">
        <div class="invoice-policy">
            <?php echo nl2br($this->config->item('announcement_special')); ?>
        </div>
    </td>
</tr>

<!--<tr style="display: none;">
        <td colspan="4" align="center" id="receipt_type_label" class="receipt_type_label invoice-policy">
            <?php echo lang('sales_merchant_copy'); ?>
        </td>
</tr>-->

<?php
if(isset($signature_file_id) && $signature_file_id)
{
?>
    <tr>
        <td colspan="4" align="center" id='barcode' class="invoice-policy" >
            <?php echo "<img src='" . app_file_url($signature_file_id)."'alt=''/>"; ?>
        </td>
    </tr>

<?php
}
?>
<?php if (!$this->config->item('hide_barcode_on_sales_and_recv_receipt')) { ?>
    <tr>
        <td colspan="4" align="center" id='barcode' class="invoice-policy" >
            <?php echo "<img src='" . site_url('barcode/index/png') . "?barcode=$sale_id&text=$sale_id' alt=''/>"; ?>
        </td>
    </tr>
<?php } ?>

<?php if ($this->config->item('show_qr_code_for_sale')  && !$store_account_payment) {?>
	<div id='qrcode' class="invoice-policy">
    <?php 
        $qrcode = '';
        if ($this->config->item('qr_code_format') == 'sale_summary_info') { 
            $qrcode_info = array(
                lang('common_company').': '.$this->config->item('company'),
                lang('common_tax_id').': '.$this->config->item('tax_id'),
                lang('sales_invoice_date').': '.H($transaction_time),
                lang('common_total', '', array(), TRUE).': '.strip_tags($total_invoice_amount),
                lang('common_tax', '', array(), TRUE).': '.strip_tags($total_tax_amount)
            );
            $qrcode = implode(",".PHP_EOL, $qrcode_info);
        } else if ($this->config->item('qr_code_format') == 'saudi_arabia_digital_receipt'){

            require_once(APPPATH . "libraries/Tlvstr.php");

            $qrdata = array(
                'seller_name' => $this->config->item('company') ? $this->config->item('company') : '', 
                'tax_number' => $this->config->item('tax_id') ? $this->config->item('tax_id') : '',
				'invoice_date' => date(DATE_ISO8601,strtotime($transaction_time)),
                'invoice_total_amount' => make_currency_no_money(strip_tags($total_invoice_amount)),
                'invoice_tax_amount' => make_currency_no_money(strip_tags($total_tax_amount)),
            );
            
            $tlvstr = new Tlvstr($qrdata);
            $qrcode = $tlvstr->generate();

        } else { 
            require_once(APPPATH . "libraries/hashids/vendor/autoload.php");

            $hashids = new Hashids\Hashids(base_url());
            $sms_id = $hashids->encode($sale_id_raw);
            $qrcode = site_url('r/' . $sms_id);
        } 
    ?>

    <?php echo "<img src='" . site_url('qrcodegenerator/index?qrcode=' . urlencode($qrcode)) . "' alt='$sale_id'/>"; ?>
</div>
<?php } ?>

        <?php
        $this->load->model('Price_rule');
        $coupons = $this->Price_rule->get_coupons_for_receipt($total);
        if (count($coupons) > 0) { ?>
            <tr>
                <td colspan="4" align="center">
                    <div class="invoice-policy">
                        <h3 class='text-center'><?php echo lang('common_coupons'); ?></h3>
                    </div>
                </td>
            </tr>
    <tr>
        <td align="center" colspan="4">
            <?php foreach ($coupons as $coupon) {  ?>
                <div class="invoice-policy coupon">
                    <?php
                    $coupon_text = H($coupon['name'] . ' - ' . $coupon['description']);
                    $coupon_barcode = H($coupon['coupon_code']);
                    $begins = date(get_date_format(), strtotime($coupon['start_date']));
                    $expires = date(get_date_format(), strtotime($coupon['end_date']));
                    ?>
                    <div><strong><?php echo H($coupon_text); ?></strong></div>
                    <?php echo "<img src='" . site_url('barcode/index/png') . "?barcode=$coupon_barcode' alt=''/>"; ?>
                    <div><?php echo lang('common_coupon_code') . ': ' . H($coupon_barcode); ?></div>
                    <div><?php echo lang('common_begins') . ': ' . H($begins); ?></div>
                    <div><?php echo lang('common_expires') . ': ' . H($expires); ?></div>
                </div><br>

            <?php } ?>
        <?php } ?>

				</td>
		</tr>
</table>
</body>
</html>