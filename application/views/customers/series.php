<?php $this->load->view("partial/header"); ?>
	
	<?php if ($this->session->flashdata('success')) { ?>
	<script>
	show_feedback('success', <?php echo json_encode($this->session->flashdata('success')); ?>, <?php echo json_encode(lang('common_success')); ?>);
	</script>
	<?php } ?>
	
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-piluku reports-printable">	
				<div class="panel-body">
					<div class="table-responsive">
						<table class="table table-bordered table-striped table-reports tablesorter" id="sortable_table">
							<thead>
								<tr>
									<?php foreach ($headers as $header) { ?>
									<th align="<?php echo $header['align'];?>"><?php echo $header['data']; ?></th>
									<?php } ?>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($data as $row) { ?>
								<tr>
									<?php foreach ($row as $cell) { ?>
									<td align="<?php echo $cell['align'];?>"><?php echo $cell['data']; ?></td>
									<?php } ?>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
<script type="text/javascript">
</script>
<?php $this->load->view("partial/footer"); ?>