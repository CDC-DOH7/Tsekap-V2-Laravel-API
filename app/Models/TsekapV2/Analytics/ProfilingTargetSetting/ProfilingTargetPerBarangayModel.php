<?php

namespace App\Models\TsekapV2\Analytics\ProfilingTargetSetting;

use Illuminate\Database\Eloquent\Model;

class ProfilingTargetPerBarangayModel extends Model
{
    protected $connection = 'mysql';

    protected $table = "profiling_target_per_barangay";

    protected $fillable = [
        'id',
        'muncity_id',
        'barangay_id',
        'male_target',
        'female_target',

        // system metadata
        'created_at',
        'updated_at'
    ];
}
