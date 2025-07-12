<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *   schema="EmployeeDetail",
 *   type="object",
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="employee_id", type="string", format="uuid"),
 *   @OA\Property(property="designation", type="string"),
 *   @OA\Property(property="salary", type="number", format="float"),
 *   @OA\Property(property="address", type="string"),
 *   @OA\Property(property="joined_date", type="string", format="date"),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class EmployeeDetail extends Model
{
    protected $table = 'employee_detail';
    protected $fillable = [
        'employee_id', 'designation', 'salary', 'address', 'joined_date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
