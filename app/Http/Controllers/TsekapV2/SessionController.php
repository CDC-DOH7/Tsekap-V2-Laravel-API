<?php

namespace App\Http\Controllers\TsekapV2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Exception;

class SessionController extends Controller
{
    public function validate(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            // Fetch user details with related facility information
            $userDetails = User::select(
                'users.id',
                'users.fname as first_name',
                'users.mname as middle_name',
                'users.lname as last_name',
                'facilities.id as facility_id',
                'facilities.name as facility_name',
                'facilities.facility_code as facility_code',
                'user_health_facility.user_designation as user_designation'
            )
                ->leftJoin('user_health_facility', 'users.id', '=', 'user_health_facility.user_id')
                ->leftJoin('facilities', 'user_health_facility.facility_id', '=', 'facilities.id')
                ->where('users.username', '=', $user->getAttribute('username'))
                ->first();

            if (!$userDetails) {
                return response()->json(['error' => 'User not found'], 404);
            }

            return response()->json([
                'id' => $userDetails->id,
                'user_fname' => $userDetails->first_name,
                'user_mname' => $userDetails->middle_name,
                'user_lname' => $userDetails->last_name,
                'user_designation' => $userDetails->user_designation,
                'facility_id' => $userDetails->facility_id,
                'facility_code' => $userDetails->facility_code,
                'facility_name' => $userDetails->facility_name,
            ]);
        } catch (Exception $e) {
            Log::error('Validation error: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred'], 500);
        }
    }
}
