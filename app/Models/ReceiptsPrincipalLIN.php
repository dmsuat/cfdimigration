<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiptsPrincipalLIN extends Model
{
    use HasFactory;
    public $timestamps = false; 
    protected $table = "SPM.ReceiptsPrincipalLIN";

    protected $fillable = [
        'RPTransNo'
      ,'ProductCode'
      ,'Location'
      ,'SOH'
      ,'StockUOM'
      ,'Qty'
      ,'ExpiryDate'
      ,'ExpiryYear'
      ,'ExpiryMonth'
      ,'ExpiryDay'
      ,'MfgDate'
      ,'TransUOM'
      ,'UnitCost'
      ,'ShelfLife'
      ,'MorD'
      ,'TotalCost'
      ,'RowNo'
      ,'Distributor'
      ,'BatchNo'
    ];
}
