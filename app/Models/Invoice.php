<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'po',
        'factory',
        'filename',
        'local_size',
        'remote_size',
        'is_uploaded',
        'is_processed',
        'is_identical_filesize',
        'is_invalid_filename',
        'archive_location',
        'archive_error',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }
}
