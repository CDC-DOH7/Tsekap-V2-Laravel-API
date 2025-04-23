<?php

namespace App\Models\TsekapV2;

use Illuminate\Database\Eloquent\Model;

class UserHealthFacility extends Model
{
    protected $table = "user_health_facility";
    // public $timestamps = true;

    protected $fillable = [
        'user_id',
        'facility_id',
        'user_designation',
        'assigned_at',

        // metadata/timestamps
        'updated_at',
        'created_at'
    ];
}
