<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SODRHDR extends Model
{
    use HasFactory;

    public $timestamps = false; 
    protected $table = "SPM.SODRHDR";

    protected $fillable = [
        'Docno'
      ,'Sono'
      ,'Docdate'
      ,'Customercode'
      ,'Discountcode'
      ,'Shipto'
      ,'Poref'
      ,'Paymentcode'
      ,'Dropship'
      ,'Procstat'
      ,'Reqdate'
      ,'Totalsales'
      ,'Lessdiscount'
      ,'Salesdiscount'
      ,'Addvat'
      ,'Grandtotal'
      ,'Spare1'
      ,'Spare2'
      ,'Spare3'
      ,'Recuser'
      ,'Recdate'
      ,'Moduser'
      ,'Moddate'
      ,'Postuser'
      ,'Postdate'
      ,'Printuser'
      ,'Printdate'
      ,'Sino'
      ,'Sidate'
      ,'Duedate'
      ,'Printaxuser'
      ,'Printaxdate'
      ,'Remarks'
      ,'Grandhome'
      ,'NoSoref'
      ,'SiRef'
      ,'DRRef'
      ,'TransactionRef'
      ,'Distributor'
      ,'Salesman'
      ,'AmountPaid'
      ,'EWT'
      ,'ORNo'
    ];
}
