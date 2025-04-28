<?php

namespace App\Http\Controllers\TsekapV2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Exception;
use App\Models\TsekapV2\UserHealthFacility;

class SessionController extends Controller
{
    private function getAuthenticatedUser($username)
    {
        $queryUser = User::where('username', '=', $username)->first();

        if (!$queryUser || $queryUser->verified !== 1) {
            Log::error('Denied access for: ' + $queryUser->id);
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        return $queryUser;
    }

    public function validate(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            // Check if the user is verified
            $queryUser = $this->getAuthenticatedUser($user->username);
            if ($queryUser instanceof \Illuminate\Http\JsonResponse) {
                return $queryUser;
            }

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
                ->where('users.username', '=', $user->username)
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
