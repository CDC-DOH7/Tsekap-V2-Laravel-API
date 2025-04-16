<?php

namespace App\Http\Controllers\TsekapV2;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    private function getAuthenticatedUser($username)
    {
        $queryUser = User::where('username', '=', $username)->first();

        if (!$queryUser) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return $queryUser;
    }

    public function updateUserPassword(Request $request)
    {
        $fields = $request->input('fields');
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $fieldsValidator = Validator::make($request->all(), [
            'fields' => 'required|array',
            'fields.currentPassword' => 'required|string',
            'fields.newPassword' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&#]/',
            ],
        ]);

        if ($fieldsValidator->fails()) {
            return response()->json(['errors' => $fieldsValidator->errors()->all()], 400);
        }

        $currentPassword = $fields['currentPassword'];
        $newPassword = $fields['newPassword'];

        $queryUser = $this->getAuthenticatedUser($user->username);
        if ($queryUser instanceof \Illuminate\Http\JsonResponse) {
            return $queryUser;
        }

        if (!Hash::check($currentPassword, $queryUser->password)) {
            return response()->json(['error' => 'Current password is incorrect'], 400);
        }

        $queryUser->password = Hash::make($newPassword);
        $queryUser->save();

        return response()->json(['message' => 'Password changed successfully'], 200);
    }

    public function updateUserFullName(Request $request)
    {
        $fields = $request->input('fields');
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $fieldsValidator = Validator::make($request->all(), [
            'fields' => 'required|array',
            'fields.fname' => 'string|max:255',
            'fields.mname' => 'string|max:255',
            'fields.lname' => 'string|max:255',
        ]);

        if ($fieldsValidator->fails()) {
            return response()->json(['errors' => $fieldsValidator->errors()->all()], 400);
        }

        $queryUser = $this->getAuthenticatedUser($user->username);
        if ($queryUser instanceof \Illuminate\Http\JsonResponse) {
            return $queryUser;
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

        return response()->json(['message' => 'Names updated successfully'], 200);
    }

    public function updateUserContact(Request $request)
    {
        $fields = $request->input('fields');
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $fieldsValidator = Validator::make($request->all(), [
            'fields' => 'required|array',
            'fields.contact' => 'required|string|min:11|max:11',
        ]);

        if ($fieldsValidator->fails()) {
            return response()->json(['errors' => $fieldsValidator->errors()->all()], 400);
        }

        $queryUser = $this->getAuthenticatedUser($user->username);
        if ($queryUser instanceof \Illuminate\Http\JsonResponse) {
            return $queryUser;
        }

        $queryUser->contact = $fields['contact'];
        $queryUser->save();

        return response()->json(['message' => 'Contact updated successfully'], 200);
    }

    public function updateUserEmail(Request $request)
    {
        $fields = $request->input('fields');
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $fieldsValidator = Validator::make($request->all(), [
            'fields' => 'required|array',
            'fields.email' => 'required|string|email|max:50',
        ]);

        if ($fieldsValidator->fails()) {
            return response()->json(['errors' => $fieldsValidator->errors()->all()], 400);
        }

        $queryUser = $this->getAuthenticatedUser($user->username);
        if ($queryUser instanceof \Illuminate\Http\JsonResponse) {
            return $queryUser;
        }

        $queryUser->email = $fields['email'];
        $queryUser->save();

        return response()->json(['message' => 'Email updated successfully'], 200);
    }
}
