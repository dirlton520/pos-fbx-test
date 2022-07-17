<div class="row">
	<div class="col-md-12">

		<div class="form-group">
			<?php 
			$required = ($controller_name == "suppliers") ? "" : "required";
			echo form_label(lang('common_first_name').':', 'first_name',array('class'=>$required.' col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
			<div class="col-sm-9 col-md-9 col-lg-10">
				<?php echo form_input(array(
					'class'=>'form-control',
					'name'=>'first_name',
					'id'=>'first_name',
					'value'=>$person_info->first_name)
				);?>
			</div>
		</div>

		<div class="form-group">
			<?php echo form_label(lang('common_last_name').':', 'last_name',array('class'=>' col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
			<div class="col-sm-9 col-md-9 col-lg-10">
			<?php echo form_input(array(
				'class'=>'form-control',
				'name'=>'last_name',
				'id'=>'last_name',
				'value'=>$person_info->last_name)
			);?>
			</div>
		</div>

		<div class="form-group">
			<?php echo form_label(lang('common_email').':', 'email',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label '.($controller_name == 'employees' || $controller_name == 'login' ? 'required' : 'not_required'))); ?>
			<div class="col-sm-9 col-md-9 col-lg-10">
			<?php echo form_input(array(
				'class'=>'form-control',
				'name'=>'email',
				'type'=>'text',
				'id'=>'email',
				'value'=>$person_info->email)
				);?>
			</div>
		</div>
		<div class="form-group">	
			<?php echo form_label(lang('common_phone_number').':', 'phone_number',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
			<div class="col-sm-9 col-md-9 col-lg-10">
			<?php echo form_input(array(
				'class'=>'form-control',
				'name'=>'phone_number',
				'id'=>'phone_number',
				'value'=>$person_info->phone_number));?>
			</div>
		</div>
		<div class="form-group">	
		<?php echo form_label(lang('common_choose_avatar').':', 'image_id',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
			<div class="col-sm-9 col-md-9 col-lg-10">
	      		<ul class="list-unstyled avatar-list">
					<li>
						<input type="file" name="image_id" id="image_id" class="filestyle" accept=".png,.jpg,.jpeg,.gif" >&nbsp;
					</li>
					<li>
						<?php echo $person_info->image_id ? '<div id="avatar">'.img(array('style' => 'width: 60%','src' => app_file_url($person_info->image_id),'class'=>'img-polaroid img-polaroid-s')).'</div>' : '<div id="avatar">'.img(array('style' => 'width: 20%','src' => base_url().'assets/img/avatar.png','class'=>'img-polaroid','id'=>'image_empty')).'</div>'; ?>		
					</li>		
				</ul>
			</div>
		</div>
	
	<?php if($person_info->image_id) {  ?>

	<div class="form-group">
	<?php echo form_label(lang('common_del_image').':', 'del_image',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
		<div class="col-sm-9 col-md-9 col-lg-10">
		<?php echo form_checkbox(array(
			'name'=>'del_image',
			'id'=>'del_image',
			'class'=>'delete-checkbox', 
			'value'=>1
		));
		echo '<label for="del_image"><span></span></label> ';
		
		?>
		</div>
	</div>

	<?php }  ?>



<div class="form-group">	
<?php echo form_label(lang('common_address_1').':', 'address_1',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
	<div class="col-sm-9 col-md-9 col-lg-10">
	<?php echo form_input(array(
		'class'=>'form-control',
		'name'=>'address_1',
		'id'=>'address_1',
		'value'=>$person_info->address_1));?>
	</div>
</div>

			<div class="form-group">	
<?php echo form_label(lang('common_address_2').':', 'address_2',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
	<div class="col-sm-9 col-md-9 col-lg-10">
	<?php echo form_input(array(
		'class'=>'form-control',
		'name'=>'address_2',
		'id'=>'address_2',
		'value'=>$person_info->address_2));?>
	</div>
</div>

			<div class="form-group">	
<?php echo form_label(lang('common_city').':', 'city',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
	<div class="col-sm-9 col-md-9 col-lg-10">
	<?php echo form_input(array(
		'class'=>'form-control ',
		'name'=>'city',
		'id'=>'city',
		'value'=>$person_info->city));?>
	</div>
</div>

			<div class="form-group">	
<?php echo form_label(lang('common_state').':', 'state',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
	<div class="col-sm-9 col-md-9 col-lg-10">
	<?php echo form_input(array(
		'class'=>'form-control ',
		'name'=>'state',
		'id'=>'state',
		'value'=>$person_info->state));?>
	</div>
</div>

			<div class="form-group">	
<?php echo form_label(lang('common_zip').':', 'zip',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
	<div class="col-sm-9 col-md-9 col-lg-10">
	<?php echo form_input(array(
		'class'=>'form-control ',
		'name'=>'zip',
		'id'=>'zip',
		'value'=>$person_info->zip));?>
	</div>
</div>

			<div class="form-group">	
<?php echo form_label(lang('common_country').':', 'country',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
	<div class="col-sm-9 col-md-9 col-lg-10">
	<?php echo form_input(array(
		'class'=>'form-control ',
		'name'=>'country',
		'id'=>'country',
		'value'=>$person_info->country));?>
	</div>
</div>

	<div class="form-group">	
<?php echo form_label(lang('common_comments').':', 'comments',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
	<div class="col-sm-9 col-md-9 col-lg-10">
	<?php echo form_textarea(array(
		'name'=>'comments',
		'id'=>'comments',
		'class'=>'form-control text-area',
		'value'=>$person_info->comments,
		'rows'=>'5',
		'cols'=>'17')		
	);?>
	</div>
</div>
<?php
if ($this->Location->get_info_for_key('mailchimp_api_key') && $controller_name != "login")
{
	$this->load->helper('mailchimp');
	
	$default_mailchimp_lists = array();
		
	if ($this->Location->get_info_for_key('default_mailchimp_lists'))
	{
		$default_mailchimp_lists =	unserialize($this->Location->get_info_for_key('default_mailchimp_lists'));
	}
	
	if (!$default_mailchimp_lists)
	{
		$default_mailchimp_lists = array();								
	}
	
?>
			<div class="form-group">
	<div class="column">	
		<?php echo form_label(lang('common_mailing_lists').':', 'mailchimp_mailing_lists',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
	</div>
	
    <div class="column">
		<ul style="list-style: none; float:left;">
	<?php
	foreach(get_all_mailchimps_lists() as $list)
	{
		echo '<li>';
		echo form_checkbox(array('name'=> 'mailing_lists[]',
		'id' => $list['id'],
		'value' => $list['id'],
		'checked' => $person_info->id ? email_subscribed_to_list($person_info->email, $list['id']) : in_array($list['id'],$default_mailchimp_lists),
		'label'	=> $list['id']));
		
		echo '<label for="'.$list['id'].'"><span></span></label> '.$list['name'];
		echo '</li>';
	}
	?>
	</ul>
	</div>
	<div class="cleared"></div>
</div>
<?php
}
?> 


<?php
if ($this->Location->get_info_for_key('platformly_api_key') && $controller_name != "login")
{
	$this->load->helper('platformly');
?>
			<div class="form-group">
	<div class="column">	
		<?php echo form_label(lang('common_segments').':', 'platformly_segments',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
	</div>
	
    <div class="column">
		<ul style="list-style: none; float:left;">
	<?php
	foreach(get_all_platformly_segments() as $segment)
	{
		echo '<li>';
		echo form_checkbox(array('name'=> 'segments[]',
		'id' => $segment['id'],
		'value' => $segment['id'],
		'checked' => email_subscribed_to_segment($person_info->email, $segment['id']),
		'label'	=> $segment['id']));
		
		echo '<label for="'.$segment['id'].'"><span></span></label> '.$segment['name'];
		echo '</li>';
	}
	?>
	</ul>
	</div>
	<div class="cleared"></div>
</div>
<?php
}
?> 
	</div><!-- /col-md-12 -->
</div><!-- /row -->
