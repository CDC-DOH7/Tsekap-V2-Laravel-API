<?php

namespace App\Models\TsekapV2;

use Illuminate\Database\Eloquent\Model;

class Facilities extends Model
{
    protected $connection = 'mysql';
    protected $table = 'facilities';

    protected $fillable = [
        'facility_code',
        'name',
        'latitude',
        'longitude',
        'abbr',
        'address',
        'brgy',
        'muncity',
        'province',
        'contact',
        'email',
        'status',
        'picture',
        'chief_hospital',
        'level',
        'hospital_type',
        'tricity_id',
        'referral_used',
    ];
}
