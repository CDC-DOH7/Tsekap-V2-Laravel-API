<?php

namespace App\Models\TsekapV2;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $connection = 'mysql';
    protected $table = 'country';

    protected $fillable = [
        'country_code',
        'country_name',

        // System metadata
        'created_at',
        'updated_at'
    ];

    public function regions()
    {
        return $this->hasMany(Region::class, 'country_id');
    }
    public function municipalities()
    {
        return $this->hasManyThrough(Muncity::class, Region::class, 'country_id', 'region_id');
    }
}
