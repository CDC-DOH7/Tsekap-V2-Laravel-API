<?php

namespace App\Http\Controllers;

use Exception;

use App\Models\User;
use App\Models\TsekapV2\UserHealthFacility;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Ensure that the request contains all fields
        $fields = $request->all();

        // **Validate the input**
        $fieldsValidator = Validator::make($fields, [
            'fname' => 'nullable|string|max:255',
            'mname' => 'nullable|string|max:255',
            'lname' => 'nullable|string|max:255',
            'muncity' => 'required|integer',
            'province' => 'required|integer',
            'facility_id' => 'required|integer',
            'user_designation' => 'nullable|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:8|max:255',
            'contact' => 'required|string|max:11',
            'user_priv' => 'required|integer',
            'email' => 'string|max:255'
        ]);

        // **Trigger validation and return 422 if it fails**
        try {
            $validatedFields = $fieldsValidator->validate();
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        // **Check if the username already exists**
        $existingUser = User::where('username', "=", $validatedFields['username'])->first();

        if ($existingUser) {
            return response()->json(['status' => 'error', 'message' => 'This account has already been taken.'], 400);
        }

        try {
            // **Create and save new user**
            $user = User::create([
                'fname' => $validatedFields['fname'] ?? null,
                'mname' => $validatedFields['mname'] ?? null,
                'lname' => $validatedFields['lname'] ?? null,
                'muncity' => $validatedFields['muncity'],
                'province' => $validatedFields['province'],
                'facility_id' => $validatedFields['facility_id'],
                'username' => $validatedFields['username'],
                'password' => bcrypt($validatedFields['password']), // Encrypt password
                'contact' => $validatedFields['contact'],
                'user_priv' => $validatedFields['user_priv'],
                'email' => $validatedFields['email']
            ]);

            $userHfMapping = UserHealthFacility::create([
                'user_id' => $user['id'] ?? null,
                'facility_id' => $validatedFields['facility_id'],
                'user_designation' => $validatedFields['user_designation'],
                'assigned_at' => \Carbon\Carbon::now() // set current timestamp
            ]);

        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        $message = "Welcome to Tsekapp, " . $user['fname'] . " (" . $userHfMapping['user_designation'] . ")!";
        return response()->json(['status' => 'success', 'message' => $message], 201);
    }

    // Used to login users
    public function login(Request $request)
    {
        // Ensure that the request contains all fields
        $fields = $request->all();

        // **Validate the input**
        $fieldsValidator = Validator::make($fields, [
            'user' => 'required|string|max:255',
            'pass' => 'required|string|max:255',
        ]);

        // **Trigger validation and return 422 if it fails**
        try {
            $validatedFields = $fieldsValidator->validate();
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        if (!$validatedFields['username'] || !$validatedFields['password']) {
            return response()->json(['status' => 'error', 'message' => 'Username and password are required'], 400);
        }

        // Attempt to find the user by username
        $user = User::select(
            'users.*',
            'muncity.description as muncity_name',
            'province.description as province_name',
            'user_health_facility.facility_id',
            'facilities.name as facility_name',
            'user_health_facility.user_designation as user_designation'
        )
            ->where('username', '=', $validatedFields['username'])
            ->join('muncity', 'users.muncity', '=', 'muncity.id')
            ->join('province', 'users.province', '=', 'province.id')
            ->leftJoin('user_health_facility', 'users.id', '=', 'user_health_facility.user_id')
            ->leftJoin('facilities', 'user_health_facility.facility_id', '=', 'facilities.id')
            ->first();

        if ($user && Hash::check($validatedFields['password'], $user->password)) {

            // Generate Sanctum token
            $token = $user->createToken('auth_token')->plainTextToken;

            // Store token in HTTP-only, secure cookie
            $cookie = Cookie::make('auth_token', $token, 60, '/', null, true, true);

            /*
            // OPTIONAL: Add XSRF and CSRF tokens
            
            // Generate a CSRF token
            $csrfToken = csrf_token();

            // Set the XSRF-TOKEN and X-CSRF-TOKEN cookies
            $xsrfCookie = Cookie::make('XSRF-TOKEN', $csrfToken, 60);
            $csrfCookie = Cookie::make('X-CSRF-TOKEN', $csrfToken, 60);
            */

            return response()->json([
                'data' => [
                    'user' => $user,
                    'facility' => $user->facility_id ? [
                        'id' => $user->facility_id,
                        'name' => $user->facility_name,
                    ] : null,
                    'token' => $token, // Return Bearer token
                ],
                'status' => 'success',
            ])->withCookie($cookie);
        }

        return response()->json(['status' => 'error', 'message' => 'Invalid credentials'], 401);
    }

    public function logoutAllSessions(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $request->user(); // This replaces Auth::check()

        // Check if the user exists
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // revoke all tokens 
        $request->user()->tokens()->delete();

        return response()->json(['status' => 'success', 'message' => 'Logged out successfully'], 200);
    }

    public function logout(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $request->user(); // This replaces Auth::check()

        // Check if the user exists
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Revoke the current access token
        $request->user()->currentAccessToken()->delete();

        return response()->json(['status' => 'success', 'message' => 'Logged out successfully'], 200);
    }
}
