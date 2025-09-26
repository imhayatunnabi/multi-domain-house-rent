<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone');
            $table->foreignId('flat_id')->nullable()->constrained()->nullOnDelete();
            $table->date('lease_start')->nullable();
            $table->date('lease_end')->nullable();
            $table->decimal('monthly_rent', 10, 2)->nullable();
            $table->decimal('security_deposit_paid', 10, 2)->nullable();
            $table->json('emergency_contact')->nullable();
            $table->json('documents')->nullable();
            $table->enum('status', ['active', 'inactive', 'pending', 'terminated'])->default('pending');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_users');
    }
};