<?php

namespace App\Models\TsekapV2;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $connection = 'mysql';
    protected $table = 'province';

    protected $fillable = [
        // metadata/timestamps
        'updated_at',
        'created_at'
    ];
}
