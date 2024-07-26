<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IVTRAN extends Model
{
    use HasFactory;
    public $timestamps = false; 
    protected $table = "SPM.IVTRAN";

    protected $fillable = ['Distributor'
      ,'DocType'
      ,'Docdate'
      ,'Docno'
      ,'ApplyTo'
      ,'Location'
      ,'Productcode'
      ,'ExpiryDate'
      ,'Inventoriable'
      ,'Qty'
      ,'UnitCost'
      ,'StockUom'
      ,'TranUom'
      ,'Rownum'
      ,'Remarks'
      ,'Procstat'
      ,'PostUser'
      ,'PostDate'
      ,'BatchNo'
    ];
}
