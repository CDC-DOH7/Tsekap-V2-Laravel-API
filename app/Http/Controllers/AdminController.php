<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\TsekapV2\UserHealthFacility;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function registerUser(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $admin = $request->user(); // This replaces Auth::check()

        // Check if the user exists
        if (!$admin || $admin->user_priv !== 1) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

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

    // reset anyone's password
    public function resetUserPassword(Request $request)
    {
        $admin = $request->user();

        if (!$admin || $admin->user_priv !== 1) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Validate the input
        $validator = Validator::make($request->all(), [
            'fields' => 'required|array',
            'fields.username' => 'required|string|max:255',
            'fields.new_password' => 'required|string|min:8|max:255'
        ]);

        try {
            $validatedFields = $validator->validate();
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        // Check if the user exists
        $existingUser = User::where('username', $validatedFields['fields']['username'])->first();

        if (!$existingUser) {
            return response()->json(['status' => 'error', 'message' => 'User not found.'], 404);
        }

        // Update the password
        $existingUser->password = bcrypt($validatedFields['fields']['new_password']);
        $existingUser->save();

        return response()->json(['message' => 'Password successfully updated for ' . $existingUser->username], 200);
    }

}
