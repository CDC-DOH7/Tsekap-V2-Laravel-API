<?php

namespace App\Models\TsekapV2;

use Illuminate\Database\Eloquent\Model;

class Citizenship extends Model
{
    protected $connection = 'mysql';
    protected $table = 'citizenships';
    protected $fillable = [
        'created_at',
        'updated_at'
    ];
}
