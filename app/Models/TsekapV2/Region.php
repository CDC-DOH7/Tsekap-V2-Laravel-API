<?php

namespace App\Models\TsekapV2;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $connection = 'mysql';
    protected $table = 'region';

    protected $fillable = [
        'country_id',
        'region_code',
        'region_name',

        // system metadata
        'created_at',
        'updated_at'
    ];

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function municipalities()
    {
        return $this->hasMany(Muncity::class, 'region_id');
    }
}
