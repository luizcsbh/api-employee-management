<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ImportStatus",
 *     @OA\Property(property="id", type="integer", example="1"),
 *     @OA\Property(property="user_id", type="integer", example="1"),
 *     @OA\Property(property="status", type="string", example="complete"),
 *     @OA\Property(property="file_path", type="string", example="imports/JOGTSfYW9GCbgvtspAnSwYLOmUIHumRUSRsv6Amn.csv"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-15T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-15T12:00:00Z")
 * )
 */
class ImportStatus extends Model
{
    use HasFactory;

    protected $table = 'import_statuses';
    protected $fillable = [
        'user_id',
        'status',
        'file_path',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}