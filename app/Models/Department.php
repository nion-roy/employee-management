<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *   schema="Department",
 *   type="object",
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="name", type="string"),
 *   @OA\Property(property="description", type="string"),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time"),
 *   @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
 * )
 */
class Department extends Model
{
    use SoftDeletes;

    protected $table = 'department';
    protected $fillable = [
        'name', 'description',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'department_id');
    }
}
