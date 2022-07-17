<?php $this->load->view("partial/header"); ?>


<div class="manage_buttons">
	<div class="row">
		<div class="col-md-3">
			
		</div>
		<div class="col-md-9">	
			<div class="buttons-list">
				<div class="pull-right-btn">
					<a href="<?php echo site_url('messages'); ?>" id="new-person-btn" class="btn btn-success btn-lg"><span class="ion-ios-filing"> <?php echo lang('messages_inbox') ?></span></a>
					<?php if ($this->Employee->has_module_action_permission('messages', 'send_message', $this->Employee->get_logged_in_employee_info()->person_id)) {?>
					<a href="<?php echo site_url('messages/send_message'); ?>" id="send_message" class="btn btn-primary btn-lg"><span class="ion-compose"> <?php echo lang('messages_new_message') ?></span></a>
					<?php } ?>

				</div>
			</div>
		</div>				
	</div>
</div>

<div class="row manage-table">

	<div class="message-holder">
		<?php if(count($messages)) { ?>
		<div class="message-box">

			<div class="col-md-12 no-pad">
				<div class="left-list" id="style-4">
					<?php
					foreach ($messages as $message) { 
						$sender = $this->Employee->get_info($message['sender_id']);
						$avatar_url=$sender->image_id ?  app_file_url($sender->image_id) : base_url()."assets/img/user.png";
						?>
						<div class="message-wrapper">
							<div class="message-options">
								<a href="<?php echo site_url('messages/delete_message/'.$message['id']) ?>" class="delete-message" data-message-id="<?php echo $message['id']; ?>"><i class="ion-trash-b"></i></a>
							</div>
							<a href="<?php echo site_url('messages/view/'.$message['id'].'/1'); ?>" class="message-body">

								<div class="avatar">
									<div class="img-holder">
										<img src="<?php echo $avatar_url; ?>" alt="">
									</div>
								</div>
								<div class="text-left">
									<h1> <?php echo H($message['sent_to']); ?></h1> 
									<div class="time"><?php echo date(get_date_format(). ' '.get_time_format(), strtotime($message['created_at'])); ?> <i class="ion-clock"></i></div>
									<p><?php echo H($message['message']) ?></p>
								</div>
							</a>
						</div>

						<?php } ?>
					</div>
					<!-- left-list -->
				</div>

			</div>

			<?php if($pagination) {  ?>
			<div class="row pagination-info">
				<div class="col-md-12 text-center ">
					<div class="pagination hidden-print alternate text-center" id="pagination_top" >
						<?php echo $pagination;?>
					</div>
				</div>
			</div>																
			<?php }  ?>
			<!-- message-box -->
			<?php } else { ?>
			<div class="alert alert-warning text-center">
				<?php echo lang('messages_no_messages');?>
			</div>

			<?php } ?>
		</div>

	</div>
</div>
	<!-- row -->

	<script>
		$(document).ready(function() {

			$('.delete-message').on('click',function(e){
				e.preventDefault();
				message_id = $(this).data('message-id');
				var current_message = $(this);
				$.post('<?php echo site_url("messages/delete_message");?>',
				{
					message_id: message_id,
				},
				function(response, status){
					var response = JSON.parse(response)
					if(response.status)
					{
						show_feedback(response.status ? 'success' : 'error', <?php echo json_encode(lang('messages_message_deleted')); ?>,response.status ? <?php echo json_encode(lang('common_success')); ?> : <?php echo json_encode(lang('common_error')); ?>);
						current_message.closest('.message-wrapper').hide("slow");
					}
				});
			});


});


</script>


<?php $this->load->view("partial/footer"); ?>