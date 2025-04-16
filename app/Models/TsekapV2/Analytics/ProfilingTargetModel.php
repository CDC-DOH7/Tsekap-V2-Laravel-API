<?php

namespace App\Models\TsekapV2\Analytics;

use Illuminate\Database\Eloquent\Model;

class ProfilingTargetModel extends Model
{
    protected $connection = 'mysql';

    protected $table = "profiling_target";

    protected $fillable = [
        "unique_id",
        "profiling_description",
        "facility_id",
        "male_population",
        "female_population"
    ];
}
