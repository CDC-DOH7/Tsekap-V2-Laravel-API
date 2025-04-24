<?php

namespace App\Models\TsekapV2;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $connection = 'mysql';
    protected $table = 'profile';
    protected $guarded = array();

    protected $fillable = [
        'unique_id',
        'familyID',
        'phicID',
        'nhtsID',
        'head',
        'relation',
        'fname',
        'mname',
        'lname',
        'suffix',
        'dob',
        'sex',
        'barangay_id',
        'muncity_id',
        'province_id',
        'income',
        'unmet',
        'water',
        'toilet',
        'education',
        'hypertension',
        'diabetic',
        'pwd',
        'pregnant',
        'dengvaxia',
        'created_at',
        'updated_at',
        'sitio_id',
        'purok_id',
        'birth_place',
        'civil_status',
        'religion',
        'other_religion',
        'contact',
        'height',
        'weight',
        'cancer',
        'cancer_type',
        'mental_med',
        'tbdots_med',
        'cvd_med',
        'covid_status',
        'menarche',
        'menarche_age',
        'newborn_screen',
        'newborn_text',
        'deceased',
        'deceased_date',
        'pwd_desc',
        'sexually_active',
        'nhts',
        'four_ps',
        'ip',
        'member_others',
        'balik_probinsya',
        'updated_by',
        'household_num',
        'philhealth_categ',
        'fourps_num',
        'health_group',
        'fam_plan',
        'fam_plan_method',
        'fam_plan_other_method',
        'fam_plan_status',
        'fam_plan_other_status',
        'other_med_history',
        'report_facilityId',
        'Hospital_caseno',
        'nameof_encoder',
        'designation',

        // metadata/timestamps
        'updated_at',
        'created_at'
    ];

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
        return $this->belongsTo(Facilities::class, 'report_facilityId', 'id');
    }
}
