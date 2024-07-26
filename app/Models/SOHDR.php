<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SOHDR extends Model
{
    use HasFactory;
    public $timestamps = false; 
    protected $table = "SPM.SOHDR";

    protected $fillable = ['Docno'
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
      ,'PriceMatrixcode'
      ,'Remarks'
      ,'Distributor'
      ,'Salesman'
      ,'isPassed'
      ,'PODate'
      ,'ForbidPartial'
    ];
}
