<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform',
        'latest_version',
        'download_url',
        'is_force_update',
        'release_notes',
    ];
}
