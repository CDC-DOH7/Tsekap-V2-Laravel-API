<?php

namespace App\Models\TsekapV2\Analytics\ProfilingPopulationSetting;

use Illuminate\Database\Eloquent\Model;

class ProfilingPopulationPerMuncityModel extends Model
{
    protected $connection = 'mysql';

    protected $table = "profiling_population_per_muncity";

    protected $fillable = [
        'id',
        'province_id',
        'muncity_id',
        'male_population',
        'female_population',

        // system metadata
        'created_at',
        'updated_at'
    ];
}
