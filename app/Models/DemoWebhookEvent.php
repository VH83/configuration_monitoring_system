<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemoWebhookEvent extends Model
{
    protected $fillable = ['event_id', 'payload'];

    protected function casts(): array
    {
        return ['payload' => 'array'];
    }
}