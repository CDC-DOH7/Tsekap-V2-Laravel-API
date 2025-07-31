<?php

namespace App\Models\TsekapV2\Analytics\ProfilingPopulationSetting;

use Illuminate\Database\Eloquent\Model;

class ProfilingPopulationPerBarangayModel extends Model
{
    protected $connection = 'mysql';

    protected $table = "profiling_population_per_barangay";

    protected $fillable = [
        'id',
        'muncity_id',
        'barangay_id',
        'male_population',
        'female_population',

        // system metadata
        'created_at',
        'updated_at'
    ];
}
