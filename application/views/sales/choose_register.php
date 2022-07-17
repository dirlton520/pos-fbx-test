<?php $this->load->view("partial/header"); ?>
<div class="row">
	<?php
		foreach($this->Register->get_all()->result() as $register) 
		{ 
	?>
		<div class="col-md-6 col-xs-12 col-sm-6 ">
            <a href="<?php echo site_url('sales/choose_register').'/'.$register->register_id ?>">
                <div class="info-logo-content">
                    <ul class="list-inline list-unstyled registers-list">
                        <li><i class="ion ion-ios-albums-outline primary-info"></i></li>
                        <li><?php echo $register->name ?></li>
                    </ul>
                    <div class="info-logo primarybg-info">
                        <i class="ion ion-ios-albums-outline"></i>
                    </div>
                </div>	
            </a>
        </div>
	<?php } ?>	
</div>
<?php $this->load->view('partial/footer.php'); ?>