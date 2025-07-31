<?php

namespace App\Http\Controllers\TsekapV2\Analytics\DataRetrieval;

use App\Http\Controllers\Controller;
use App\Models\TsekapV2\Forms\RiskAssessment\RiskProfile;
use App\Models\TsekapV2\Forms\PchRiskAssessment\PchRiskProfile;
use App\Models\TsekapV2\UserHealthFacility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Exception;

class AdministrativeAnalyticsDataController extends Controller
{
    // Get authenticated user or return unauthorized response
    private function getAuthenticatedUser($username)
    {
        $user = User::where('username', $username)
            ->whereIn('user_priv', [1, 3, 10])
            ->where('verified', 1)
            ->first();

        if (!$user) {
            Log::error('Denied administrative access for: ' . ($username ?? "unknown"));
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        return $user;
    }

    // ================== GENERAL CONTROLLERS (/admin_analytics) ==================
    public function countNumberOfEntriesPerByUserPerFacility(Request $request)
    {
        $admin = $this->getAuthenticatedUser($request->user()->getAttribute('username'));

        if ($admin instanceof \Illuminate\Http\JsonResponse) {
            return $admin;
        }

        try {
            $hf = UserHealthFacility::where('user_id', $request->user()->getAttribute('id'))->first();
            if (!$hf) {
                return response()->json(['error' => 'No health facility found for user.'], 404);
            }

            $form_type = $request->query('form');
            if (!in_array($form_type, ['pch', 'raf'])) {
                return response()->json(['error' => 'Form type unsupported'], 400);
            }

            $model = $form_type === 'pch' ? PchRiskProfile::class : RiskProfile::class;

            $entries = $model::where('facility_id_updated', $hf->getAttribute('facility_id'))
                ->selectRaw('encoded_by as id, COUNT(*) as total_entries, MAX(created_at) as last_entry_date')
                ->groupBy('encoded_by')
                ->orderByDesc('total_entries')
                ->get();

            $users = User::whereIn('id', $entries->pluck('id'))
                ->select('id', 'fname', 'mname', 'lname')
                ->get()
                ->keyBy('id');

            $entriesWithNames = $entries->map(function ($entry) use ($users) {
                $user = $users->get($entry->id);
                $entry->fname = $user->fname ?? null;
                $entry->mname = $user->mname ?? null;
                $entry->lname = $user->lname ?? null;
                return $entry;
            });

            return response()->json([
                'status' => 'success',
                'data' => $entriesWithNames
            ]);
        } catch (Exception $e) {
            Log::error("Error in administrative analytics: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Server error'], 500);
        }
    }
}
