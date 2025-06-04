<?php

namespace App\Http\Controllers\TsekapV2\Analytics\ProfilingTargetSetting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\TsekapV2\Analytics\ProfilingTargetSetting\ProfilingTargetModel;
use Exception;

class ProfilingTargetController extends Controller
{
    private function getAuthenticatedAdmin($username)
    {
        $queryUser = User::where('username', '=', $username)->first();

        // do not authorize update unless 1, 3, 10
        if ((!$queryUser || !in_array($queryUser->user_priv, [1, 3, 10])) || ($queryUser->verified !== 1)) {
            Log::error('Denied access to (ProfilingTargetController) for: ' . " " . $queryUser->id);
            return response()->json(['error' => 'Unauthorized'], 401);
        }
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

    public function createProfilingTarget(Request $request)
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
            'fields.barangay_id' => 'required|integer',
            'fields.male_population' => 'required|integer',
            'fields.female_population' => 'required|integer'
        ]);

        try {
            $validatedFields = $validator->validate();
        } catch (ValidationException $e) {
            Log::error('Validation error in the creation of a profiling target: ' . $e->getMessage());
            return response()->json([
                'message' => 'Validation failed'
            ], 422);
        }

        $validatedFields = $validatedFields['fields'];

        if (ProfilingTargetModel::where('barangay_id', "=", $validatedFields['barangay_id'])->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Mapping already exists.'], 400);
        }

        try {
            $profilingTarget = ProfilingTargetModel::create([
                'barangay_id' => $validatedFields["barangay_id"],
                'male_population' => $validatedFields["male_population"],
                'female_population' => $validatedFields["female_population"],
            ]);
        } catch (Exception $e) {
            Log::error('Error in creation of a profiling target: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        $message = "Profiling target: {$profilingTarget->id} created successfully";
        return response()->json(['status' => 'success', 'message' => $message], 200);
    }

    public function retrieveProfilingTarget(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedUser($request->user()->username); // This replaces Auth::check()

        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $validator = Validator::make($request->all(), [
            'barangay_id' => 'required|integer',
        ]);

        try {
            $validatedFields = $validator->validate();
        } catch (ValidationException $e) {
            Log::error('Validation error in retrieving profiling target: ' . $e->getMessage());
            return response()->json([
                'message' => 'Validation failed'
            ], 422);
        }
        // $validatedFields = $validatedFields['fields'];
        $barangayId = $validatedFields['barangay_id'];

        $profilingTarget = ProfilingTargetModel::where('barangay_id', "=", $barangayId)->get();

        if ($profilingTarget->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'No profiling target found.'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $profilingTarget], 200);
    }

    public function retrieveProfilingTargetValues(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedUser($request->user()->username); // This replaces Auth::check()

        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $validator = Validator::make($request->all(), [
            'barangay_id' => 'required|integer',
        ]);

        try {
            $validatedFields = $validator->validate();
        } catch (ValidationException $e) {
            Log::error('Validation error in retrieving profiling target: ' . $e->getMessage());
            return response()->json([
                'message' => 'Validation failed'
            ], 422);
        }

        $barangayId = $validatedFields['barangay_id'];

        $profilingTarget = ProfilingTargetModel::where('barangay_id', "=", $barangayId)->first();

        if (!$profilingTarget) {
            return response()->json(['status' => 'error', 'message' => 'No profiling target found.'], 404);
        }

        // Return only the values
        return response()->json([
            'status' => 'success',
            'data' => [
                'barangay_id' => $profilingTarget->barangay_id,
                'male_population' => $profilingTarget->male_population,
                'female_population' => $profilingTarget->female_population,
            ]
        ], 200);
    }

    public function updateProfilingTarget(Request $request)
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
            'fields.barangay_id' => 'required|integer',
            'fields.male_population' => 'required|integer',
            'fields.female_population' => 'required|integer'
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

        $profilingTarget = ProfilingTargetModel::where('barangay_id', "=", $validatedFields['barangay_id'])->first();

        if (!$profilingTarget) {
            Log::error('Mapping does not exist for barangay_id: ' . $validatedFields['barangay_id']);
            return response()->json(['status' => 'error', 'message' => 'Mapping does not exist.'], 400);
        }

        try {
            $profilingTarget->update([
                'male_population' => $validatedFields['male_population'],
                'female_population' => $validatedFields['female_population'],
            ]);
        } catch (Exception $e) {
            Log::error('Error in updating a profiling target: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        $message = "Profiling target: {$profilingTarget->id} updated successfully";
        return response()->json(['status' => 'success', 'message' => $message], 200);
    }

    public function deleteProfilingTarget(Request $request)
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
            'fields.barangay_id' => 'required|integer',
        ]);

        try {
            $validatedFields = $validator->validate();
        } catch (ValidationException $e) {
            Log::error('Validation error in attempt to delete the profiling target: ' . $e->getMessage());
            return response()->json([
                'message' => 'Validation failed'
            ], 422);
        }

        $validatedFields = $validatedFields['fields'];

        $profilingTarget = ProfilingTargetModel::where('barangay_id', "=", $validatedFields['barangay_id'])->first();

        if (!$profilingTarget) {
            Log::error('Mapping does not exist for barangay_id: ' . $validatedFields['barangay_id']);
            return response()->json(['status' => 'error', 'message' => 'Mapping does not exist.'], 400);
        }

        try {
            $profilingTarget->delete();
        } catch (Exception $e) {
            Log::error('Error in deleting a profiling target: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        $message = "Profiling target: {$profilingTarget->id} deleted successfully";
        return response()->json(['status' => 'success', 'message' => $message], 200);
    }
}
