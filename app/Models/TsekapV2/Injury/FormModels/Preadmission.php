<?php

namespace App\Models\TsekapV2\Injury\FormModels;

use Illuminate\Database\Eloquent\Model;

class Preadmission extends Model
{
    protected $connection = 'mysql';
    protected $table = 'resupre_admission';
}
