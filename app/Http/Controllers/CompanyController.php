<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PhpParser\Node\Stmt\TryCatch;

class CompanyController extends Controller
{
    //
    public function index()
    {
        return view('company');
    }


    /**
     * Get all companies.
     */
    public function getCompanies()
    {
        try {
            // Retrieve session values correctly
            $isAdmin   = session('isAdmin', 0); // default 0 if not found
            $companyId = session('companyId', null);

            if ($isAdmin == 1) {
                $companies = Company::all();
            } else {
                $companies = Company::where('id', $companyId)->get();
            }

            return response()->json([
                'status'  => 200,
                'message' => 'Companies fetched successfully!',
                'data'    => $companies,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'An error occurred while fetching companies.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Show a single company by ID.
     */
    public function show($id)
    {
        try {
            $company = Company::findOrFail($id);

            return response()->json([
                'status'  => 200,
                'message' => 'Company fetched successfully!',
                'data'    => $company,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => 404,
                'message' => 'Company not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'An error occurred while fetching the company.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new company.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:3',
            ]);

            $company = Company::create($validated);

            return response()->json([
                'status'  => 200,
                'message' => 'Company created successfully!',
                'data'    => $company,
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
                'message' => 'An error occurred while creating the company.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing company.
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50' . $id,
            ]);

            $company = Company::findOrFail($id);
            $company->update($validated);

            return response()->json([
                'status'  => 200,
                'message' => 'Company updated successfully!',
                'data'    => $company,
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
                'message' => 'Company not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'An error occurred while updating the company.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a company.
     */
    public function delete($id)
    {
        try {
            $company = Company::findOrFail($id);
            $company->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Company deleted successfully!',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 404,
                'message' => 'Company not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'An error occurred while deleting the company.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
