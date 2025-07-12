<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\EmployeeRepositoryInterface;
use Illuminate\Http\JsonResponse;

class EmployeeController extends Controller
{
    protected $employeeRepository;

    public function __construct(EmployeeRepositoryInterface $employeeRepository)
    {
        $this->employeeRepository = $employeeRepository;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = min($request->get('per_page', 20), 100);
            $search = $request->get('search');
            $departmentId = $request->get('department_id');
            $salaryMin = $request->get('salary_min');
            $salaryMax = $request->get('salary_max');
            $sortBy = $request->get('sort_by', 'name');
            $sortOrder = $request->get('sort_order', 'asc');

            $employees = $this->employeeRepository->getPaginated(
                $perPage,
                $search,
                $departmentId,
                $salaryMin,
                $salaryMax,
                $sortBy,
                $sortOrder
            );

            return response()->json([
                'success' => true,
                'data' => $employees,
                'message' => 'Employees retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving employees: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(EmployeeRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $employee = $this->employeeRepository->create($request->validated());
            DB::commit();
            
            return response()->json([
                'success' => true,
                'data' => $employee,
                'message' => 'Employee created successfully'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating employee: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $employee = $this->employeeRepository->find($id);
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $employee,
                'message' => 'Employee retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving employee: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(EmployeeRequest $request, string $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $employee = $this->employeeRepository->update($id, $request->validated());
            if (!$employee) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ], 404);
            }
            DB::commit();
            
            return response()->json([
                'success' => true,
                'data' => $employee,
                'message' => 'Employee updated successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating employee: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $deleted = $this->employeeRepository->delete($id);
            if (!$deleted) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ], 404);
            }
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Employee deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting employee: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportCsv(Request $request): JsonResponse
    {
        try {
            $search = $request->get('search');
            $departmentId = $request->get('department_id');
            $salaryMin = $request->get('salary_min');
            $salaryMax = $request->get('salary_max');

            $result = $this->employeeRepository->exportToCsv($search, $departmentId, $salaryMin, $salaryMax);
            
            return response()->json([
                'success' => true,
                'message' => 'Export completed',
                'data' => $result
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error exporting data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function dashboardStats(): JsonResponse
    {
        try {
            $stats = $this->employeeRepository->getDashboardStats();
            
            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Statistics retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving statistics: ' . $e->getMessage()
            ], 500);
        }
    }
} 