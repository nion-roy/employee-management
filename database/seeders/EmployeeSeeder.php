<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Create 10 departments
        $departmentNames = [
            'Human Resources',
            'Information Technology',
            'Marketing',
            'Sales',
            'Finance',
            'Operations',
            'Customer Service',
            'Research & Development',
            'Legal',
            'Administration'
        ];
        
        $departments = [];
        for ($i = 0; $i < 10; $i++) {
            $departments[] = [
                'name' => $departmentNames[$i],
                'description' => $faker->sentence(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Insert departments
        DB::table('department')->insert($departments);
        
        // Get department IDs for reference
        $departmentIds = DB::table('department')->pluck('id')->toArray();
        
        // Create employees in batches for better performance
        $batchSize = 1000;
        $totalEmployees = 100000;
        
        for ($batch = 0; $batch < $totalEmployees / $batchSize; $batch++) {
            $employees = [];
            $employeeDetails = [];
            
            for ($i = 0; $i < $batchSize; $i++) {
                $employeeId = Str::uuid();
                $departmentId = $faker->randomElement($departmentIds);
                
                // Employee data
                $employees[] = [
                    'id' => $employeeId,
                    'name' => $faker->name(),
                    'email' => $faker->unique()->safeEmail(),
                    'department_id' => $departmentId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                // Employee detail data
                $employeeDetails[] = [
                    'employee_id' => $employeeId,
                    'designation' => $faker->jobTitle(),
                    'salary' => $faker->randomFloat(2, 30000, 150000),
                    'address' => $faker->address(),
                    'joined_date' => $faker->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            // Insert employees
            DB::table('employee')->insert($employees);
            
            // Insert employee details
            DB::table('employee_detail')->insert($employeeDetails);
            
            // Show progress
            $progress = ($batch + 1) * $batchSize;
            $this->command->info("Inserted {$progress} employees...");
        }
        
        $this->command->info('Successfully inserted 10 departments and 100,000 employees with their details!');
    }
}
