<?php

namespace App\Http\Controllers\TsekapV2\Websockets;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TsekapV2\Facilities;
use App\Models\TsekapV2\Websockets\NotificationModel;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    private function getAuthenticatedUser($username)
    {
        $queryUser = User::where('username', '=', $username)->first();

        if (!$queryUser || $queryUser->getAttribute('verified') !== 1) {
            Log::error('Denied access for: ' . " " . $queryUser->getAttribute('id'));
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        return $queryUser;
    }

    private function getAuthenticatedAdmin($username)
    {
        $queryUser = User::where('username', '=', $username)->first();

        // do not authorize update unless 1, 3, 10
        if ((!$queryUser || !in_array($queryUser->getAttribute('user_priv'), [1, 3, 10])) || ($queryUser->getAttribute('verified') !== 1)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        return $queryUser;
    }

    // get notifications by facility
    public function retrieveNotificationsByFacility(Request $request)
    {
        $user = $this->getAuthenticatedUser($request->user()->getAttribute('username'));
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $rules = [
            'fields' => 'required|array',
            'fields.facility_id' => 'required|string|max:100',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
        }

        $fields = $request->input('fields');

        try {
            $notification = NotificationModel::where('destination_facility_id', "=", $fields['facility_id'])->first();

            if (!$notification) {
                return response()->json(['status' => 'error', 'message' => 'No notifications not found'], 404);
            }

            return response()->json(['status' => 'success', 'data' => $notification], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving notifications: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['status' => 'error', 'message' => 'An error occurred. Please try again later.'], 500);
        }
    }

    // add notification
    public function addNotification(Request $request)
    {
        // Ensure the user is authenticated via Sanctum
        $user = $this->getAuthenticatedAdmin($request->user()->getAttribute('username')); // This replaces Auth::check()
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $fields = $request->input('fields');

        $rules = [
            'fields' => 'required|array',
            'fields.origin_facility_id' => 'required|integer',
            'fields.destination_facility_id' => 'required|integer',
            'fields.sent_by_user_id' => 'required|integer',
            'fields.sent_to_user_id' => 'sometimes|nullable|integer',
            'fields.title' => 'required|string|max:255',
            'fields.message' => 'required|string|max:1000',
            'fields.is_read' => 'required|boolean',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
        }

        $fields = $request->input('fields');

        try {
            $notification = NotificationModel::create($fields);
            return response()->json(['status' => 'success', 'message' => 'Notification successfully logged!', 'data' => $notification], 201);
        } catch (\Exception $e) {
            Log::error('Error adding notification: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['status' => 'error', 'message' => 'An error occurred. Please try again later.'], 500);
        }
    }

    // mark notification as read (update)
    public function markNotificationAsRead(Request $request)
    {
        $user = $this->getAuthenticatedAdmin($request->user()->getAttribute('username'));
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $rules = [
            'fields' => 'required|array',
            'fields.id' => 'required|string|max:100',
            'fields.is_read' => 'required|boolean',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
        }

        $fields = $request->input('fields');

        try {
            $notification = NotificationModel::where('id', "=", $fields['id'])->first();

            if (!$notification) {
                return response()->json(['status' => 'error', 'message' => 'Facility not found'], 404);
            }

            $notification->update($fields);

            return response()->json(['status' => 'success', 'message' => 'Successfully marked notification as read', 'data' => $notification], 200);
        } catch (\Exception $e) {
            Log::error('Error in marking notification as read: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['status' => 'error', 'message' => 'An error occurred. Please try again later.'], 500);
        }
    }

    // delete notification
    public function deleteNotification(Request $request)
    {
        $user = $this->getAuthenticatedAdmin($request->user()->getAttribute('username'));
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $rules = [
            'fields' => 'required|array',
            'fields.id' => 'required|integer',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
        }

        $fields = $request->input('fields');

        try {
            $notification = Facilities::where('id', "=", $fields['id'])->first();

            if (!$notification) {
                return response()->json(['status' => 'error', 'message' => 'Notification not found'], 404);
            }

            $notification->delete();

            return response()->json(['status' => 'success', 'message' => 'Notification deleted successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting notification: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['status' => 'error', 'message' => 'An error occurred. Please try again later.'], 500);
        }
    }
}
