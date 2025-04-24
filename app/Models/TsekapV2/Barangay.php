<?php

namespace App\Models\TsekapV2;

use Illuminate\Database\Eloquent\Model;

class Barangay extends Model
{
    protected $connection = 'mysql';
    protected $table = 'barangay';

    protected $fillable = [
        'created_at',
        'updated_at'
    ];
}
