<?php

namespace App\Http\Controllers\TsekapV2\Analytics\ProfilingTargetSetting;

use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\TsekapV2\Barangay;
use App\Models\TsekapV2\Analytics\ProfilingTargetSetting\ProfilingTargetModel;
use App\Models\TsekapV2\Analytics\ProfilingTargetSetting\ProfilingTotalPopulationModel;

use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Exception;

class ProfilingTotalPopulationController extends Controller
{
    private function getAuthenticatedAdmin($username)
    {
        $queryUser = User::where('username', '=', $username)->first();

        // do not authorize update unless 1, 3, 10
        if ((!$queryUser || !in_array($queryUser->user_priv, [1, 3, 10])) || ($queryUser->verified !== 1)) {
            Log::error('Denied access to (ProfilingTargetController) for: ' . " " . $queryUser->id);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $queryUser;
    }

    private function getAuthenticatedUser($username)
    {
        $queryUser = User::where('username', '=', $username)->first();

        if (!$queryUser || $queryUser->verified !== 1) {
            Log::error('Denied access for: ' . " " . $queryUser->id);
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        return $queryUser;
    }

    public function createProfilingTotalPopulation(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedAdmin($request->user()->username); // This replaces Auth::check()

        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $fields = $request->input('fields');

        if (!$fields) {
            return response()->json(['error' => 'Invalid input: fields are required', 422]);
        }

        $validator = Validator::make($request->all(), [
            'fields' => 'required|array',
            'fields.muncity_id' => 'required|integer',
            'fields.total_population' => 'required|integer',
        ]);

        try {
            $validatedFields = $validator->validate();
        } catch (ValidationException $e) {
            Log::error('Validation error in the creation of a profiling population: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid input: fields are required'], 422);
        }

        $validatedFields = $validatedFields['fields'];

        if (ProfilingTotalPopulationModel::where('muncity_id', "=", $validatedFields['muncity_id'])->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Mapping already exists.'], 400);
        }

        try {
            $profilingTotalPopulation = ProfilingTotalPopulationModel::create([
                'muncity_id' => $validatedFields["muncity_id"],
                'total_population' => $validatedFields["total_population"],
            ]);
        } catch (Exception $e) {
            Log::error('Error in creation of a profiling target: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        $message = "Profiling total population: {$profilingTotalPopulation->id} created successfully";
        return response()->json(['status' => 'success', 'message' => $message], 200);
    }

    public function retrieveProfilingTotalPopulation(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedUser($request->user()->username); // This replaces Auth::check()

        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $validator = Validator::make($request->all(), [
            'muncity_id' => 'required|integer',
        ]);

        try {
            $validatedFields = $validator->validate();
        } catch (ValidationException $e) {
            Log::error('Validation error in retrieving profiling target: ' . $e->getMessage());
            return response()->json([
                'message' => 'Validation failed'
            ], 422);
        }

        $muncityId = $validatedFields['muncity_id'];

        $profilingTotalPopulation = ProfilingTotalPopulationModel::where('muncity_id', "=", $muncityId)->get();

        if ($profilingTotalPopulation->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'No profiling population found.'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $profilingTotalPopulation], 200);
    }

    public function retrieveProfilingTotalPopulationValues(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedUser($request->user()->username); // This replaces Auth::check()

        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $validator = Validator::make($request->all(), [
            'muncity_id' => 'required|integer',
        ]);

        try {
            $validatedFields = $validator->validate();
        } catch (ValidationException $e) {
            Log::error('Validation error in retrieving profiling population: ' . $e->getMessage());
            return response()->json([
                'message' => 'Validation failed'
            ], 422);
        }

        $muncityId = $validatedFields['muncity_id'];

        $profilingTotalPopulation = ProfilingTotalPopulationModel::where('muncity_id', "=", $muncityId)->first();

        if (!$profilingTotalPopulation) {
            return response()->json(['status' => 'error', 'message' => 'No profiling population found.'], 404);
        }

        // Return only the values
        return response()->json([
            'status' => 'success',
            'data' => [
                'muncity_id' => $profilingTotalPopulation->muncity_id,
                'total_population' => $profilingTotalPopulation->total_population,
            ]
        ], 200);
    }

    public function retrieveProfilingTotalPopulationValuesBreakdown(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedUser($request->user()->username);

        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $validator = Validator::make($request->all(), [
            'muncity_id' => 'required|integer',
        ]);

        try {
            $validatedFields = $validator->validate();
        } catch (ValidationException $e) {
            Log::error('Validation error in retrieving sex breakdown: ' . $e->getMessage());
            return response()->json([
                'message' => 'Validation failed'
            ], 422);
        }

        $muncityId = $validatedFields['muncity_id'];

        // retrieve the list of barangays in the muncity
        $barangays = Barangay::where('muncity_id', "=", $muncityId)
            ->select('id', 'muncity_id', 'description')
            ->get();

        if ($barangays->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'No barangays found.'], 404);
        }

        // Isolate only the barangay IDs as an array
        $barangayIds = $barangays->pluck('id')->toArray();

        $totalMalePopulationCount = 0;
        $totalFemalePopulationCount = 0;

        foreach ($barangayIds as $barangayId) {
            $population = ProfilingTargetModel::where('barangay_id', "=", $barangayId)
                ->select('id', 'male_population', 'female_population')
                ->first();

            $male_population = $population ? $population->male_population : 0;
            $female_population = $population ? $population->female_population : 0;

            $totalMalePopulationCount += $male_population;
            $totalFemalePopulationCount += $female_population;
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'muncity_id' => $muncityId,
                'male' => $totalMalePopulationCount,
                'female' => $totalFemalePopulationCount,
                'total_population' => $totalMalePopulationCount + $totalFemalePopulationCount,
            ] // <-- add this
        ], 200); // <-- and this
    }

    public function updateProfilingTotalPopulation(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedAdmin($request->user()->username); // This replaces Auth::check()

        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $fields = $request->input('fields');

        if (!$fields) {
            return response()->json(['error' => 'Invalid input: fields are required'], 422);
        }

        $validator = Validator::make($request->all(), [
            'fields' => 'required|array',
            'fields.muncity_id' => 'required|integer',
            'fields.total_population' => 'required|integer',
        ]);

        try {
            $validatedFields = $validator->validate();
        } catch (ValidationException $e) {
            Log::error('Validation error in attempt to update the profiling target: ' . $e->getMessage());
            return response()->json([
                'message' => 'Validation failed'
            ], 422);
        }

        $validatedFields = $validatedFields['fields'];

        $profilingTotalPopulation = ProfilingTotalPopulationModel::where('muncity_id', "=", $validatedFields['muncity_id'])->first();

        if (!$profilingTotalPopulation) {
            Log::error('Mapping does not exist for muncity_id: ' . $validatedFields['muncity_id']);
            return response()->json(['status' => 'error', 'message' => 'Mapping does not exist.'], 400);
        }

        try {
            $profilingTotalPopulation->update([
                'total_population' => $validatedFields['total_population'],
            ]);
        } catch (Exception $e) {
            Log::error('Error in updating a profiling total population: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        $message = "Profiling total population: {$profilingTotalPopulation->id} updated successfully";
        return response()->json(['status' => 'success', 'message' => $message], 200);
    }

    public function deleteProfilingTotalPopulation(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedAdmin($request->user()->username);

        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $fields = $request->input('fields');

        if (!$fields) {
            return response()->json(['error' => 'Invalid input: fields are required'], 422);
        }

        $validator = Validator::make($request->all(), [
            'fields' => 'required|array',
            'fields.muncity_id' => 'required|integer',
        ]);

        try {
            $validatedFields = $validator->validate();
        } catch (ValidationException $e) {
            Log::error('Validation error in attempt to delete the profiling total population: ' . $e->getMessage());
            return response()->json([
                'message' => 'Validation failed'
            ], 422);
        }

        $validatedFields = $validatedFields['fields'];

        $profilingTotalPopulation = ProfilingTotalPopulationModel::where('muncity_id', "=", $validatedFields['muncity_id'])->first();

        if (!$profilingTotalPopulation) {
            Log::error('Mapping does not exist for muncity_id: ' . $validatedFields['muncity_id']);
            return response()->json(['status' => 'error', 'message' => 'Mapping does not exist.'], 400);
        }

        try {
            $profilingTotalPopulation->delete();
        } catch (Exception $e) {
            Log::error('Error in deleting a profiling total population: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        $message = "Profiling population: {$$profilingTotalPopulation->id} deleted successfully";
        return response()->json(['status' => 'success', 'message' => $message], 200);
    }
}
