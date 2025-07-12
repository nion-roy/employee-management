<?php

namespace App;

/**
 * Interface EmployeeRepositoryInterface
 * @package App
 */
interface EmployeeRepositoryInterface
{
    public function all();
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function getPaginated($perPage, $search = null, $departmentId = null, $salaryMin = null, $salaryMax = null, $sortBy = 'name', $sortOrder = 'asc');
    public function exportToCsv($search = null, $departmentId = null, $salaryMin = null, $salaryMax = null);
    public function getDashboardStats();
}
