<div class="form-group">
	<?php echo form_label($specific_input_label.':', $specific_input_name, array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?> 
	<div class="col-sm-9 col-md-2 col-lg-2">
		
		<?php if (isset($search_suggestion_url)) {?>
			<?php echo form_input(array(
				'name'=>$specific_input_name,
				'id'=>$specific_input_name,
				'size'=>'10',
				'value'=>$this->input->get($specific_input_name)));
			?>									
		<?php } else { ?>
			<?php echo form_dropdown($specific_input_name,$specific_input_data, $this->input->get($specific_input_name), 'id="'.$specific_input_name.'" class=""'); ?>
		<?php } ?>
	</div>
</div>

<script type="text/javascript" language="javascript">
	$(document).ready(function()
	{
		<?php
		if (isset($search_suggestion_url))
		{
		?>
			$("#<?php echo $specific_input_name;?>").select2(
			{
				placeholder: <?php echo json_encode(lang('common_search')); ?>,
				id: function(suggestion){ return suggestion.value; },
				ajax: {
					url: <?php echo json_encode($search_suggestion_url); ?>,
					dataType: 'json',
				   data: function(term, page) 
					{
				      return {
				          'term': term
				      };
				    },
					results: function(data, page) {
						return {results: data};
					}
				},
				formatSelection: function(suggestion) {
					return suggestion.label;
				},
				formatResult: function(suggestion) {
					return suggestion.label;
				}
			});
		<?php
		}
		else
		{
		?>
			$("#<?php echo $specific_input_name; ?>").select2();		
		<?php
		}
		?>
	});
</script>
		