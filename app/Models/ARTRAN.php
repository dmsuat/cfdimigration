<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ARTRAN extends Model
{
    use HasFactory;
    public $timestamps = false; 

    protected $table = "SPM.ARTRAN";

    protected $fillable = ['Docno'
      ,'Applyto'
      ,'Doctype'
      ,'Customercode'
      ,'Paymentcode'
      ,'Docdate'
      ,'Duedate'
      ,'Docamt'
      ,'Recuser'
      ,'Recdate'
      ,'Dochome'
      ,'Distributor'
    ];
}
