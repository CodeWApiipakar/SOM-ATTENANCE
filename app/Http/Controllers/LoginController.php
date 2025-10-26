<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    // Show login form
    public function create()
    {
        return view('auth.login');
    }

    // Handle login submit
    public function store(Request $request)
    {
        try {
            // 1️⃣ Validate input
            $request->validate([
                'username' => ['required', 'string'],
                'password' => ['required', 'string'],
            ]);

            // 2️⃣ Find user
            $user = User::where('username', $request->username)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status'  => 401,
                    'message' => 'Invalid username or password.',
                ], 401);
            }

            // 3️⃣ Check permission
            if (!$user->isAllowed) {
                return response()->json([
                    'status'  => 403,
                    'message' => 'You don’t have permission to log in. Please contact support.',
                ], 403);
            }

            // 4️⃣ Check subscription expiry — skip if user is premium
            if (!$user->isPremium) {
                $expireDate = Carbon::parse($user->expireDate);

                if ($expireDate->isPast() || $expireDate->isToday()) {
                    return response()->json([
                        'status'  => 403,
                        'message' => 'Your subscription has expired. Please renew to continue.',
                    ], 403);
                }
            }

            // 5️⃣ Log user in manually
            Auth::login($user, $request->boolean('remember'));

            // 6️⃣ Store session data
            session([
                'user_id'     => $user->id,
                'username'    => $user->username,
                'name'        => $user->name,
                'isAdmin'     => $user->isAdmin ?? false,
                'companyId'   => $user->companyId,
                'isAllowed'   => $user->isAllowed,
                'isPremium'   => $user->isPremium,
                'expireDate'  => $user->expireDate,
            ]);

            $request->session()->regenerate();

            // 7️⃣ Success
            return response()->json([
                'status'   => 200,
                'message'  => 'Login successful!',
                'redirect' => route('dashboard'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'An error occurred during login.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }





    // Handle logout
    public function destroy(Request $request)
    {
        // Clear Laravel Auth session
        Auth::logout();

        // Clear custom session data
        $request->session()->flush();

        // Regenerate token for CSRF protection
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
