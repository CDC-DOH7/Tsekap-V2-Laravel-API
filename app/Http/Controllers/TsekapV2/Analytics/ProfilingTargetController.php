<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\TsekapV2\Analytics\ProfilingTargetModel;
use Exception;

class ProfilingTargetController extends Controller
{
    private function getDescriptor(int $requestCode)
    {
        $code = "";
        switch ($requestCode) {
            case 0: // philpen risk profiling
                $code = "PRP";
                break;
            case 1:
                $code = "PIF"; // patient injury form
                break;
            // may add more here in case of other profiling forms to be made
            default:
                $code = "";
                break;
        }
        return $code;
    }

    private function generateUniqueId(Request $request)
    {
        $fields = $request->input('fields');
        $user = $request->user();

        $descriptor = $this->getDescriptor($fields->request_code);
        if ($descriptor == "") {
            return response()->json(['error' => 'Invalid request code'], 422);
        }

        // Generate a unique ID
        $uniqueId = $descriptor . "-" . $user->facility_id;

        return response()->json(['unique_id' => $uniqueId]);
    }

    private function getAuthenticatedAdmin($username)
    {
        $queryUser = User::where('username', '=', $username)->first();

        // do not authorize update unless 1, 3, 10
        if ((!$queryUser || !in_array($queryUser->user_priv, [1, 3, 10])) || ($queryUser->verified !== 1)) {
            Log::error('Denied access to (ProfilingTargetController) for: ' . " " . $queryUser->id);
            return response()->json(['error' => 'Unauthorized'], 401);
        }
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
        $generatedUniqueId = $this->generateUniqueId($request);

        if (ProfilingTargetModel::where('unique_id', $generatedUniqueId)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Mapping already exists.'], 400);
        }

        try {
            $profilingTarget = ProfilingTargetModel::create([
                'unique_id' => $generatedUniqueId,
                'facility_id' => $request->user()->facility_id,
                'male_population' => $validatedFields["fields.male_population"],
                'female_population' => $validatedFields["fields.female_population"],
            ]);
        } catch (Exception $e) {
            Log::error('Error in creation of a profiling target: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        $message = "Profiling target: " . $profilingTarget->id . "created successfully";
        return response()->json(['status' => 'success', 'message' => $message], 200);
    }

    public function retrieveProfilingTarget(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedAdmin($request->user()->username); // This replaces Auth::check()

        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $profilingTarget = ProfilingTargetModel::where('facility_id', $request->user()->facility_id)->get();

        if ($profilingTarget->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'No profiling target found.'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $profilingTarget], 200);
    }

    public function retrieveProfilingTargetById(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedAdmin($request->user()->username); // This replaces Auth::check()

        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $profilingTarget = ProfilingTargetModel::where('id', $request->input('fields.id'))->first();

        if (!$profilingTarget) {
            return response()->json(['status' => 'error', 'message' => 'No profiling target found.'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $profilingTarget], 200);
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
            'fields.id' => 'required|integer|exists:profiling_targets,id',
            'fields.male_population' => 'required|integer',
            'fields.female_population' => 'required|integer'
        ]);

        try {
            $validatedFields = $validator->validate();
        } catch (ValidationException $e) {
            Log::error('Validation error in deleting profiling target: ' . $e->getMessage());
            return response()->json([
                'message' => 'Validation failed'
            ], 422);
        }

        $validatedFields = $validatedFields['fields'];

        try {
            $profilingTarget = ProfilingTargetModel::findOrFail($validatedFields['id']);
            $profilingTarget->update([
                'male_population' => $validatedFields['male_population'],
                'female_population' => $validatedFields['female_population'],
            ]);
        } catch (Exception $e) {
            Log::error('Error in updating a profiling target: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        $message = "Profiling target: " . $profilingTarget->id . " updated successfully";
        return response()->json(['status' => 'success', 'message' => $message], 200);
    }

    public function deleteProfilingTarget(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedAdmin($request->user()->username); // This replaces Auth::check()

        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $fields = $request->input('fields');

        if (!$fields || !isset($fields['id'])) {
            return response()->json(['error' => 'Invalid input: ID is required'], 422);
        }

        $validator = Validator::make($request->all(), [
            'fields.id' => 'required|integer|exists:profiling_targets,id',
        ]);

        try {
            $validatedFields = $validator->validate();
        } catch (ValidationException $e) {
            Log::error('Validation error in deleting profiling target: ' . $e->getMessage());
            return response()->json([
                'message' => 'Validation failed'
            ], 422);
        }

        try {
            $profilingTarget = ProfilingTargetModel::findOrFail($validatedFields['fields']['id']);
            $profilingTarget->delete();
        } catch (Exception $e) {
            Log::error('Error in a deletion of a profiling target: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        $message = "Profiling target: " . $validatedFields['fields']['id'] . " deleted successfully";
        return response()->json(['status' => 'success', 'message' => $message], 200);
    }
}
