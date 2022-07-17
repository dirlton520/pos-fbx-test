<h1><?php echo lang('reports_reports'); ?> - <?php echo $title ?></h1>
<h2><?php echo $subtitle ?></h2>

<table class="table table-bordered table-striped table-reports tablesorter" id="sortable_table">
	<tbody>
		<?php foreach ($data as $row) { ?>
		<tr>
			<?php $i = 0; foreach ($row as $cell) { ?>
			<td align="<?php echo $cell['align'];?>" class="colsho <?php echo $i; ?>"><?php echo $cell['data']; ?></td>
			<?php $i++; } ?>
		</tr>
		<?php } ?>
	</tbody>
</table>
	