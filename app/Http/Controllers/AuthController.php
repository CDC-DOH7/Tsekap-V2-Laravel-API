<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Models\TsekapV2\UserHealthFacility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Exception;

class AuthController extends Controller
{
    // self-registration functionality
    public function selfRegisterUser(Request $request): JsonResponse
    {
        // Validate the input
        $validator = Validator::make($request->all(), [
            'fields' => 'required|array',
            'fields.fname' => 'nullable|string|max:255',
            'fields.mname' => 'nullable|string|max:255',
            'fields.lname' => 'nullable|string|max:255',
            'fields.muncity_id' => 'required|integer',
            'fields.province_id' => 'required|integer',
            'fields.facility_id' => 'required|integer',
            'fields.user_designation' => 'nullable|string|max:255',
            'fields.username' => 'required|string|max:255|unique:users,username',
            'fields.password' => 'required|string|min:8|max:255',
            'fields.contact' => 'required|string|max:11',
            'fields.user_priv' => 'required|integer',
            'fields.email' => 'sometimes|nullable|string|max:255|email',
        ]);

        // Trigger validation and return 422 if it fails
        try {
            $validatedFields = $validator->validate();
        } catch (ValidationException $e) {
            Log::error('Validation error in AuthController (function: selfRegisterUser)' . $e->getMessage());
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $validatedFields = $validatedFields['fields']; // Extract fields correctly

        // Check if the username already exists
        if (User::where('username', $validatedFields['username'])->exists()) {
            return response()->json(['status' => 'error', 'message' => 'This account has already been taken.'], 400);
        }

        try {
            // **Create and save new user**
            $user = User::create([
                'fname' => $validatedFields['fname'] ?? null,
                'mname' => $validatedFields['mname'] === null ? "" : $validatedFields['mname'],
                'lname' => $validatedFields['lname'] ?? null,
                'muncity' => $validatedFields['muncity_id'],
                'province' => $validatedFields['province_id'],
                'username' => $validatedFields['username'],
                // 'facility_id' => $validatedFields['facility_id'] ?? null,
                'password' => bcrypt($validatedFields['password']), // Encrypt password
                'contact' => $validatedFields['contact'],
                'user_priv' => $validatedFields['user_priv'],
                'verified' => 0, // make this field zero because it is self-registered and still needs to be verified
                'email' => !empty($validatedFields['email']) ? $validatedFields['email'] : null,
            ]);

            $userHfMapping = UserHealthFacility::create([
                'user_id' => $user['id'] ?? null,
                'facility_id' => $validatedFields['facility_id'],
                'user_designation' => $validatedFields['user_designation'],
                'assigned_at' => \Carbon\Carbon::now() // set current timestamp
            ]);
        } catch (Exception $e) {
            Log::error("Failure in user self-registration: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        $message = "Welcome to Tsekapp, " . $user['fname'] . " (" . $userHfMapping['user_designation'] . ")! Please wait for the admin to verify your account before you can log in.";
        return response()->json(['status' => 'success', 'message' => $message], 201);
    }

    // Used to login users
    public function login(Request $request): JsonResponse
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
            Log::error('Validation error in AuthController (function: login)' . $e->getMessage());
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        if (!$validatedFields['user'] || !$validatedFields['pass']) {
            return response()->json(['status' => 'error', 'message' => 'Username and password are required'], 400);
        }

        // Attempt to find the user by username
        $user = User::select(
            'users.*',
            'muncity.description as muncity_name',
            'province.description as province_name',
            'user_health_facility.facility_id',
            'facilities.name as facility_name',
            'facilities.province as facility_province_id',
            'facilities.muncity as facility_muncity_id',
            'facilities.brgy as facility_barangay_id',
            'facilities.facility_code as facility_code',
            'facilities.address as facility_address',
            'facilities.hospital_type as facility_hospital_type',
            'facilities.level as facility_level',
            'facilities.abbr as facility_abbr',
            'user_health_facility.user_designation as user_designation'
        )
            ->where('username', '=', $validatedFields['user'])
            ->join('muncity', 'users.muncity', '=', 'muncity.id')
            ->join('province', 'users.province', '=', 'province.id')
            ->leftJoin('user_health_facility', 'users.id', '=', 'user_health_facility.user_id')
            ->leftJoin('facilities', 'user_health_facility.facility_id', '=', 'facilities.id')
            ->first();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Invalid credentials.'], 401);
        }

        // Check if the user is verified
        if (!$user->verified) {
            return response()->json(['status' => 'error', 'message' => 'Your account is not yet verified. Please contact the administrator.'], 403);
        }

        if (Hash::check($validatedFields['pass'], strval($user->password))) {

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

            // Old backend return
            /*
            return response()->json([
                'data' => [
                    'user' => $user,
                    'facility' => $user->facility_id ? [
                        'id' => $user->facility_id,
                        'name' => $user->facility_name,
                        'province_id' => $user->facility_province_id,
                        'muncity_id' => $user->facility_muncity_id
                    ] : null,
                    'token' => $token, // Return Bearer token
                ],
                'status' => 'success',
            ])->withCookie($cookie);
            
            */

            return response()->json([
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'fname' => $user->fname,
                        'mname' => $user->mname,
                        'lname' => $user->lname,
                        'username' => $user->username,
                        'contact' => $user->contact,
                        'type_rdu' => $user->type_rdu,
                        'email' => $user->email,
                        'user_priv' => $user->user_priv,
                        'verified' => $user->verified,
                        'muncity' => $user->muncity,
                        'muncity_name' => $user->muncity_name,
                        'province' => $user->province,
                        'province_name' => $user->province_name,
                        'user_designation' => $user->user_designation,
                        'facility_id' => $user->facility_id,
                        'facility_name' => $user->facility_name,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at,
                    ],
                    'facility' => $user->facility_id ? [
                        'id' => $user->facility_id,
                        'facility_code' => $user->facility_code,
                        'name' => $user->facility_name,
                        'level' => $user->facility_level,
                        'abbr' => $user->facility_abbr,
                        'hospital_type' => $user->facility_hospital_type,
                        'province_id' => $user->facility_province_id,
                        'muncity_id' => $user->facility_muncity_id,
                        'barangay_id' => $user->facility_barangay_id,
                        'address' => $user->facility_address,
                    ] : null,
                    'token' => $token, // Return Bearer token
                ],
                'status' => 'success',
            ])->withCookie($cookie);
        }

        return response()->json(['status' => 'error', 'message' => 'Invalid credentials'], 401);
    }

    public function logoutAllSessions(Request $request): JsonResponse
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

    public function logout(Request $request): JsonResponse
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
