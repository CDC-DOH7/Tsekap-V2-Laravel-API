<?php

namespace App\Models\TsekapV2\Analytics\ProfilingTargetSetting;

use Illuminate\Database\Eloquent\Model;

class ProfilingTargetPerMuncityModel extends Model
{
    protected $connection = 'mysql';

    protected $table = "profiling_target_per_muncity";

    protected $fillable = [
        'id',
        'province_id',
        'muncity_id',
        'male_target',
        'female_target',
        'total_target',

        // system metadata
        'created_at',
        'updated_at'
    ];
}
