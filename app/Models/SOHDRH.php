<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SOHDRH extends Model
{
    use HasFactory;
    public $timestamps = false; 
    protected $table = "SPM.SOHDRH";

    protected $fillable = [
        'DocNo'
        ,'DocDate'
        ,'CustomerCode'
        ,'DiscountCode'
        ,'ShipTo'
        ,'PORef'
        ,'PaymentCode'
        ,'DropShip'
        ,'Procstat'
        ,'ReqDate'
        ,'TotalSales'
        ,'LessDiscount'
        ,'SalesDiscount'
        ,'AddVAT'
        ,'GrandTotal'
        ,'Spare1'
        ,'Spare2'
        ,'Spare3'
        ,'RecUser'
        ,'RecDate'
        ,'ModUser'
        ,'ModDate'
        ,'PostUser'
        ,'PostDate'
        ,'PrintUser'
        ,'PrintDate'
        ,'PriceMatrixCode'
        ,'Remarks'
        ,'Distributor'
        ,'Salesman'
        ,'isPassed'
        ,'PODate'
        ,'ForbidPartial'
    ];
}
