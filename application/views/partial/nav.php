<nav class="navbar navbar-default panel-piluku manage-table">
		<ul class="nav nav-justified nav-wizard <?php echo $progression ? 'nav-progression' : ''; ?>">
			<?php if($this->uri->segment(1) == 'items') { ?>
	 			<li <?php echo $this->uri->segment(2) == 'view' ? 'class="active"' : '' ?>><?php echo anchor("items/view/".($item_info->item_id ? $item_info->item_id : -1).($query ? '?'.$query : ''),''.lang('common_item_info'),array('class'=> 'outbound_link', 'role'=>'button', 'title'=>lang('common_item_info')))?></li>
	 			<li <?php echo $this->uri->segment(2) == 'variations' ? 'class="active"' : '' ?>><?php echo anchor("items/variations/".($item_info->item_id ? $item_info->item_id : -1).($query ? '?'.$query : ''),''.lang('items_edit_variations'),array('class'=> 'outbound_link', 'role'=>'button', 'title'=>lang('items_edit_variations')))?></li>
				<?php if ($this->Employee->has_module_action_permission('items','edit_prices', $this->Employee->get_logged_in_employee_info()->person_id)) { ?>
					<li <?php echo $this->uri->segment(2) == 'pricing' ? 'class="active"' : '' ?>><?php echo anchor("items/pricing/".($item_info->item_id ? $item_info->item_id : -1).($query ? '?'.$query : ''),''.lang('common_edit_pricing'),array('class'=> 'outbound_link', 'role'=>'button', 'title'=>lang('common_edit_pricing')));?></li>
	 			<?php } ?>
				<li class="is-service-toggle <?php echo $this->uri->segment(2) == 'inventory' ? 'active' : '' ?> <?php if ($item_info->is_service){ echo 'hidden';} ?>"><?php echo anchor("items/inventory/".($item_info->item_id ? $item_info->item_id : -1).($query ? '?'.$query : ''),''.lang('items_edit_inventory'),array('class'=> 'outbound_link', 'role'=>'button', 'title'=>lang('items_edit_inventory')));?></li>
				<li <?php echo $this->uri->segment(2) == 'images' ? 'class="active"' : '' ?>> <?php echo anchor("items/images/".($item_info->item_id ? $item_info->item_id : -1).($query ? '?'.$query : ''),''.lang('common_images'),array('class'=> 'outbound_link', 'role'=>'button', 'title'=>lang('common_images')));?></li>
	 			<?php if ($this->Location->count_all() > 0) { ?>
	 			<li <?php echo $this->uri->segment(2) == 'location_settings' ? 'class="active"' : '' ?>><?php echo anchor("items/location_settings/".($item_info->item_id ? $item_info->item_id : -1).($query ? '?'.$query : ''),''.lang('common_edit_location_settings'),array('class'=> 'outbound_link', 'role'=>'button', 'title'=>lang('common_edit_location_settings')));?></li>
	 			<?php } /*End if for multi locations*/ ?>
			<?php } ?>
			<?php if($this->uri->segment(1) == 'item_kits') { ?>
				<li <?php echo $this->uri->segment(2) == 'view' ? 'class="active"' : '' ?>><?php echo anchor("item_kits/view/".($item_kit_info->item_kit_id ? $item_kit_info->item_kit_id : -1).($query ? '?'.$query : ''),''.lang('common_item_kit_info'),array('class'=> 'outbound_link', 'role'=>'button', 'title'=>lang('common_item_kit_info')))?></li>
				<li <?php echo $this->uri->segment(2) == 'items' ? 'class="active"' : '' ?>><?php echo anchor("item_kits/items/".($item_kit_info->item_kit_id ? $item_kit_info->item_kit_id : -1).($query ? '?'.$query : ''),''.lang('common_items'),array('class'=> 'outbound_link', 'role'=>'button', 'title'=>lang('common_items')))?></li>
				<?php if ($this->Employee->has_module_action_permission('items','edit_prices', $this->Employee->get_logged_in_employee_info()->person_id)) { ?>
					<li <?php echo $this->uri->segment(2) == 'pricing' ? 'class="active"' : '' ?>><?php echo anchor("item_kits/pricing/".($item_kit_info->item_kit_id ? $item_kit_info->item_kit_id : -1).($query ? '?'.$query : ''),''.lang('common_edit_pricing'),array('class'=> 'outbound_link', 'role'=>'button', 'title'=>lang('common_edit_pricing')));?></li>
				<?php } ?>
				<li <?php echo $this->uri->segment(2) == 'images' ? 'class="active"' : '' ?>> <?php echo anchor("item_kits/images/".($item_kit_info->item_kit_id ? $item_kit_info->item_kit_id : -1).($query ? '?'.$query : ''),''.lang('common_images'),array('class'=> 'outbound_link', 'role'=>'button', 'title'=>lang('common_images')));?></li>
				<?php if ($this->Location->count_all() > 0) { ?>
				<li <?php echo $this->uri->segment(2) == 'location_settings' ? 'class="active"' : '' ?>><?php echo anchor("item_kits/location_settings/".($item_kit_info->item_kit_id ? $item_kit_info->item_kit_id : -1).($query ? '?'.$query : ''),''.lang('common_edit_location_settings'),array('class'=> 'outbound_link', 'role'=>'button', 'title'=>lang('common_edit_location_settings')));?></li>
				<?php } /*End if for multi locations*/ ?>
			<?php } ?>
		</ul>
</nav>