<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChangeEvent extends Model
{
    protected $fillable = ['monitored_file_id', 'event_type', 'payload', 'status', 'detected_at', 'processed_at'];

    protected function casts(): array
    {
        return ['payload' => 'array', 'detected_at' => 'datetime', 'processed_at' => 'datetime'];
    }

    public function monitoredFile(): BelongsTo
    {
        return $this->belongsTo(MonitoredFile::class);
    }
}