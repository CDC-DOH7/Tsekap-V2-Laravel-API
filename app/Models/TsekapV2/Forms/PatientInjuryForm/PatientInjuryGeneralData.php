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

        'perm_province_id',
        'perm_municipal_id',
        'perm_barangay_id',

        'temp_province_id',
        'temp_municipal_id',
        'temp_barangay_id',

        'phic_id',
        'created_at',
        'updated_at'
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id', 'id');
    }

    public function permProvince()
    {
        return $this->belongsTo(Province::class, 'perm_province_id');
    }

    public function permMuncity()
    {
        return $this->belongsTo(Muncity::class, 'perm_municipal_id');
    }

    public function permBarangay()
    {
        return $this->belongsTo(Barangay::class, 'perm_barangay_id');
    }

    public function tempProvince()
    {
        return $this->belongsTo(Province::class, 'temp_province_id');
    }

    public function tempMuncity()
    {
        return $this->belongsTo(Muncity::class, 'temp_municipal_id');
    }

    public function tempBarangay()
    {
        return $this->belongsTo(Barangay::class, 'temp_barangay_id');
    }

    public function facility()
    {
        return $this->belongsTo(Facilities::class, 'facility_id', 'id');
    }
}
