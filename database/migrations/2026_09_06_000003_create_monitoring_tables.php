<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitored_files', function (Blueprint $table) {
            $table->id();
            $table->string('path')->unique();
            $table->string('file_type', 32);
            $table->boolean('is_active')->default(true)->index();
            $table->string('last_hash', 64)->nullable();
            $table->timestamp('last_modified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('file_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitored_file_id')->constrained()->cascadeOnDelete();
            $table->string('hash', 64)->index();
            $table->longText('content');
            $table->json('parsed_content');
            $table->timestamps();
            $table->index(['monitored_file_id', 'created_at']);
        });

        Schema::create('change_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitored_file_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type', 64)->index();
            $table->json('payload');
            $table->string('status', 32)->default('pending')->index();
            $table->timestamp('detected_at')->index();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('demo_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->index();
            $table->json('payload');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_webhook_events');
        Schema::dropIfExists('change_events');
        Schema::dropIfExists('file_snapshots');
        Schema::dropIfExists('monitored_files');
    }
};