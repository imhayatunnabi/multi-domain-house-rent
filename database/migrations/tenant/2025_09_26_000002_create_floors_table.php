<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('floors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('house_id')->constrained()->cascadeOnDelete();
            $table->integer('floor_number');
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->integer('total_flats')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['house_id', 'floor_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('floors');
    }
};