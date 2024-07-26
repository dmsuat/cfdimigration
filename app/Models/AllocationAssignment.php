<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllocationAssignment extends Model
{
    use HasFactory;

    public $timestamps = false; 
    protected $table = "SPM.AllocationAssignment";

    protected $fillable = [
        'AllocationNo'
      ,'AllocationDate'
      ,'SONo'
      ,'SODate'
      ,'ReqDeliveryDate'
      ,'Distributor'
      ,'ProductCode'
      ,'Location'
      ,'OrderQty'
      ,'OrderUOM'
      ,'AllocatedQty'
      ,'ExpiryDate'
      ,'ManualAllocQty'
      ,'ManualAllocUOM'
      ,'SORowNo'
      ,'RecUser'
      ,'RecDate'
      ,'ModUser'
      ,'ModDate'
      ,'PostStatus'
      ,'PostUser'
      ,'PostDate'
      ,'SOHCase'
      ,'SOHTins'
      ,'PendingDRCase'
      ,'PendingDRTins'
      ,'AvailableCase'
      ,'AvailableTins'
      ,'AllocatedCase'
      ,'AllocatedTins'
      ,'ConvertCase'
      ,'ConvertTins'
      ,'RemainingCase'
      ,'RemainingTins'
      ,'BatchNo'
    ];
}
