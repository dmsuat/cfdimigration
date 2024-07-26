<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnappliedAmount extends Model
{
    use HasFactory;

    public $timestamps = false; 
    protected $table = "SPM.UnappliedAmount";

    protected $fillable = [
        'Docno'
        ,'Applyto'
        ,'Doctype'
        ,'Customercode'
        ,'Distributor'
        ,'Salesman'
        ,'Docdate'
        ,'Duedate'
        ,'Docamt'
        ,'Recuser'
        ,'Recdate'
        ,'Dochome'
    ];
}
