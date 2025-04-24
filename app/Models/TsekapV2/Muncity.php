<?php

namespace App\Models\TsekapV2;

use Illuminate\Database\Eloquent\Model;

class Muncity extends Model
{
    protected $connection = 'mysql';
    protected $table = 'muncity';
    protected $fillable = [
        // metadata/timestamps
        "created_at",
        "updated_at",
    ];
}
