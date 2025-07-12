<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\EmployeeRepositoryInterface;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *   name="Employee",
 *   description="Employee management APIs"
 * )
 */
class EmployeeController extends Controller
{
    protected $employeeRepository;

    public function __construct(EmployeeRepositoryInterface $employeeRepository)
    {
        $this->employeeRepository = $employeeRepository;
    }

    /**
     * @OA\Get(
     *   path="/api/employees",
     *   summary="Get all employees",
     *   tags={"Employee"},
     *   @OA\Response(response=200, description="Success")
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $employees = $this->employeeRepository->all();
            return response()->json(['data' => $employees], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *   path="/api/employees",
     *   summary="Create a new employee",
     *   tags={"Employee"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/Employee")
     *   ),
     *   @OA\Response(response=201, description="Created"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(EmployeeRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $employee = $this->employeeRepository->create($request->validated());
            DB::commit();
            return response()->json(['data' => $employee], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *   path="/api/employees/{id}",
     *   summary="Get an employee by ID",
     *   tags={"Employee"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *   @OA\Response(response=200, description="Success"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(string $id): JsonResponse
    {
        try {
            $employee = $this->employeeRepository->find($id);
            if (!$employee) {
                return response()->json(['error' => 'Employee not found'], 404);
            }
            return response()->json(['data' => $employee], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Put(
     *   path="/api/employees/{id}",
     *   summary="Update an employee",
     *   tags={"Employee"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/Employee")
     *   ),
     *   @OA\Response(response=200, description="Updated"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function update(EmployeeRequest $request, string $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $employee = $this->employeeRepository->update($id, $request->validated());
            if (!$employee) {
                DB::rollBack();
                return response()->json(['error' => 'Employee not found'], 404);
            }
            DB::commit();
            return response()->json(['data' => $employee], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Delete(
     *   path="/api/employees/{id}",
     *   summary="Delete an employee",
     *   tags={"Employee"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *   @OA\Response(response=204, description="Deleted"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $deleted = $this->employeeRepository->delete($id);
            if (!$deleted) {
                DB::rollBack();
                return response()->json(['error' => 'Employee not found'], 404);
            }
            DB::commit();
            return response()->json([], 204);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
