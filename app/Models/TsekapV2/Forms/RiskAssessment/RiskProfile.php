<?php

namespace App\Models\TsekapV2\Forms\RiskAssessment;

use Illuminate\Database\Eloquent\Model;

use App\Models\TsekapV2\Profile;
use App\Models\TsekapV2\Province;
use App\Models\TsekapV2\Muncity;
use App\Models\TsekapV2\Barangay;
use App\Models\TsekapV2\Facilities;
use App\Models\TsekapV2\Forms\RiskAssessment\RiskAssessmentForm;

class RiskProfile extends Model
{
    protected $connection = 'mysql';
    protected $table = 'risk_profile';
    protected $guarded = array();

       // Attributes
       protected $fillable = [
        'id',
        'profile_id',
        'lname',
        'fname',
        'mname',
        'suffix',
        'sex',
        'dob',
        'age',
        'civil_status',
        'religion',
        'other_religion',
        'contact',
        'province_id',
        'municipal_id',
        'barangay_id',
        'street',
        'purok',
        'sitio',
        'phic_id',
        'pwd_id',
        'citizenship',
        'other_citizenship',
        'indigenous_person',
        'employment_status',
        'facility_id_updated',
        'offline_entry',
        'encoded_by',
        'created_at',
        'updated_at',
    ];

    // Relationships
    public function profileId()
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function muncity()
    {
        return $this->belongsTo(Muncity::class, 'municipal_id');
    }

    public function barangay()
    {
        return $this->belongsTo(Barangay::class, 'barangay_id');
    }

    public function facility()
    {
        return $this->belongsTo(Facilities::class, 'facility_id_updated', 'id');
    }

    public function riskForm()
    {
        return $this->hasOne(RiskAssessmentForm::class, 'risk_profile_id', 'id');
    }
}