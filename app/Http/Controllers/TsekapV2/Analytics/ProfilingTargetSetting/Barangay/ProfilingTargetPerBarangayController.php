<?php

namespace App\Http\Controllers\TsekapV2\Analytics\ProfilingTargetSetting\Barangay;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\TsekapV2\Analytics\ProfilingTargetSetting\ProfilingTargetPerBarangayModel;

use Exception;
use Psy\Util\Json;

class ProfilingTargetPerBarangayController extends Controller
{
    private function getAuthenticatedAdmin(?string $username): JsonResponse|User
    {
        $queryUser = User::where('username', '=', $username)->first();

        // do not authorize update unless 1, 3, 5, 10
        if ((!$queryUser || !in_array($queryUser->getAttribute('user_priv'), [1, 3, 5, 10])) || ($queryUser->getAttribute('verified') !== 1)) {
            Log::error('Denied access to (ProfilingTargetPerBarangayController) for: ' . " " . ($queryUser ? $queryUser->getAttribute('id') : 'unknown'));
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $queryUser;
    }

    private function getAuthenticatedUser(?string $username): JsonResponse|User
    {
        $queryUser = User::where('username', '=', $username)->first();

        if (!$queryUser || $queryUser->getAttribute('verified') !== 1) {
            Log::error('Denied access for: ' . ($queryUser ? $queryUser->getAttribute('id') : 'unknown'));
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        return $queryUser;
    }

    public function checkTargetMappingPerBarangayExists(Request $request): JsonResponse
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedAdmin($request->user()->getAttribute('username'));
        if ($user instanceof JsonResponse) {
            return $user;
        }

        // Use query parameters for GET
        $validator = Validator::make($request->query(), [
            'muncity_id' => 'required|integer',
            'barangay_id' => 'required|integer',
        ]);

        try {
            $validatedFields = $validator->validate();
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
            ], 422);
        }

        $exists = ProfilingTargetPerBarangayModel::where('muncity_id', $validatedFields['muncity_id'])
            ->where('barangay_id', $validatedFields['barangay_id'])
            ->exists();

        return response()->json([
            'status' => 'success',
            'exists' => $exists,
        ], 200);
    }

    public function createTargetPerBarangay(Request $request): JsonResponse
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedAdmin($request->user()->getAttribute('username')); // This replaces Auth::check()

        if ($user instanceof JsonResponse) {
            return $user;
        }

        $fields = $request->input('fields');

        if (!$fields) {
            return response()->json(['error' => 'Invalid input: fields are required'], 422);
        }

        $validator = Validator::make($request->all(), [
            'fields' => 'required|array',
            'fields.muncity_id' => 'required|integer',
            'fields.barangay_id' => 'required|integer',
            'fields.male_target' => 'required|integer',
            'fields.female_target' => 'required|integer',
            'fields.total_target' => 'required|integer',
        ]);

        try {
            $validatedFields = $validator->validate();
        } catch (ValidationException $e) {
            Log::error('Validation error in the creation of a profiling target (barangay): ' . $e->getMessage());
            return response()->json([
                'message' => 'Validation failed'
            ], 422);
        }

        $validatedFields = $validatedFields['fields'];

        if (ProfilingTargetPerBarangayModel::where('muncity_id', "=", $validatedFields['muncity_id'])
            ->where('barangay_id', "=", $validatedFields['barangay_id'])
            ->exists()
        ) {
            return response()->json(['status' => 'error', 'message' => 'Mapping already exists.'], 400);
        }

        try {
            $target = ProfilingTargetPerBarangayModel::create([
                'muncity_id' => $validatedFields["muncity_id"],
                'barangay_id' => $validatedFields["barangay_id"],
                'male_target' => $validatedFields["male_target"],
                'female_target' => $validatedFields["female_target"],
                'total_target' => $validatedFields["total_target"],
            ]);
        } catch (Exception $e) {
            Log::error('Error in creation of a profiling target (barangay): ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        $message = "Profiling target (barangay): (" . ($target ? $target->getAttribute('id') : 0) . ") created successfully";
        return response()->json(['status' => 'success', 'message' => $message], 200);
    }

    public function retrieveTargetPerBarangay(Request $request): JsonResponse
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedUser($request->user()->getAttribute('username'));
        if ($user instanceof JsonResponse) {
            return $user;
        }

        // Use query parameters for GET
        $validator = Validator::make($request->query(), [
            'muncity_id' => 'required|integer',
            'barangay_id' => 'required|integer',
        ]);

        try {
            $validatedFields = $validator->validate();
        } catch (ValidationException $e) {
            Log::error('Validation error in retrieving target values (barangay): ' . $e->getMessage());
            return response()->json([
                'message' => 'Validation failed'
            ], 422);
        }

        $barangayId = $validatedFields['barangay_id'];
        $muncityId = $validatedFields['muncity_id'];

        $target = ProfilingTargetPerBarangayModel::where('muncity_id', $muncityId)
            ->where('barangay_id', $barangayId)
            ->first();

        if (!$target) {
            return response()->json(['status' => 'error', 'message' => 'No profiling target (barangay) found.'], 404);
        }

        // Return only the values
        return response()->json([
            'status' => 'success',
            'data' => [
                'muncity_id' => $target->getAttribute('muncity_id'),
                'barangay_id' => $target->getAttribute('barangay_id'),
                'male_target' => $target->getAttribute('male_target'),
                'female_target' => $target->getAttribute('female_target'),
                'total_target' => $target->getAttribute('total_target'),
            ]
        ], 200);
    }

    public function updateTargetPerBarangay(Request $request): JsonResponse
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedAdmin($request->user()->getAttribute('username')); // This replaces Auth::check()

        if ($user instanceof JsonResponse) {
            return $user;
        }

        $fields = $request->input('fields');

        if (!$fields) {
            return response()->json(['error' => 'Invalid input: fields are required'], 422);
        }

        $validator = Validator::make($request->all(), [
            'fields' => 'required|array',
            'fields.muncity_id' => 'required|integer',
            'fields.barangay_id' => 'required|integer',
            'fields.male_target' => 'required|integer',
            'fields.female_target' => 'required|integer',
            'fields.total_target' => 'required|integer',
        ]);

        try {
            $validatedFields = $validator->validate();
        } catch (ValidationException $e) {
            Log::error('Validation error in attempt to update the profiling target (barangay): ' . $e->getMessage());
            return response()->json([
                'message' => 'Validation failed'
            ], 422);
        }

        $validatedFields = $validatedFields['fields'];

        $target = ProfilingTargetPerBarangayModel::where('muncity_id', "=", $validatedFields['muncity_id'])
            ->where('barangay_id', "=", $validatedFields['barangay_id'])
            ->first();

        if (!$target) {
            Log::error('Mapping does not exist for barangay_id: ' . $validatedFields['barangay_id']);
            return response()->json(['status' => 'error', 'message' => 'Mapping does not exist.'], 400);
        }

        try {
            $target->update([
                'male_target' => $validatedFields['male_target'],
                'female_target' => $validatedFields['female_target'],
                'total_target' => $validatedFields['total_target'],
            ]);
        } catch (Exception $e) {
            Log::error('Error in updating a profiling target: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        $message = "Profiling target (barangay) for: (" .  ($target ? $target->getAttribute('id') : 0) . ") has been updated successfully";
        return response()->json(['status' => 'success', 'message' => $message], 200);
    }

    public function deleteTargetPerBarangay(Request $request): JsonResponse
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedAdmin($request->user()->getAttribute('username'));

        if ($user instanceof JsonResponse) {
            return $user;
        }

        $fields = $request->input('fields');

        if (!$fields) {
            return response()->json(['error' => 'Invalid input: fields are required'], 422);
        }

        $validator = Validator::make($request->all(), [
            'fields' => 'required|array',
            'fields.muncity_id' => 'required|integer',
            'fields.barangay_id' => 'required|integer',
        ]);

        try {
            $validatedFields = $validator->validate();
        } catch (ValidationException $e) {
            Log::error('Validation error in attempt to delete the profiling target (barangay): ' . $e->getMessage());
            return response()->json([
                'message' => 'Validation failed'
            ], 422);
        }

        $validatedFields = $validatedFields['fields'];

        $target = ProfilingTargetPerBarangayModel::where('muncity_id', "=", $validatedFields['muncity_id'])
            ->where('barangay_id', "=", $validatedFields['barangay_id'])
            ->first();

        if (!$target) {
            Log::error('Mapping does not exist for barangay_id: ' . $validatedFields['barangay_id']);
            return response()->json(['status' => 'error', 'message' => 'Mapping does not exist.'], 400);
        }

        try {
            $target->delete();
        } catch (Exception $e) {
            Log::error('Error in deleting a profiling target (barangay): ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        $message = "Profiling target (barangay): (" . ($target ? $target->getAttribute('id') : 0) . ") has been deleted successfully";
        return response()->json(['status' => 'success', 'message' => $message], 200);
    }
}
