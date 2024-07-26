<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SOCreditCheck extends Model
{
    use HasFactory;
    public $timestamps = false; 
    protected $table = "SPM.SOCreditCheck";

    protected $fillable = [
        'SONo'
        ,'CustomerCode'
        ,'CreditLimit'
        ,'AvailableForSO'
        ,'OpenApprovedSO'
        ,'PendingDR'
        ,'ARAmount'
        ,'ApprovedDR'
        ,'ApprovedDM'
        ,'ApprovedCM'
        ,'ApprovedARBegBal'
        ,'ApprovedCol'
        ,'ApprovedColPDC'
        ,'ReturnofGoodStock'
        ,'ReturnofBadStock'
        ,'DMQAJ'
        ,'CMQAJ'
        ,'PrePayment'
        ,'BaseAging'
        ,'ActualAging'
        ,'CreditStatus'
        ,'AgingStatus'
        ,'Distributor'
    ];
}
