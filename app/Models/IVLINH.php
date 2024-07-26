<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IVLINH extends Model
{
    use HasFactory;
    public $timestamps = false; 
    protected $table = "SPM.IVLINH";

    protected $fillable = [
        'Docno'
        ,'Productcode'
        ,'Docdate'
        ,'Location'
        ,'Uom'
        ,'Tranuom'
        ,'Soh'
        ,'Qty'
        ,'Unitcost'
        ,'Amount'
        ,'Avecost'
        ,'Rownum'
        ,'Eventid'
        ,'Trantype'
        ,'Ref2'
        ,'Ref3'
        ,'Currency'
        ,'Unitcosthme'
        ,'Totalhme'
        ,'ExpiryDate'
        ,'ManufactureDate'
        ,'Distributor'
        ,'BatchNo'
    ];
}
