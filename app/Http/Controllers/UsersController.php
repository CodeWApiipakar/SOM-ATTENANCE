<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UsersController extends Controller
{
    //
    public function index()
    {
        return view('users');
    }

    //----------------------------------- get users -----------------------------------
    public function getUsers()
    {
        try {
            // $users = User::select('id', 'name', 'username', 'email', 'companyId',DB::raw("IFNULL(phone,'-') as phone"), 'isAdmin','isAllowed','isPremium')
            // ->get();

            $users = User::select(
                'users.id',
                'users.name',
                'users.username',
                'users.email',
                DB::raw("IFNULL(users.phone, '-') as phone"),
                DB::raw("CASE WHEN users.isAdmin = 1 THEN 'Yes' ELSE 'No' END as is_admin"),
                DB::raw("CASE WHEN users.isPremium = 1 THEN 'Yes' ELSE 'No' END as Premium"),
                DB::raw("CASE WHEN users.isAllowed = 1 THEN 'Active' ELSE 'Blocked' END as status"),
                DB::raw("IFNULL(company.name, '-') as company_name"),
                DB::raw("DATE_FORMAT(users.created_at, '%Y-%m-%d %H:%i') as created_on"),
                DB::raw("DATE_FORMAT(users.expireDate, '%Y-%m-%d %H:%i') as expireDate"),
            )
                ->leftJoin('company', 'users.companyId', '=', 'company.id')
                ->get();

            return response()->json([
                'status'  => 200,
                'message' => 'users fetched successfully!',
                'data'    => $users,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'An error occurred while fetching users.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    //----------------------------------- register user -----------------------------------
    public function register(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'name'     => 'required|string|max:255',
                'username' => 'required|string|unique:users,username',
                'company' => 'required',
            ]);
            $companyDB = Company::findOrFail($validated['company']);
            $newPassword = $companyDB->code . "@" . date('Y');

            // Create user
            $user = User::create([
                'name'     => $validated['name'],
                'username' => $validated['username'],
                'email' => $request->email,
                'companyId' => $validated['company'],
                'isAdmin' => $request->isAdmin,
                'isAllowed' => $request->isAllowed,
                'isPremium' => $request->isPremium,
                'expireDate' => Carbon::now()->addMonths(3),
                'enableSms' => $request->enableSms,
                'password' => Hash::make($newPassword),
            ]);

            return response()->json([
                'status'   => 200,
                'message'  => 'User created successfully!',
                'redirect' => route('dashboard'),
            ], 200);
        } catch (ValidationException $e) {
            // Return validation errors
            return response()->json([
                'status'  => 422,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (QueryException $e) {
            // Handle DB-level issues (e.g. duplicate username)
            return response()->json([
                'status'  => 409,
                'message' => 'Database error: ' . $e->getMessage(),
            ], 409);
        } catch (\Throwable $e) {
            // Catch-all fallback for unexpected errors
            return response()->json([
                'status'  => 500,
                'message' => 'Unexpected error occurred.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    //----------------------------------- show user -----------------------------------
    public function show($id)
    {
        try {
            $user = User::findOrFail($id);

            return response()->json([
                'status'  => 200,
                'message' => 'user fetched successfully!',
                'data'    => $user,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 404,
                'message' => 'user not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'An error occurred while fetching the user.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    //----------------------------------- update user -----------------------------------
    public function update(Request $request, $id)
    {
        try {
            // $user = Auth::user();
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'user not found.',
                ], 401);
            }

            // Validate input
            $validated = $request->validate([
                'company'     => 'required',
                'username'     => 'required|string|max:20'
            ]);



            // Apply updates
            $user->name =  $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->username = $validated['username'];
            $user->companyId = $validated['company'];
            $user->isAdmin =  $request->isAdmin;
            $user->isAllowed =  $request->isAllowed;
            $user->isPremium =  $request->isPremium;
            $user->enableSms =  $request->enableSms;
            $user->save();

            // Sync session data
            session([
                'name' => $user->name,
            ]);

            return response()->json([
                'status'  => 200,
                'message' => 'user updated successfully!',
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status'  => 422,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (QueryException $e) {
            return response()->json([
                'status'  => 409,
                'message' => 'Database error: ' . $e->getMessage(),
            ], 409);
        } catch (\Throwable $e) {
            // Log::error('user update error', ['error' => $e->getMessage()]);
            return response()->json([
                'status'  => 500,
                'message' => 'Unexpected error occurred.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    //----------------------------------- delete user -----------------------------------
    public function delete($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
            return response()->json([
                'status'  => 200,
                'message' => 'user deleted successfully!',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 404,
                'message' => 'user not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'An error occurred while deleting the user.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    //----------------------------------- update expire user -----------------------------------
    public function updateExpireDate(Request $request, $userId)
    {
        try {
            $user = User::findOrFail($userId);
            // $user->expireDate = Carbon::now()->addMonths(3);
            $user->expireDate = Carbon::parse($request->expireDate);
            $user->save();

            return response()->json([
                'status'  => 200,
                'message' => 'Expire date updated successfully!',
                'new_expire_date' => $user->expireDate->toDateString(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Error updating expire date',
                'error'   => $e->getMessage(),
            ]);
        }
    }


    //----------------------------------- change user password -----------------------------------
    public function changeUserPassword(Request $request, $id)
    {
        try {
            // 1️⃣ Validate the request
            $validated =  $request->validate([
                'newPassword' => 'required|string|min:8',
            ]);

            // 2️⃣ Find the user by ID
            $user = User::findOrFail($id);

            // 3️⃣ Update the password
            $user->password = Hash::make($validated['newPassword']);
            $user->save();

            return response()->json([
                'status'  => 200,
                'message' => 'Password changed successfully for user: ' . $user->username,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 404,
                'message' => 'User not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Error updating password',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
