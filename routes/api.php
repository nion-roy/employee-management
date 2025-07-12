<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\DepartmentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    
    Route::prefix('employees')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('employees.index');
        Route::post('/', [EmployeeController::class, 'store'])->name('employees.store');
        Route::get('/{id}', [EmployeeController::class, 'show'])->name('employees.show');
        Route::put('/{id}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::delete('/{id}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
        Route::get('/export/csv', [EmployeeController::class, 'exportCsv'])->name('employees.export.csv');
    });
    
    Route::prefix('departments')->group(function () {
        Route::get('/', [DepartmentController::class, 'index'])->name('departments.index');
        Route::get('/{id}/employees', [DepartmentController::class, 'employees'])->name('departments.employees');
    });
    
    Route::prefix('statistics')->group(function () {
        Route::get('/dashboard', [EmployeeController::class, 'dashboardStats'])->name('statistics.dashboard');
    });
}); 