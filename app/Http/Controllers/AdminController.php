<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\TsekapV2\UserHealthFacility;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    private function getAuthenticatedUser($username)
    {
        $queryUser = User::where('username', '=', $username)->first();

        if (!$queryUser || $queryUser->user_priv !== 1 || $queryUser->verified !== 1) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        return $queryUser;
    }

    public function registerUser(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $admin = $this->getAuthenticatedUser($request->user()->username);

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
                'user_id' => $user->id,
                'facility_id' => $validatedFields['facility_id'],
                'user_designation' => $validatedFields['user_designation'],
                'assigned_at' => now(),
            ]);
        } catch (Exception $e) {
            Log::error('Error registering user: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['status' => 'error', 'message' => 'An error occurred while creating the user.'], 500);
        }

        $message = "Welcome to Tsekapp, " . $user->fname . " (" . $userHfMapping->user_designation . ")!";
        return response()->json(['status' => 'success', 'message' => $message], 201);
    }

    public function resetUserPassword(Request $request)
    {
        $admin = $this->getAuthenticatedUser($request->user()->username);

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

        return response()->json(['status' => 'success', 'message' => 'Password successfully updated for ' . $existingUser->username], 200);
    }
}
