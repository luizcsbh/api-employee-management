<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Employee",
 *     @OA\Property(property="id", type="integer", example="1"),
 *     @OA\Property(property="company_id", type="integer", example="1"),
 *     @OA\Property(property="name", type="string", example="Jonh Doe"),
 *     @OA\Property(property="cpf", type="integer", example="12345678901"),
 *     @OA\Property(property="email", type="string", example="jonh.doe@email.com"),
 *     @OA\Property(property="position", type="string", example="Desenvolvedor"),
 *     @OA\Property(property="hired_at", type="string", format="date", example="2025-01-15"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-15T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-15T12:00:00Z")
 * )
 */
class Employee extends Model
{
    use HasFactory;

    protected $tables = 'employees';
    protected $fillable = ['company_id', 'name', 'cpf', 'email', 'position', 'hired_at'];
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
