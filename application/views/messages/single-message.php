<?php $this->load->view("partial/header"); ?>


<div class="manage_buttons">
	<div class="row">
		<div class="col-md-3">
			
		</div>
		<div class="col-md-9">	
			<div class="buttons-list">
				<div class="pull-right-btn">
					<a href="<?php echo site_url('messages'); ?>" id="new-person-btn" class="btn btn-success btn-lg"><span class=""><?php echo lang('messages_inbox') ?></span></a>
				  	<?php if ($this->Employee->has_module_action_permission('messages', 'send_message', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
					  	<a href="<?php echo site_url('messages/send_message'); ?>" id="new-person-btn" class="btn btn-primary btn-lg"><span class=""><?php echo lang('employees_send_message') ?></span></a>
					  	<a href="<?php echo site_url('messages/sent_messages'); ?>" id="new-person-btn" class="btn btn-warning btn-lg"><span class=""><?php echo lang('messages_sent_messages') ?></span></a>
				  	<?php } ?>

					</div>
				</div>
			</div>				
		</div>
	</div>
</div>
<div class=" manage-table">
	<div class="main-content">					
		<div class="mail_holder">						
			<?php if(count($message)) { 
				$sender = $this->Employee->get_info($message[0]['sender_id']);
				$avatar_url=$sender->image_id ?  app_file_url($sender->image_id) : base_url()."assets/img/user.png";
			?>
			<div class="mail_body">								
				
				<!-- mail_list_block -->
				<div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 no_padding mail_brief_holder">
					<div class="heading_block">
						<?php if($this->uri->segment(4) == 1) { ?>
							<a href="<?php echo site_url('messages/sent_messages') ?>" class="pull-left back"><i class="ion-android-arrow-back"></i></a>
							<div class="message-options">
								<a href="<?php echo site_url('messages/delete_message/'.$message[0]['id']) ?>" class="delete-message" data-message-id="<?php echo $message[0]['id']; ?>"><i class="ion-trash-b"></i></a>
							</div>

						<?php } else { ?>
							<a href="<?php echo site_url('messages') ?>" class="pull-left back"><i class="ion-android-arrow-back"></i></a>
						<?php } ?>
						<h1>
							<div class="name"> <?php echo $sender->first_name.' '.$sender->last_name; ?></div>
							<span class="time"><?php echo date(get_date_format(). ' '.get_time_format(), strtotime($message[0]['created_at'])); ?></span>
						</h1>
						
					</div>
					<!-- heading_block -->
					<div class="mail_brief_body">
						<?php echo nl2br($message[0]['message']) ?>
					</div>
					<!-- mail_brief_body -->
				</div>
			</div>
			<!-- mail-body -->	
			<?php } else { ?>
				<div class="mail_body">
					<div class="mail_list_block">
						<div class="none">
							<p><?php echo lang('messages_no_messages');?></p>
						</div>
					</div>
				</div>
			<?php } ?>										
		</div>
		<!-- mail-holder -->				
	</div>
	<!-- main_content-->
</div>


<?php $this->load->view("partial/footer"); ?>