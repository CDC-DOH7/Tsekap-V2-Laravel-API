<?php

namespace App\Models\TsekapV2;

use Illuminate\Database\Eloquent\Model;

class ProfileOtherDetails extends Model
{
    protected $connection = 'mysql';
    protected $table = 'profile_other_details';

    protected $fillable = [
        'profile_id',
        'purok_name',
        'sitio_name',
        'street_name'
    ];
}
