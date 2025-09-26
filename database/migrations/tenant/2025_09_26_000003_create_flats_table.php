<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('house_id')->constrained()->cascadeOnDelete();
            $table->foreignId('floor_id')->constrained()->cascadeOnDelete();
            $table->string('flat_number');
            $table->string('name')->nullable();
            $table->enum('type', ['studio', '1bhk', '2bhk', '3bhk', '4bhk', 'penthouse', 'duplex']);
            $table->integer('bedrooms');
            $table->integer('bathrooms');
            $table->integer('size_sqft')->nullable();
            $table->decimal('rent_amount', 10, 2);
            $table->decimal('security_deposit', 10, 2);
            $table->text('description')->nullable();
            $table->json('amenities')->nullable();
            $table->enum('status', ['available', 'occupied', 'maintenance', 'reserved'])->default('available');
            $table->boolean('is_furnished')->default(false);
            $table->date('available_from')->nullable();
            $table->timestamps();

            $table->unique(['house_id', 'floor_id', 'flat_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flats');
    }
};