<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('residents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('rt_id')->constrained('rts')->restrictOnDelete();

            // NIK & KK disimpan terenkripsi; nik_hash dipakai untuk cek duplikasi.
            $table->text('nik');
            $table->string('nik_hash', 64)->unique();
            $table->text('kk_number')->nullable();

            $table->string('full_name');
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender', 1)->nullable();
            $table->string('religion', 20)->nullable();
            $table->string('marital_status', 20)->nullable();
            $table->string('occupation')->nullable();
            $table->string('nationality', 10)->default('WNI');
            $table->string('address')->nullable();
            $table->string('house_number', 10)->nullable();

            $table->string('verification_status', 20)->default('pending')->index();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('verification_note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['rt_id', 'verification_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('residents');
    }
};
