<?php

namespace App\Http\Controllers\TsekapV2\Forms;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Query\Builder;
use Carbon\Carbon;
use Exception;

class GeneralDataController extends Controller
{
    private function getProfileTypes(): array
    {
        $encoderColumns = [
            'muncity.description as municipal_name',
            'province.description as province_name',
            'barangay.description as barangay_name',
            DB::raw('CONCAT(users.fname, " ", users.mname, " ", users.lname) as encoder'),
        ];

        $encoderColumnsWithFacility = array_merge($encoderColumns, [
            'facilities.id as encoder_hf_id',
            'facilities.name as encoder_hf_name',
        ]);

        return [
            'RiskProfile' => [
                'table' => 'risk_profile',
                'muncity_col'   => 'municipal_id',
                'province_col'  => 'province_id',
                'barangay_col'  => 'barangay_id',
                'columns' => [
                    'id',
                    'profile_id',
                    'fname',
                    'mname',
                    'lname',
                    'dob',
                    'sex',
                    'age',
                    'religion',
                    'other_religion',
                    'citizenship',
                    'other_citizenship',
                    'indigenous_person',
                    'employment_status',
                    'contact',
                    'civil_status',
                    'barangay_id',
                    'municipal_id',
                    'province_id',
                    'facility_id_updated',
                    'phic_id',
                    'pwd_id',
                    'offline_entry',
                    'encoded_by',
                    'created_at',
                    'updated_at',
                ],
                'joins' => [
                    ['users',               'risk_profile.encoded_by',      '=', 'users.id'],
                    ['user_health_facility', 'users.id',                     '=', 'user_health_facility.user_id'],
                    ['facilities',          'facilities.id',                '=', 'user_health_facility.facility_id'],
                    ['barangay',            'risk_profile.barangay_id',     '=', 'barangay.id'],
                    ['muncity',             'risk_profile.municipal_id',    '=', 'muncity.id'],
                    ['province',            'risk_profile.province_id',     '=', 'province.id'],
                ],
                'additional_columns' => $encoderColumnsWithFacility,
            ],

            'PatientInjuryGeneralData' => [
                'table' => 'patient_injury_form_general_data',
                'muncity_col'   => 'perm_municipal_id',
                'province_col'  => 'perm_province_id',
                'barangay_col'  => 'perm_barangay_id',
                'columns' => [
                    'id',
                    'profile_id',
                    'facility_id_updated',
                    'encoded_by',
                    'offline_entry',
                    'lname',
                    'fname',
                    'mname',
                    'sex',
                    'dob',
                    'age',
                    'age_bracket_id',
                    'phic_id as philhealth_number',
                    'created_at',
                    'updated_at',
                ],
                'joins' => [
                    ['users',               'patient_injury_form_general_data.encoded_by',        '=', 'users.id'],
                    ['user_health_facility', 'users.id',                                           '=', 'user_health_facility.user_id'],
                    ['facilities',          'facilities.id',                                      '=', 'user_health_facility.facility_id'],
                    ['barangay',            'patient_injury_form_general_data.perm_barangay_id',  '=', 'barangay.id'],
                    ['muncity',             'patient_injury_form_general_data.perm_municipal_id', '=', 'muncity.id'],
                    ['province',            'patient_injury_form_general_data.perm_province_id',  '=', 'province.id'],
                ],
                'additional_columns' => $encoderColumnsWithFacility,
            ],

            'PchRiskProfile' => [
                'table' => 'pch_risk_assessment_tool_profile',
                'muncity_col'   => 'muncity_id',
                'province_col'  => 'province_id',
                'barangay_col'  => 'barangay_id',
                'columns' => [
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
                    'philhealth_category',
                    'pcb_eligible',
                    'created_at',
                    'updated_at',
                ],
                'joins' => [
                    ['users',               'pch_risk_assessment_tool_profile.encoded_by',   '=', 'users.id'],
                    ['user_health_facility', 'users.id',                                      '=', 'user_health_facility.user_id'],
                    ['facilities',          'facilities.id',                                 '=', 'user_health_facility.facility_id'],
                    ['barangay',            'pch_risk_assessment_tool_profile.barangay_id',  '=', 'barangay.id'],
                    ['muncity',             'pch_risk_assessment_tool_profile.muncity_id',   '=', 'muncity.id'],
                    ['province',            'pch_risk_assessment_tool_profile.province_id',  '=', 'province.id'],
                ],
                'additional_columns' => $encoderColumnsWithFacility,
            ],
        ];
    }

    private function getAuthenticatedUser(?string $username): User|JsonResponse
    {
        $queryUser = User::where('username', '=', $username)->first();

        if (!$queryUser || $queryUser->getAttribute('verified') !== 1) {
            Log::error('Denied access for: ' . $username);
            return response()->json(['error' => 'User not found'], 404);
        }

        return $queryUser;
    }

    private function getMuncityId(?string $muncity_name): ?int
    {
        if (empty($muncity_name)) {
            return null;
        }

        return DB::table('muncity')->where('description', $muncity_name)->value('id');
    }

    private function getUserHealthFacility(?int $user_id): ?object
    {
        return DB::table('user_health_facility')
            ->where('user_id', $user_id)
            ->first();
    }

    private function getFacilityMuncity(?int $facility_id)
    {
        $facility = DB::table('facilities')
            ->where('id', $facility_id)
            ->first();
        return $facility?->muncity;
    }

    private function getBarangayId(Request $request, ?string $barangay_name): ?int
    {
        // Unattached functionality, used for filtering by barangay

        // Get user's health facility
        $user_hf = $this->getUserHealthFacility($request->user()->getAttribute('id'));
        if (!$user_hf) {
            return null;
        }

        // Get muncity of the facility
        $muncity_id = $this->getFacilityMuncity($user_hf->facility_id);
        if (!$muncity_id) {
            return null;
        }

        return DB::table('barangay')->where('muncity_id', $muncity_id)->where('description', $barangay_name)->value('id');
    }

    // -------------------------------------------------------------------------
    // Shared query builder
    // -------------------------------------------------------------------------

    /**
     * Build the base query for a given profile config, applying joins, privilege
     * scoping, and keyword/filter conditions. Date constraints and pagination are
     * left to the caller so this method stays reusable.
     */
    private function buildProfileQuery(array $config, User $user, Request $request): Builder
    {
        $table = $config['table'];

        // Base select
        $query = DB::table($table)->select(array_merge(
            array_map(fn($col) => "{$table}.{$col}", $config['columns']),
            $config['additional_columns']
        ));

        // Joins
        foreach ($config['joins'] as [$related, $first, $op, $second]) {
            $query->join($related, $first, $op, $second);
        }

        // Privilege scoping
        $this->applyPrivilegeScope($query, $config, $user);

        // Keyword / filter
        $filter  = $request->query('filter');
        $keyword = $request->query('keyword');

        if ($keyword) {
            $this->applyKeywordFilter($query, $config, $filter, $keyword, $request);
        }

        return $query;
    }

    /**
     * Restrict rows to what the authenticated user is allowed to see based on
     * their privilege level (BHW/MHO = muncity, Province = province, others = all).
     */
    private function applyPrivilegeScope(Builder $query, array $config, User $user): void
    {
        $table     = $config['table'];
        $user_priv = $user->getAttribute('user_priv');
        $user_hf   = $this->getUserHealthFacility($user->getAttribute('id'));
        $hf_muncity = $user_hf ? $this->getFacilityMuncity($user_hf->facility_id) : null;

        if (in_array($user_priv, [2, 5]) && $hf_muncity) { // BHW / MHO
            $query->where("{$table}.{$config['muncity_col']}", '=', $hf_muncity);
        } elseif ($user_priv == 3) { // Province
            $query->where("{$table}.{$config['province_col']}", '=', $user->getAttribute('province_id'));
        }
        // Other privilege levels see all records — no additional constraint.
    }

    /**
     * Apply keyword searching, scoped to a specific filter column or a full
     * text search across all plain columns.
     */
    private function applyKeywordFilter(
        Builder $query,
        array $config,
        ?string $filter,
        string $keyword,
        Request $request
    ): void {
        $table = $config['table'];

        // Strip aliases so we can safely reference raw column names.
        $plainColumns = array_values(array_map(
            fn($col) => trim(preg_replace('/\s+as\s+\w+$/i', '', $col)),
            $config['columns']
        ));

        $query->where(function ($q) use ($table, $config, $filter, $keyword, $plainColumns, $request) {
            if ($filter === 'barangay') {
                $barangay_id = $this->getBarangayId($request, $keyword);
                $barangay_id
                    ? $q->where("{$table}.{$config['barangay_col']}", '=', $barangay_id)
                    : $q->whereRaw('1 = 0');
            } elseif ($filter === 'muncity' || $filter === 'municipal') {
                $muncity_id = $this->getMuncityId($keyword);
                $muncity_id
                    ? $q->where("{$table}.{$config['muncity_col']}", '=', $muncity_id)
                    : $q->whereRaw('1 = 0');
            } elseif ($filter === 'all' || !$filter) {
                // Full-text search across every simple (non-expression) column.
                foreach ($plainColumns as $column) {
                    if (!str_contains($column, '(') && !str_contains($column, '.')) {
                        $q->orWhere("{$table}.{$column}", 'like', "%{$keyword}%");
                    }
                }
            } elseif (in_array($filter, $plainColumns)) {
                $q->where("{$table}.{$filter}", 'like', "%{$keyword}%");
            }
        });
    }

    // -------------------------------------------------------------------------
    // Public endpoints
    // -------------------------------------------------------------------------

    public function retrieveAllForms(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser($request->user()->getAttribute('username'));
            if ($user instanceof JsonResponse) {
                return $user;
            }

            $formType  = $request->query('form_type');
            $startDate = $this->parseDate($request->query('start_date'));
            $endDate   = $this->parseDate($request->query('end_date'));

            $results = [];

            foreach ($this->getProfileTypes() as $profileType => $config) {
                if ($formType && $formType !== $profileType && $formType !== 'all') {
                    continue;
                }

                $query = $this->buildProfileQuery($config, $user, $request);

                // Date range
                if ($startDate && $endDate) {
                    $query->whereBetween("{$config['table']}.created_at", ["{$startDate} 00:00:00", "{$endDate} 23:59:59"]);
                } elseif ($startDate) {
                    $query->whereDate("{$config['table']}.created_at", '>=', $startDate);
                } elseif ($endDate) {
                    $query->whereDate("{$config['table']}.created_at", '<=', $endDate);
                }

                $query->orderByDesc("{$config['table']}.created_at");

                $results[$profileType] = $query->paginate(10, ['*'], 'page')->appends($request->except('page'));
            }

            return response()->json($results, 200);
        } catch (Exception $e) {
            Log::error('Error retrieving all forms: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['status' => 'error', 'message' => 'An error occurred while retrieving forms. Please try again later.'], 500);
        }
    }

    public function retrieveRecentlyUploadedForms(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser($request->user()->getAttribute('username'));
            if ($user instanceof JsonResponse) {
                return $user;
            }

            $today   = now()->toDateString();
            $results = [];

            foreach ($this->getProfileTypes() as $profileType => $config) {
                $query = $this->buildProfileQuery($config, $user, $request);

                $query->whereDate("{$config['table']}.created_at", '=', $today)
                    ->orderByDesc("{$config['table']}.created_at");

                $results[$profileType] = $query->paginate(50)->appends($request->except('page'));
            }

            return response()->json($results, 200);
        } catch (Exception $e) {
            Log::error('Error retrieving recently uploaded forms: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['status' => 'error', 'message' => 'An error occurred while retrieving recently uploaded forms. Please try again later.'], 500);
        }
    }
 
    // -------------------------------------------------------------------------
    // Utilities
    // -------------------------------------------------------------------------

    /** Parse a MM-DD-YYYY date string into YYYY-MM-DD, returning null on failure. */
    private function parseDate(?string $date): ?string
    {
        if (!$date) {
            return null;
        }

        try {
            return Carbon::createFromFormat('m-d-Y', $date)->toDateString();
        } catch (Exception) {
            return null;
        }
    }
}
