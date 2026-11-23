<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('letter_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            // Siapa yang menandatangani surat: rt, rw, atau rt_rw (RT + mengetahui RW).
            $table->string('signatory', 10)->default('rt_rw');
            $table->unsignedSmallInteger('processing_days')->default(3);
            // Definisi isian tambahan per jenis surat (key, label, type, required, help).
            $table->json('fields')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('letter_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('letter_type_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('is_required')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('letter_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('letter_type_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->string('title');
            $table->text('opening');
            $table->text('body');
            $table->text('closing');
            $table->boolean('is_active')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['letter_type_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letter_templates');
        Schema::dropIfExists('letter_requirements');
        Schema::dropIfExists('letter_types');
    }
};
