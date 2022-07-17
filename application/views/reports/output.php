<?php

if (isset($view))
{
?>
<div class="row">
	<div class="col-md-12">
		<?php	
			$this->load->view('reports/outputs/'.$view);
		?>
	</div>
</div>
<script>	
	$('.expand-collapse').click(function() {
		$('#options').slideToggle();
		$('#expand-collapse-icon').toggleClass('ion-chevron-up');
	});
</script>
<?php
}
?>