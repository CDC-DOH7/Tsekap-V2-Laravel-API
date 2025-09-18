<?php

namespace App\Http\Controllers\TsekapV2\Analytics\ProfilingTargetSetting\Muncity;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\TsekapV2\Analytics\ProfilingPopulationSetting\ProfilingPopulationPerMuncityModel;

use Exception;

class ProfilingTotalPerMuncityController extends Controller
{
    private function getAuthenticatedAdmin($username)
    {
        $queryUser = User::where('username', '=', $username)->first();

        // do not authorize update unless 1, 3, 5, and 10
        if ((!$queryUser || !in_array($queryUser->getAttribute('user_priv'), [1, 3, 5, 10])) || ($queryUser->getAttribute('verified') !== 1)) {
            Log::error('Denied access to (ProfilingTotalPerMuncityController) for: ' . ($queryUser ? $queryUser->getAttribute('id') : 'unknown'));
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

    public function checkPopulationMappingPerMuncityExists(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedAdmin($request->user()->getAttribute('username'));

        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $validator = Validator::make($request->all(), [
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

        $exists = ProfilingPopulationPerMuncityModel::where('province_id', $validatedFields['province_id'])
            ->where('muncity_id', $validatedFields['muncity_id'])
            ->exists();

        return response()->json([
            'status' => 'success',
            'exists' => $exists,
        ], 200);
    }

    public function createTotalsPerMuncity(Request $request)
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
            'fields.male_population' => 'required|integer',
            'fields.female_population' => 'required|integer',
            'fields.total_population' => 'required|integer',
        ]);

        try {
            $validatedFields = $validator->validate();
        } catch (ValidationException $e) {
            Log::error('Validation error in the creation of a profiling total (barangay): ' . $e->getMessage());
            return response()->json(['message' => 'Validation failed'], 422);
        }

        $validatedFields = $validatedFields['fields'];

        if (ProfilingPopulationPerMuncityModel::where('province_id', "=", $validatedFields['province_id'])
            ->where('muncity_id', "=", $validatedFields['muncity_id'])
            ->exists()
        ) {
            return response()->json(['status' => 'error', 'message' => 'Mapping already exists.'], 400);
        }

        try {
            $totals = ProfilingPopulationPerMuncityModel::create([
                'province_id' => $validatedFields["province_id"],
                'muncity_id' => $validatedFields["muncity_id"],
                'male_population' => $validatedFields["male_population"],
                'female_population' => $validatedFields["female_population"],
                'total_population' => $validatedFields["total_population"],
            ]);
        } catch (Exception $e) {
            Log::error('Error in creation of profiling totals (muncity): ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        $message = "Profiling totals (muncity): (" . ($totals ? $totals->getAttribute('id') : 0) . ") created successfully";
        return response()->json(['status' => 'success', 'message' => $message], 200);
    }

    public function retrieveTotalsPerMuncity(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedUser($request->user()->getAttribute('username'));

        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $validator = Validator::make($request->query(), [
            'province_id' => 'required|integer',
            'muncity_id' => 'required|integer',
        ]);

        try {
            $validatedFields = $validator->validate();
        } catch (ValidationException $e) {
            Log::error('Validation error in retrieving total population values (muncity): ' . $e->getMessage());
            return response()->json([
                'message' => 'Validation failed'
            ], 422);
        }

        $provinceId = $validatedFields['province_id'];
        $muncityId = $validatedFields['muncity_id'];

        $totals = ProfilingPopulationPerMuncityModel::where('province_id', $provinceId)
            ->where('muncity_id', $muncityId)
            ->first();

        if (!$totals) {
            return response()->json(['status' => 'error', 'message' => 'No profiling totals (muncity) found.'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'province_id' => $totals->getAttribute('province_id'),
                'muncity_id' => $totals->getAttribute('muncity_id'),
                'male_population' => $totals->getAttribute('male_population'),
                'female_population' => $totals->getAttribute('female_population'),
                'total_population' => $totals->getAttribute('total_population'),
            ]
        ], 200);
    }

    public function updateTotalsPerMuncity(Request $request)
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
            'fields.male_population' => 'required|integer',
            'fields.female_population' => 'required|integer',
            'fields.total_population' => 'required|integer',
        ]);

        try {
            $validatedFields = $validator->validate();
        } catch (ValidationException $e) {
            Log::error('Validation error in attempt to update the profiling totals (muncity): ' . $e->getMessage());
            return response()->json([
                'message' => 'Validation failed'
            ], 422);
        }

        $validatedFields = $validatedFields['fields'];

        $totals = ProfilingPopulationPerMuncityModel::where('province_id', "=", $validatedFields['province_id'])
            ->where('muncity_id', "=", $validatedFields['muncity_id'])
            ->first();

        if (!$totals) {
            Log::error('Mapping does not exist for muncity_id: ' . $validatedFields['muncity_id']);
            return response()->json(['status' => 'error', 'message' => 'Mapping does not exist.'], 400);
        }

        try {
            $totals->update([
                'male_population' => $validatedFields['male_population'],
                'female_population' => $validatedFields['female_population'],
                'total_population' => $validatedFields['total_population'],
            ]);
        } catch (Exception $e) {
            Log::error('Error in updating a profiling totals: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        $message = "Profiling totals (muncity) for: (" . ($totals ? $totals->getAttribute('id') : 0) . ") has been updated successfully";
        return response()->json(['status' => 'success', 'message' => $message], 200);
    }

    public function deleteTotalsPerMuncity(Request $request)
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
            'fields.province_id' => 'required|integer',
            'fields.muncity_id' => 'required|integer',
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

        $totals = ProfilingPopulationPerMuncityModel::where('province_id', "=", $validatedFields['province_id'])
            ->where('muncity_id', "=", $validatedFields['muncity_id'])
            ->first();

        if (!$totals) {
            Log::error('Mapping does not exist for muncity_id: ' . $validatedFields['muncity_id']);
            return response()->json(['status' => 'error', 'message' => 'Mapping does not exist.'], 400);
        }

        try {
            $totals->delete();
        } catch (Exception $e) {
            Log::error('Error in deleting a profiling totals for (muncity): ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        $message = "Profiling totals (muncity): (" . ($totals ? $totals->getAttribute('id') : 0) . ") has been deleted successfully";
        return response()->json(['status' => 'success', 'message' => $message], 200);
    }
}
