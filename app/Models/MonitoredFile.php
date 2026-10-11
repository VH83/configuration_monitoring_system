<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonitoredFile extends Model
{
    protected $fillable = ['path', 'file_type', 'is_active', 'last_hash', 'last_modified_at'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'last_modified_at' => 'datetime'];
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(FileSnapshot::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ChangeEvent::class);
    }
}