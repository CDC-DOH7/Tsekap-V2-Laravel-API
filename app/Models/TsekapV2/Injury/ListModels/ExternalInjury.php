<?php

namespace App\Models\TsekapV2\Injury\ListModels;

use Illuminate\Database\Eloquent\Model;

class ExternalInjury extends Model
{
    protected $connection = 'mysql';
    protected $table = 'resu_externalinjury';
}
