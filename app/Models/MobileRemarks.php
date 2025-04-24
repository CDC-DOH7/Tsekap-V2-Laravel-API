<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileRemarks extends Model
{
    protected $table = "mobile_remarks"; // Specify the table name if it's different from the model name
    protected $fillable = ['user_id', 'remarks']; // Specify the fillable fields for mass assignment
}
