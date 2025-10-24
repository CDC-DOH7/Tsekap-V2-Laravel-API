<?php

namespace App\Models\TsekapV2\Websockets;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\TsekapV2\Facilities;

class NotificationModel extends Model
{
    protected $connection = 'mysql';
    protected $table = 'push_notifications';
    protected $fillable = [
        'facility_id',
        'user_id',
        'title',
        'message',
        'is_read',
        'data',
    ];

    protected $cast = [
        'is_read' => 'boolean',
        'data' => 'array',
    ];

    public function facility()
    {
        return $this->belongsTo(Facilities::class, 'facility_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
