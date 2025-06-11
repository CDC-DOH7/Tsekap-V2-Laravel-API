<?php

namespace App\Http\Controllers\TsekapV2;

use App\Models\User;
use App\Models\MobileRemarks;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Exception;

class UserController extends Controller
{
    private function getAuthenticatedUser($username)
    {
        $queryUser = User::where('username', '=', $username)->first();

        if (!$queryUser || $queryUser->verified !== 1) {
            Log::error('Denied access for: ' . " " . $queryUser->id);
            throw new Exception('User not found or not verified');
        }

        return $queryUser;
    }

    public function updateUserPassword(Request $request)
    {
        $fields = $request->input('fields');
        $user = $request->user();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $queryUser = $this->getAuthenticatedUser($user->username);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $rules = [
            'fields' => 'required|array',
            'fields.current_password' => 'required|string',
            'fields.new_password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&#]/',
            ],
        ];

        $fieldsValidator = Validator::make($request->all(), $rules);

        if ($fieldsValidator->fails()) {
            return response()->json(['status' => 'error', 'message' => $fieldsValidator->errors()->all()], 400);
        }

        $currentPassword = $fields['current_password'];
        $newPassword = $fields['new_password'];

        if (!Hash::check($currentPassword, $queryUser->password)) {
            return response()->json(['status' => 'error', 'message' => 'Current password is incorrect'], 400);
        }

        try {
            $queryUser->password = Hash::make($newPassword);
            $queryUser->save();
        } catch (Exception $e) {
            Log::error("Failed to update password: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 404);
        }

        return response()->json(['status' => 'success', 'message' => 'Password changed successfully'], 200);
    }

    public function updateUserFullName(Request $request)
    {
        $fields = $request->input('fields');
        $user = $request->user();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $queryUser = $this->getAuthenticatedUser($user->username);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $rules = [
            'fields' => 'required|array',
            'fields.fname' => 'nullable|string|max:255',
            'fields.mname' => 'nullable|string|max:255',
            'fields.lname' => 'nullable|string|max:255',
        ];

        $fieldsValidator = Validator::make($request->all(), $rules);

        if ($fieldsValidator->fails()) {
            return response()->json(['status' => 'error', 'message' => $fieldsValidator->errors()->all()], 400);
        }

        try {
            if (!isset($fields['fname']) && !isset($fields['mname']) && !isset($fields['lname'])) {
                return response()->json(['status' => 'error', 'message' => 'At least one of fname, mname, or lname must be provided'], 400);
            }

            if (isset($fields['fname'])) {
                $queryUser->fname = $fields['fname'];
            }
            if (isset($fields['mname'])) {
                $queryUser->mname = $fields['mname'];
            }
            if (isset($fields['lname'])) {
                $queryUser->lname = $fields['lname'];
            }
            $queryUser->save();
        } catch (Exception $e) {
            Log::error("Failed to update name: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 404);
        }

        return response()->json(['status' => 'success', 'message' => 'Names updated successfully'], 200);
    }

    public function updateUserContact(Request $request)
    {
        $fields = $request->input('fields');
        $user = $request->user();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $queryUser = $this->getAuthenticatedUser($user->username);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $rules = [
            'fields' => 'required|array',
            'fields.contact' => 'required|string|min:11|max:11',
        ];

        $fieldsValidator = Validator::make($request->all(), $rules);

        if ($fieldsValidator->fails()) {
            return response()->json(['status' => 'error', 'message' => $fieldsValidator->errors()->all()], 400);
        }

        try {
            $queryUser->contact = $fields['contact'];
            $queryUser->save();
        } catch (Exception $e) {
            Log::error("Failed to update contact: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 404);
        }

        return response()->json(['status' => 'success', 'message' => 'Contact updated successfully'], 200);
    }

    public function updateUserEmail(Request $request)
    {
        $fields = $request->input('fields');
        $user = $request->user();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $queryUser = $this->getAuthenticatedUser($user->username);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $rules = [
            'fields' => 'required|array',
            'fields.email' => 'required|string|email|max:50',
        ];

        $fieldsValidator = Validator::make($request->all(), $rules);

        if ($fieldsValidator->fails()) {
            return response()->json(['status' => 'error', 'message' => $fieldsValidator->errors()->all()], 400);
        }

        try {
            $queryUser->email = $fields['email'];
            $queryUser->save();
        } catch (Exception $e) {
            Log::error("Failed to update email: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 404);
        }

        return response()->json(['status' => 'success', 'message' => 'Email updated successfully'], 200);
    }

    // deactivate own account
    public function deactivateUserAccount(Request $request)
    {
        $authUser = $request->user();

        if (!$authUser) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        // Validate the input
        $validator = Validator::make($request->all(), [
            'fields.user_id' => 'required|integer',
        ]);

        try {
            $validatedFields = $validator->validate();
        } catch (ValidationException $e) {
            Log::error('Validation error in UserController (function: deactivateUserAccount) ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Deactivation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $userId = $validatedFields['fields']['user_id'];

        // Only allow deactivation if the user_id matches the authenticated user's id
        if ($authUser->id !== $userId) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $existingUser = User::find($userId);

        if (!$existingUser) {
            return response()->json(['status' => 'error', 'message' => 'User not found.'], 404);
        }

        // Set verified to false (unverified)
        $existingUser->update([
            'verified' => false,
            'updated_at' => now(),
        ]);

        return response()->json(['status' => 'success', 'message' => 'User account deactivated.'], 200);
    }

    public function storeUserRemarks(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $fieldsValidator = Validator::make($request->all(), [
            'fields' => 'required|array',
            'fields.user_id' => 'required|integer',
            'fields.remarks' => 'required|string',
        ]);

        if ($fieldsValidator->fails()) {
            return response()->json(['status' => 'error', 'message' => $fieldsValidator->errors()->all()], 400);
        }

        try {
            $fields = $fieldsValidator->validated()['fields'];

            $remarks = MobileRemarks::create([
                'user_id' => $fields['user_id'],
                'remarks' => $fields['remarks'],
                'created_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Remarks saved successfully.',
                'data' => $remarks,
            ], 201);
        } catch (Exception $e) {
            Log::error("Failed to save remarks: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to save remarks'], 500);
        }
    }
}
