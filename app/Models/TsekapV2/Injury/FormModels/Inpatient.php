<?php

namespace App\Models\TsekapV2\Injury\FormModels;

use Illuminate\Database\Eloquent\Model;

class Inpatients extends Model
{
    protected $connection = 'mysql';
    protected $table = 'resuinpatients';
}
