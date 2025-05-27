<?php

namespace App\Models\TsekapV2\Analytics\ProfilingTargetSetting;

use Illuminate\Database\Eloquent\Model;

class ProfilingTotalPopulationModel extends Model
{
    protected $connection = 'mysql';

    protected $table = "profiling_total_population";

    protected $fillable = [
        'id',
        'muncity_id',
        'total_population',

        // system metadata
        'created_at',
        'updated_at'
    ];
}
