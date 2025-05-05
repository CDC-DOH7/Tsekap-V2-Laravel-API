<?php

namespace App\Http\Controllers\TsekapV2\Forms;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GeneralDataController extends Controller
{
    private function getAuthenticatedUser($username)
    {
        $queryUser = User::where('username', '=', $username)->first();

        if (!$queryUser || $queryUser->verified !== 1) {
            Log::error('Denied access for: ' . $username);
            return response()->json(['error' => 'User not found'], 404);
        }

        return $queryUser;
    }

    public function retrieveAllForms(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedUser($request->user()->username); // Use query parameter for GET request
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $fields = [
            'filter' => $request->query('filter', null),
            'keyword' => $request->query('keyword', null),
            'start_date' => $request->query('start_date', null),
            'end_date' => $request->query('end_date', null),
        ];

        $filter = $fields['filter'];
        $keyword = $fields['keyword'];
        $startDate = $fields['start_date'];
        $endDate = $fields['end_date'];

        // Parse dates from MM-DD-YYYY to YYYY-MM-DD
        if ($startDate) {
            $startDate = Carbon::createFromFormat('m-d-Y', $startDate)->format('Y-m-d');
        }
        if ($endDate) {
            $endDate = Carbon::createFromFormat('m-d-Y', $endDate)->format('Y-m-d');
        }

        // Define the profiles to query
        $profileTypes = [
            'RiskProfile' => [
                'table' => 'risk_profile',
                'columns' => [
                    'id',
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
                    'offline_entry',
                    'encoded_by',
                    'created_at',
                    'updated_at'
                ],
                'joins' => [
                    ['users', 'risk_profile.encoded_by', '=', 'users.id'],
                    ['muncity', 'risk_profile.municipal_id', '=', 'muncity.id'],
                    ['province', 'risk_profile.province_id', '=', 'province.id']
                ],
                'additional_columns' => [
                    'muncity.description as municipal_name',
                    'province.description as province_name',
                    DB::row('CONCAT(users.fname, " ", users.mname, " ", users.lname) as encoder')
                ]
            ],
        ];

        $results = [];

        foreach ($profileTypes as $profileType => $config) {
            $query = DB::table($config['table'])->select(
                array_merge(
                    array_map(fn($col) => "{$config['table']}.{$col}", $config['columns']),
                    $config['additional_columns']
                )
            );

            // Apply joins
            foreach ($config['joins'] as $join) {
                $query->join($join[0], $join[1], $join[2], $join[3]);
            }

            // Apply user privilege filters
            if (!in_array($user->user_priv, [1, 3, 10])) {
                $query->where("{$config['table']}.facility_id_updated", "=", $user->facility_id);
            }

            // Apply keyword filter
            if ($keyword) {
                $query->where(function ($q) use ($filter, $keyword, $config) {
                    $columns = array_combine($config['columns'], $config['columns']);

                    if ($filter && isset($columns[$filter])) {
                        $q->where("{$config['table']}.{$filter}", 'like', "%$keyword%");
                    } else {
                        foreach ($columns as $column) {
                            $q->orWhere("{$config['table']}.{$column}", 'like', "%$keyword%");
                        }
                    }
                });
            }

            // Apply date range filter
            if ($startDate && $endDate) {
                $query->whereBetween("{$config['table']}.created_at", ["$startDate 00:00:00", "$endDate 23:59:59"]);
            } elseif ($startDate) {
                $query->whereDate("{$config['table']}.created_at", '>=', "$startDate 00:00:00");
            } elseif ($endDate) {
                $query->whereDate("{$config['table']}.created_at", '<=', "$endDate 23:59:59");
            }

            // Paginate and collect results
            $results[$profileType] = $query->simplePaginate(30);
        }
        return response()->json($results, 200);
    }

    public function retrieveRecentlyUploadedForms(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedUser($request->user()->username); // Use query parameter for GET request
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $fields = [
            'filter' => $request->query('filter', null),
            'keyword' => $request->query('keyword', null),
        ];

        $filter = $fields['filter'];
        $keyword = $fields['keyword'];
        $today = now()->startOfDay();

        // Define the profiles to query
        $profileTypes = [
            'RiskProfile' => [
                'table' => 'risk_profile',
                'columns' => [
                    'id',
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
                    'offline_entry',
                    'encoded_by',
                    'created_at',
                    'updated_at'
                ],
                'joins' => [
                    ['muncity', 'risk_profile.municipal_id', '=', 'muncity.id'],
                    ['province', 'risk_profile.province_id', '=', 'province.id']
                ],
                'additional_columns' => [
                    'muncity.description as municipal_name',
                    'province.description as province_name'
                ]
            ],
        ];

        $results = [];

        foreach ($profileTypes as $profileType => $config) {
            $query = DB::table($config['table'])->select(
                array_merge(
                    array_map(fn($col) => "{$config['table']}.{$col}", $config['columns']),
                    $config['additional_columns']
                )
            );

            // Apply joins
            foreach ($config['joins'] as $join) {
                $query->join($join[0], $join[1], $join[2], $join[3]);
            }

            // Apply user privilege filters
            if (!in_array($user->user_priv, [1, 3, 10])) {
                $query->where("{$config['table']}.facility_id_updated", "=", $user->facility_id);
            }

            // Apply keyword filter
            if ($keyword) {
                $query->where(function ($q) use ($filter, $keyword, $config) {
                    $columns = array_combine($config['columns'], $config['columns']);

                    if ($filter && isset($columns[$filter])) {
                        $q->where("{$config['table']}.{$filter}", 'like', "%$keyword%");
                    } else {
                        foreach ($columns as $column) {
                            $q->orWhere("{$config['table']}.{$column}", 'like', "%$keyword%");
                        }
                    }
                });
            }

            // Filter by today's date
            $query->whereDate("{$config['table']}.created_at", '=', $today);

            // Paginate and collect results
            $results[$profileType] = $query->simplePaginate(30);
        }
        return response()->json($results, 200);
    }
}
