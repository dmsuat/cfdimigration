<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IVHDRH extends Model
{
    use HasFactory;
    public $timestamps = false; 
    protected $table = "SPM.IVHDRH";

    protected $fillable = [
        'Docno'
        ,'Docdate'
        ,'Desc1'
        ,'Desc2'
        ,'Trantype'
        ,'BU'
        ,'Supcode'
        ,'Procstat'
        ,'Spare1'
        ,'Spare2'
        ,'Spare3'
        ,'Recuser'
        ,'Recdate'
        ,'Moduser'
        ,'Moddate'
        ,'Postuser'
        ,'Postdate'
        ,'Distributor'
        ,'ReferenceNo'
        ,'Customer'
        ,'Salesman'
    ];
}
