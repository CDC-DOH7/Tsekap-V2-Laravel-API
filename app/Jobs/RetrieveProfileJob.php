<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\TsekapV2\Profile;
use Illuminate\Support\Facades\DB;

class RetrieveProfileJob implements ShouldQueue
{
    use Queueable;

    protected array $fields;

    /**
     * Create a new job instance.
     */
    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    /**
     * Execute the job.
     */
    public function handle(): array|string
    {
        // Validate the fields
        $validator = Validator::make($this->fields, [
            'family_id' => 'nullable|string',
            'first_name' => 'nullable|string',
            'middle_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'dob' => 'nullable|date',
            'barangay_id' => 'nullable|integer',
            'municipal_id' => 'nullable|integer',
            'province_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed:', $validator->errors()->toArray());
            return 'Validation failed';
        }

        // Extract and filter non-empty fields efficiently
        $allowedKeys = [
            'family_id' => 'familyID',
            'first_name' => 'fname',
            'middle_name' => 'mname',
            'last_name' => 'lname',
            'dob' => 'dob',
            'barangay_id' => 'barangay_id',
            'municipal_id' => 'muncity_id',
            'province_id' => 'province_id',
        ];

        $filters = array_filter(array_intersect_key($this->fields, $allowedKeys), fn($value) => !is_null($value) && $value !== '');

        // Initialize query
        $query = Profile::select([
            'profile.id',
            'profile.unique_id',
            'profile.fname',
            'profile.mname',
            'profile.lname',
            'profile.suffix',
            'profile.sex',
            'profile.dob',
            'profile.relation',
            'profile.familyID',
            'profile.barangay_id',
            'profile.muncity_id',
            'profile.province_id',
            'profile.deceased',
            'profile.deceased_date',
            DB::raw("DATE_FORMAT(profile.created_at, '%Y-%m-%d %H:%i:%s') as created_at"), // Extract date only
            'barangay.description as barangay_name',
            'muncity.description as muncity_name',
            'province.description as province_name',
        ])
            ->join('barangay', 'profile.barangay_id', '=', 'barangay.id')
            ->join('muncity', 'profile.muncity_id', '=', 'muncity.id')
            ->join('province', 'profile.province_id', '=', 'province.id');

        // Apply filters efficiently
        foreach ($filters as $key => $value) {
            $column = "profile." . $allowedKeys[$key];

            $query->when(in_array($allowedKeys[$key], ['familyID', 'fname', 'mname', 'lname']), function ($q) use ($column, $value) {
                return $q->where($column, 'like', Str::lower($value) . '%');
            }, function ($q) use ($column, $value) {
                return $q->where($column, $value);
            });
        }

        // Fetch 50 results with sorting
        $profiles = $query->orderBy('profile.lname')->limit(50)->get();

        Log::info('Profiles retrieved:', $profiles->toArray());

        return $profiles->toArray();
    }
}