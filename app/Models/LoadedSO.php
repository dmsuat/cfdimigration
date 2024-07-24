<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoadedSO extends Model
{
    use HasFactory;
    public $timestamps = false; 

    protected $table = "SPM.LoadedSO";

    protected $fillable = [
        'Distributor',
        'Salesman',
        'Docno',
        'Location',
        'OrderDate',
        'ReqDelDate',
        'PaymentTerm',
        'AccountCode',
        'ProductCode',
        'NumberofOrders',
        'OrderUOM',
        'SystemDate',
        'SystemUser',
        'Rownum',
        'LoadedBy',
        'DateLoaded',
        'Status',
        'SONo',
        'SendStatus',
        'Remarks',
    ];
}
