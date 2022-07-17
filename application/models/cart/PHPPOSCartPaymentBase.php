<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class PHPPOSCartPaymentBase
{
	public $payment_type;
	public $payment_amount;
	public $payment_date;

	public function __construct(array $params = array())
	{		 
		foreach($params as $name=>$value)
		{
	 		if (property_exists($this,$name))
	 		{
	 	 	 $this->$name = $value;
			}
		}
		
		//If we never set date; set it to now
		if (!$this->payment_date)
		{
			$this->payment_date = date('Y-m-d H:i:s');
			
		}
	}	
	
	//This method prevents properties from being set that don't exist
	public function __set($property, $value)
	{
	    //Checking for non-existing properties
	    if (!property_exists($this, $property)) 
	    {
	        throw new Exception("Property {$property} does not exist");
	    }
	    $this->$property = $value;
	}
}