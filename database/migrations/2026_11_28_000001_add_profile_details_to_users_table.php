<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Shared by both roles
            $table->string('contact_number', 30)->nullable();
            $table->string('position', 100)->nullable();   // council position (officer) / title (admin)

            // Officers (students)
            $table->string('student_number', 50)->nullable();
            $table->string('course', 100)->nullable();
            $table->string('year_level', 20)->nullable();
            $table->string('section', 50)->nullable();

            // Admins (teachers / advisers)
            $table->string('department', 100)->nullable();
            $table->json('handled_courses')->nullable();
            $table->json('handled_year_levels')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'contact_number', 'position',
                'student_number', 'course', 'year_level', 'section',
                'department', 'handled_courses', 'handled_year_levels',
            ]);
        });
    }
};