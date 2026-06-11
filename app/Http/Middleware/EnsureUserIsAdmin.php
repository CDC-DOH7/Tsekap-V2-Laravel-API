<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // administrative privileges are 1, 3, 5, and 10
        // priv_number: 0, - Municipal/BHS Head
        // priv_number: 1, - Administrator
        // priv_number: 2, - BHS/Barangay
        // priv_number: 3, - Provincial
        // priv_number: 4, - Dentist
        // priv_number: 5, - Rural Health Unit (RHU)
        // priv_number: 6, - Hospital
        // priv_number: 10, - "Disease Surveillance Officer (DSO)
        // priv_number: 11, - "Disease Surveillance Officer (DSO) - Staff

        // do not authorize update unless 1, 3, 5, and 10
        if (!$user || !in_array($user->getAttribute('user_priv'), [1, 3, 5, 10]) || $user->getAttribute('verified') !== 1) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
