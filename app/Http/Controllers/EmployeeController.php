<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Section;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeeController extends Controller
{
    public function index()
    {
        return view('employee');
    }


    public function manageEmployeeDataIndex()
    {
        return view('manageData');
    }


    //----------------------------------- get users -----------------------------------
    public function getEmployees()
    {
        try {
            // $users = User::select('id', 'name', 'username', 'email', 'companyId',DB::raw("IFNULL(phone,'-') as phone"), 'isAdmin','isAllowed','isPremium')
            // ->get();

            $employee = Employee::select(
                'employees.id',
                DB::raw("IFNULL(employees.emp_code, '-') as emp_code"),
                'employees.name',
                DB::raw("IFNULL(employees.phone, '-') as phone"),
                DB::raw("IFNULL(employees.dob, '-') as phone"),
                DB::raw("IFNULL(employees.pob, '-') as phone"),
                DB::raw("IFNULL(employees.phone, '-') as phone"),
                DB::raw("IFNULL(employees.phone, '-') as phone"),
                DB::raw("IFNULL(company.name, '-') as company"),
                DB::raw("IFNULL(department.name, '-') as department"),
                DB::raw("IFNULL(section.name, '-') as section"),
                DB::raw("IFNULL(employees.status, '-') as status"),
                DB::raw("IFNULL(employees.jop_title, '-') as jop_title"),
            )
                ->leftJoin('company', 'employees.companyId', '=', 'company.id')
                ->leftJoin('department', 'employees.companyId', '=', 'company.id')
                ->leftJoin('section', 'employees.companyId', '=', 'company.id')
                ->get();
            return response()->json([
                'status'  => 200,
                'message' => 'employees fetched successfully!',
                'data'    => $employee,
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
                'department' => 'required',
                'section' => 'required',
            ]);
            $companyDB = Company::findOrFail($validated['company']);
            $departmentDB = Department::findOrFail($validated['department']);
            $sectionDB = Section::findOrFail($validated['section']);

            // Create user
            $user = Employee::create([
                'name'     => $validated['name'],
                'emp_code' => $request->emp_code,
                'name' => $request->name,
                'phone' => $request->phone,
                'dob' => $request->dob,
                'pob' => $request->pob,
                'account' => $request->isPremium,
                'jop_title' => $request->joptitle,
                'status' => $request->status,
                'salary' => $request->salary,
                'bonus' => $request->bonus,
                'companyId' => $companyDB->id,
                'departmentId' => $validated['department'],
                'sectionId' => $validated['section'],
            ]);

            return response()->json([
                'status'   => 200,
                'message'  => 'employee created successfully!',
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
            $employee = Employee::findOrFail($id);

            return response()->json([
                'status'  => 200,
                'message' => 'employee fetched successfully!',
                'data'    => $employee,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 404,
                'message' => 'employee not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'An error occurred while fetching the employee.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    //----------------------------------- update user -----------------------------------
    public function update(Request $request, $id)
    {
        try {
            $employee = Employee::find($id);
            if (!$employee) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'employee not found.',
                ], 401);
            }

            // Validate input
            $validated = $request->validate([
                'company'     => 'required',
                'employeename'     => 'required|string|max:20'
            ]);



            // Apply updates
            $employee->name =  $request->name;
            $employee->email = $request->email;
            $employee->phone = $request->phone;
            $employee->employeename = $validated['employeename'];
            $employee->companyId = $validated['company'];
            $employee->isAdmin =  $request->isAdmin;
            $employee->isAllowed =  $request->isAllowed;
            $employee->isPremium =  $request->isPremium;
            $employee->enableSms =  $request->enableSms;
            $employee->save();

            // Sync session data
            session([
                'name' => $employee->name,
            ]);

            return response()->json([
                'status'  => 200,
                'message' => 'employee updated successfully!',
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
            $employee = Employee::findOrFail($id);
            $employee->delete();
            return response()->json([
                'status'  => 200,
                'message' => 'employe deleted successfully!',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 404,
                'message' => 'employee  not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'An error occurred while deleting the employee.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }



    //----------------------------------- getMaxEmpCode -----------------------------------
    public function getMaxEmpCode($companyId, $branchId)
    {
        try {
            $company = Company::find($companyId);
            $branch = Branch::find($branchId);

            if (!$company || !$branch) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Invalid company or branch',
                ]);
            }

            // Combine both codes to form prefix
            $prefix = $company->code . $branch->code;

            // Get max employee ID where company and branch match
            $maxEmp = Employee::where('companyId', $companyId)
                ->where('branchId', $branchId)
                ->max('id');

            $nextCode = $maxEmp ? str_pad($maxEmp + 1, 3, '0', STR_PAD_LEFT) : '001';

            return response()->json([
                'status' => 200,
                'data' => [
                    'prefix' => $prefix,
                    'nextCode' => $nextCode,
                    "empCode" => $prefix . $nextCode
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error generating employee code',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
