<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SODRLIN extends Model
{
    use HasFactory;

    public $timestamps = false; 
    protected $table = "SPM.SODRLIN";

    protected $fillable = [
        'Docno'
        ,'Linetype'
        ,'Productcode'
        ,'Description'
        ,'Location'
        ,'Stockuom'
        ,'Orderuom'
        ,'Qty'
        ,'Delqty'
        ,'Backqty'
        ,'Unitcost'
        ,'Amount'
        ,'Disc1'
        ,'Disc2'
        ,'Disc3'
        ,'Disc4'
        ,'Disc5'
        ,'Discount'
        ,'NetAmount'
        ,'Rownum'
        ,'Ref1'
        ,'Ref2'
        ,'Ref3'
        ,'Acctcode'
        ,'Sorownum'
        ,'Delpc'
        ,'Rempc'
        ,'Avecost'
        ,'ExpiryDate'
        ,'ExpYear'
        ,'ExpMonth'
        ,'ExpDay'
        ,'MonthDay'
        ,'PromoQty'
        ,'Distributor'
        ,'AllocationNo'
        ,'RATE1'
        ,'RATE2'
        ,'RATE3'
        ,'RATE1BASIS'
        ,'RATE2BASIS'
        ,'RATE3BASIS'
        ,'BatchNo'
    ];
}
