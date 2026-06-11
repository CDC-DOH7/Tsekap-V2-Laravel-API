<?php

namespace App\Http\Controllers\TsekapV2\Websockets;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TsekapV2\Facilities;
use App\Models\TsekapV2\Websockets\NotificationModel;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    // get notifications by facility
    public function retrieveNotificationsByFacility(Request $request): JsonResponse
    {
        $rules = [
            'facility_id' => 'required|integer',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
        }

        // Prefer query parameter for GET, fallback to input()
        $facilityId = $request->query('facility_id', $request->input('facility_id'));

        try {
            $notifications = NotificationModel::where('facility_id', $facilityId)->where('is_read', 0)->get();

            if ($notifications->isEmpty()) {
                return response()->json(['status' => 'error', 'message' => 'No notifications found'], 404);
            }

            return response()->json(['status' => 'success', 'data' => $notifications], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving notifications: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['status' => 'error', 'message' => 'An error occurred. Please try again later.'], 500);
        }
    }

    public function retrieveNotificationsCountByFacility(Request $request): JsonResponse
    {
        $rules = [
            'facility_id' => 'required|integer',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
        }

        // Prefer query parameter for GET, fallback to input()
        $facilityId = $request->query('facility_id', $request->input('facility_id'));

        try {
            $notificationsCount = NotificationModel::where('facility_id', $facilityId)->where('is_read', 0)->count();

            return response()->json(['status' => 'success', 'data' => ['unread_count' => $notificationsCount]], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving notifications count: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['status' => 'error', 'message' => 'An error occurred. Please try again later.'], 500);
        }
    }

    // add notification
    public function addNotification(Request $request): JsonResponse
    {
        $fields = $request->input('fields');

        $rules = [
            'fields' => 'required|array',
            'fields.facility_id' => 'required|integer',
            'fields.user_id' => 'required|integer',
            'fields.title' => 'required|string|max:255',
            'fields.message' => 'required|string|max:1000',
            'fields.is_read' => 'required|boolean',
            'fields.data' => 'sometimes|nullable|array',
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
    public function markNotificationAsRead(Request $request): JsonResponse
    {
        $rules = [
            'fields' => 'required|array',
            'fields.id' => 'required|integer',
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
    public function deleteNotification(Request $request): JsonResponse
    {
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
