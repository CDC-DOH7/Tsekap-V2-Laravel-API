<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\TsekapV2\UserHealthFacility;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    // function used in getting authenticated admins
    private function getAuthenticatedAdmin($username)
    {
        $queryUser = User::where('username', '=', $username)->first();

        if (!$queryUser || $queryUser->getAttribute('user_priv') !== 1 || $queryUser->getAttribute('verified') !== 1) {
            Log::error('Denied administrative access for: ' . $queryUser->getAttribute('id'));
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        return $queryUser;
    }

    // function used in getting authenticated users
    private function getAuthenticatedUser($username)
    {
        $queryUser = User::where('username', '=', $username)->first();

        if (
            !$queryUser ||
            !in_array($queryUser->getAttribute('user_priv'), [1, 3, 5, 10]) ||
            $queryUser->getAttribute('verified') !== 1
        ) {
            Log::error('Denied administrative access for: ' . ($queryUser->getAttribute('id') ?? 'unknown'));
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        return $queryUser;
    }


    // register a user
    public function registerUser(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $admin = $this->getAuthenticatedUser($request->user()->getAttribute('username'));

        if ($admin instanceof \Illuminate\Http\JsonResponse) {
            return $admin; // Return the unauthorized response
        }

        // Validate the input
        $validator = Validator::make($request->all(), [
            'fields' => 'required|array',
            'fields.fname' => 'nullable|string|max:255',
            'fields.mname' => 'nullable|string|max:255',
            'fields.lname' => 'nullable|string|max:255',
            'fields.muncity' => 'required|integer',
            'fields.province' => 'required|integer',
            'fields.facility_id' => 'required|integer',
            'fields.user_designation' => 'nullable|string|max:255',
            'fields.username' => 'required|string|max:255|unique:users,username',
            'fields.password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&#]/',
            ],
            'fields.contact' => 'required|string|max:11',
            'fields.user_priv' => 'required|integer',
            'fields.email' => 'nullable|string|max:255|email',
            'fields.verified' => 'nullable|integer|in:0,1',
        ]);

        try {
            $validatedFields = $validator->validate();
        } catch (ValidationException $e) {
            Log::error("Validation error in AdminController (function: registerUser)" . " " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $validatedFields = $validatedFields['fields'];

        try {
            // Create and save new user
            $user = User::create([
                'fname' => $validatedFields['fname'] ?? null,
                'mname' => $validatedFields['mname'] === null ? "" : $validatedFields['mname'],
                'lname' => $validatedFields['lname'] ?? null,
                'muncity' => $validatedFields['muncity'],
                'province' => $validatedFields['province'],
                'username' => $validatedFields['username'],
                'password' => bcrypt($validatedFields['password']),
                'contact' => $validatedFields['contact'],
                'user_priv' => $validatedFields['user_priv'],
                'verified' => $validatedFields['verified'] ?? 1,
                'email' => $validatedFields['email'] ?? null,
            ]);

            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'Failed to create user.'], 500);
            }

            $userHfMapping = UserHealthFacility::create([
                'user_id' => $user->getAttribute('id'),
                'facility_id' => $validatedFields['facility_id'],
                'user_designation' => $validatedFields['user_designation'],
                'assigned_at' => \Carbon\Carbon::now(),
            ]);
        } catch (Exception $e) {
            Log::error('Error registering user: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['status' => 'error', 'message' => 'An error occurred while creating the user.'], 500);
        }

        $message = "Welcome to Tsekapp, " . $user->getAttribute('fname') . " (" . $userHfMapping->getAttribute('user_designation') . ")!";
        return response()->json(['status' => 'success', 'message' => $message], 201);
    }

    // reset a user's password
    public function resetUserPassword(Request $request)
    {
        $admin = $this->getAuthenticatedUser($request->user()->getAttribute('username'));

        if ($admin instanceof \Illuminate\Http\JsonResponse) {
            return $admin; // Return the unauthorized response
        }

        // Validate the input
        $validator = Validator::make($request->all(), [
            'fields' => 'required|array',
            'fields.username' => 'required|string|max:255',
            'fields.new_password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&#]/',
            ],
        ]);

        try {
            $validatedFields = $validator->validate();
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $validatedFields = $validatedFields['fields'];

        // Check if the user exists
        $existingUser = User::where('username', '=', $validatedFields['username'])->first();

        if (!$existingUser) {
            return response()->json(['status' => 'error', 'message' => 'User not found.'], 404);
        }

        // Update the password
        $existingUser->password = bcrypt($validatedFields['new_password']);
        $existingUser->save();

        return response()->json(['status' => 'success', 'message' => 'Password successfully updated for ' . $existingUser->getAttribute('username')], 200);
    }

    // verify a user
    public function verifyUser(Request $request)
    {
        $admin = $this->getAuthenticatedUser($request->user()->getAttribute('username'));

        if ($admin instanceof \Illuminate\Http\JsonResponse) {
            return $admin; // Return the unauthorized response
        }

        // Validate the input
        $validator = Validator::make($request->all(), [
            'fields.user_id' => 'required|integer',
        ]);

        try {
            $validatedFields = $validator->validate();
        } catch (ValidationException $e) {
            Log::error('Validation error in AdminController (function: verifyUser) ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        // Check if the user exists
        $existingUser = User::find($validatedFields['fields']['user_id']);

        if (!$existingUser) {
            return response()->json(['status' => 'error', 'message' => 'User not found.'], 404);
        }

        $existingUser->update([
            'verified' => true,
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User successfully verified.'
        ], 200);
    }

    // unverify a user
    public function unverifyUser(Request $request)
    {
        $admin = $this->getAuthenticatedUser($request->user()->getAttribute('username'));

        if ($admin instanceof \Illuminate\Http\JsonResponse) {
            return $admin; // Return the unauthorized response
        }

        // Validate the input
        $validator = Validator::make($request->all(), [
            'fields.user_id' => 'required|integer',
        ]);

        try {
            $validatedFields = $validator->validate();
        } catch (ValidationException $e) {
            Log::error('Validation error in AdminController (function: unverifyUser) ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        // Check if the user exists
        $existingUser = User::find($validatedFields['fields']['user_id']);

        if (!$existingUser) {
            return response()->json(['status' => 'error', 'message' => 'User not found.'], 404);
        }

        $existingUser->update([
            'verified' => false,
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User successfully unverified.'
        ], 200);
    }

    // list unverified users
    public function listUnverifiedUsers(Request $request)
    {
        $admin = $this->getAuthenticatedUser($request->user()->getAttribute('username'));

        if ($admin instanceof \Illuminate\Http\JsonResponse) {
            return $admin; // Return the unauthorized response
        }

        try {
            $users = User::where('verified', 0)
                ->select(['id', 'fname', 'mname', 'lname', 'username', 'user_priv'])
                ->latest('created_at')
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Unverified users retrieved successfully.',
                'users' => $users
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching unverified users: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching unverified users.',
            ], 500);
        }
    }

    // list all users, verified or not (priv 1)
    public function listAllUsers(Request $request)
    {
        $admin = $this->getAuthenticatedAdmin($request->user()->getAttribute('username'));

        if ($admin instanceof \Illuminate\Http\JsonResponse) {
            return $admin; // Return the unauthorized response
        }

        try {
            $users = User::select(['id', 'fname', 'mname', 'lname', 'username', 'user_priv', 'verified', 'created_at'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'User list retrieved successfully.',
                'users' => $users
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching users: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching users.',
            ], 500);
        }
    }

    public function listAllUsersByFacility(Request $request)
    {
        $admin = $this->getAuthenticatedUser($request->user()->getAttribute('username'));

        if ($admin instanceof \Illuminate\Http\JsonResponse) {
            return $admin; // Return unauthorized
        }

        try {
            // Only allow access if user_priv is 3, 5, or 10
            if (!in_array($admin->getAttribute('user_priv'), [3, 5, 10])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized to view users by facility.',
                ], 403);
            }

            // Get the admin's facility ID from the user_health_facility table
            $adminFacilityId = DB::table('user_health_facility')
                ->where('user_id', $admin->getAttribute('id'))
                ->value('facility_id');

            if (!$adminFacilityId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Health facility not found for the authenticated user.',
                ], 404);
            }

            // Join users with the user_health_facility table, exclude user_priv = 1
            $users = DB::table('users')
                ->join('user_health_facility', 'users.id', '=', 'user_health_facility.user_id')
                ->where('users.id', '!=', $admin->getAttribute('id'))
                ->where('user_health_facility.facility_id', $adminFacilityId)
                ->where('users.user_priv', '!=', 1)
                ->select('users.id', 'users.fname', 'users.mname', 'users.lname', 'users.username', 'users.user_priv', 'users.verified', 'users.created_at')
                ->orderBy('users.created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'User list retrieved successfully.',
                'users' => $users
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching users by facility: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching users.',
            ], 500);
        }
    }
}
