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
        $query = Profile::select('unique_id', 'fname', 'mname', 'lname', 'dob', 'id', 'barangay_id', 'muncity_id', 'province_id');

        // Add conditions based on filters
        foreach ($filters as $column => $value) {
            if ($column === 'fname' || $column === 'mname' || $column === 'lname'){
                $query->where($column, "like", "$value%"); // Partial match for strings
            } else {
                $query->where($column, '=', "$value"); // Exact match for everything
            }
        }

        // Simulate processing time by iterating and checking elapsed time
        while (true) {
            // Fetch 15 closest results
            $profiles = $query->orderBy('lname', 'asc')->limit(15)->get();

            if ($profiles->count() > 0) {
                break;
            }
        }

        \Log::info('Profiles retrieved:', $profiles->toArray());

        // Return the profiles as an array
        return $profiles->toArray();
    }
}
