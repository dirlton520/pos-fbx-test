<?php
trait taxOverrideTrait
{
	public function edit_taxes()
	{
		$data = array();
		$data['controller_name']=strtolower(get_class());
		$data['tax_info'] = $this->cart->get_override_tax_info();
		$data['tax_class_selected'] = $this->cart->override_tax_class;
		$data['tax_classes'] = array();
		$data['tax_classes'][''] = lang('common_none');
		
		foreach($this->Tax_class->get_all()->result_array() as $tax_class)
		{
			$data['tax_classes'][$tax_class['id']] = $tax_class['name'];
		}
		
		$this->load->view("tax_override",$data);
		
	}
	
	function save_tax_overrides()
	{
		$data = array();
		$this->cart->override_tax_names = $this->input->post('tax_names');
		$this->cart->override_tax_percents = $this->input->post('tax_percents');
		$this->cart->override_tax_cumulatives = $this->input->post('tax_cumulatives');
		$this->cart->override_tax_class = $this->input->post('tax_class');
		$this->cart->save();
  	$this->_reload($data);
		
	}
	
	public function edit_taxes_line($line)
	{
		$data = array();
		$data['controller_name']=strtolower(get_class());
		$data['line']=$line;
		$data['tax_info'] = $this->cart->get_item($line)->get_override_tax_info();
		$data['tax_class_selected'] = $this->cart->get_item($line)->override_tax_class;
		
		$data['tax_classes'] = array();
		$data['tax_classes'][''] = lang('common_none');
		
		foreach($this->Tax_class->get_all()->result_array() as $tax_class)
		{
			$data['tax_classes'][$tax_class['id']] = $tax_class['name'];
		}
		
		$this->load->view("tax_override",$data);
		
	}
	
	function save_tax_overrides_line($line)
	{
		$data = array();
		$this->cart->get_item($line)->override_tax_names = $this->input->post('tax_names');
		$this->cart->get_item($line)->override_tax_percents = $this->input->post('tax_percents');
		$this->cart->get_item($line)->override_tax_cumulatives = $this->input->post('tax_cumulatives');
		$this->cart->get_item($line)->override_tax_class = $this->input->post('tax_class');
		$this->cart->save();
  	$this->_reload($data);
		
	}
}