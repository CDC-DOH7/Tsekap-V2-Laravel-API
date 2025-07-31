<?php

namespace App\Http\Controllers\TsekapV2\Analytics\ProfilingTargetSetting\Muncity;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\TsekapV2\Analytics\ProfilingTargetSetting\ProfilingTargetPerMuncityModel;
use Exception;

class ProfilingTargetPerMuncityController extends Controller
{
    private function getAuthenticatedAdmin($username)
    {
        $queryUser = User::where('username', '=', $username)->first();

        // do not authorize update unless 1, 3, 10
        if ((!$queryUser || !in_array($queryUser->getAttribute('user_priv'), [1, 3, 10])) || ($queryUser->getAttribute('verified') !== 1)) {
            Log::error('Denied access to (ProfilingTargetPerMuncityController) for: ' . " " . ($queryUser ? $queryUser->getAttribute('id') : 'unknown'));
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $queryUser;
    }

    private function getAuthenticatedUser($username)
    {
        $queryUser = User::where('username', '=', $username)->first();

        if (!$queryUser || $queryUser->getAttribute('verified') !== 1) {
            Log::error('Denied access for: ' . ($queryUser ? $queryUser->getAttribute('id') : 'unknown'));
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        return $queryUser;
    }

    public function checkTargetMappingPerMuncityExists(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedAdmin($request->user()->getAttribute('username'));
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        // Use query parameters for GET
        $validator = Validator::make($request->query(), [
            'province_id' => 'required|integer',
            'muncity_id' => 'required|integer',
        ]);

        try {
            $validatedFields = $validator->validate();
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
            ], 422);
        }

        $exists = ProfilingTargetPerMuncityModel::where('province_id', $validatedFields['province_id'])
            ->where('muncity_id', $validatedFields['muncity_id'])
            ->exists();

        return response()->json([
            'status' => 'success',
            'exists' => $exists,
        ], 200);
    }

    public function createTargetPerMuncity(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedAdmin($request->user()->getAttribute('username')); // This replaces Auth::check()

        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $fields = $request->input('fields');

        if (!$fields) {
            return response()->json(['error' => 'Invalid input: fields are required'], 422);
        }

        $validator = Validator::make($request->all(), [
            'fields' => 'required|array',
            'fields.province_id' => 'required|integer',
            'fields.muncity_id' => 'required|integer',
            'fields.male_target' => 'required|integer',
            'fields.female_target' => 'required|integer',
            'fields.total_target' => 'required|integer',
        ]);

        try {
            $validatedFields = $validator->validate();
        } catch (ValidationException $e) {
            Log::error('Validation error in the creation of a profiling target (muncity): ' . $e->getMessage());
            return response()->json([
                'message' => 'Validation failed'
            ], 422);
        }

        $validatedFields = $validatedFields['fields'];

        if (ProfilingTargetPerMuncityModel::where('province_id', "=", $validatedFields['province_id'])
            ->where('muncity_id', "=", $validatedFields['muncity_id'])
            ->fresh()
            ->exists()
        ) {
            return response()->json(['status' => 'error', 'message' => 'Mapping already exists.'], 400);
        }

        try {
            $target = ProfilingTargetPerMuncityModel::create([
                'province_id' => $validatedFields["province_id"],
                'muncity_id' => $validatedFields["muncity_id"],
                'male_target' => $validatedFields["male_target"],
                'female_target' => $validatedFields["female_target"],
                'total_target' => $validatedFields["total_target"],
            ])->fresh();
        } catch (Exception $e) {
            Log::error('Error in creation of a profiling target (muncity): ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        $message = "Profiling target (muncity): (" . ($target ? $target->getAttribute('id') : 0) . ") created successfully";
        return response()->json(['status' => 'success', 'message' => $message], 200);
    }

    public function retrieveTargetPerMuncity(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedUser($request->user()->getAttribute('username'));
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        // Use query parameters for GET
        $validator = Validator::make($request->query(), [
            'province_id' => 'required|integer',
            'muncity_id' => 'required|integer',
        ]);

        try {
            $validatedFields = $validator->validate();
        } catch (ValidationException $e) {
            Log::error('Validation error in retrieving target values (muncity): ' . $e->getMessage());
            return response()->json([
                'message' => 'Validation failed'
            ], 422);
        }

        $provinceId = $validatedFields['province_id'];
        $muncityId = $validatedFields['muncity_id'];

        $target = ProfilingTargetPerMuncityModel::where('province_id', $provinceId)
            ->where('muncity_id', $muncityId)
            ->first();

        if (!$target) {
            return response()->json(['status' => 'error', 'message' => 'No profiling target (muncity) found.'], 404);
        }

        // Return only the values
        return response()->json([
            'status' => 'success',
            'data' => [
                'province_id' => $target->getAttribute('province_id'),
                'muncity_id' => $target->getAttribute('muncity_id'),
                'male_target' => $target->getAttribute('male_target'),
                'female_target' => $target->getAttribute('female_target'),
                'total_target' => $target->getAttribute('total_target'),
            ]
        ], 200);
    }

    public function updateTargetPerMuncity(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedAdmin($request->user()->getAttribute('username')); // This replaces Auth::check()

        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $fields = $request->input('fields');

        if (!$fields) {
            return response()->json(['error' => 'Invalid input: fields are required'], 422);
        }

        $validator = Validator::make($request->all(), [
            'fields' => 'required|array',
            'fields.province_id' => 'required|integer',
            'fields.muncity_id' => 'required|integer',
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

        $target = ProfilingTargetPerMuncityModel::where('province_id', "=", $validatedFields['province_id'])
            ->where('muncity_id', "=", $validatedFields['muncity_id'])
            ->first()
            ->fresh();

        if (!$target) {
            Log::error('Mapping does not exist for muncity_id: ' . $validatedFields['muncity_id']);
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

        $message = "Profiling target (muncity) for: (" .  ($target ? $target->getAttribute('id') : 0) . ") has been updated successfully";
        return response()->json(['status' => 'success', 'message' => $message], 200);
    }

    public function deleteTargetPerMuncity(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedAdmin($request->user()->getAttribute('username'));

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
            'fields.barangay_id' => 'required|integer',
        ]);

        try {
            $validatedFields = $validator->validate();
        } catch (ValidationException $e) {
            Log::error('Validation error in attempt to delete the profiling target (muncity): ' . $e->getMessage());
            return response()->json([
                'message' => 'Validation failed'
            ], 422);
        }

        $validatedFields = $validatedFields['fields'];

        $target = ProfilingTargetPerMuncityModel::where('province_id', "=", $validatedFields['province_id'])
            ->where('muncity_id', "=", $validatedFields['muncity_id'])
            ->first();

        if (!$target) {
            Log::error('Mapping does not exist for muncity_id: ' . $validatedFields['muncity_id']);
            return response()->json(['status' => 'error', 'message' => 'Mapping does not exist.'], 400);
        }

        try {
            $target->delete();
        } catch (Exception $e) {
            Log::error('Error in deleting a profiling target (muncity): ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        $message = "Profiling target (muncity): (" . ($target ? $target->getAttribute('id') : 0) . ") has been deleted successfully";
        return response()->json(['status' => 'success', 'message' => $message], 200);
    }
}
