<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiptsPrincipalHDR extends Model
{
    use HasFactory;
    public $timestamps = false; 
    protected $table = "SPM.ReceiptsPrincipalHDR";

    protected $fillable = [
            'RPTransNo'
        ,'RPTransDate'
        ,'Distributor'
        ,'Principal'
        ,'POTransNo'
        ,'InvoiceNo'
        ,'DRNo'
        ,'Remarks'
        ,'TotalCost'
        ,'RecUser'
        ,'RecDate'
        ,'ModUser'
        ,'ModDate'
        ,'PostStatus'
        ,'PostUser'
        ,'PostDate'
        ,'Reason'
        ,'TransType'
        ,'ActualReceiptDate'
    ];
}
