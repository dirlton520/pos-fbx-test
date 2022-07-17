		<div id="footers" class="col-md-12 hidden-print text-center">
			<?php echo lang('common_please_visit_my'); ?>
			<a tabindex="-1" href="http://phppointofsale.com" target="_blank"><?php echo lang('common_website'); ?></a> <?php echo lang('common_learn_about_project'); ?>.
			<span class="text-info"><?php echo lang('common_you_are_using_phppos') ?> <span class="badge bg-primary"> <?php echo APPLICATION_VERSION; ?></span></span> <?php echo lang('common_built_on') . ' ' . BUILT_ON_DATE; ?>
		</div>
	</div>
	<!---content -->
</div> <!-- wrapper -->
</body>
<?php
if (($this->uri->segment(1) == 'sales' || $this->uri->segment(1) == 'receivings')) {
?>
	<script>
		function getBodyScrollTop() {
			var el = document.scrollingElement || document.documentElement;

			return el.scrollTop;
		}

		$(window).on("beforeunload", function() {

			var scroll_top =
				$.ajax(<?php echo json_encode(site_url('home/save_scroll')); ?>, {
					async: false,
					data: {
						scroll_to: getBodyScrollTop()
					}
				});
		});
	</script>
	<?php
	if ($this->session->userdata('scroll_to')) {
	?>
		<script>
			$([document.documentElement, document.body]).animate({
				scrollTop: <?php echo json_encode($this->session->userdata('scroll_to')); ?>
			}, 100);
		</script>
<?php
		$this->session->unset_userdata('scroll_to');
	}
}
?>

<script>
	async function delete_all_client_side_dbs()
	{
		//If we can list out all datbases this is the best method in case we are in an odd state
		//Supports chrome, safari
		if (window.indexedDB.databases)
		{
			 window.indexedDB.databases().then((r) => 
			 {
			     for (var i = 0; i < r.length; i++)
		         {
		             window.indexedDB.deleteDatabase(r[i].name);     
		         } 
			 })
		}
		else //For firefox
		{
			try
			{
	 			var phppos_customers = new PouchDB('phppos_customers',{revs_limit: 1});
	 			var phppos_items = new PouchDB('phppos_items',{revs_limit: 1});
				var phppos_settings = new PouchDB('phppos_settings',{revs_limit: 1});
				await phppos_customers.destroy();
				await phppos_items.destroy();
				await phppos_settings.destroy();
			}
			catch(exception_var)
			{
				
			}
		}
		
	}
</script>

<?php
if ($this->config->item('offline_mode'))
{
?>
<script>
	<?php
	$offline_assets = array();
	foreach(get_css_files() as $css_file)
	{
		$offline_assets[] = base_url().$css_file['path'].'?'.ASSET_TIMESTAMP;
	}
	foreach(get_js_files() as $js_file) 
	{
		$offline_assets[] = base_url().$js_file['path'].'?'.ASSET_TIMESTAMP;		
	}	
	
	$offline_assets[] = base_url().'favicon.ico';
	$offline_assets[] = base_url().'assets/fonts/themify.woff?-fvbane';
	$offline_assets[] = base_url().'assets/fonts/themify.ttf?-fvbane';
	$offline_assets[] = base_url().'assets/fonts/ionicons.woff?v=2.0.0';
	$offline_assets[] = base_url().'assets/fonts/ionicons.ttf?v=2.0.0';
	$offline_assets[] = base_url().'assets/assets/images/avatar-default.jpg';
	$offline_assets[] = base_url().'assets/img/item.png';
	$offline_assets[] = base_url().'assets/img/header_logo.png';
	$offline_assets[] = base_url().'assets/img/user.png';

	?>

	var offline_mode_sync_period = parseInt("<?php echo $this->config->item('offline_mode_sync_period')?$this->config->item('offline_mode_sync_period'): '24'; ?>");

	//Offline support
	UpUp.start({
		'cache-version': '<?php echo BUILD_TIMESTAMP; ?>',
		'content-url': '<?php echo site_url('home/offline/').BUILD_TIMESTAMP?>',
		'assets': <?php echo json_encode($offline_assets); ?>,
		'service-worker-url': '<?php echo  base_url().'upup.sw.min.js?'.BUILD_TIMESTAMP;?>'
	});

	//Background worker for syncing offline data
	var w;
	function startWorker() 
	{
		if (typeof(Worker) !== "undefined") {
			if (typeof(w) == "undefined") {
				w = new Worker('<?php echo base_url(); ?>'+"assets/js/load_sales_offline_data_worker.js?<?php echo BUILD_TIMESTAMP;?>");
					
				//Event handler coming back from worker that posts messages
				w.onmessage = function(event) 
				{
					var data = event.data;
					
					if (data == 'delete_all_client_side_dbs')
					{
						delete_all_client_side_dbs();
					}
				};

				//Post message to worker; some init params
				w.postMessage({
					base_url:BASE_URL,
					site_url:SITE_URL,
					offline_mode_sync_period: offline_mode_sync_period
				});
			}
		} else{
			document.getElementById("result").innerHTML = "Sorry! No Web Worker support.";
		}
	}

	function stopWorker() 
	{ 
	   w.terminate();
	   w = undefined;
	}
	localStorage.setItem('APPLICATION_VERSION',<?php echo json_encode(APPLICATION_VERSION); ?>);
	localStorage.setItem('BUILD_TIMESTAMP',<?php echo json_encode(BUILD_TIMESTAMP); ?>);

	startWorker();	 
</script>
<?php } ?>
</html>
