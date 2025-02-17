<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Validator;
use App\Models\TsekapV2\Profile;

class RetrieveProfileJob implements ShouldQueue
{
    use Queueable;
    protected $fields;
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
            'first_name' => 'nullable|string',
            'middle_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'dob' => 'nullable|string|date',
            'barangay_id' => 'nullable|integer',
            'municipal_id' => 'nullable|integer',
            'province_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            \Log::error('Validation failed:', $validator->errors()->toArray());
            return 'Validation failed';
        }

        // Extract and filter non-empty fields
        $filters = array_filter([
            'fname' => $this->fields['first_name'] ? $this->fields['first_name'] : null,
            'mname' => $this->fields['middle_name'] ? $this->fields['middle_name'] : null,
            'lname' => $this->fields['last_name'] ? $this->fields['last_name'] : null,
            'dob' => $this->fields['dob'] ? $this->fields['dob'] : null,
            'barangay_id' => $this->fields['barangay_id'] ? $this->fields['barangay_id'] : null,
            'muncity_id' => $this->fields['municipal_id'] ? $this->fields['municipal_id'] : null,
            'province_id' => $this->fields['province_id'] ? $this->fields['province_id'] : null,
        ]);

        // Initialize query
        $query = Profile::select(
            'profile.unique_id',
            'profile.fname',
            'profile.mname',
            'profile.lname',
            'profile.dob',
            'profile.id',
            'profile.barangay_id',
            'profile.muncity_id',
            'profile.province_id',
            'barangay.description as barangay_name',
            'muncity.description as muncity_name',
            'province.description as province_name'
        )
            ->join("barangay", "profile.barangay_id", "=", "barangay.id")
            ->join("muncity", "profile.muncity_id", "=", "muncity.id")
            ->join("province", "profile.province_id", "=", "province.id");

        // Add conditions based on filters
        foreach ($filters as $column => $value) {
            // Explicitly prefix the column with "profile." to avoid ambiguity
            $columnWithTable = "profile.$column";

            if (in_array($column, ['fname', 'mname', 'lname'], true)) {
                $query->where($columnWithTable, "like", "$value%"); // Partial match for strings
            } else {
                $query->where($columnWithTable, '=', $value); // Exact match for integers
            }
        }

        // Fetch 30 closest results
        $profiles = $query->orderBy('profile.lname', 'asc')->limit(30)->get();

        \Log::info('Profiles retrieved:', $profiles->toArray());

        return $profiles->toArray();
    }

}
