<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DepartmentController extends Controller
{
    //
    public function index(){
        return view('department');
    }


     /**
     * Get all departments.
     */
    public function getDepartments()
    {
        try {
            // Retrieve session values correctly
            $isAdmin   = session('isAdmin', 0); // default 0 if not found
            $companyid = session('companyId', null);
            if ($isAdmin == 1) {
                $branch = Department::select(
                    'department.id',
                    'department.name',
                    'department.code',
                    DB::raw("IFNULL(company.name, '-') as company_name"),
                    DB::raw("DATE_FORMAT(department.created_at, '%Y-%m-%d %H:%i') as created_on"),
                    DB::raw("DATE_FORMAT(department.updated_at, '%Y-%m-%d %H:%i') as update_at"),
                )
                    ->leftJoin('company', 'department.companyId', '=', 'company.id')
                    ->get();
            } else {
                $branch = Department::select(
                    'department.id',
                    'department.name',
                    'department.code',
                    DB::raw("IFNULL(company.name, '-') as company_name"),
                    DB::raw("DATE_FORMAT(department.created_at, '%Y-%m-%d %H:%i') as created_on"),
                    DB::raw("DATE_FORMAT(department.updated_at, '%Y-%m-%d %H:%i') as update_at"),
                )
                    ->leftJoin('company', 'department.companyId', '=', 'company.id')
                    ->where('company.id', $companyid)
                    ->get();
            }
            return response()->json([
                'status'  => 200,
                'message' => 'branch fetched successfully!',
                'data'    => $branch,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'An error occurred while fetching branch.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function getdepartmentBycompany($id)
    {
        try {
            // Retrieve session values correctly
                $department = Department::select(
                    'department.id',
                    'department.name',
                    DB::raw("IFNULL(company.name, '-') as company_name"),
                    DB::raw("DATE_FORMAT(department.created_at, '%Y-%m-%d %H:%i') as created_on"),
                    DB::raw("DATE_FORMAT(department.updated_at, '%Y-%m-%d %H:%i') as update_at"),
                )
                    ->leftJoin('company', 'department.companyId', '=', 'company.id')
                    ->where('company.id', $id)
                    ->get();
            
            return response()->json([
                'status'  => 200,
                'message' => 'department fetched successfully!',
                'data'    => $department,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'An error occurred while fetching department.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Show a single department by ID.
     */
    public function show($id)
    {
        try {
            $department = Department::findOrFail($id);
            
            return response()->json([
                'status'  => 200,
                'message' => 'department fetched successfully!',
                'data'    => $department,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 404,
                'message' => 'department not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'An error occurred while fetching the department.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new department.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:3',
                'company' => 'required'
            ]);

            $department = Department::create([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'companyId' => $validated['company'],
            ]);

            return response()->json([
                'status'  => 200,
                'message' => 'Department created successfully!',
                'data'    => $department,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status'  => 422,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'An error occurred while creating the department.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing department.
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:3',
                'company' => 'required'
            ]);

            $department = Department::findOrFail($id);

            $department->name =  $validated['name'];
            $department->code =  $validated['code'];
            $department->companyId =  $validated['company'];
            $department->save();
            return response()->json([
                'status'  => 200,
                'message' => 'department updated successfully!',
                'data'    => $department,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status'  => 422,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 404,
                'message' => 'department not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'An error occurred while updating the department.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a department.
     */
    public function delete($id)
    {
        try {
            $department = Department::findOrFail($id);
            $department->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'department deleted successfully!',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 404,
                'message' => 'department not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'An error occurred while deleting the department.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    
}
