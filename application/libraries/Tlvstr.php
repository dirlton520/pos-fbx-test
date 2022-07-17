<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once (APPPATH."libraries/tlv/vendor/autoload.php");

use Salla\ZATCA\GenerateQrCode;
use Salla\ZATCA\Tags\InvoiceDate;
use Salla\ZATCA\Tags\InvoiceTaxAmount;
use Salla\ZATCA\Tags\InvoiceTotalAmount;
use Salla\ZATCA\Tags\Seller;
use Salla\ZATCA\Tags\TaxNumber;

class Tlvstr
{
    protected $seller_name;
    protected $tax_number;
    protected $invoice_date;
    protected $invoice_total_amount;
    protected $invoice_tax_amount;

    public function __construct($data)
    {
        $this->seller_name = $data['seller_name'];
        $this->tax_number = $data['tax_number'];
        $this->invoice_date = $data['invoice_date'];
        $this->invoice_total_amount = $data['invoice_total_amount'];
        $this->invoice_tax_amount = $data['invoice_tax_amount'];
    }
	public function generate(){
		$displayQRCodeAsBase64 = GenerateQrCode::fromArray([
			new Seller($this->seller_name),
			new TaxNumber($this->tax_number),
			new InvoiceDate($this->invoice_date), // invoice date as Zulu ISO8601 @see https://en.wikipedia.org/wiki/ISO_8601
			new InvoiceTotalAmount($this->invoice_total_amount),
			new InvoiceTaxAmount($this->invoice_tax_amount)
		])->toBase64();

		return $displayQRCodeAsBase64;
	}
}