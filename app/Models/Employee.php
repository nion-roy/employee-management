<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @OA\Schema(
 *   schema="Employee",
 *   type="object",
 *   @OA\Property(property="id", type="string", format="uuid"),
 *   @OA\Property(property="name", type="string"),
 *   @OA\Property(property="email", type="string"),
 *   @OA\Property(property="department_id", type="integer"),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time"),
 *   @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
 * )
 */
class Employee extends Model
{
    use SoftDeletes;

    protected $table = 'employee';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id', 'name', 'email', 'department_id', 'salary', 'joining_date',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function detail()
    {
        return $this->hasOne(EmployeeDetail::class, 'employee_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
