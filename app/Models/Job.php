<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_payload_error',
        'payload_error_msg',
        'is_upload_error',
        'upload_error_msg',
        'is_archive_error',
        'archive_error_msg',
    ];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
