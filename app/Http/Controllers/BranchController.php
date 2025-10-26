<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BranchController extends Controller
{
    //
    public function index()
    {
        return view('branch');
    }


    /**
     * Get all branch.
     */
    public function getBranches()
    {
        try {
            // Retrieve session values correctly
            $isAdmin   = session('isAdmin', 0); // default 0 if not found
            $companyid = session('companyId', null);
            if ($isAdmin == 1) {
                $branch = Branch::select(
                    'branches.id',
                    'branches.name',
                    DB::raw("IFNULL(company.name, '-') as company_name"),
                    DB::raw("DATE_FORMAT(branches.created_at, '%Y-%m-%d %H:%i') as created_on"),
                    DB::raw("DATE_FORMAT(branches.updated_at, '%Y-%m-%d %H:%i') as update_at"),
                )
                    ->leftJoin('company', 'branches.companyId', '=', 'company.id')
                    ->get();
            } else {
                $branch = Branch::select(
                    'branches.id',
                    'branches.name',
                    DB::raw("IFNULL(company.name, '-') as company_name"),
                    DB::raw("DATE_FORMAT(branches.created_at, '%Y-%m-%d %H:%i') as created_on"),
                    DB::raw("DATE_FORMAT(branches.updated_at, '%Y-%m-%d %H:%i') as update_at"),
                )
                    ->leftJoin('company', 'branches.companyId', '=', 'company.id')
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


    public function getBranchBycompany($id)
    {
        try {
            // Retrieve session values correctly
                $branch = Branch::select(
                    'branches.id',
                    'branches.name',
                    DB::raw("IFNULL(company.name, '-') as company_name"),
                    DB::raw("DATE_FORMAT(branches.created_at, '%Y-%m-%d %H:%i') as created_on"),
                    DB::raw("DATE_FORMAT(branches.updated_at, '%Y-%m-%d %H:%i') as update_at"),
                )
                    ->leftJoin('company', 'branches.companyId', '=', 'company.id')
                    ->where('company.id', $id)
                    ->get();
            
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


    /**
     * Show a single branch by ID.
     */
    public function show($id)
    {
        try {
            $branch = Branch::findOrFail($id);
            
            return response()->json([
                'status'  => 200,
                'message' => 'branch fetched successfully!',
                'data'    => $branch,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 404,
                'message' => 'branch not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'An error occurred while fetching the branch.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new branch.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'company' => 'required'
            ]);

            $branch = Branch::create([
                'name' => $validated['name'],
                'companyId' => $validated['company'],
            ]);

            return response()->json([
                'status'  => 200,
                'message' => 'branch created successfully!',
                'data'    => $branch,
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
                'message' => 'An error occurred while creating the branch.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing branch.
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'company' => 'required'
            ]);

            $branch = Branch::findOrFail($id);

            $branch->name =  $validated['name'];
            $branch->companyId =  $validated['company'];
            $branch->save();
            return response()->json([
                'status'  => 200,
                'message' => 'branch updated successfully!',
                'data'    => $branch,
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
                'message' => 'branch not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'An error occurred while updating the branch.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a branch.
     */
    public function delete($id)
    {
        try {
            $branch = Branch::findOrFail($id);
            $branch->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'branch deleted successfully!',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 404,
                'message' => 'branch not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'An error occurred while deleting the branch.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
