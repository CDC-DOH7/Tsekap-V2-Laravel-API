<?php

namespace App\Http\Controllers\TsekapV2;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // changes the user's password
    public function updateUserPassword(Request $request)
    {
        $fields = $request->input('fields');

        // Ensure the user is authenticated via Sanctum
        $user = $request->user(); // This replaces Auth::check()

        // Check if the user exists
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Validate the input
        $fieldsValidator = Validator::make($fields, [
            'currentPassword' => 'required|string',
            'newPassword' => 'required|string|min:8'
        ]);

        if ($fieldsValidator->fails()) {
            return response()->json(['error' => array_merge($fieldsValidator->errors()->all())], 400);
        }

        $currentPassword = $fields['currentPassword'];
        $newPassword = $fields['newPassword'];
        $username = $user->username; // user auth

        // Fetch the user based on the provided username
        $queryUser = User::where('username', "=", $username)->first();

        if (!$queryUser) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Verify that the current password matches the user's existing password
        if (!Hash::check($currentPassword, $queryUser->password)) {
            return response()->json(['error' => 'Current password is incorrect'], 400);
        }

        // Update the user's password with the new password
        $queryUser->password = Hash::make($newPassword);
        $queryUser->save();

        // Return a success response
        return response()->json(['message' => 'Password changed successfully'], 200);
    }

    // changes/updates any of the ff. user's name (fname, mname, lname)
    public function updateUserFullName(Request $request)
    {
        $fields = $request->input('fields');

        // Ensure the user is authenticated via Sanctum
        $user = $request->user(); // This replaces Auth::check()

        // Check if the user exists
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Validate the input
        $fieldsValidator = Validator::make($fields, [
            'fname' => 'string|max:255',
            'mname' => 'string|max:255',
            'lname' => 'string|max:255',
        ]);

        if ($fieldsValidator->fails()) {
            return response()->json(['error' => array_merge($fieldsValidator->errors()->all())], 400);
        }

        $username = $user->username; // user auth

        // Fetch the user based on the provided username
        $queryUser = User::where('username', '=', $username)->first();

        if (!$queryUser) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Update the user's full name if the fields are not blank
        if (isset($fields['fname'])) {
            $queryUser->fname = $fields['fname'];
        }
        if (isset($fields['mname'])) {
            $queryUser->mname = $fields['mname'];
        }
        if (isset($fields['lname'])) {
            $queryUser->lname = $fields['lname'];
        }

        // Save the changes
        $queryUser->save();

        // Return a success response
        return response()->json(['message' => 'Names updated successfully'], 200);
    }

    // changes the user's contact
    public function updateUserContact(Request $request)
    {
        $fields = $request->input('fields');

        // Ensure the user is authenticated via Sanctum
        $user = $request->user(); // This replaces Auth::check()

        // Check if the user exists
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $fieldsValidator = Validator::make($fields, [
            'contact' => 'required|string|min:11|max:11'
        ]);

        if ($fieldsValidator->fails()) {
            return response()->json(['error' => array_merge($fieldsValidator->errors()->all())], 400);
        }

        // Get username and contact from the fields
        $username = $user->username; // user auth
        $contact = $fields['contact'];

        // Fetch the user based on the provided username
        $queryUser = User::where('username', '=', $username)->first();

        if (!$queryUser) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Update the user's contact
        $queryUser->contact = $contact;

        // Save the changes
        $queryUser->save();

        // Return a success response
        return response()->json(['message' => 'Contact updated successfully'], 200);
    }

    // changes the user's email
    public function updateUserEmail(Request $request)
    {
        $fields = $request->input('fields');

        // Ensure the user is authenticated via Sanctum
        $user = $request->user(); // This replaces Auth::check()

        // Check if the user exists
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $fieldsValidator = Validator::make($fields, [
            'email' => 'required|string|max:50'
        ]);

        if ($fieldsValidator->fails()) {
            return response()->json(['error' => array_merge($fieldsValidator->errors()->all())], 400);
        }

        // Get username and contact from the fields
        $username = $user->username; // user auth
        $email = $fields['email'];

        // Fetch the user based on the provided username
        $queryUser = User::where('username', '=', $username)->first();

        if (!$queryUser) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Update the user's contact
        $queryUser->email = $email;

        // Save the changes
        $queryUser->save();

        // Return a success response
        return response()->json(['message' => 'Contact updated successfully'], 200);
    }
}
