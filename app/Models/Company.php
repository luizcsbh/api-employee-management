<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Company",
 *     @OA\Property(property="id", type="integer", example="1"),
 *     @OA\Property(property="name", type="string", example="ACME Corporation"),
 *     @OA\Property(property="address", type="integer", example="123 Main St."),
 *     @OA\Property(property="cnpj", type="integer", example="12345678901234"),
 *     @OA\Property(property="opening_date", type="string", format="date", example="2024-06-17"),
 *     @OA\Property(property="email", type="string", example="admin@acme.com"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-15T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-15T12:00:00Z")
 * )
 */
class Company extends Model
{
    use HasFactory;

    protected $tables = 'companies';
    protected $fillable = ['name', 'address','cnpj', 'opening_date', 'email'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
