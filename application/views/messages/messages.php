<?php $this->load->view("partial/header"); ?>
<div class="manage_buttons">
	<div class="row">
		<div class="col-md-3">
		</div>
		<div class="col-md-9">	
			<div class="buttons-list">
				<div class="pull-right-btn">
				  	<?php if ($this->Employee->has_module_action_permission('messages', 'send_message', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
					  	<a href="<?php echo site_url('messages/send_message'); ?>" id="send_message" class="btn btn-primary btn-lg"><span class="ion-compose"> <?php echo lang('messages_new_message') ?></span></a>
					  	<a href="<?php echo site_url('messages/sent_messages'); ?>" id="sent_messages" class="btn btn-warning btn-lg"><span class="ion-paper-airplane"> <?php echo lang('messages_sent_messages') ?></span></a>
				  	<?php } ?>

					</div>
				</div>
			</div>				
		</div>
	</div>
</div>

<div class="manage-table">
	<div class="main-content">					
		<div class="mail_holder">						
			<?php if(count($messages)) { ?>
			<div class="mail_body">								
				<div class="mail_list_block col-md-12 col-sm-12 no_padding col-xs-12 col-lg-12">
					<div class="mail_list">
						<ul class="list-unstyled mails_holder">
							<li>
							<?php
								 foreach ($messages as $message) { 
								 	$sender = $this->Employee->get_info($message['sender_id']);
								 	$avatar_url=$sender->image_id ?  app_file_url($sender->image_id) : base_url()."assets/img/user.png";
							?>
								<a href="<?php echo site_url('messages/view/'.$message['message_id']); ?>"  data-message-id="<?php echo $message['id'] ?>">
									<div class="message_list_block <?php echo $message['message_read']==0 ? 'active' : '';  ?>">
										<div class="left">
											<div class="avatar_holder">
												<img src="<?php echo $avatar_url; ?>" alt="">
											</div>
										</div>
										<div class="right">
											<span class="name"><?php echo H($sender->first_name.' '.$sender->last_name); ?></span>
											<span class="pull-right right_details">
												<ul class="list-unstyled list-inline">
													<li class="time"><?php echo date(get_date_format(). ' '.get_time_format(), strtotime($message['created_at'])); ?></li>
													<li><i class="ion ion-record <?php echo $message['message_read']==1 ? 'flatGreyc' : 'flatGreenc';  ?> status"></i></li>
												</ul>
											</span>
											<h4><?php echo H($message['message']) ?></h4>
										</div>
										<!-- right -->													
									</div>
								</a>
							<?php } ?>
							</li>												
						</ul>
					</div>
					<!-- mail-list -->
				</div>
				<!-- col-md-4 -->
				
			</div>
			<!-- mail-body -->	
			<?php } else { ?>
				<div class="alert alert-warning text-center">
					<?php echo lang('messages_no_messages');?>
				</div>
			<?php } ?>										
		</div>
		<!-- mail-holder -->		

		<?php if($pagination) {  ?>
			<div class="row pagination-info text-center">
				<div class="col-md-12">
					<div class="pagination hidden-print alternate text-center" id="pagination_top" >
						<?php echo $pagination;?>
					</div>
				</div>
			</div>																
		<?php }  ?>
		
	</div>
	<!-- main_content-->
</div>
<?php $this->load->view("partial/footer"); ?>