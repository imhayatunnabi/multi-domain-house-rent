<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->string('email')->unique()->after('name');
            $table->string('phone')->nullable()->after('email');
            $table->text('address')->nullable()->after('phone');
            $table->string('owner_name')->after('address');
            $table->enum('status', ['active', 'suspended', 'inactive'])->default('active')->after('owner_name');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['name', 'email', 'phone', 'address', 'owner_name', 'status']);
        });
    }
};