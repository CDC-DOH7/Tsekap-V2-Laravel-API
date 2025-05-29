<?php

namespace App\Models\TsekapV2\Websockets;

use Illuminate\Database\Eloquent\Model;

class NotificationModel extends Model
{
    protected $connection = 'mysql';
    protected $table = 'push_notifications';
    protected $fillable = [
        'origin_facility_id',
        'destination_facility_id',
        'sent_by_user_id',
        'title',
        'message',
        'is_read'
    ];
}
