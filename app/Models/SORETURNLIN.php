<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SORETURNLIN extends Model
{
    use HasFactory;
    public $timestamps = false; 
    protected $table = "SPM.SORETURNLIN";

    protected $fillable = [
        'DocNo'
        ,'LineType'
        ,'ProductCode'
        ,'Description'
        ,'Location'
        ,'StockUOM'
        ,'OrderUOM'
        ,'Qty'
        ,'DelQty'
        ,'ReturnQty'
        ,'UnitCost'
        ,'Amount'
        ,'Disc1'
        ,'Disc2'
        ,'Disc3'
        ,'Disc4'
        ,'Disc5'
        ,'Discount'
        ,'RowNum'
        ,'Ref1'
        ,'Ref2'
        ,'Ref3'
        ,'AcctCode'
        ,'DelPC'
        ,'ReturnUOM'
        ,'AveCost'
        ,'DRRowNum'
        ,'RemPC'
        ,'ExpiryDate'
        ,'ExpYear'
        ,'ExpMonth'
        ,'ExpDay'
        ,'BoQty'
        ,'BoUom'
        ,'BoUnitCost'
        ,'Purpose'
        ,'MonthDay'
        ,'NetAmount'
        ,'AppliedAmount'
        ,'Reason'
        ,'RefDocNo'
        ,'VAT'
        ,'TotalAmount'
        ,'DestinationLocation'
        ,'Distributor'
        ,'RATE1'
        ,'RATE2'
        ,'RATE3'
        ,'RATE1BASIS'
        ,'RATE2BASIS'
        ,'RATE3BASIS'
        ,'BatchNo'
    ];
}
