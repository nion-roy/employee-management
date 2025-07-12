<?php

namespace App\Repositories;

use App\EmployeeRepositoryInterface;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmployeeRepository implements EmployeeRepositoryInterface
{
    public function all()
    {
        return Employee::with('department', 'detail')->select('id', 'name', 'email', 'salary', 'joining_date', 'department_id')->latest('id')->get();
    }

    public function find($id)
    {
        return Employee::with('department', 'detail')
            ->select('id', 'name', 'email', 'department_id')
            ->find($id);
    }

    public function create(array $data)
    {
        DB::beginTransaction();
        try {
            $employee = Employee::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'department_id' => $data['department_id'],
            ]);

            if (isset($data['address']) || isset($data['position']) || isset($data['salary']) || isset($data['joining_date'])) {
                $employee->detail()->create([
                    'designation' => $data['position'] ?? null,
                    'salary' => $data['salary'] ?? null,
                    'address' => $data['address'] ?? null,
                    'joined_date' => $data['joining_date'] ?? null,
                ]);
            }

            DB::commit();
            return $employee->load('department', 'detail');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update($id, array $data)
    {
        DB::beginTransaction();
        try {
            $employee = Employee::find($id);
            if (!$employee) {
                return null;
            }

            $employee->update([
                'name' => $data['name'] ?? $employee->name,
                'email' => $data['email'] ?? $employee->email,
                'department_id' => $data['department_id'] ?? $employee->department_id,
            ]);

            if (isset($data['address']) || isset($data['position'])) {
                $employee->detail()->updateOrCreate(
                    ['employee_id' => $employee->id],
                    [
                        'address' => $data['address'] ?? null,
                        'position' => $data['position'] ?? null,
                    ]
                );
            }

            DB::commit();
            return $employee->load('department', 'detail');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $employee = Employee::find($id);
            if (!$employee) {
                return false;
            }

            $employee->detail()->delete();
            $employee->delete();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getPaginated($perPage, $search = null, $departmentId = null, $salaryMin = null, $salaryMax = null, $sortBy = 'name', $sortOrder = 'asc')
    {
        $query = Employee::with('department', 'detail');

        // Search functionality
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Department filter
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        // Salary range filter (on detail table)
        if ($salaryMin || $salaryMax) {
            $query->whereHas('detail', function ($q) use ($salaryMin, $salaryMax) {
                if ($salaryMin) {
                    $q->where('salary', '>=', $salaryMin);
                }
                if ($salaryMax) {
                    $q->where('salary', '<=', $salaryMax);
                }
            });
        }

        // Sorting (handle joined_date as detail.joined_date)
        if ($sortBy === 'joined_date') {
            $query->join('employee_detail', 'employee.id', '=', 'employee_detail.employee_id')
                ->orderBy('employee_detail.joined_date', $sortOrder)
                ->select('employee.*');
        } else {
            $allowedSortFields = ['name', 'email'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }
        }

        return $query->paginate($perPage);
    }

    public function exportToCsv($search = null, $departmentId = null, $salaryMin = null, $salaryMax = null)
    {
        $query = Employee::with('department', 'detail');

        // Apply filters
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        if ($salaryMin) {
            $query->where('salary', '>=', $salaryMin);
        }
        if ($salaryMax) {
            $query->where('salary', '<=', $salaryMax);
        }

        $employees = $query->get();

        // Create CSV content
        $csvContent = "ID,Name,Email,Salary,Joining Date,Department,Address,Position\n";

        foreach ($employees as $employee) {
            $csvContent .= sprintf(
                "%d,%s,%s,%s,%s,%s,%s,%s,%s\n",
                $employee->id,
                $employee->name,
                $employee->email,
                $employee->salary,
                $employee->joining_date,
                $employee->department ? $employee->department->name : '',
                $employee->detail ? $employee->detail->address : '',
                $employee->detail ? $employee->detail->position : ''
            );
        }

        // Save to storage
        $filename = 'employees_' . date('Y-m-d_H-i-s') . '.csv';
        $path = 'exports/' . $filename;
        Storage::put($path, $csvContent);

        return [
            'download_url' => Storage::url($path),
            'total_records' => $employees->count()
        ];
    }

    public function getDashboardStats()
    {
        $totalEmployees = Employee::count();
        $totalDepartments = Department::count();
        $averageSalary = Employee::avg('salary');
        $recentHires = Employee::where('joining_date', '>=', now()->subDays(30))->count();

        // Department distribution
        $departmentDistribution = Department::withCount('employees')
            ->orderBy('employees_count', 'desc')
            ->get()
            ->map(function ($department) {
                return [
                    'department' => $department->name,
                    'count' => $department->employees_count
                ];
            });

        return [
            'total_employees' => $totalEmployees,
            'total_departments' => $totalDepartments,
            'average_salary' => round($averageSalary, 2),
            'recent_hires' => $recentHires,
            'department_distribution' => $departmentDistribution
        ];
    }
}
