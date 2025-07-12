<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Department;

class DepartmentController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $departments = Department::withCount('employees')->get();
            
            return response()->json([
                'success' => true,
                'data' => $departments,
                'message' => 'Departments retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving departments: ' . $e->getMessage()
            ], 500);
        }
    }

    public function employees(string $id, Request $request): JsonResponse
    {
        try {
            $department = Department::find($id);
            if (!$department) {
                return response()->json([
                    'success' => false,
                    'message' => 'Department not found'
                ], 404);
            }

            $perPage = min($request->get('per_page', 20), 100);
            $employees = $department->employees()->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'department' => [
                        'id' => $department->id,
                        'name' => $department->name
                    ],
                    'employees' => $employees
                ],
                'message' => 'Department employees retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving department employees: ' . $e->getMessage()
            ], 500);
        }
    }
} 