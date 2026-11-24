<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds the role (admin / officer) and an active flag to the default users table.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('officer');
            $table->boolean('is_active')->default(true);
        });

        // PostgreSQL-level guard so an invalid role can never be stored.
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'officer'))");
    }

    public function down(): void
    {
        // Dropping the column also drops its CHECK constraint in PostgreSQL.
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_active']);
        });
    }
};
