<?php

namespace App\Models\TsekapV2\Forms\PatientInjuryForm;

use Illuminate\Database\Eloquent\Model;
use App\Models\TsekapV2\Province;
use App\Models\TsekapV2\Muncity;
use App\Models\TsekapV2\Barangay;
use App\Models\TsekapV2\Facilities;
use App\Models\TsekapV2\Profile;


class PatientInjuryGeneralData extends Model
{
    protected $connection = 'mysql';
    protected $table = 'patient_injury_form_general_data';
    protected $guarded = array();

    protected $fillable = [
        'id',
        'profile_id',

        'facility_id_updated',
        'name_of_reporting_facility',
        'address_of_reporting_facility',
        'type_of_dru',
        'type_of_patient',
        'encoded_by',
        'offline_entry',
        'hospital_case_no',

        'lname',
        'fname',
        'mname',
        'sex',
        'dob',
        'age',
        'age_in_months',
        'age_in_days',
        'age_bracket_id',

        'purok_sitio',
        'province_id',
        'municipal_id',
        'barangay_id',
        'phic_id',
        'created_at',
        'updated_at'
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id', 'id');
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
        return $this->belongsTo(Facilities::class, 'facility_id', 'id');
    }
}
