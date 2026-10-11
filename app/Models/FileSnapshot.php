<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileSnapshot extends Model
{
    protected $fillable = ['monitored_file_id', 'hash', 'content', 'parsed_content'];

    protected function casts(): array
    {
        return ['parsed_content' => 'array'];
    }

    public function monitoredFile(): BelongsTo
    {
        return $this->belongsTo(MonitoredFile::class);
    }
}