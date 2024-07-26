<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SOLINH extends Model
{
    use HasFactory;
    public $timestamps = false; 
    protected $table = "SPM.SOLINH";

    protected $fillable = [
        'DocNo'
      ,'LineType'
      ,'ProductCode'
      ,'Description'
      ,'Location'
      ,'StockUOM'
      ,'OrderUOM'
      ,'Qty'
      ,'UnitCost'
      ,'Amount'
      ,'Disc1'
      ,'Disc2'
      ,'Disc3'
      ,'Disc4'
      ,'Disc5'
      ,'Discount'
      ,'NetAmount'
      ,'RowNum'
      ,'Ref1'
      ,'Ref2'
      ,'Ref3'
      ,'RemQty'
      ,'CustomerCode'
      ,'Distributor'
      ,'RATE1'
      ,'RATE2'
      ,'RATE3'
      ,'RATE1BASIS'
      ,'RATE2BASIS'
      ,'RATE3BASIS'
    ];
}
