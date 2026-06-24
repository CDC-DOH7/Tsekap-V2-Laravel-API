<?php

namespace App\Models\TsekapV2\Forms\PchRiskAssessment;

use Illuminate\Database\Eloquent\Model;

use App\Models\TsekapV2\Profile;
use App\Models\TsekapV2\Province;
use App\Models\TsekapV2\Muncity;
use App\Models\TsekapV2\Barangay;
use App\Models\TsekapV2\Facilities;
use App\Models\TsekapV2\Forms\PchRiskAssessment\PchRiskAssessmentForm;

class PchRiskProfile extends Model
{
    protected $connection = 'mysql';
    protected $table = 'pch_risk_assessment_tool_profile';
    protected $guarded = [];

    // Attributes
    protected $fillable = [
        'id',
        'profile_id',
        'facility_id_updated',
        'encoded_by',
        'offline_entry',
        'prefix',
        'lname',
        'fname',
        'mname',
        'suffix',
        'sex',
        'dob',
        'age',
        'age_bracket_id',
        'birth_place',
        'civil_status',
        'educational_attainment',
        'employment_status',
        'occupation',

        'monthly_income',
        'religion',
        'other_religion',
        'indigenous',
        'blood_type',
        'mother_fname',
        'mother_mname',
        'mother_lname',
        'mother_dob',
        'country_id',
        'region_id',
        'province_id',
        'muncity_id',
        'barangay_id',
        'number_or_street_name',
        'zip_code',
        'email_address',
        'mobile_number',
        'landline_number',
        'family_member',
        'dswd_nhts_member',
        'four_ps_member',
        'facility_household_number',
        'family_serial_number',
        'philhealth_member',
        'philhealth_number',
        'philhealth_membership_type',
        'philhealth_category',
        'pcb_eligible',
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
        return $this->belongsTo(Muncity::class, 'muncity_id');
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
        return $this->hasOne(PchRiskAssessmentForm::class, 'pch_profile_id', 'id');
    }
}
