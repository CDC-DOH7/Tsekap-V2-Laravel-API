<?php

namespace App\Models\TsekapV2;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $connection = 'mysql';
    protected $table = 'profile';
    protected $guarded = array();

    public function province(){
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function muncity(){
        return $this->belongsTo(Muncity::class, 'muncity_id');
    }

    public function barangay(){
        return $this->belongsTo(Barangay::class, 'barangay_id');
    }

    public function facility()
    {
        return $this->belongsTo(Facilities::class, 'report_facilityId', 'id');
    }
}
