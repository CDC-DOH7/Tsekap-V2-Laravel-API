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
          // Start timing
          $startTime = time();

          // Validate the fields
          $validator = Validator::make($this->fields, [
              'firstname' => 'string',
              'middlename' => 'string',
              'lastname' => 'string',
              'dob' => 'date',
          ]);
  
          if ($validator->fails()) {
              \Log::error('Validation failed:', $validator->errors()->toArray());
              return 'Validation failed';
          }
  
          // Extract and filter non-empty fields
          $filters = array_filter([
              'fname' => $this->fields['firstname'] ? $this->fields['firstname'] : null,
              'mname' => $this->fields['middlename'] ? $this->fields['middlename'] : null,
              'lname' => $this->fields['lastname'] ? $this->fields['lastname'] : null,
              'dob' => $this->fields['dob'] ? $this->fields['dob']  : null,
          ]);
  
          // Initialize query
          $query = Profile::select('unique_id', 'fname', 'mname', 'lname', 'dob', 'id');
  
          // Add conditions based on filters
          foreach ($filters as $column => $value) {
              if ($column === 'dob') {
                  $query->where($column, $value); // Exact match for date
              } else {
                  $query->where($column, 'like', "%$value%"); // Partial match for strings
              }
          }
  
          // Simulate processing time by iterating and checking elapsed time
          while (true) {
              if (time() - $startTime >= 30) {
                  \Log::warning('Processing exceeded time limit');
                  return 'timeout';
              }
  
              // Fetch 15 closest results
              $profiles = $query->orderBy('lname', 'asc')->limit(15)->get();
  
              if ($profiles->count() > 0) {
                  break;
              }
  
              // Sleep for a short duration to avoid overloading the server in retries
              usleep(100000); // 0.1 seconds
          }
  
          \Log::info('Profiles retrieved:', $profiles->toArray());
  
          // Return the profiles as an array
          return $profiles->toArray();
    }
}
