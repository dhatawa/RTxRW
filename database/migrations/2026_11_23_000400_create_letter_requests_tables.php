<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('letter_requests', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->string('reference_number', 30)->unique();
            $table->foreignId('resident_id')->constrained()->restrictOnDelete();
            $table->foreignId('rt_id')->constrained('rts')->restrictOnDelete();
            $table->foreignId('letter_type_id')->constrained()->restrictOnDelete();
            $table->text('purpose');
            $table->json('form_data')->nullable();
            $table->string('status', 30)->default('draft');
            $table->unsignedSmallInteger('revision_count')->default(0);
            $table->text('last_note')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('rt_decided_at')->nullable();
            $table->timestamp('rw_decided_at')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index(['rt_id', 'status']);
            $table->index(['resident_id', 'status']);
        });

        Schema::create('letter_request_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('letter_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('letter_requirement_id')->nullable()->constrained()->nullOnDelete();
            $table->string('original_name');
            $table->string('path');
            $table->string('mime_type', 100);
            $table->unsignedInteger('size');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('approval_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('letter_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_role', 20);
            $table->string('action', 30);
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['letter_request_id', 'created_at']);
        });

        Schema::create('issued_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('letter_request_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('letter_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('letter_number', 60)->unique();
            $table->unsignedInteger('sequence');
            $table->unsignedSmallInteger('year');
            $table->string('verification_code', 20)->unique();
            $table->string('file_path');
            $table->json('snapshot');
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('issued_at');
            $table->timestamp('first_downloaded_at')->nullable();
            $table->timestamps();

            $table->unique(['year', 'sequence']);
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 50)->index();
            $table->nullableMorphs('subject');
            $table->string('description');
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('issued_letters');
        Schema::dropIfExists('approval_histories');
        Schema::dropIfExists('letter_request_documents');
        Schema::dropIfExists('letter_requests');
    }
};
