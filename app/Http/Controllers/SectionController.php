<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class SectionController extends Controller
{
    //
    public function index(){
        return view('section');
    }

     /**
     * Get all Sections.
     */
    public function getSections()
    {
        try {
            // Retrieve session values correctly
            $isAdmin   = session('isAdmin', 0); // default 0 if not found
            $companyid = session('companyId', null);
            if ($isAdmin == 1) {
                $section = Section::select(
                    'section.id',
                    'section.name',
                    'section.code',
                    DB::raw("IFNULL(department.name, '-') as Department"),
                    DB::raw("DATE_FORMAT(section.created_at, '%Y-%m-%d %H:%i') as created_on"),
                    DB::raw("DATE_FORMAT(section.updated_at, '%Y-%m-%d %H:%i') as update_at"),
                )
                    ->leftJoin('department', 'section.deparmentId', '=', 'department.id')
                    ->get();
            } else {
                $section = Section::select(
                    'section.id',
                    'section.name',
                    'section.code',
                    DB::raw("IFNULL(department.name, '-') as Department"),
                    DB::raw("DATE_FORMAT(section.created_at, '%Y-%m-%d %H:%i') as created_on"),
                    DB::raw("DATE_FORMAT(section.updated_at, '%Y-%m-%d %H:%i') as update_at"),
                )
                    ->leftJoin('department', 'section.deparmentId', '=', 'department.id')
                    // ->where('department.comp', $companyid)
                    ->get();
            }
            return response()->json([
                'status'  => 200,
                'message' => 'section fetched successfully!',
                'data'    => $section,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'An error occurred while fetching section.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function getsectionByDepartment($id)
    {
        try {
            // Retrieve session values correctly
                $section = Section::select(
                    'section.id',
                    'section.name',
                    'section.code',
                    DB::raw("IFNULL(department.name, '-') as Department"),
                    DB::raw("DATE_FORMAT(section.created_at, '%Y-%m-%d %H:%i') as created_on"),
                    DB::raw("DATE_FORMAT(section.updated_at, '%Y-%m-%d %H:%i') as update_at"),
                )
                    ->leftJoin('department', 'section.deparmentId', '=', 'department.id')
                    ->where('department.id', $id)
                    ->get();
            
            return response()->json([
                'status'  => 200,
                'message' => 'section fetched successfully!',
                'data'    => $section,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'An error occurred while fetching section.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Show a single section by ID.
     */
    public function show($id)
    {
        try {
            $section = Section::findOrFail($id);
            return response()->json([
                'status'  => 200,
                'message' => 'section fetched successfully!',
                'data'    => $section,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 404,
                'message' => 'section not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'An error occurred while fetching the section.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new section.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:3',
                'department' => 'required'
            ]);

            $section = Section::create([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'deparmentId' => $validated['department'],
            ]);

            return response()->json([
                'status'  => 200,
                'message' => 'section created successfully!',
                'data'    => $section,
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
                'message' => 'An error occurred while creating the section.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing section.
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:3',
                'department' => 'required'
            ]);

            $section = Section::findOrFail($id);

            $section->name =  $validated['name'];
            $section->code =  $validated['code'];
            $section->deparmentId =  $validated['department'];
            $section->save();
            return response()->json([
                'status'  => 200,
                'message' => 'section updated successfully!',
                'data'    => $section,
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
                'message' => 'section not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'An error occurred while updating the section.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a section.
     */
    public function delete($id)
    {
        try {
            $section = Section::findOrFail($id);
            $section->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'section deleted successfully!',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 404,
                'message' => 'section not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'An error occurred while deleting the section.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

}
