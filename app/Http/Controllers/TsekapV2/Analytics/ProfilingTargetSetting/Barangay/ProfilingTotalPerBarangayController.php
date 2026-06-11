<?php

namespace App\Http\Controllers\TsekapV2\Analytics\ProfilingTargetSetting\Barangay;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Models\TsekapV2\Analytics\ProfilingPopulationSetting\ProfilingPopulationPerBarangayModel;

use Exception;

class ProfilingTotalPerBarangayController extends Controller
{
    public function checkPopulationMappingPerBarangayExists(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
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

        $exists = ProfilingPopulationPerBarangayModel::where('muncity_id', $validatedFields['muncity_id'])
            ->where('barangay_id', $validatedFields['barangay_id'])
            ->exists();

        return response()->json([
            'status' => 'success',
            'exists' => $exists,
        ], 200);
    }

    public function createTotalsPerBarangay(Request $request): JsonResponse
    {
        $fields = $request->input('fields');

        if (!$fields) {
            return response()->json(['error' => 'Invalid input: fields are required'], 422);
        }

        $validator = Validator::make($request->all(), [
            'fields' => 'required|array',
            'fields.muncity_id' => 'required|integer',
            'fields.barangay_id' => 'required|integer',
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

        if (ProfilingPopulationPerBarangayModel::where('muncity_id', "=", $validatedFields['muncity_id'])
            ->where('barangay_id', "=", $validatedFields['barangay_id'])
            ->exists()
        ) {
            return response()->json(['status' => 'error', 'message' => 'Mapping already exists.'], 400);
        }

        try {
            $totals = ProfilingPopulationPerBarangayModel::create([
                'muncity_id' => $validatedFields["muncity_id"],
                'barangay_id' => $validatedFields["barangay_id"],
                'male_population' => $validatedFields["male_population"],
                'female_population' => $validatedFields["female_population"],
                'total_population' => $validatedFields["total_population"],
            ]);
        } catch (Exception $e) {
            Log::error('Error in creation of profiling totals (barangay): ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        $message = "Profiling totals (barangay): (" . ($totals ? $totals->getAttribute('id') : 0) . ") created successfully";
        return response()->json(['status' => 'success', 'message' => $message], 200);
    }

    public function retrieveTotalsPerBarangay(Request $request): JsonResponse
    {
        // For GET requests, use query parameters
        $validator = Validator::make($request->query(), [
            'muncity_id' => 'required|integer',
            'barangay_id' => 'required|integer',
        ]);

        try {
            $validatedFields = $validator->validate();
        } catch (ValidationException $e) {
            Log::error('Validation error in retrieving total population values (barangay): ' . $e->getMessage());
            return response()->json([
                'message' => 'Validation failed'
            ], 422);
        }

        $barangayId = $validatedFields['barangay_id'];
        $muncityId = $validatedFields['muncity_id'];

        $totals = ProfilingPopulationPerBarangayModel::where('muncity_id', $muncityId)
            ->where('barangay_id', $barangayId)
            ->first();

        if (!$totals) {
            return response()->json(['status' => 'error', 'message' => 'No profiling totals (barangay) found.'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'muncity_id' => $totals->getAttribute('muncity_id'),
                'barangay_id' => $totals->getAttribute('barangay_id'),
                'male_population' => $totals->getAttribute('male_population'),
                'female_population' => $totals->getAttribute('female_population'),
                'total_population' => $totals->getAttribute('total_population'),
            ]
        ], 200);
    }

    public function updateTotalsPerBarangay(Request $request): JsonResponse
    {
        $fields = $request->input('fields');

        if (!$fields) {
            return response()->json(['error' => 'Invalid input: fields are required'], 422);
        }

        $validator = Validator::make($request->all(), [
            'fields' => 'required|array',
            'fields.muncity_id' => 'required|integer',
            'fields.barangay_id' => 'required|integer',
            'fields.male_population' => 'required|integer',
            'fields.female_population' => 'required|integer',
            'fields.total_population' => 'required|integer',
        ]);

        try {
            $validatedFields = $validator->validate();
        } catch (ValidationException $e) {
            Log::error('Validation error in attempt to update the profiling totals (barangay): ' . $e->getMessage());
            return response()->json([
                'message' => 'Validation failed'
            ], 422);
        }

        $validatedFields = $validatedFields['fields'];

        $totals = ProfilingPopulationPerBarangayModel::where('muncity_id', "=", $validatedFields['muncity_id'])
            ->where('barangay_id', "=", $validatedFields['barangay_id'])
            ->first();

        if (!$totals) {
            Log::error('Mapping does not exist for barangay_id: ' . $validatedFields['barangay_id']);
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

        $message = "Profiling totals (barangay) for: (" . ($totals ? $totals->getAttribute('id') : 0) . ") has been updated successfully";
        return response()->json(['status' => 'success', 'message' => $message], 200);
    }

    public function deleteTotalsPerBarangay(Request $request): JsonResponse
    {
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

        $totals = ProfilingPopulationPerBarangayModel::where('muncity_id', "=", $validatedFields['muncity_id'])
            ->where('barangay_id', "=", $validatedFields['barangay_id'])
            ->first();

        if (!$totals) {
            Log::error('Mapping does not exist for barangay_id: ' . $validatedFields['barangay_id']);
            return response()->json(['status' => 'error', 'message' => 'Mapping does not exist.'], 400);
        }

        try {
            $totals->delete();
        } catch (Exception $e) {
            Log::error('Error in deleting a profiling totals for (barangay): ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        $message = "Profiling totals (barangay): (" . ($totals ? $totals->getAttribute('id') : 0) . ") has been deleted successfully";
        return response()->json(['status' => 'success', 'message' => $message], 200);
    }
}
