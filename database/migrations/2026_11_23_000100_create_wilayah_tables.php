<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rws', function (Blueprint $table) {
            $table->id();
            $table->string('number', 3);
            $table->string('kelurahan');
            $table->string('kecamatan');
            $table->string('city');
            $table->string('province');
            $table->string('postal_code', 10)->nullable();
            $table->string('secretariat_address')->nullable();
            $table->foreignId('ketua_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('rts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rw_id')->constrained('rws')->cascadeOnDelete();
            $table->string('number', 3);
            $table->string('area_description')->nullable();
            $table->foreignId('ketua_user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['rw_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rts');
        Schema::dropIfExists('rws');
    }
};
